<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerSpecExtrasTest extends TestCase
{
	public function testUnicodeRangeToken()
	{
		$css = 'u+00A-00FF {}';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::UNICODE_RANGE === $t->type) {
				$this->assertEquals('00A-00FF', $t->value);
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'unicode-range token should be produced');
	}

	public function testRawUrlToken()
	{
		$css = 'background: url(http://example.com/a.png);';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = null;
		foreach ($tokens as $t) {
			if (CSS3TokenType::URL === $t->type) {
				$found = $t;
				break;
			}
		}

		$this->assertNotNull($found, 'URL token should be produced');
		$this->assertEquals('http://example.com/a.png', $found->value);
	}

	public function testBadUrlWithWhitespaceProducesBadUrl()
	{
		$css = 'background: url(http ://example.com)'; // whitespace inside unquoted URL
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::BAD_URL === $tok->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Whitespace in unquoted URL should produce BAD_URL');
	}

	public function testEscapedParenInsideUrlIsAllowed()
	{
		// url containing escaped ')' should be treated as part of URL
		$css = "background: url(http://example.com/a\)b.png);";
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = null;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::URL === $tok->type) {
				$found = $tok;
				break;
			}
		}

		$this->assertNotNull($found, 'Escaped parenthesis inside URL should produce URL token');
		$this->assertStringContainsString(')', $found->value);
	}
}
