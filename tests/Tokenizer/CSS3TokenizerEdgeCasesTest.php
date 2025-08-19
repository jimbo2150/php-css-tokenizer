<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerEdgeCasesTest extends TestCase
{
	public function testUrlToken()
	{
		$css = 'background: url(https://example.com/image.png);';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::URL === $t->type) {
				$this->assertStringContainsString('https://example.com/image.png', $t->value);
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected a URL token');
	}

	public function testBadUrlDueToWhitespace()
	{
		$css = 'background: url(https://example.com/image.png )';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::BAD_URL === $t->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected a BAD_URL token due to whitespace in raw url');
	}

	public function testBadStringNewline()
	{
		$css = "content: \"line1\nline2\";";
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::BAD_STRING === $t->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected a BAD_STRING token for newline inside string');
	}

	public function testUnterminatedUrlBecomesBadUrl()
	{
		$css = 'background: url(http://example.com'; // missing ')'
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::BAD_URL === $t->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected BAD_URL for unterminated url');
	}

	public function testNewlineInUnquotedUrlIsBadUrl()
	{
		$css = "background: url(http://ex\nample.com)";
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$found = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::BAD_URL === $t->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected BAD_URL when newline is present in unquoted url');
	}

	public function testWhitespaceFlagOff()
	{
		$css = 'color: red;';
		$tokenizer = new CSS3Tokenizer($css, false); // do not emit whitespace
		$tokens = $tokenizer->tokenize();

		foreach ($tokens as $t) {
			$this->assertNotEquals(CSS3TokenType::WHITESPACE, $t->type);
		}
	}
}
