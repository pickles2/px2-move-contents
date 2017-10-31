<?php
function run_pickles2_object($callback){
	$cd = realpath('.');
	$SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'];
	chdir(__DIR__.'/../testdata/standard/');
	$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../testdata/standard/.px_execute.php';

	$px = new picklesFramework2\px('./px-files/');

	$callback($px);

	chdir($cd);
	$_SERVER['SCRIPT_FILENAME'] = $SCRIPT_FILENAME;
	$px->__destruct();// <- required on Windows
	unset($px);
	return true;
}
