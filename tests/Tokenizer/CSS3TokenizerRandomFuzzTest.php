<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;

final class CSS3TokenizerRandomFuzzTest extends TestCase
{
	public function testShortRandomizedFuzzRun(): void
	{
		// Run a small, deterministic fuzz run using the harness for CI speed
		$cmd = 'php '.escapeshellarg(__DIR__.'/../../scripts/fuzz.php').' --seed='.mt_rand().' --iterations=50 --maxlen=64';
		exec($cmd, $out, $rc);
		$this->assertSame(0, $rc, 'Randomized fuzz harness failed: '.implode("\n", $out));
	}
}
