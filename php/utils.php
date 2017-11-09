<?php
/**
 * px2-move-contents utilitie class
 */
namespace tomk79\pickles2\moveContents;

/**
 * px2-move-contents utilitie class
 */
class utils{

	/** Pickles 2 オブジェクト または EntryScript のパス */
	private $px;

	/** オプション */
	private $options;

	/**
	 * constructor
	 * @param mixed $px Pickles 2 オブジェクト または EntryScript のパス
	 * @param array $options オプション
	 */
	public function __construct($px, $options = array()){
		$this->px = $px;
		$this->options = $options;
	}

	/**
	 * Pickles 2 を実行する
	 */
	public function execute_pickles2_cmd($path){
		$cmd = array();
		if( strlen($this->options->php->bin) ){
			array_push($cmd, $this->options->php->bin);
		}else{
			array_push($cmd, 'php');
		}
		if( strlen($this->options->php->ini) ){
			array_push($cmd, '-c');
			array_push($cmd, $this->options->php->ini);
		}
		if( strlen($this->options->php->extension_dir) ){
			array_push($cmd, '-d');
			array_push($cmd, $this->options->php->extension_dir);
		}
		array_push($cmd, $this->px);
		array_push($cmd, $path);
		$result = $this->cmd($cmd);
		$result = json_decode($result);
		return $result;
	}

	/**
	 * コマンドを実行する
	 */
	public function cmd($php_command){
		foreach($php_command as $key=>$row){
			if($row == 'php'){
				$php_command[$key] = addslashes($php_command[$key]);
					// ↑ Windows でこれを `escapeshellarg()` でエスケープすると、なぜかエラーに。
			}else{
				$php_command[$key] = escapeshellarg($php_command[$key]);
			}
		}
		$cmd = implode( ' ', $php_command );

		// コマンドを実行
		ob_start();
		$proc = proc_open($cmd, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);
		$io = array();
		foreach($pipes as $idx=>$pipe){
			$io[$idx] = stream_get_contents($pipe);
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		$bin = $io[1]; // stdout
		// if( strlen( $io[2] ) ){
		// 	$this->error($io[2]); // stderr
		// }

		if( @$options['output'] == 'json' ){
			$bin = json_decode($bin);
		}

		return $bin;
	} // internal_sub_request()

}
