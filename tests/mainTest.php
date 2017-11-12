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
		$this->assertTrue( $this->fs->copy_r(
			__DIR__.'/testdata/cont/index.html',
			__DIR__.'/testdata/standard/index.html'
		));
		$this->assertTrue( $this->fs->copy_r(
			__DIR__.'/testdata/cont/test1/',
			__DIR__.'/testdata/standard/test1/'
		));

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

			$src = $this->fs->read_file(__DIR__.'/testdata/standard/test_tmp_after/abc.html');
			// var_dump($src);
			$this->assertTrue( 0 < strpos( $src, '<a href="javascript:alert(123);">javascript</a>' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="data:" alt="data scheme" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="<?php $px->path_files("/image.gif") ?>" alt="php" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="abc_files/image.gif" alt="relative" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="./abc_files/image.gif" alt="relative_dot_slash" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="/test_tmp_after/abc_files/image.gif" alt="absolute" />' ) );

			$src = $this->fs->read_file(__DIR__.'/testdata/standard/test_tmp_after/index.html.md');
			// var_dump($src);
			$this->assertTrue( 0 < strpos( $src, '<a href="javascript:alert(123);">javascript</a>' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="data:" alt="data scheme" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="<?php $px->path_files("/image.gif") ?>" alt="php" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="index_files/image.gif" alt="relative" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="./index_files/image.gif" alt="relative_dot_slash" />' ) );
			$this->assertTrue( 0 < strpos( $src, '<img src="/test_tmp_after/index_files/image.gif" alt="absolute" />' ) );

			$src = $this->fs->read_file(__DIR__.'/testdata/standard/test_tmp_after/broccoli_after.html');
			// var_dump($src);
			$src = $this->fs->read_file(__DIR__.'/testdata/standard/test_tmp_after/broccoli_after_files/guieditor.ignore/data.json');
			// var_dump($src);
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

		$src = $this->fs->read_file(__DIR__.'/testdata/standard/test1/index.html');
		// var_dump($src);
		$this->assertTrue( 0 < strpos( $src, '<a href="javascript:alert(123);">javascript</a>' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="data:" alt="data scheme" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="<?php $px->path_files("/image.gif") ?>" alt="php" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="index_files/image.gif" alt="relative" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="./index_files/image.gif" alt="relative_dot_slash" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="/test1/index_files/image.gif" alt="absolute" />' ) );

		$src = $this->fs->read_file(__DIR__.'/testdata/standard/test1/test1.html.md');
		// var_dump($src);
		$this->assertTrue( 0 < strpos( $src, '<a href="javascript:alert(123);">javascript</a>' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="data:" alt="data scheme" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="<?php $px->path_files("/image.gif") ?>" alt="php" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="test1_files/image.gif" alt="relative" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="./test1_files/image.gif" alt="relative_dot_slash" />' ) );
		$this->assertTrue( 0 < strpos( $src, '<img src="/test1/test1_files/image.gif" alt="absolute" />' ) );
	}

}
