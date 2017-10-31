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

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 */
	public function __construct($px){
		$this->px = $px;
		if( is_string($this->px) ){
			// EntryScript のパスを受け取った場合
			var_dump(__LINE__);
		}elseif( is_object($this->px) ){
			// Pickles 2 オブジェクト を受け取った場合
			var_dump(__LINE__);
		}
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
