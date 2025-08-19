<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerNumberEdgeTest extends TestCase
{
	public function testPlusMinusDelimVsNumber(): void
	{
		$cases = [
			'+5' => CSS3TokenType::NUMBER,
			'-.5' => CSS3TokenType::NUMBER,
			'+.' => CSS3TokenType::DELIM,
			'+a' => CSS3TokenType::DELIM,
		];

		foreach ($cases as $input => $expectedType) {
			$t = new CSS3Tokenizer($input);
			$tokens = $t->tokenize();
			$this->assertGreaterThan(0, count($tokens));
			$this->assertEquals($expectedType, $tokens[0]->type, "Input: $input");
		}
	}

	public function testExponentParsing(): void
	{
		$t = new CSS3Tokenizer('1e3 2E+2 3e-1');
		$tokens = $t->tokenize();

		$nums = [];
		foreach ($tokens as $tok) {
			if (CSS3TokenType::NUMBER === $tok->type) {
				$nums[] = $tok->value;
			}
		}

		$this->assertContains('1000', $nums);
		$this->assertContains('200', $nums);
		$this->assertContains('0.3', $nums);
	}

	public function testDotLeadingNumber(): void
	{
		$t = new CSS3Tokenizer('.5 . .5a');
		$tokens = $t->tokenize();

		$foundNumerics = 0;
		foreach ($tokens as $tok) {
			if (in_array($tok->type, [CSS3TokenType::NUMBER, CSS3TokenType::DIMENSION, CSS3TokenType::PERCENTAGE], true)) {
				++$foundNumerics;
			}
		}

		$this->assertGreaterThanOrEqual(2, $foundNumerics);
	}
}
