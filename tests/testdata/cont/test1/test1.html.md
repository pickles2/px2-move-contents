<script src="test1_files/script.js"></script>
<script src="<?= htmlspecialchars( $px->path_files('/script.js') ) ?>"></script>


Test Data

<dl>
	<dt>リンク - 相対パス</dt>
		<dd><a href="index.html">index.html</a></dd>
	<dt>リンク - 相対パス (./)</dt>
		<dd><a href="./index.html">./index.html</a></dd>
	<dt>リンク - 相対パス (./; index.html を省略)</dt>
		<dd><a href="./">./ -> ./index.html</a></dd>
	<dt>JavaScript</dt>
		<dd><a href="javascript:alert(123);">javascript</a></dd>
	<dt>data scheme</dt>
		<dd><img src="data:" alt="data scheme" /></dd>
	<dt>PHP</dt>
		<dd><img src="<?php $px->path_files("/image.gif") ?>" alt="php" /></dd>
	<dt>相対パス</dt>
		<dd><img src="test1_files/image.gif" alt="relative" /></dd>
	<dt>相対パス (./)</dt>
		<dd><img src="./test1_files/image.gif" alt="relative_dot_slash" /></dd>
	<dt>絶対パス</dt>
		<dd><img src="/test1/test1_files/image.gif" alt="absolute" /></dd>
	<dt>パスの前後に改行が含まれる</dt>
		<dd><img src="
			test1_files/image.gif
		" alt="br" /></dd>
</dl>

- [./broccoli.html](./broccoli.html)
- [broccoli.html](broccoli.html "broccoli")
- [./index.html](./index.html)
- ![テスト画像1 ./test1_files/](./test1_files/image.gif "テスト画像1")
- ![テスト画像2 ./test1_files/](./test1_files/image.gif)
