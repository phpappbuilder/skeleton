<?php
require_once __DIR__ . '/vendor/autoload.php';

use Core\Builder;

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
//print_r(Core\Space::GetKey('root/core/attr'));
$a = new Builder();
print_r( $a->DeletePath('App'));
//print_r($a->test('root/core/arra'));