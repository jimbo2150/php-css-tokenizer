<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerBadStringTest extends TestCase
{
	public function testBOMIsStripped()
	{
		$bom = "\xEF\xBB\xBF";
		$css = $bom.'a: b;';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$this->assertNotEmpty($tokens);
		// first token should be a property 'a'
		$this->assertEquals(CSS3TokenType::PROPERTY, $tokens[0]->type);
		$this->assertEquals('a', $tokens[0]->value);
	}

	public function testUnterminatedStringProducesBadString()
	{
		$css = 'content: "unterminated';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::BAD_STRING === $tok->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Unterminated string should produce BAD_STRING token');
	}

	public function testBackslashAtEOFInStringProducesBadString()
	{
		$css = 'content: "escape-at-eof\\';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::BAD_STRING === $tok->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Backslash at EOF in string should produce BAD_STRING');
	}
}
