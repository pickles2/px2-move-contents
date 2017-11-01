<?php
/**
 * px2-move-contents
 */
namespace tomk79\pickles2\moveContents;

/**
 * Move Content
 */
class move_content{

	/** メインオブジェクト */
	private $main;

	/** 環境情報 */
	private $realpath_controot;

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 * @param array $options オプション
	 */
	public function __construct($main){
		$this->main = $main;
		$env = $this->main->get_env();
		$this->realpath_controot = $env['realpath_controot'];
	}

	/**
	 * コンテンツを移動させる
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	public function move($from, $to){
		// コンテンツファイル本体を移動
		$result = $this->main->fs()->rename_f(
			$this->realpath_controot.$from,
			$this->realpath_controot.$to
		);
		// コンテンツの専用リソースパスを移動
		$this->main->fs()->rename_f(
			$this->realpath_controot.$this->main->px2agent()->get_path_files($from),
			$this->realpath_controot.$this->main->px2agent()->get_path_files($to)
		);

		return true;
	}



}
