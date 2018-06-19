<?php
require_once __DIR__ . '/vendor/autoload.php';

use Core\Space\Builder;


$a = new Builder();
print_r($a->Build('App'));