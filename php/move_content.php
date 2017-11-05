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
		$rtn = true;

		// 対象コンテンツファイルのリストを作成する
		$pathsFromTo = $this->make_content_file_list($from, $to);
		if(!is_array($pathsFromTo)){
			return false;
		}

		// 対象コンテンツファイルを移動する
		$this->move_content_files($pathsFromTo);

		// コンテンツに記述されたリソースファイルのリンクを解決する
		$this->resolve_content_resource_links($pathsFromTo, $from, $to);

		return $rtn;
	}


	/**
	 * 対象コンテンツファイルのリストを作成する
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return array      ファイルの一覧
	 */
	private function make_content_file_list($from, $to){
		$pathsFromTo = array();

		$dirname = dirname($from);
		if( !is_dir($this->realpath_controot.$dirname) ){
			// 対象ディレクトリが存在しません。
			return false;
		}
		$ls = $this->main->fs()->ls($this->realpath_controot.$dirname);
		foreach($ls as $basename){
			if( !is_file($this->realpath_controot.$dirname.'/'.$basename) ){
				// ファイル以外はスキップ
				continue;
			}
			if( $basename == basename($from) ){
				// ズバリ存在したら
				array_push(
					$pathsFromTo,
					array(
						$this->main->fs()->get_realpath($from),
						$this->main->fs()->get_realpath($to)
					)
				);
				continue;
			}
			if( preg_match( '/^'.preg_quote(basename($from), '/').'\\.([a-zA-Z0-9]+)$/s', $basename, $matched ) ){
				// 2重拡張子と判定できる場合
				array_push(
					$pathsFromTo,
					array(
						$this->main->fs()->get_realpath($from.'.'.$matched[1]),
						$this->main->fs()->get_realpath($to.'.'.$matched[1])
					)
				);
				continue;
			}
		}

		// コンテンツの専用リソースパス
		array_push(
			$pathsFromTo,
			array(
				$this->main->fs()->get_realpath($this->main->px2agent()->get_path_files($from)),
				$this->main->fs()->get_realpath($this->main->px2agent()->get_path_files($to))
			)
		);
		return $pathsFromTo;
	}

	/**
	 * コンテンツのファイルを移動させる
	 * @param  array $pathsFromTo 対象コンテンツのパス
	 * @return boolean      実行結果
	 */
	private function move_content_files($pathsFromTo){

		// 実際の移動処理
		foreach( $pathsFromTo as $fromTo ){
			$this->main->fs()->rename_f(
				$this->realpath_controot.$fromTo[0],
				$this->realpath_controot.$fromTo[1]
			);
		}

		return true;
	}


	/**
	 * コンテンツに記述されたリソースファイルのリンクを解決する
	 * @param  array $pathsFromTo 対象コンテンツのパス
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	private function resolve_content_resource_links($pathsFromTo, $from, $to){
		foreach($pathsFromTo as $fromTo){
			if( !is_file($this->realpath_controot.$fromTo[1]) ){
				continue;
			}
			$realpath_file = $this->realpath_controot.$fromTo[1];

			$bin = $this->main->fs()->read_file( $realpath_file );

			$resolve_path = new resolve_path($this->main, $from, $to);
			$bin = $resolve_path->path_resolve_in_html($bin);

			$result = $this->main->fs()->save_file( $realpath_file, $bin );
		}
		return true;
	}

}
