<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use PHPUnit\Framework\TestCase;

final class CSS3TokenizerPreludeAstTest extends TestCase
{
	public function testNestedFunctionChildrenInPrelude(): void
	{
		// Example: @complex calc(min(100%, var(--x))) { }
		$css = '@complex calc(min(100%, var(--x))) { }';
		$t = new CSS3Tokenizer($css);

		// consume all tokens to complete preludes
		foreach ($t->tokenizeStream() as $tk) {
			// no-op
		}

		$all = $t->getAllCompletedPreludes();
		$this->assertNotEmpty($all, 'Expected at least one completed prelude');

		// find the AST entry that contains a function 'calc'
		$found = false;
		foreach ($all as $entry) {
			$ast = $entry['ast'];
			foreach ($ast as $node) {
				if ('function' === $node->type && str_contains($node->value, 'calc')) {
					// calc should have a child function 'min'
					$this->assertNotEmpty($node->children, 'calc should have children');
					$minFound = false;
					foreach ($node->children as $child) {
						if ('function' === $child->type && str_contains($child->value, 'min')) {
							$minFound = true;
							// min should contain a function 'var' in its children
							$varFound = false;
							foreach ($child->children as $c2) {
								if ('function' === $c2->type && str_contains($c2->value, 'var')) {
									$varFound = true;
									break;
								}
							}
							$this->assertTrue($varFound, 'min should contain var() as nested function');
						}
					}

					$this->assertTrue($minFound, 'calc should contain min() as child function');
					$found = true;
					break 2;
				}
			}
		}

		$this->assertTrue($found, 'Expected to find calc() function in prelude AST');
	}
}
