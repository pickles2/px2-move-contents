<?php
/**
 * px2-move-contents resolve_path
 */
namespace tomk79\pickles2\moveContents;

/**
 * px2-move-contents resolve_path
 */
class resolve_path{

	/** メインオブジェクト */
	private $main;

	/** 対象コンテンツのパス */
	private $from;

	/** 移動先のコンテンツパス */
	private $to;

	/**
	 * constructor
	 * @param mixed $main メインオブジェクト
	 * @param string $from 対象コンテンツのパス
	 * @param string $to   移動先のコンテンツパス
	 */
	public function __construct($main, $from, $to){
		$this->main = $main;
		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * HTMLファイル中のパスを解決
	 */
	public function path_resolve_in_html( $src ){

		// HTMLをパース
		$html = str_get_html(
			$src ,
			false, // $lowercase
			false, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);

		if($html === false){
			// HTMLパースに失敗した場合、無加工のまま返す。
			return $src;
		}

		$conf_dom_selectors = array(
			'*[href]'=>'href',
			'*[src]'=>'src',
			'form[action]'=>'action',
		);

		foreach( $conf_dom_selectors as $selector=>$attr_name ){
			$ret = $html->find($selector);
			foreach( $ret as $retRow ){
				$val = $retRow->getAttribute($attr_name);
				$val = $this->get_new_path($val);
				$retRow->setAttribute($attr_name, $val);
			}
		}

		$ret = $html->find('*[style]');
		foreach( $ret as $retRow ){
			$val = $retRow->getAttribute('style');
			$val = str_replace('&quot;', '"', $val);
			$val = str_replace('&lt;', '<', $val);
			$val = str_replace('&gt;', '>', $val);
			$val = $this->path_resolve_in_css($val);
			$val = str_replace('"', '&quot;', $val);
			$val = str_replace('<', '&lt;', $val);
			$val = str_replace('>', '&gt;', $val);
			$retRow->setAttribute('style', $val);
		}

		$ret = $html->find('style');
		foreach( $ret as $retRow ){
			$val = $retRow->innertext;
			$val = $this->path_resolve_in_css($val);
			$retRow->innertext = $val;
		}

		$src = $html->outertext;

		return $src;
	}

	/**
	 * CSSファイル中のパスを解決
	 */
	private function path_resolve_in_css( $bin ){

		$rtn = '';

		// url()
		while( 1 ){
			if( !preg_match( '/^(.*?)url\s*\\((.*?)\\)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= 'url("';
			$res = trim( $matched[2] );
			if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
				$res = trim( $matched2[2] );
			}
			$res = $this->get_new_path( $res );
			$rtn .= $res;
			$rtn .= '")';
			$bin = $matched[3];
		}

		// @import
		$bin = $rtn;
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)@import\s*([^\s\;]*)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= '@import ';
			$res = trim( $matched[2] );
			if( !preg_match('/^url\s*\(/', $res) ){
				$rtn .= '"';
				if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
					$res = trim( $matched2[2] );
				}
				$res = $this->get_new_path( $res );
				$rtn .= $res;
				$rtn .= '"';
			}else{
				$rtn .= $res;
			}
			$bin = $matched[3];
		}

		return $rtn;
	}

	/**
	 * 変換後の新しいパスを取得
	 */
	private function get_new_path( $path ){
		if( preg_match( '/^(?:[a-zA-Z0-9]+\:|\/\/|\#)/', $path ) ){
			return $path;
		}
		return $path;

		// $cd = $this->px->href( $this->px->req()->get_request_file_path() );
		// $cd = preg_replace( '/^(.*)(\/.*?)$/si', '$1', $cd );
		// if( !strlen($cd) ){
		// 	$cd = '/';
		// }
		//
		// switch(strtolower($this->options->to)){
		// 	case 'relate':
		// 		// 相対パスへ変換
		// 		$path = $this->main->fs()->get_realpath($path, $cd);
		// 		$path = $this->main->fs()->get_relatedpath($path, $cd);
		// 		break;
		// 	case 'absolute':
		// 		// 絶対パスへ変換
		// 		$path = $this->main->fs()->get_realpath($path, $cd);
		// 		break;
		// 	case 'pass':
		// 	default:
		// 		// 処理を行わない
		// 		break;
		// }
		//
		// $path = $this->main->fs()->normalize_path($path);
		//
		// return $path;
	}

}
