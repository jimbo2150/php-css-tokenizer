<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Jimbo2150\PhpCssTypedOm\Tokenizer\CSS3Tokenizer;
use Jimbo2150\PhpCssTypedOm\TypedOM\CSSStyleDeclaration;
use Jimbo2150\PhpCssTypedOm\TypedOM\Values\CSSUnitValue;

echo "=== CSS3 Tokenizer and Typed OM Demo ===\n\n";

// Demo 1: CSS3 Tokenizer
echo "1. CSS3 Tokenizer Demo:\n";
$css = '
/* Main styles */
.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.button:hover {
    background-color: #0056b3;
}
';

$tokenizer = new CSS3Tokenizer($css);
$tokens = $tokenizer->tokenize();

echo 'Tokenized '.count($tokens)." tokens from CSS\n";
echo "First 10 tokens:\n";
for ($i = 0; $i < min(10, count($tokens)); ++$i) {
	$token = $tokens[$i];
	echo sprintf("  %-15s: %s\n", $token->type->value, $token->value);
}

// Demo 2: CSS Typed OM
echo "\n2. CSS Typed OM Demo:\n";

// Create a style declaration
$style = new CSSStyleDeclaration('width: 100px; height: 50%; color: #ff0000;');

echo 'Original CSS: '.$style->getCssText()."\n";

// Get and modify values
$width = $style->getPropertyValue('width');
if ($width instanceof CSSUnitValue) {
	echo 'Width: '.$width->getNumericValue().$width->getUnit()."\n";

	// Double the width
	$newWidth = $width->multiply(2);
	$style->setProperty('width', $newWidth);
	echo 'New width: '.$style->getPropertyValue('width')->toString()."\n";
}

// Add a new property
$margin = new CSSUnitValue(10, 'px');
$style->setProperty('margin', $margin);
echo 'Updated CSS: '.$style->getCssText()."\n";

// Demo 3: Complex CSS manipulation
echo "\n3. Complex CSS Manipulation:\n";

$complexCSS = '
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    color: white;
    text-align: center;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    text-align: center;
}
';

$complexStyle = new CSSStyleDeclaration($complexCSS);
echo "Complex CSS properties:\n";
foreach ($complexStyle->getProperties() as $property => $value) {
	echo sprintf("  %-20s: %s\n", $property, $value->toString());
}

// Demo 4: Unit conversions
echo "\n4. Unit Conversions:\n";
$pxValue = new CSSUnitValue(16, 'px');
$emValue = $pxValue->to('em');
if ($emValue) {
	echo '16px = '.$emValue->getNumericValue()."em\n";
}

// Demo 5: CSS calculations
echo "\n5. CSS Calculations:\n";
$baseSize = new CSSUnitValue(16, 'px');
$largeSize = $baseSize->multiply(1.5);
$smallSize = $baseSize->divide(2);

echo 'Base: '.$baseSize->toString()."\n";
echo 'Large: '.$largeSize->toString()."\n";
echo 'Small: '.$smallSize->toString()."\n";

echo "\n=== Demo Complete ===\n";
