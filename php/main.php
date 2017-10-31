<?php
/**
 * px2-move-contents
 */
namespace tomk79\pickles2\moveContents;

/**
 * px2-move-contents
 */
class main{

	/** Pickles 2 オブジェクト または EntryScript のパス */
	private $px;

	/** オプション */
	private $options;

	/** tomk79\filesystem のインスタンス */
	private $fs;

	/** ユーティリティ */
	private $utils;

	/** Pickles 2 操作のための仲介オブジェクト */
	private $px2agent;

	/** Pickles 2 の環境情報 */
	private $realpath_docroot;
	private $path_controot;

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 * @param array $options オプション
	 */
	public function __construct($px, $options = array()){
		$this->px = $px;
		$this->options = $options;
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/utils.php');
		$this->utils = new utils($px, $options);
		require_once(__DIR__.'/px2agent.php');
		$this->px2agent = new px2agent($px, $options, $this->utils);

		$this->realpath_docroot = $this->px2agent->get_realpath_docroot();
		$this->path_controot = $this->px2agent->get_path_controot();
		$this->realpath_controot = $this->fs->get_realpath($this->realpath_docroot.$this->path_controot);
	}

	/**
	 * 実行する
	 * @param  string $realpath_csv 変換対処表のパス
	 * @return boolean              実行結果
	 */
	public function run($realpath_csv){
		return true;
	}

}
