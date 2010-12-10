<?php
 
class Pommo_User_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		require_once '../bootstrap.php';
		require_once '../classes/Pommo_User.php';
		
		//	Empty table users to have a know state
		$dbo = Pommo::$_dbo;
		$dbo->query($dbo->prepare('TRUNCATE TABLE '.$dbo->table['users']));
	}

	public function testsave()
	{
		$user = new Pommo_User();
		
		$saved = $user->save('admin', 'password');
		PHPUnit_Framework_Assert::assertTrue($saved, 'Test user save');
		
		$saved = $user->save('admin', 'password');
		PHPUnit_Framework_Assert::assertFalse($saved,
				'Test not saving duplicate');
		
		$saved = $user->save('', '');
		PHPUnit_Framework_Assert::assertFalse($saved,
				'Test dont allow empty data');
	}
	
	/**
     * @depends testsave
     */
	public function testlogin()
	{
		$user = new Pommo_User();

		$saved 	= $user->save('admin', 'password');
		$logged	= $user->login('admin', 'password');
		
		PHPUnit_Framework_Assert::assertTrue($logged, 'Test loggin user');
	}
}

