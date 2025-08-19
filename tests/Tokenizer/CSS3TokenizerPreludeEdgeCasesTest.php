<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerPreludeEdgeCasesTest extends TestCase
{
	public function testBadUrlInPreludeProducesBadUrlAst(): void
	{
		// Unquoted url with whitespace inside should become BAD_URL when inside prelude
		$css = '@import url(example com); body {}';
		$t = new CSS3Tokenizer($css);
		foreach ($t->tokenizeStream() as $tk) {
			// consume
		}

		$all = $t->getAllCompletedPreludes();
		$this->assertNotEmpty($all);

		$found = false;
		foreach ($all as $entry) {
			foreach ($entry['tokens'] as $tk) {
				if (CSS3TokenType::BAD_URL === $tk->type) {
					$found = true;
					break 2;
				}
			}
		}

		$this->assertTrue($found, 'Expected a BAD_URL token in prelude tokens');
	}

	public function testBadStringInPreludeProducesBadStringAst(): void
	{
		// Unclosed string in an at-rule prelude should produce a BAD_STRING token among emitted tokens
		$css = '@rule "unfinished { }';
		$t = new CSS3Tokenizer($css);

		$found = false;
		foreach ($t->tokenizeStream() as $tk) {
			if (CSS3TokenType::BAD_STRING === $tk->type) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Expected a BAD_STRING token to be emitted');
	}

	public function testStreamPreludeAstsLiveYieldsCompletedAsts(): void
	{
		$css = '@one a { } @two b { }';
		$t = new CSS3Tokenizer($css);

		// Fully tokenize first so completed preludes are available to the live stream
		foreach ($t->tokenizeStream() as $tk) {
			// consume
		}

		$astList = [];
		foreach ($t->streamPreludeAstsLive() as $ast) {
			$astList[] = $ast;
			// stop after we have collected both preludes
			if (count($astList) >= 2) {
				break;
			}
		}

		$this->assertCount(2, $astList);
	}
}
