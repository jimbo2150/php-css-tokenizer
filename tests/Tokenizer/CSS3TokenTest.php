<?php

declare(strict_types=1);

namespace Tests\Tokenizer;

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Token;
use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3TokenType;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CSS3Token class.
 */
class CSS3TokenTest extends TestCase
{
    public function testConstructor()
    {
        $token = new CSS3Token(CSS3TokenType::IDENT, 'test', 'px', '100px', 5, 10, ['meta' => 'data']);
        
        $this->assertSame(CSS3TokenType::IDENT, $token->type);
        $this->assertSame('test', $token->value);
        $this->assertSame('px', $token->unit);
        $this->assertSame('100px', $token->representation);
        $this->assertSame(5, $token->line);
        $this->assertSame(10, $token->column);
        $this->assertSame(['meta' => 'data'], $token->metadata);
    }

    public function testIsNumeric()
    {
        $numberToken = CSS3Token::number(42.5, '42.5');
        $this->assertTrue($numberToken->isNumeric());
        
        $dimensionToken = CSS3Token::dimension(100, 'px', '100px');
        $this->assertTrue($dimensionToken->isNumeric());
        
        $percentageToken = CSS3Token::percentage(50, '50%');
        $this->assertTrue($percentageToken->isNumeric());
        
        $identToken = CSS3Token::ident('test');
        $this->assertFalse($identToken->isNumeric());
    }

    public function testIsWhitespace()
    {
        $whitespaceToken = CSS3Token::whitespace(' ');
        $this->assertTrue($whitespaceToken->isWhitespace());
        
        $identToken = CSS3Token::ident('test');
        $this->assertFalse($identToken->isWhitespace());
    }

    public function testIsComment()
    {
        $commentToken = CSS3Token::comment('/* test */');
        $this->assertTrue($commentToken->isComment());
        
        $identToken = CSS3Token::ident('test');
        $this->assertFalse($identToken->isComment());
    }

    public function testIsIdentifier()
    {
        $identToken = CSS3Token::ident('test');
        $this->assertTrue($identToken->isIdentifier());
        
        $propertyToken = CSS3Token::property('color');
        $this->assertTrue($propertyToken->isIdentifier());
        
        $numberToken = CSS3Token::number(42, '42');
        $this->assertFalse($numberToken->isIdentifier());
    }

    public function testIsString()
    {
        $stringToken = CSS3Token::string('hello', '"hello"');
        $this->assertTrue($stringToken->isString());
        
        $badStringToken = CSS3Token::badString('hello');
        $this->assertTrue($badStringToken->isString());
        
        $identToken = CSS3Token::ident('test');
        $this->assertFalse($identToken->isString());
    }

    public function testGetNumericValue()
    {
        $numberToken = CSS3Token::number(42.5, '42.5');
        $this->assertSame(42.5, $numberToken->getNumericValue());
        
        $dimensionToken = CSS3Token::dimension(100, 'px', '100px');
        $this->assertSame(100.0, $dimensionToken->getNumericValue());
        
        $percentageToken = CSS3Token::percentage(50, '50%');
        $this->assertSame(50.0, $percentageToken->getNumericValue());
        
        $identToken = CSS3Token::ident('test');
        $this->assertNull($identToken->getNumericValue());
    }

    public function testGetUnit()
    {
        $dimensionToken = CSS3Token::dimension(100, 'px', '100px');
        $this->assertSame('px', $dimensionToken->getUnit());
        
        $percentageToken = CSS3Token::percentage(50, '50%');
        $this->assertSame('%', $percentageToken->getUnit());
        
        $numberToken = CSS3Token::number(42, '42');
        $this->assertNull($numberToken->getUnit());
        
        $identToken = CSS3Token::ident('test');
        $this->assertNull($identToken->getUnit());
    }

    public function testToString()
    {
        $token = new CSS3Token(CSS3TokenType::IDENT, 'test');
        $this->assertSame('CSS3Token{IDENT: "test"}', (string) $token);
        
        $tokenWithUnit = new CSS3Token(CSS3TokenType::DIMENSION, '100', 'px');
        $this->assertSame('CSS3Token{DIMENSION: "100px"}', (string) $tokenWithUnit);
    }

