<?php

namespace mehen;

use atoum;

require_once __DIR__ . '/../../autoloader.php';

if (defined('atoum\scripts\runner') === false)
{
	define('atoum\scripts\runner', __FILE__);
}

require_once __DIR__ . '/atoum/scripts/runner.php';
