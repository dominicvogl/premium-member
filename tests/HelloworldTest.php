<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class HelloworldTest extends TestCase
{
	public function testHelloWorld()
	{

		$expected = 'Hello World';

		$actual = 'Hello Wp';

		$this->assertSame($expected, $actual);
	}
}
