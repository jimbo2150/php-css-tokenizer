<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Token;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerEscapeHeavyTest extends TestCase
{
	public function testHexEscapeInIdentifier()
	{
		// "id\41 {}" => backslash+41 -> 'A'
		$css = 'id\\41 {}';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::IDENT === $tok->type && 'idA' === $tok->value) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Hex escape in identifier should decode to character');
	}

	public function testBackslashNewlineLineContinuationInIdentifier()
	{
		// backslash + newline should be removed producing 'myident'
		$css = "my\\\nident {}";
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::IDENT === $tok->type && 'myident' === $tok->value) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Backslash-newline should produce line continuation in identifier');
	}

	public function testEscapedLeadingDigitInIdentifier()
	{
		// "\\31 a {}" -> \31 -> '1' then 'a' follows, so identifier becomes '1a'
		$css = '\\31 a {}';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::IDENT === $tok->type && '1a' === $tok->value) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Escaped leading digit should be part of identifier');
	}

	public function testLongEscapedIdentifierTruncation()
	{
		$old = CSS3Token::$maxTokenLength;
		CSS3Token::$maxTokenLength = 5;

		// create a long identifier using repeated hex escapes (\61 -> 'a')
		$parts = array_fill(0, 50, '\\61');
		$css = implode('', $parts).' {}';

		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$ident = null;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::IDENT === $tok->type) {
				$ident = $tok;
				break;
			}
		}

		$this->assertNotNull($ident, 'Expected an identifier token');
		$this->assertArrayHasKey('truncated', $ident->metadata, 'Identifier should be marked truncated');
		$this->assertLessThanOrEqual(5, strlen($ident->value), 'Identifier value should be truncated to maxTokenLength');

		CSS3Token::$maxTokenLength = $old;
	}
}
