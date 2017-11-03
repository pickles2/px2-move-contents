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

		$realpathsFromTo = array();

		$dirname = dirname($this->realpath_controot.$from);
		if( !is_dir($dirname) ){
			// 対象ディレクトリが存在しません。
			return false;
		}
		$ls = $this->main->fs()->ls($dirname);
		foreach($ls as $basename){
			if( !is_file($dirname.'/'.$basename) ){
				// ファイル以外はスキップ
				continue;
			}
			if( $basename == basename($from) ){
				// ズバリ存在したら
				array_push(
					$realpathsFromTo,
					array(
						$this->main->fs()->get_realpath($this->realpath_controot.$from),
						$this->main->fs()->get_realpath($this->realpath_controot.$to)
					)
				);
				continue;
			}
			if( preg_match( '/^'.preg_quote(basename($from), '/').'\\.([a-zA-Z0-9]+)$/s', $basename, $matched ) ){
				// 2重拡張子と判定できる場合
				array_push(
					$realpathsFromTo,
					array(
						$this->main->fs()->get_realpath($this->realpath_controot.$from.'.'.$matched[1]),
						$this->main->fs()->get_realpath($this->realpath_controot.$to.'.'.$matched[1])
					)
				);
				continue;
			}
		}
		array_push(
			$realpathsFromTo,
			array(
				$this->main->fs()->get_realpath($this->realpath_controot.$this->main->px2agent()->get_path_files($from)),
				$this->main->fs()->get_realpath($this->realpath_controot.$this->main->px2agent()->get_path_files($to))
			)
		);

		// 実際の移動処理
		foreach( $realpathsFromTo as $fromTo ){
			$this->main->fs()->rename_f(
				$fromTo[0],
				$fromTo[1]
			);
		}

		// コンテンツファイル本体を移動
		// コンテンツの専用リソースパスを移動
		$this->main->fs()->rename_f(
			$this->realpath_controot.$this->main->px2agent()->get_path_files($from),
			$this->realpath_controot.$this->main->px2agent()->get_path_files($to)
		);

		return true;
	}

}