    public function testStaticFactoryMethods()
    {
        $whitespace = CSS3Token::whitespace('  ', 1, 1);
        $this->assertSame(CSS3TokenType::WHITESPACE, $whitespace->type);
        $this->assertSame('  ', $whitespace->value);
        
        $ident = CSS3Token::ident('my-ident', 2, 5);
        $this->assertSame(CSS3TokenType::IDENT, $ident->type);
        $this->assertSame('my-ident', $ident->value);
        $this->assertSame(2, $ident->line);
        $this->assertSame(5, $ident->column);
        
        $property = CSS3Token::property('color', 1, 1);
        $this->assertSame(CSS3TokenType::PROPERTY, $property->type);
        $this->assertSame('color', $property->value);
        
        $number = CSS3Token::number(42.5, '42.5', 3, 10);
        $this->assertSame(CSS3TokenType::NUMBER, $number->type);
        $this->assertSame('42.5', $number->value);
        $this->assertSame(42.5, $number->getNumericValue());
        
        $dimension = CSS3Token::dimension(100, 'px', '100px', 1, 1);
        $this->assertSame(CSS3TokenType::DIMENSION, $dimension->type);
        $this->assertSame('100', $dimension->value);
        $this->assertSame('px', $dimension->unit);
        
        $percentage = CSS3Token::percentage(75, '75%', 1, 1);
        $this->assertSame(CSS3TokenType::PERCENTAGE, $percentage->type);
        $this->assertSame('75', $percentage->value);
        $this->assertSame('%', $percentage->unit);
        
        $string = CSS3Token::string('hello world', '"hello world"', 1, 1);
        $this->assertSame(CSS3TokenType::STRING, $string->type);
        $this->assertSame('hello world', $string->value);
        
        $badString = CSS3Token::badString('incomplete', 1, 1);
        $this->assertSame(CSS3TokenType::BAD_STRING, $badString->type);
        $this->assertSame('incomplete', $badString->value);
        
        $url = CSS3Token::url('http://example.com', 1, 1);
        $this->assertSame(CSS3TokenType::URL, $url->type);
        $this->assertSame('http://example.com', $url->value);
        
        $badUrl = CSS3Token::badUrl('invalid-url', 1, 1);
        $this->assertSame(CSS3TokenType::BAD_URL, $badUrl->type);
        $this->assertSame('invalid-url', $badUrl->value);
        
        $unicodeRange = CSS3Token::unicodeRange('U+0020-007F', 1, 1);
        $this->assertSame(CSS3TokenType::UNICODE_RANGE, $unicodeRange->type);
        $this->assertSame('U+0020-007F', $unicodeRange->value);
        
        $hash = CSS3Token::hash('ff0000', 1, 1);
        $this->assertSame(CSS3TokenType::HASH, $hash->type);
        $this->assertSame('ff0000', $hash->value);
        
        $delim = CSS3Token::delim(':', 1, 1);
        $this->assertSame(CSS3TokenType::DELIM, $delim->type);
        $this->assertSame(':', $delim->value);
        
        $comment = CSS3Token::comment('/* test */', 1, 1);
        $this->assertSame(CSS3TokenType::COMMENT, $comment->type);
        $this->assertSame('/* test */', $comment->value);
        
        $eof = CSS3Token::eof(1, 1);
        $this->assertSame(CSS3TokenType::EOF, $eof->type);
        $this->assertSame('', $eof->value);
    }

    public function testTokenNormalization()
    {
        CSS3Token::$normalize = true;
        
        $ident = CSS3Token::ident('Test-IDENT', 1, 1);
        $this->assertSame(CSS3TokenType::IDENT, $ident->type);
        
        CSS3Token::$normalize = false;
    }

    public function testTokenTruncation()
    {
        $longString = str_repeat('a', 2000);
        $token = CSS3Token::ident($longString);
        
        $this->assertLessThanOrEqual(1024, strlen($token->value));
        $this->assertArrayHasKey('truncated', $token->metadata);
        $this->assertTrue($token->metadata['truncated']);
        $this->assertSame(2000, $token->metadata['originalLength']);
    }

    public function testClone()
    {
        $original = CSS3Token::ident('test', 1, 1, ['key' => 'value']);
        $clone = clone $original;
        
        $this->assertEquals($original, $clone);
        $this->assertNotSame($original, $clone);
    }

    public function testJsonSerialize()
    {
        $token = CSS3Token::dimension(100, 'px', '100px', 1, 1, ['key' => 'value']);
        $json = json_encode($token);
        
        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertSame(CSS3TokenType::DIMENSION->value, $decoded['type']);
        $this->assertSame('100', $decoded['value']);
        $this->assertSame('px', $decoded['unit']);
        $this->assertSame('100px', $decoded['representation']);
        $this->assertSame(1, $decoded['line']);
        $this->assertSame(1, $decoded['column']);
        $this->assertSame(['raw' => '100px', 'isInteger' => true, 'unit' => 'px', 'key' => 'value'], $decoded['metadata']);
    }
}