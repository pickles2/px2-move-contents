<?php
/**
 * test for px2-move-contents
 */
class cleanupTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		require_once(__DIR__.'/helper/run_pickles2_object.php');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * 後始末
	 */
	public function testCleanup(){
		run_pickles2_object(function($px){
			$px->internal_sub_request('/?PX=clearcache');

			$this->assertFalse( is_dir(__DIR__.'/testdata/standard/caches/p/') );
			// $this->assertFalse( is_dir(__DIR__.'/testdata/standard/px-files/_sys/ram/caches/sitemaps/') );
		});
	}

}
