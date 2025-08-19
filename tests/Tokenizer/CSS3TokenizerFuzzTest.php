<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerFuzzTest extends TestCase
{
	public function testNoEmptyIdentFloodForFuzzString()
	{
		$fuzz = "input: [0ek1gm_p0t62grq/x:/8f.04(p'/h4,o6)jip,8'rb2ym(0-tro\\yk0\n2 d8;'g#h, 95nevi;]";
		$tokenizer = new CSS3Tokenizer($fuzz);
		$tokens = $tokenizer->tokenize();

		$emptyIdentCount = 0;
		foreach ($tokens as $token) {
			if (CSS3TokenType::IDENT === $token->type && 0 === strlen((string) $token->value)) {
				++$emptyIdentCount;
			}
		}

		$this->assertLessThan(2, $emptyIdentCount, 'Too many empty IDENT tokens emitted');
		$this->assertNotEmpty($tokens, 'Tokenizer should produce tokens for the fuzz input');
	}
}
