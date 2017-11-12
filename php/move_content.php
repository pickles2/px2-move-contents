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
	private $directory_index_primary,
			$realpath_controot;

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 * @param array $options オプション
	 */
	public function __construct($main){
		$this->main = $main;
		$env = $this->main->get_env();
		$this->directory_index_primary = $env['directory_index_primary'];
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
			$this->main->stdout('contents not found.'."\n");
			return false;
		}

		// 対象コンテンツファイルを移動する
		$this->main->stdout('target files:'."\n");
		$this->move_content_files($pathsFromTo);
		$this->main->stdout("\n");

		// コンテンツに記述されたリソースファイルのリンクを解決する
		$this->main->stdout('resolve links ');
		$this->resolve_content_resource_links($pathsFromTo, $from, $to);
		$this->main->stdout(' done.'."\n");

		// コンテンツの `data.json` に記述されたリソースファイルのリンクを解決する
		$this->main->stdout('resolve links in data.json ');
		$this->resolve_content_resource_links_in_datajson($pathsFromTo, $from, $to);
		$this->main->stdout(' done.'."\n");

		// コンテンツの被リンクを解決する
		$this->main->stdout('resolve incoming links ');
		$this->resolve_content_incoming_links($pathsFromTo, $from, $to);
		$this->main->stdout(' done.'."\n");

		return $rtn;
	}


	/**
	 * 対象コンテンツファイルのリストを作成する
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return array      ファイルの一覧
	 */
	private function make_content_file_list($from, $to){
		if(preg_match('/\/$/s', $from)){ $from = $from.$this->directory_index_primary; }
		if(preg_match('/\/$/s', $to  )){ $to   = $to  .$this->directory_index_primary; }

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
						$this->main->fs()->normalize_path($this->main->fs()->get_realpath($from)),
						$this->main->fs()->normalize_path($this->main->fs()->get_realpath($to))
					)
				);
				continue;
			}
			if( preg_match( '/^'.preg_quote(basename($from), '/').'\\.([a-zA-Z0-9]+)$/s', $basename, $matched ) ){
				// 2重拡張子と判定できる場合
				array_push(
					$pathsFromTo,
					array(
						$this->main->fs()->normalize_path($this->main->fs()->get_realpath($from.'.'.$matched[1])),
						$this->main->fs()->normalize_path($this->main->fs()->get_realpath($to.'.'.$matched[1]))
					)
				);
				continue;
			}
		}

		// コンテンツの専用リソースパス
		array_push(
			$pathsFromTo,
			array(
				$this->main->fs()->normalize_path($this->main->fs()->get_realpath($this->main->px2agent()->get_path_files($from))),
				$this->main->fs()->normalize_path($this->main->fs()->get_realpath($this->main->px2agent()->get_path_files($to)))
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
			$this->main->stdout('    '.implode(' -> ', $fromTo)."\n");
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
			$bin_md5 = md5($bin);

			$bin = $this->resolve_content_resource_links_in_src($bin, $from, $to);

			if( $bin_md5 !== md5($bin) ){
				$this->main->fs()->save_file( $realpath_file, $bin );
				$this->main->stdout('.');
			}
		}
		return true;
	}

	/**
	 * コンテンツの `data.json` に記述されたリソースファイルのリンクを解決する
	 * @param  array $pathsFromTo 対象コンテンツのパス
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	private function resolve_content_resource_links_in_datajson($pathsFromTo, $from, $to){
		$fnc_resolve_r = function($bin_obj) use (&$fnc_resolve_r, $from, $to){
			foreach($bin_obj as $key=>$row){
				if(is_object($row) || is_array($row)){
					if( is_object($bin_obj) ){
						$bin_obj->$key = $fnc_resolve_r($bin_obj->$key);
					}elseif( is_array($bin_obj) ){
						$bin_obj[$key] = $fnc_resolve_r($bin_obj[$key]);
					}
				}elseif(is_string($row)){
					if( preg_match('/^(?:\.\/|\/)(?:[^\s]*)(?:\.[a-zA-Z0-9]+)$/s', $row) ){
						// 値全体として1つのパスと認識できる場合
						if( is_object($bin_obj) ){
							$bin_obj->$key = $this->resolve_path($bin_obj->$key, $from, $to);
						}elseif( is_array($bin_obj) ){
							$bin_obj[$key] = $this->resolve_path($bin_obj[$key], $from, $to);
						}
					}else{
						if( is_object($bin_obj) ){
							$bin_obj->$key = $this->resolve_content_resource_links_in_src($bin_obj->$key, $from, $to);
						}elseif( is_array($bin_obj) ){
							$bin_obj[$key] = $this->resolve_content_resource_links_in_src($bin_obj[$key], $from, $to);
						}
					}
				}
			}
			return $bin_obj;
		};

		foreach($pathsFromTo as $fromTo){
			if( !is_dir($this->realpath_controot.$fromTo[1]) ){
				continue;
			}
			if( !is_file($this->realpath_controot.$fromTo[1].'/guieditor.ignore/data.json') ){
				continue;
			}
			$realpath_file = $this->realpath_controot.$fromTo[1].'/guieditor.ignore/data.json';

			$bin = $this->main->fs()->read_file( $realpath_file );
			$bin_obj = json_decode($bin);
			$json_encode_option = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
			$bin_md5 = md5(json_encode($bin_obj, $json_encode_option));

			$bin_obj = $fnc_resolve_r($bin_obj);

			$bin = json_encode($bin_obj, $json_encode_option);
			if( $bin_md5 !== md5($bin) ){
				$this->main->fs()->save_file( $realpath_file, $bin );
				$this->main->stdout('.');
			}
		}
		return true;
	}

	/**
	 * コンテンツに記述されたリソースファイルのリンクを解決する
	 * @param  string $src 対象コンテンツのソース
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return string      実行後の新しい `$src`
	 */
	private function resolve_content_resource_links_in_src($src, $from, $to){
		$path_detector = new path_detector($this->main);
		$src = $path_detector->path_detect_in_html($src, function( $path ) use ($from, $to){
			return $this->resolve_path($path, $from, $to);
		});
		return $src;
	}

	/**
	 * コンテンツ内のリンクを張り替える新しいパスを生成する
	 * @param  string $path 張り替えるパス
	 * @param  string $from 元のパス
	 * @param  string $to   移動先のパス
	 * @return string       変換後のパス文字列
	 */
	private function resolve_path($path, $from, $to){
		preg_match('/^([\s]*)(.*?)([\s]*)$/s', $path, $matched);
		$pre_s = $matched[1];
		$path = $matched[2];
		$s_end = $matched[3];
		if( preg_match('/^#/', $path) ){
			return $pre_s.$path.$s_end;
		}

		$path_type = 'relative';
		if( preg_match('/^\<\?(?:php|\=)?/', $path) ){
			$path_type = 'php';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^[a-zA-Z0-9]+\:\/\//', $path) ){
			$path_type = 'url';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^\/\//', $path) ){
			$path_type = 'absolute_double_slashes';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^data\:/i', $path) ){
			$path_type = 'data';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^javascript\:/i', $path) ){
			$path_type = 'javascript';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^\//', $path) ){
			$path_type = 'absolute';
			$path_abs = $this->main->fs()->get_realpath($path, dirname($from));
		}elseif( preg_match('/^\.\//', $path) ){
			$path_type = 'relative_dot_slash';
			$path_abs = $this->main->fs()->get_realpath($path, dirname($from));
		}else{
			$path_type = 'relative';
			$path_abs = $this->main->fs()->get_realpath($path, dirname($from));
		}
		$path_abs = $this->main->fs()->normalize_path($path_abs);

		$new_path_abs = $path_abs;
		$from_files = $this->main->px2agent()->get_path_files($from);
		$to_files = $this->main->px2agent()->get_path_files($to);
		if( preg_match( '/^'.preg_quote($from_files, '/').'(.*)$/s', $path_abs, $matched ) ){
			$new_path_abs = $this->main->fs()->get_realpath($to_files.$matched[1]);
		}else{
			$new_path_abs = $this->main->fs()->get_realpath($path_abs);
		}
		$new_path_abs = $this->main->fs()->normalize_path($new_path_abs);

		$rtn = $path;
		switch($path_type){
			case 'url':
				break;
			case 'absolute_double_slashes':
				break;
			case 'absolute':
				$rtn = $new_path_abs;
				break;
			case 'relative_dot_slash':
				$path_rel = $this->main->fs()->get_relatedpath($new_path_abs, dirname($to));
				$path_rel = $this->main->fs()->normalize_path($path_rel);
				$path_rel = './'.preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
			case 'relative':
				$path_rel = $this->main->fs()->get_relatedpath($new_path_abs, dirname($to));
				$path_rel = $this->main->fs()->normalize_path($path_rel);
				$path_rel = preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
		}

		return $pre_s.$rtn.$s_end;
	}

	/**
	 * コンテンツの被リンクを解決する
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	private function resolve_content_incoming_links($pathsFromTo, $from, $to){
		$find_contents = new find_contents($this->main);
		$find_contents->find(function($path_current) use ($pathsFromTo, $from, $to){

			foreach($pathsFromTo as $pathFromTo){
				if( $pathFromTo[1] == $path_current ){
					return;
				}
			}

			// コンテンツを更新
			$realpath_file = $this->realpath_controot.$path_current;
			$bin = $this->main->fs()->read_file( $realpath_file );
			$bin_md5 = md5($bin);

			$bin = $this->resolve_content_resource_incoming_links_in_src($bin, $path_current, $from, $to);

			if( $bin_md5 !== md5($bin) ){
				$this->main->fs()->save_file( $realpath_file, $bin );
				$this->main->stdout('.');
			}

			// data.json を更新
			$fnc_resolve_r = function($bin_obj) use (&$fnc_resolve_r, $path_current, $from, $to){
				foreach($bin_obj as $key=>$row){
					if(is_object($row) || is_array($row)){
						if( is_object($bin_obj) ){
							$bin_obj->$key = $fnc_resolve_r($bin_obj->$key);
						}elseif( is_array($bin_obj) ){
							$bin_obj[$key] = $fnc_resolve_r($bin_obj[$key]);
						}
					}elseif(is_string($row)){
						if( preg_match('/^(?:\.\/|\/)(?:[^\s]*)(?:\.[a-zA-Z0-9]+)$/s', $row) ){
							// 値全体として1つのパスと認識できる場合
							if( is_object($bin_obj) ){
								$bin_obj->$key = $this->resolve_incoming_path($bin_obj->$key, $path_current, $from, $to);
							}elseif( is_array($bin_obj) ){
								$bin_obj[$key] = $this->resolve_incoming_path($bin_obj[$key], $path_current, $from, $to);
							}
						}else{
							if( is_object($bin_obj) ){
								$bin_obj->$key = $this->resolve_content_resource_incoming_links_in_src($bin_obj->$key, $path_current, $from, $to);
							}elseif( is_array($bin_obj) ){
								$bin_obj[$key] = $this->resolve_content_resource_incoming_links_in_src($bin_obj[$key], $path_current, $from, $to);
							}
						}
					}
				}
				return $bin_obj;
			};
			$realpath_files = $this->main->fs()->normalize_path($this->realpath_controot.$this->main->px2agent()->get_path_files($path_current).'guieditor.ignore/data.json');
			if( is_file( $realpath_files ) ){
				$bin = $this->main->fs()->read_file( $realpath_files );
				$bin_obj = json_decode($bin);
				$json_encode_option = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
				$bin_md5 = md5(json_encode($bin_obj, $json_encode_option));

				$bin_obj = $fnc_resolve_r($bin_obj);

				$bin = json_encode($bin_obj, $json_encode_option);
				if( $bin_md5 !== md5($bin) ){
					$this->main->fs()->save_file( $realpath_files, $bin );
					$this->main->stdout('.');
				}
			}

		});
		return true;
	}

	/**
	 * コンテンツに記述されたリソースファイルのリンクを解決する
	 * @param  string $src 対象コンテンツのソース
	 * @param  string $path_current リンク元のパス
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return string      実行後の新しい `$src`
	 */
	private function resolve_content_resource_incoming_links_in_src($src, $path_current, $from, $to){
		$path_detector = new path_detector($this->main);
		$src = $path_detector->path_detect_in_html($src, function( $path ) use ($path_current, $from, $to){
			return $this->resolve_incoming_path($path, $path_current, $from, $to);
		});
		return $src;
	}

	/**
	 * コンテンツへの被リンクを張り替える新しいパスを生成する
	 * @param  string $path 張り替えるパス
	 * @param  string $path_current リンク元のパス
	 * @param  string $from 元のパス
	 * @param  string $to   移動先のパス
	 * @return string       変換後のパス文字列
	 */
	private function resolve_incoming_path($path, $path_current, $from, $to){
		if(preg_match('/^'.preg_quote($to, '/').'(?:\.[a-zA-Z0-9]+)?$/s', $path_current)){
			// 対象ページ自身は変換対象にしない(処理済みなので)
			return $pre_s.$path.$s_end;
		}

		preg_match('/^([\s]*)(.*?)([\s]*)$/s', $path, $matched);
		$pre_s = $matched[1];
		$path = $matched[2];
		$s_end = $matched[3];

		if( preg_match('/^#/', $path) ){
			return $pre_s.$path.$s_end;
		}

		$path_type = 'relative';
		if( preg_match('/^\<\?(?:php|\=)?/', $path) ){
			$path_type = 'php';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^[a-zA-Z0-9]+\:\/\//', $path) ){
			$path_type = 'url';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^\/\//', $path) ){
			$path_type = 'absolute_double_slashes';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^data\:/i', $path) ){
			$path_type = 'data';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^javascript\:/i', $path) ){
			$path_type = 'javascript';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^\//', $path) ){
			$path_type = 'absolute';
			$path_abs = $this->main->fs()->get_realpath($path, dirname($path_current));
		}elseif( preg_match('/^\.\//', $path) ){
			$path_type = 'relative_dot_slash';
			$path_abs = $this->main->fs()->get_realpath($path, dirname($path_current));
		}else{
			$path_type = 'relative';
			$path_abs = $this->main->fs()->get_realpath($path, dirname($path_current));
		}
		$path_abs = $this->main->fs()->normalize_path($path_abs);

		if( $path_abs != $from && $path_abs.$this->directory_index_primary != $from ){
			return $pre_s.$path.$s_end;
		}

		$new_path_abs = $this->main->fs()->get_realpath($to);
		$new_path_abs = $this->main->fs()->normalize_path($new_path_abs);

		$rtn = $path;
		switch($path_type){
			case 'url':
				break;
			case 'absolute_double_slashes':
				break;
			case 'absolute':
				$rtn = $new_path_abs;
				break;
			case 'relative_dot_slash':
				$path_rel = $this->main->fs()->get_relatedpath($new_path_abs, dirname($path_current));
				$path_rel = $this->main->fs()->normalize_path($path_rel);
				$path_rel = './'.preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
			case 'relative':
				$path_rel = $this->main->fs()->get_relatedpath($new_path_abs, dirname($path_current));
				$path_rel = $this->main->fs()->normalize_path($path_rel);
				$path_rel = preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
		}

		return $pre_s.$rtn.$s_end;
	}

}
