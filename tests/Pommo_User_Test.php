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
	
	public function testgetList()
	{
		$user = new Pommo_User();
		
		$user->save('admin', 'password');
		$user->save('admin02', 'password');
		$user->save('admin03', 'password');
		$user->save('admin04', 'password');
		$user->save('admin05', 'password');
		$user->save('admin06', 'password');
		$user->save('admin07', 'password');
		$user->save('admin08', 'password');
		$user->save('admin09', 'password');
		$user->save('admin10', 'password');
		$user->save('admin11', 'password');
		$user->save('admin12', 'password');
		
		$data = array('limit' => 10, 'order' => 'ASC', 'page' => 1);
		$results = $user->getList($data);

		PHPUnit_Framework_Assert::assertEquals(2, $user->pages);
		PHPUnit_Framework_Assert::assertEquals('admin', $results[0]['username']);
		PHPUnit_Framework_Assert::assertEquals('admin10', $results[9]['username']);
		
		$data = array('limit' => 5, 'order' => 'DESC', 'page' => 2);
		$results = $user->getList($data);
		PHPUnit_Framework_Assert::assertEquals(3, $user->pages);
		PHPUnit_Framework_Assert::assertEquals('admin07', $results[0]['username']);
	}
}

