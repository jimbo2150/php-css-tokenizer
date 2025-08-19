# PHP CSS Tokenizer
## A CSS parser written in PHP.

## THIS IS A WORK IN PROGRESS (WIP)

## CI and Tests

A GitHub Actions workflow (`.github/workflows/phpunit.yml`) runs PHPUnit on push and pull requests using PHP 8.4. It installs dependencies and runs the test suite.

Run tests locally:

```bash
composer install
./vendor/bin/phpunit
```

## Fuzzing

A deterministic fuzz harness is provided at `scripts/fuzz.php` to exercise tokenizer edge-cases. It is suitable for local use and debugging.

Usage examples:

```bash
# Run with a fixed seed and 500 iterations
php scripts/fuzz.php --seed=12345 --iterations=500 --maxlen=256

# For CI-friendly quick runs, use the PHPUnit wrapper test which executes a short fuzz run
./vendor/bin/phpunit --filter RandomFuzzTest
```

Notes:

- The harness prints the seed so runs can be reproduced.
- For long fuzz runs run the script directly; avoid running large fuzzes inside PHPUnit.

## Listening for at-rule prelude events

The tokenizer emits a few events useful for streaming parsers:

- `at-prelude-start` — when an at-rule prelude begins; payload: ['name'=>string,'start'=>int,'line'=>int,'column'=>int]
- `at-prelude` — emitted for each token produced while inside the prelude; payload: the token object
- `at-prelude-complete` — emitted when the prelude ends (on `;` or `{`); payload: ['prelude' => AT_RULE_PRELUDE token, 'terminator' => token]

Example listener:

```php
require __DIR__ . '/vendor/autoload.php';

use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTokenizer\Tokenizer\CSS3TokenType;

$t = new CSS3Tokenizer('@media screen and (min-width: 600px) { body {} }');

$t->on('at-prelude-start', function($info) {
	echo "At-rule prelude started: " . $info['name'] . "\n";
});

$t->on('at-prelude', function($token) {
	// streaming inspection of tokens in the prelude
	echo "Prelude token: " . $token->type->value . " => " . ($token->representation ?? $token->value) . "\n";
});

$t->on('at-prelude-complete', function($payload) {
	$prelude = $payload['prelude'];
	echo "Prelude complete: raw='" . $prelude->value . "'\n";
	if (!empty($prelude->metadata['tokens'])) {
		echo "Collected tokens:\n";
		foreach ($prelude->metadata['tokens'] as $tk) {
			echo " - " . $tk->type->value . " => " . ($tk->representation ?? $tk->value) . "\n";
		}
	}
});

foreach ($t->tokenizeStream() as $tk) {
	// normal token stream processing
}
```

### Programmatic prelude access

You can also retrieve the last completed prelude programmatically and convert its token list to a small AST:

```php
// run tokenization
foreach ($t->tokenizeStream() as $tk) {
	// consume stream
}

$info = $t->getLastAtPrelude();
if ($info !== null) {
	echo "Last prelude raw: " . $info['raw'] . "\n";
	$ast = $t->preludeTokensToAst($info['tokens']);
	var_export($ast);
}
```

