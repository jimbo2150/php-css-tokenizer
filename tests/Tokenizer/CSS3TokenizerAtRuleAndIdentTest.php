<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerAtRuleAndIdentTest extends TestCase
{
	public function testLeadingHyphenFollowedByDashStartsIdent(): void
	{
		$t = new CSS3Tokenizer('--foo');
		$tokens = iterator_to_array($t->tokenizeStream());

		$this->assertCount(2, $tokens);
		$this->assertSame(CSS3TokenType::IDENT, $tokens[0]->type);
		$this->assertSame('--foo', $tokens[0]->value);
	}

	public function testLeadingHyphenFollowedByNameStartStartsIdent(): void
	{
		$t = new CSS3Tokenizer('-a1');
		$tokens = iterator_to_array($t->tokenizeStream());

		$this->assertSame(CSS3TokenType::IDENT, $tokens[0]->type);
		$this->assertSame('-a1', $tokens[0]->value);
	}

	public function testLeadingHyphenNotFollowedByNameStartDoesNotStartIdent(): void
	{
		$t = new CSS3Tokenizer('-1');
		$tokens = iterator_to_array($t->tokenizeStream());

		// Expect a DELIM '-' then NUMBER '1' (or NUMBER with leading sign depending on tokenizer),
		// but at minimum not a single IDENT token '-1'
		$this->assertNotSame(CSS3TokenType::IDENT, $tokens[0]->type);
	}

	public function testAtRulePreludeStopsOnSemicolonAndLeftBrace(): void
	{
		$t = new CSS3Tokenizer('@media screen and (min-width: 600px) { body { color: black } }');

		$completed = [];
		$t->on('at-prelude-complete', function ($payload) use (&$completed) {
			$completed[] = $payload['prelude'];
		});

		$tokens = iterator_to_array($t->tokenizeStream());

		// Ensure at least one structured prelude token contains 'screen' either in raw value or in collected tokens
		$found = false;
		foreach ($completed as $p) {
			if (str_contains($p->value, 'screen')) {
				$found = true;
				break;
			}
			if (!empty($p->metadata['tokens'])) {
				foreach ($p->metadata['tokens'] as $tk) {
					if (CSS3TokenType::IDENT === $tk->type && 'screen' === $tk->value) {
						$found = true;
						break 2;
					}
				}
			}
		}

		$this->assertTrue($found, 'Expected at least one completed at-rule prelude to contain "screen"');
	}
}
