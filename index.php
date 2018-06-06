<?php
require_once __DIR__ . '/vendor/autoload.php';

use Core\Space;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
/*
$code = <<<'CODE'
<?php return ;
CODE;

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
try {
    $ast = $parser->parse($code);
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
    return;
}

echo var_export(json_decode(json_encode($ast, JSON_PRETTY_PRINT), true));
*/
$a = new Space();
print_r( $a->Build('App'));