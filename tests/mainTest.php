<?php
/**
 * test for px2-move-contents
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * Main
	 */
	public function testMain(){
		$this->assertEquals( true, true );
	}

}
