<?php
/**
 * px2-move-contents px2agent
 */
namespace tomk79\pickles2\moveContents;

/**
 * px2-move-contents px2agent
 */
class px2agent{

	/** Pickles 2 オブジェクト または EntryScript のパス */
	private $px;

	/** オプション */
	private $options;

	/** ユーティリティ */
	private $utils;

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 * @param object $utils ユーティリティ
	 */
	public function __construct($px, $options, $utils){
		$this->px = $px;
		$this->options = $options;
		$this->utils = $utils;
	}


	/**
	 * Pickles 2 の `get_realpath_docroot()` を仲介する
	 * @return string ドキュメントルートディレクトリの絶対パス
	 */
	public function get_realpath_docroot(){
		if( is_string($this->px) ){
			// EntryScript のパスを受け取った場合
			$realpath_docroot = $this->utils->execute_pickles2_cmd('/?PX=api.get.realpath_docroot');
		}elseif( is_object($this->px) ){
			// Pickles 2 オブジェクト を受け取った場合
			$realpath_docroot = $this->px->get_realpath_docroot();
		}
		return $realpath_docroot;
	}

	/**
	 * Pickles 2 の `get_path_controot()` を仲介する
	 * @return string コンテンツルートディレクトリのパス
	 */
	public function get_path_controot(){
		if( is_string($this->px) ){
			// EntryScript のパスを受け取った場合
			$path_controot = $this->utils->execute_pickles2_cmd('/?PX=api.get.path_controot');
			$path_controot = json_decode($path_controot);
		}elseif( is_object($this->px) ){
			// Pickles 2 オブジェクト を受け取った場合
			$path_controot = $this->px->get_path_controot();
		}
		return $path_controot;
	}

}
