<?php

require_once 'Pommo_User_Test.php';

class cancunEnRentaTest extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
    	$suite = new PHPUnit_Framework_TestSuite();

		$suite->addTestSuite('Pommo_User_Test');

        return $suite;
    }
}

