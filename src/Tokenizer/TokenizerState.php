<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tokenizer;

enum TokenizerState: string
{
	case Data = 'data';
	case String = 'string';
	case Url = 'url';
	case Comment = 'comment';
	case AtRule = 'at-rule';
	case Block = 'block';
	case Paren = 'paren';
	case IdentLike = 'ident-like';
	case Number = 'number';
	case FunctionState = 'function';
	case Tag = 'tag';
	case UrlEscape = 'url-escape';
}
