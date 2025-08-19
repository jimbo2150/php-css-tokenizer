<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

class CSS3TokenizerExtendedFuzzTest extends TestCase
{
	/**
	 * Deterministic fuzz inputs that previously triggered issues.
	 */
	public function fuzzProvider(): array
	{
		return [
			["input: [0ek1gm_p0t62grq/x:/8f.04(p'/h4,o6)jip,8'rb2ym(0-tro\\yk0\n2 d8;'g#h, 95nevi;]"],
			[str_repeat('a', 5000)], // long identifier to test truncation behaviour
			['calc(1 + 2)'],
			['url(http ://example.com)'],
			['u+00A-00FF {}'],
		];
	}

	public function testTokenizerHandlesFuzzInputs(): void
	{
		foreach ($this->fuzzProvider() as [$input]) {
			$t = new CSS3Tokenizer($input);
			$tokens = $t->tokenize();

			$this->assertNotEmpty($tokens, 'Tokenizer should produce tokens for fuzz input');

			$emptyIdentCount = 0;
			foreach ($tokens as $tok) {
				if (CSS3TokenType::IDENT === $tok->type && 0 === strlen((string) $tok->value)) {
					++$emptyIdentCount;
				}
			}

			// Guard against repeated empty IDENT emissions (should be rare/none)
			$this->assertLessThan(3, $emptyIdentCount, 'Too many empty IDENT tokens emitted for fuzz input');
		}
	}
}
