<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CSS3 Tokenizer.
 */
class CSS3TokenizerTest extends TestCase
{
	private function createTokenizer(string $css): CSS3Tokenizer
	{
		return new CSS3Tokenizer($css);
	}

	public function testBasicTokenization()
	{
		$css = 'color: red; font-size: 16px;';
		$tokenizer = $this->createTokenizer($css);
		$tokens = $tokenizer->tokenize();

		// tokenizer now emits whitespace tokens per CSS Syntax Level 3
		$this->assertCount(12, $tokens);

		$this->assertEquals(CSS3TokenType::PROPERTY, $tokens[0]->type);
		$this->assertEquals('color', $tokens[0]->value);

		$this->assertEquals(CSS3TokenType::COLON, $tokens[1]->type);
		$this->assertEquals(':', $tokens[1]->value);
	}

	public function testNumbers()
	{
		$css = 'width: 100px; height: 50%;';
		$tokenizer = $this->createTokenizer($css);
		$tokens = $tokenizer->tokenize();

		// Find dimension token
		$dimensionToken = null;
		foreach ($tokens as $token) {
			if (CSS3TokenType::DIMENSION === $token->type) {
				$dimensionToken = $token;
				break;
			}
		}

		$this->assertNotNull($dimensionToken);
		$this->assertEquals('100', $dimensionToken->value);
		$this->assertEquals('px', $dimensionToken->unit);
	}

	public function testStrings()
	{
		$css = 'content: "Hello World";';
		$tokenizer = $this->createTokenizer($css);
		$tokens = $tokenizer->tokenize();

		// Find string token
		$stringToken = null;
		foreach ($tokens as $token) {
			if (CSS3TokenType::STRING === $token->type) {
				$stringToken = $token;
				break;
			}
		}

		$this->assertNotNull($stringToken);
		$this->assertEquals('Hello World', $stringToken->value);
	}

	public function testComments()
	{
		$css = '/* This is a comment */ color: blue;';
		$tokenizer = $this->createTokenizer($css);
		$tokens = $tokenizer->tokenize();

		// Comments are skipped; whitespace tokens are emitted
		$this->assertCount(7, $tokens);
	}

	public function testComplexCSS()
	{
		$css = '.class { margin: 10px 20px; color: #ff0000; height: min(calc(100% - 5px), 1000px); }';
		$tokenizer = $this->createTokenizer($css);
		$tokens = $tokenizer->tokenize();

		$this->assertGreaterThan(5, count($tokens));

		// Check for hash token
		$hashToken = null;
		foreach ($tokens as $token) {
			if (CSS3TokenType::HASH === $token->type) {
				$hashToken = $token;
				break;
			}
		}

		$this->assertNotNull($hashToken);
		$this->assertEquals('ff0000', $hashToken->value);
	}

	public function testInvalidCssProperty()
	{
		$css = '.class { margin: 10px; invalidProperty: "test"; }';
		$tokenizer = $this->createTokenizer($css);
		$tokens = $tokenizer->tokenize();

		$propertyToken = null;
		foreach ($tokens as $token) {
			if (CSS3TokenType::PROPERTY === $token->type && 'invalidProperty' === $token->value) {
				$propertyToken = $token;
				break;
			}
		}

		$this->assertNotNull($propertyToken);
	}
}
