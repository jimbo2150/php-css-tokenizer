<?php

require __DIR__.'/../vendor/autoload.php';

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;

function randomCss(int $len = 200): string
{
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789(){}[];:@,.-# \t\n\r'\"\\/ %";
	$s = '';
	for ($i = 0; $i < $len; ++$i) {
		$s .= $chars[random_int(0, strlen($chars) - 1)];
	}

	return $s;
}

$iterations = (int) ($argv[1] ?? 1000);
$maxLen = (int) ($argv[2] ?? 256);

echo "Running fuzz recursion harness: iterations=$iterations maxLen=$maxLen\n";

for ($i = 0; $i < $iterations; ++$i) {
	$len = random_int(1, $maxLen);
	$s = randomCss($len);
	try {
		$t = new CSS3Tokenizer($s);
		// use tokenize() which pulls stream
		$tokens = $t->tokenize();
	} catch (Throwable $e) {
		echo "Fuzz iteration $i FAILED: ", $e->getMessage(), "\n";
		echo "INPUT:\n", $s, "\n";
		echo $e->getTraceAsString(), "\n";
		exit(1);
	}
}

echo "Fuzz run completed OK\n";
