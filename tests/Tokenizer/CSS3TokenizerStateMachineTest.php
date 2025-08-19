<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerStateMachineTest extends TestCase
{
	public function testIdentFunctionAndParenState(): void
	{
		$css = 'calc(1 + 2)';
		$t = new CSS3Tokenizer($css);

		$entered = [];
		$exited = [];

		$t->on('state-enter', function ($e) use (&$entered) { $entered[] = $e['state']; });
		$t->on('state-exit', function ($e) use (&$exited) { $exited[] = $e['state']; });

		$tokens = $t->tokenize();

		$this->assertContains('paren', $entered, 'paren state should be entered when FUNCTION is encountered');
		$this->assertContains('paren', $exited, 'paren state should be exited when ) is consumed');

		// ensure FUNCTION token exists
		$foundFunction = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::FUNCTION === $tok->type && 'calc' === $tok->value) {
				$foundFunction = true;
				break;
			}
		}

		$this->assertTrue($foundFunction, 'FUNCTION token for calc should be present');
	}

	public function testNumberToDimensionTransition(): void
	{
		$css = 'width: 100px;';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$dim = null;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::DIMENSION === $tok->type) {
				$dim = $tok;
				break;
			}
		}

		$this->assertNotNull($dim);
		$this->assertEquals('100', $dim->value);
		$this->assertEquals('px', $dim->unit);
	}
}
