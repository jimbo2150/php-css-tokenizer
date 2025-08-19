<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerEscapeTest extends TestCase
{
	public function testUrlHexEscapeDecodes(): void
	{
		// include a whitespace after the hex escape to terminate it per spec
		$css = 'background: url(foo\\20 bar);';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$found = false;
		foreach ($tokens as $tok) {
			if (CSS3TokenType::URL === $tok->type) {
				$this->assertEquals('foo bar', $tok->value);
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected URL token with decoded escape');
	}
}
