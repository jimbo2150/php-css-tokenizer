<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTypedOm\Process;

use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSMathDifference;
use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSMathDivision;
use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSMathProduct;
use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSMathSum;
use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSNumericValue;
use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSUnitValue;

class CSSCalcParser
{
	private const OPERATORS = [
		'+' => ['precedence' => 1, 'associativity' => 'left'],
		'-' => ['precedence' => 1, 'associativity' => 'left'],
		'*' => ['precedence' => 2, 'associativity' => 'left'],
		'/' => ['precedence' => 2, 'associativity' => 'left'],
	];

	public static function parse(string $calcString): CSSNumericValue
	{
		// Remove 'calc()' wrapper
		$expression = trim(substr($calcString, 5, -1));

		// Tokenization
		preg_match_all(
			'/([+\-*\/()])|([0-9]+\.?[0-9]*(?:[a-zA-Z%]+)?)|([0-9]*\.?[0-9]+(?:[a-zA-Z%]+)?)/i',
			$expression,
			$matches,
			PREG_SET_ORDER
		);

		$tokens = [];
		foreach ($matches as $match) {
			if (!empty($match[1])) { // Operator or parenthesis
				$tokens[] = $match[1];
			} elseif (!empty($match[2])) { // Number with optional unit
				$tokens[] = $match[2];
			} elseif (!empty($match[3])) { // Number with optional unit (for leading dot numbers)
				$tokens[] = $match[3];
			}
		}

		// Shunting-yard algorithm
		$outputQueue = [];
		$operatorStack = [];

		foreach ($tokens as $token) {
			if (is_numeric($token) || preg_match('/^[0-9]+\.?[0-9]*(?:[a-zA-Z%]+)?$/i', $token)) { // Operand (number with unit)
				$outputQueue[] = $token;
			} elseif (isset(self::OPERATORS[$token])) { // Operator
				$op1 = $token;
				while (
					!empty($operatorStack) &&
					($op2 = end($operatorStack)) &&
					isset(self::OPERATORS[$op2]) &&
					(
						('left' === self::OPERATORS[$op1]['associativity'] && self::OPERATORS[$op1]['precedence'] <= self::OPERATORS[$op2]['precedence']) ||
						('right' === self::OPERATORS[$op1]['associativity'] && self::OPERATORS[$op1]['precedence'] < self::OPERATORS[$op2]['precedence'])
					)
				) {
					$outputQueue[] = array_pop($operatorStack);
				}
				$operatorStack[] = $op1;
			} elseif ('(' === $token) {
				$operatorStack[] = $token;
			} elseif (')' === $token) {
				while (!empty($operatorStack) && '(' !== end($operatorStack)) {
					$outputQueue[] = array_pop($operatorStack);
				}
				if (empty($operatorStack)) {
					throw new \InvalidArgumentException('Mismatched parentheses.');
				}
				array_pop($operatorStack); // Pop the '('
			} else {
				throw new \InvalidArgumentException('Unknown token: '.$token);
			}
		}

		while (!empty($operatorStack)) {
			$op = array_pop($operatorStack);
			if ('(' === $op || ')' === $op) {
				throw new \InvalidArgumentException('Mismatched parentheses.');
			}
			$outputQueue[] = $op;
		}

		// Evaluation of RPN
		$valueStack = [];
		foreach ($outputQueue as $token) {
			if (isset(self::OPERATORS[$token])) { // Operator
				if (count($valueStack) < 2) {
					throw new \InvalidArgumentException('Insufficient operands for operator: '.$token);
				}
				$operand2 = array_pop($valueStack);
				$operand1 = array_pop($valueStack);

				switch ($token) {
					case '+':
						$valueStack[] = new CSSMathSum($operand1, $operand2);
						break;
					case '-':
						$valueStack[] = new CSSMathDifference($operand1, $operand2);
						break;
					case '*':
						$valueStack[] = new CSSMathProduct($operand1, $operand2);
						break;
					case '/':
						$valueStack[] = new CSSMathDivision($operand1, $operand2);
						break;
				}
			} else { // Operand
				$valueStack[] = CSSUnitValue::parse($token); // Assuming CSSUnitValue can parse basic numeric strings
			}
		}

		if (1 !== count($valueStack)) {
			throw new \InvalidArgumentException('Invalid expression.');
		}

		return $valueStack[0];
	}
}
