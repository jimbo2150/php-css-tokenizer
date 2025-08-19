<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Token;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerUnicodeAndNormalizeTest extends TestCase
{
	public function testUnicodeRange()
	{
		$css = 'u+00A0-00FF';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::UNICODE_RANGE === $tok->type) {
				$this->assertStringContainsString('00A0-00FF', $tok->value);
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected UNICODE_RANGE token');
	}

	public function testNormalizationFlag()
	{
		// enable normalization globally on token class
		CSS3Token::$normalize = true;

		$css = 'width: 16px; url(  https://example.com/a.png  ); u+abcd';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$hasNormalized = false;
		foreach ($tokens as $tok) {
			if (!empty($tok->metadata['normalized'])) {
				$hasNormalized = true;
				break;
			}
		}

		CSS3Token::$normalize = false;

		$this->assertTrue($hasNormalized, 'Expected at least one token to have normalized metadata');
	}
}
