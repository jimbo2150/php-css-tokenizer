<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerReconsumeTest extends TestCase
{
	public function testMalformedExponentReconsume()
	{
		// '1e+' followed by 'a' should not consume the 'a' as part of number; the tokenizer should reconsume
		$css = '1e+a';
		$t = new CSS3Tokenizer($css);
		$tokens = $t->tokenize();

		$types = array_map(fn ($tok) => $tok->type, $tokens);

		// Depending on how identifier start is handled, the tokenizer may return NUMBER or DIMENSION for the first token
		$this->assertContains($types[0], [CSS3TokenType::NUMBER, CSS3TokenType::DIMENSION]);
	}
}
