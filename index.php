<?php
require_once __DIR__ . '/vendor/autoload.php';

use Core\Space;
/*
  $code = file_get_contents('App/root/core/SpaceBundle.php');
  $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
  $prettyPrinter = new PrettyPrinter\Standard;

  try {
      // parse
      $stmts = $parser->parse($code);

      // change
      print_r(json_encode($stmts[0]->expr->items));

      // pretty print
    //  $code = $prettyPrinter->prettyPrint($stmts);

    //  echo $code;
  } catch (Error $e) {
      echo 'Parse Error: ', $e->getMessage();
  }

*/
$a = new Space();
print_r( $a->test('App/root/core'));