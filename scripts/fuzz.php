<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;

$options = getopt('', ['seed::', 'iterations::', 'maxlen::']);
$seed = isset($options['seed']) ? (int) $options['seed'] : (int) (microtime(true) * 1000) & 0x7FFFFFFF;
$iterations = isset($options['iterations']) ? (int) $options['iterations'] : 1000;
$maxLen = isset($options['maxlen']) ? (int) $options['maxlen'] : 256;

mt_srand($seed);

$alphabet = str_split("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -_(),:;{}[]%#@.+*/\\\"'!~^=<>|");

fwrite(STDOUT, "Fuzz harness seed={$seed} iterations={$iterations} maxlen={$maxLen}\n");

$errors = 0;
for ($i = 0; $i < $iterations; ++$i) {
	$len = mt_rand(1, $maxLen);
	$s = '';
	for ($j = 0; $j < $len; ++$j) {
		$s .= $alphabet[mt_rand(0, count($alphabet) - 1)];
	}

	try {
		$t = new CSS3Tokenizer($s);
		// iterate the stream to detect stalls/crashes
		$count = 0;
		foreach ($t->tokenizeStream() as $tok) {
			++$count;
			if ($count > 10000) {
				fwrite(STDERR, "Too many tokens for input iteration={$i}, len={$len}\n");
				++$errors;
				break;
			}
		}
	} catch (Throwable $e) {
		fwrite(STDERR, "Exception on iteration={$i} seed={$seed} len={$len}: ".$e->getMessage()."\n");
		++$errors;
		if ($errors > 10) {
			fwrite(STDERR, "Too many errors; aborting.\n");
			break;
		}
	}
}

fwrite(STDOUT, "Fuzz run complete seed={$seed} errors={$errors}\n");
exit($errors > 0 ? 1 : 0);
