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

	/** コンテンツ単位で移動させるオブジェクト */
	private $move_content;

	/** ユーティリティ */
	private $utils;

	/** Pickles 2 操作のための仲介オブジェクト */
	private $px2agent;

	/** Pickles 2 の環境情報 */
	private $realpath_docroot,
	        $path_controot,
	        $realpath_controot,
	        $realpath_homedir;

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 * @param array $options オプション
	 */
	public function __construct($px, $options = array()){
		$this->px = $px;
		$options = json_decode(json_encode($options));
		if(!is_object($options)){
			$options = json_decode('{}');
		}
		if(!is_object($options->php)){
			$options->php = json_decode('{}');
		}
		if(!strlen($options->php->bin)){
			$options->php->bin = null;
		}
		if(!strlen($options->php->ini)){
			$options->php->ini = null;
		}
		if(!strlen($options->php->extension_dir)){
			$options->php->extension_dir = null;
		}
		$this->options = $options;

		$this->fs = new \tomk79\filesystem();
		$this->utils = new utils($px, $options);
		$this->px2agent = new px2agent($px, $options, $this->utils);

		$this->realpath_docroot = $this->px2agent->get_realpath_docroot();
		$this->path_controot = $this->px2agent->get_path_controot();
		$this->realpath_controot = $this->fs->get_realpath($this->realpath_docroot.$this->path_controot);
		$this->realpath_homedir = $this->px2agent->get_realpath_homedir();

		$this->move_content = new move_content($this);
	}

	/**
	 * $fs を呼び出す
	 * @return object $fs
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * $utils を呼び出す
	 * @return object $utils
	 */
	public function utils(){
		return $this->utils;
	}

	/**
	 * $px2agent を呼び出す
	 * @return object $px2agent
	 */
	public function px2agent(){
		return $this->px2agent;
	}

	/**
	 * $options を取得する
	 * @return object $options
	 */
	public function options(){
		return $this->options;
	}

	/**
	 * 環境情報を取得する
	 * @return array 環境情報を含む連想配列
	 */
	public function get_env(){
		$rtn = array();
		$rtn['realpath_docroot'] = $this->realpath_docroot;
		$rtn['path_controot'] = $this->path_controot;
		$rtn['realpath_controot'] = $this->realpath_controot;
		$rtn['realpath_homedir'] = $this->realpath_homedir;
		return $rtn;
	}

	/**
	 * 実行する
	 * @param  string $realpath_csv 変換対処表のパス
	 * @return boolean              実行結果
	 */
	public function run($realpath_csv){
		if( !is_file($realpath_csv) || !is_readable($realpath_csv) ){
			return false;
		}
		$csv = $this->fs->read_csv($realpath_csv);

		foreach( $csv as list($from, $to) ){
			// 1件ずつ処理
			$this->move_content->move($from, $to);
		}

		return true;
	}

}
