<?php

namespace mehen\tests\units\tunnel;

require __DIR__ . '/../../runner.php';

use
	atoum
;

class exception extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('runtimeException');
	}
}
