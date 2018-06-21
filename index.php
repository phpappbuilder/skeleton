<?php
require_once __DIR__ . '/vendor/autoload.php';

use Space\Builder;


$a = new Builder();
print_r($a->Build('App'));