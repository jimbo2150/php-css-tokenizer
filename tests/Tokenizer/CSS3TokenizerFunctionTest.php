<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerFunctionTest extends TestCase
{
	public function testFunctionTokenAndParenHandling()
	{
		$css = 'width: calc(100% - 5px);';
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$foundFunction = false;
		$foundRightParen = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::FUNCTION === $t->type) {
				$foundFunction = true;
			}
			if (CSS3TokenType::RIGHT_PAREN === $t->type) {
				$foundRightParen = true;
			}
		}

		$this->assertTrue($foundFunction, 'FUNCTION token should be present for calc(');
		$this->assertTrue($foundRightParen, 'RIGHT_PAREN token should be present for )');
	}

	public function testBadUrlWhitespaceProducesBadUrl()
	{
		$css = 'background: url(http ://)'; // whitespace inside unquoted URL -> bad-url
		$tokenizer = new CSS3Tokenizer($css);
		$tokens = $tokenizer->tokenize();

		$foundBadUrl = false;
		foreach ($tokens as $t) {
			if (CSS3TokenType::BAD_URL === $t->type) {
				$foundBadUrl = true;
				break;
			}
		}

		$this->assertTrue($foundBadUrl, 'Bad-url token should be produced for whitespace in raw url');
	}
}
