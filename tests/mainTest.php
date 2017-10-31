<?php
/**
 * test for px2-move-contents
 */
class mainTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		require_once(__DIR__.'/helper/run_pickles2_object.php');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * Pickles 2 オブジェクトを提供して実行
	 */
	public function testExecuteWithSupplyingPickles2Object(){
		run_pickles2_object(function($px){
			$px2moveContents = new tomk79\pickles2\moveContents\main($px);
			$this->assertEquals( gettype($px2moveContents), gettype(json_decode('{}')) );

			$result = $px2moveContents->run(__DIR__.'/testdata/csv/test1.csv');

			$this->assertTrue( $result );
		});
	}

	/**
	 * Entry Script のパスを提供して実行
	 */
	public function testExecuteWithSupplyingEntryScriptPath(){
		$px2moveContents = new tomk79\pickles2\moveContents\main(__DIR__.'/testdata/standard/.px_execute.php');
		$this->assertEquals( gettype($px2moveContents), gettype(json_decode('{}')) );

		$result = $px2moveContents->run(__DIR__.'/testdata/csv/fin.csv');

		$this->assertTrue( $result );
	}

}
