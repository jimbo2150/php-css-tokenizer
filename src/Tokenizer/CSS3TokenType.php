<?php

declare(strict_types=1);

namespace Jimbo2150\PhpCssTokenizer\Tokenizer;

/**
 * CSS3 Token Types for the tokenizer
 * Based on CSS Syntax Module Level 3 specification.
 */
enum CSS3TokenType: string
{
	// Identifiers and At-rules
	case IDENT = 'ident';
	case AT_KEYWORD = 'at-keyword';
	case HASH = 'hash';
	case PROPERTY = 'property';
	case AT_RULE_PRELUDE = 'at-rule-prelude';

	// Strings and URLs
	case STRING = 'string';
	case URL = 'url';
	case BAD_URL = 'bad-url';
	case BAD_STRING = 'bad-string';

	// Numbers and dimensions
	case NUMBER = 'number';
	case PERCENTAGE = 'percentage';
	case DIMENSION = 'dimension';

	// Delimiters and operators
	case DELIM = 'delim';
	case COMMA = 'comma';
	case COLON = 'colon';
	case SEMICOLON = 'semicolon';

	// Brackets and parentheses
	case LEFT_PAREN = '(';
	case RIGHT_PAREN = ')';
	case LEFT_BRACKET = '[';
	case RIGHT_BRACKET = ']';
	case LEFT_BRACE = '{';
	case RIGHT_BRACE = '}';

	// Whitespace and comments
	case WHITESPACE = 'whitespace';
	case COMMENT = 'comment';

	// Functions
	case FUNCTION = 'function';

	// Special tokens
	case EOF = 'eof';
	case CDO = 'cdo'; // <!--
	case CDC = 'cdc'; // -->
	case UNICODE_RANGE = 'unicode-range';

	// Match operators
	case INCLUDE_MATCH = '~='; // ~=
	case DASH_MATCH = '|='; // |=
	case PREFIX_MATCH = '^='; // ^=
	case SUFFIX_MATCH = '$='; // $=
	case SUBSTRING_MATCH = '*='; // *=

	// Column token
	case COLUMN = '||'; // ||
}
