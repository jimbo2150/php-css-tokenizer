<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use PHPUnit\Framework\TestCase;

/**
 * Event API tests for the tokenizer.
 */
final class CSS3TokenizerEventsTest extends TestCase
{
	public function testStateEnterExitAndInvalidPropertyEvents(): void
	{
		$css = '@media (min-width: 30px) { .x { color: nonexistent-prop: 1; } }';
		// Provide a DB path that is absent so invalid-property will be emitted for unknown props
		// strictValidation=true so a missing DB causes properties to be considered invalid
		$tokenizer = new CSS3Tokenizer($css, true, __DIR__.'/../../dist/does-not-exist.sqlite', true);

		$states = [];
		$invalid = [];

		$tokenizer->on('state-enter', function ($e) use (&$states) {
			$states[] = 'enter:'.$e['state'];
		});
		$tokenizer->on('state-exit', function ($e) use (&$states) {
			$states[] = 'exit:'.$e['state'];
		});
		$tokenizer->on('invalid-property', function ($e) use (&$invalid) {
			$invalid[] = $e['name'];
		});

		foreach ($tokenizer->tokenize() as $token) {
			// iterate to trigger events
		}

		$this->assertContains('enter:paren', $states, 'FUNCTION or paren state should be entered');
		$this->assertContains('exit:paren', $states, 'paren state should be exited when ) is consumed');
		$this->assertNotEmpty($invalid, 'invalid-property should be emitted for unknown property');
		$this->assertContains('nonexistent-prop', $invalid);
	}
}
