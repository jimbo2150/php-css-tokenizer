<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerNestedAndFuzzTest extends TestCase
{
	public function testNestedBlocksAndAtRules()
	{
		$css = '@media screen and (min-width: 900px) { .foo { color: red; @supports (display: grid) { .bar { display: grid; } } } }';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		// Ensure we have at-keyword tokens and braces
		$hasAt = false;
		$leftBraces = 0;
		foreach ($tokens as $t) {
			if (CSS3TokenType::AT_KEYWORD === $t->type) {
				$hasAt = true;
			}
			if (CSS3TokenType::LEFT_BRACE === $t->type) {
				++$leftBraces;
			}
		}

		$this->assertTrue($hasAt, 'Expected at-keyword tokens');
		$this->assertGreaterThanOrEqual(3, $leftBraces, 'Expected multiple nested left braces');
	}

	public function testSimpleFuzzing()
	{
		// Simple fuzzing: generate many small random snippets and ensure tokenizer doesn't throw
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789:{}()@#\"' .,-_;\/\n";

		// Reduced iterations/length to avoid intermittent memory spikes in CI
		for ($i = 0; $i < 100; ++$i) {
			$len = rand(1, 40);
			$s = '';
			for ($j = 0; $j < $len; ++$j) {
				$s .= $chars[rand(0, strlen($chars) - 1)];
			}

			$t = new CSS3Tokenizer($s);
			$tokens = $t->tokenize();

			$this->assertIsArray($tokens);
			// basic sanity: last token should be EOF
			$this->assertNotEmpty($tokens);
			$last = end($tokens);
			$this->assertEquals(CSS3TokenType::EOF, $last->type);

			// free token memory immediately to avoid accumulating large arrays
			unset($tokens, $last);
			gc_collect_cycles();
		}
	}

	public function testNestedFunctionsAndCommas()
	{
		$css = 'background: linear-gradient(45deg, rgba(0,0,0,0.5), url(http://example.com/a.png));';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$foundNestedFunction = false;
		$foundComma = false;
		$foundRightParen = 0;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::FUNCTION === $tok->type && 'rgba' === $tok->value) {
				$foundNestedFunction = true;
			}
			if (CSS3TokenType::COMMA === $tok->type) {
				$foundComma = true;
			}
			if (CSS3TokenType::RIGHT_PAREN === $tok->type) {
				++$foundRightParen;
			}
		}

		$this->assertTrue($foundNestedFunction, 'Nested function token (rgba) should be present');
		$this->assertTrue($foundComma, 'Comma should be present separating arguments');
		$this->assertGreaterThanOrEqual(2, $foundRightParen, 'At least two RIGHT_PAREN tokens expected');
	}

	public function testMultiLayerWithMediaQueries()
	{
		$css = ':root{--test-one:green;} html{ & .test { @media (min-width:768px) { & { width: min(1000px, 100%); & .test2 { height: calc(10% - 10px); } } } } }';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		// Ensure we have at-keyword tokens and braces
		$hasAt = false;
		$leftBraces = 0;
		foreach ($tokens as $t) {
			if (CSS3TokenType::AT_KEYWORD === $t->type) {
				$hasAt = true;
			}
			if (CSS3TokenType::LEFT_BRACE === $t->type) {
				++$leftBraces;
			}
		}

		$this->assertTrue($hasAt, 'Expected at-keyword tokens');
		$this->assertGreaterThanOrEqual(5, $leftBraces, 'Expected multiple nested left braces');
	}
}
