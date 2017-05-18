<?php

$loader = require_once __DIR__. '/../../vendor/autoload.php';

$loader->add('CarrooiTests', __DIR__ . '/../');

define('TEMP_DIR', __DIR__ . '/../tmp/' . (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid()));

date_default_timezone_set('Europe/Prague');

Tester\Helpers::purge(TEMP_DIR);
Tester\Environment::setup();
