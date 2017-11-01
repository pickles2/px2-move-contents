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

			clearstatcache();
			$this->assertTrue( $result );
			$this->assertFalse( is_file(__DIR__.'/testdata/standard/test1/index.html') );
			$this->assertFalse( is_dir(__DIR__.'/testdata/standard/test1/index_files/') );
			$this->assertFalse( is_file(__DIR__.'/testdata/standard/test1/test1.html.md') );
			$this->assertFalse( is_dir(__DIR__.'/testdata/standard/test1/test1_files/') );
			$this->assertTrue( is_file(__DIR__.'/testdata/standard/test_tmp_after/abc.html') );
			$this->assertTrue( is_dir(__DIR__.'/testdata/standard/test_tmp_after/abc_files/') );
			$this->assertTrue( is_file(__DIR__.'/testdata/standard/test_tmp_after/index.html.md') );
			$this->assertTrue( is_dir(__DIR__.'/testdata/standard/test_tmp_after/index_files/') );
		});
	}

	/**
	 * Entry Script のパスを提供して実行
	 */
	public function testExecuteWithSupplyingEntryScriptPath(){
		$px2moveContents = new tomk79\pickles2\moveContents\main(__DIR__.'/testdata/standard/.px_execute.php');
		$this->assertEquals( gettype($px2moveContents), gettype(json_decode('{}')) );

		$result = $px2moveContents->run(__DIR__.'/testdata/csv/fin.csv');

		clearstatcache();
		$this->assertTrue( $result );
		$this->assertTrue( is_file(__DIR__.'/testdata/standard/test1/index.html') );
		$this->assertTrue( is_dir(__DIR__.'/testdata/standard/test1/index_files/') );
		$this->assertTrue( is_file(__DIR__.'/testdata/standard/test1/test1.html.md') );
		$this->assertTrue( is_dir(__DIR__.'/testdata/standard/test1/test1_files/') );
		$this->assertFalse( is_file(__DIR__.'/testdata/standard/test_tmp_after/abc.html') );
		$this->assertFalse( is_dir(__DIR__.'/testdata/standard/test_tmp_after/abc_files/') );
		$this->assertFalse( is_file(__DIR__.'/testdata/standard/test_tmp_after/index.html.md') );
		$this->assertFalse( is_dir(__DIR__.'/testdata/standard/test_tmp_after/index_files/') );
	}

}
