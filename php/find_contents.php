<?php
/**
 * px2-move-contents find_contents
 */
namespace tomk79\pickles2\moveContents;

/**
 * px2-move-contents find_contents
 */
class find_contents{

	/** メインオブジェクト */
	private $main;

	/** 環境情報 */
	private $env;

	/**
	 * constructor
	 * @param mixed $main メインオブジェクト
	 */
	public function __construct($main){
		$this->main = $main;
		$this->env = $this->main->get_env();
	}

	/**
	 * コンテンツファイルを検索する
	 * @param  callback $callback 引数に `$realpath` を返す
	 * @return boolean           実行結果
	 */
	public function find($callback){
		return $this->read_dir_r(null, $callback);
	}

	/**
	 * ディレクトリを再帰的に読み込む
	 * @param  string $path_current_dir カレントディレクトリ
	 * @param  callback $callback  [description]
	 * @return boolean           実行結果
	 */
	private function read_dir_r($path_current_dir, $callback){

		if( preg_match('/^'.preg_quote($this->env['realpath_homedir'], '/').'/s', $this->main->fs()->get_realpath($this->env['realpath_controot'].$path_current_dir)) ){
			// homedir 内は検索しない
			return true;
		}

		$ls = $this->main->fs()->ls($this->env['realpath_controot'].$path_current_dir);
		foreach($ls as $basename){
			if( is_dir($this->env['realpath_controot'].$path_current_dir.$basename) ){
				$this->read_dir_r($path_current_dir.$basename.'/', $callback);
			}elseif( is_file($this->env['realpath_controot'].$path_current_dir.$basename) ){
				if( !preg_match('/^.*\.html?(?:\.[a-zA-Z0-9]+)?$/s', $basename) ){
					// 拡張子 .html, .htm, およびその2重拡張子以外の場合はスキップ
					continue;
				}

				$callback($path_current_dir.$basename);
			}
		}
		return true;
	}
}
