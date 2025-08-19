<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tokenizer;

final class PreludeAstNode
{
	public function __construct(
		public string $type,
		public string $value = '',
		public int $line = 0,
		public int $column = 0,
		public array $children = [],
	) {
	}
}
