# px2-move-contents
Pickles 2 の既に制作済みのコンテンツの物理パスを変更(移動)します。

- やること
    - CSVを受け取り、1列目のパスが指すコンテンツを、2列名のパスに移動させます。
    - コンテンツファイル本体と専用リソースディレクトリを合わせて移動します。
    - コンテンツファイル名が2重拡張子である場合、自動的に探します。
    - コンテンツファイル内のリンクのパスは移動先のパスを基準に書き換えられます。
    - コンテンツルートディレクトリを検索し、すべてのコンテンツ中の移動対象へのリンクを更新します。
    - GUI編集のデータファイル `data.json` 中のリンクを書き換えます。
    - Markdown文法で書かれたパスを検出して書き換えます。
- やらないこと
    - サイトマップは書き換えません。


## セットアップ - Setup

### 1. [Pickles 2](http://pickles2.pxt.jp/) をセットアップ

### 2. composer.json に、パッケージ情報を追加

```
{
    "require": {
        "pickles2/px2-move-contents": "dev-master"
    }
}
```

### 3. composer update

更新したパッケージ情報を反映します。

```
$ composer update
```


## 使い方 - Usage

### `$px` を渡せる場合

```php
<?php
$px = new picklesFramework2\px('/path/to/px-files/');
$px2moveContents = new tomk79\pickles2\moveContents\main($px);
$result = $px2moveContents->run('/path/to/move_list.csv');
```

### EntryScript (`.px_execute.php`) のパスを渡せる場合

```php
<?php
$px2moveContents = new tomk79\pickles2\moveContents\main('/path/to/.px_execute.php');
$result = $px2moveContents->run('/path/to/move_list.csv');
```

### CSVの仕様

A列のパスにあるコンテンツファイルを探し、B列のパスに移動します。

パスはスラッシュから始まる絶対パスで書きますが、コンテンツルートディレクトリを起点として解釈されることに注意してください。

<table>
<thead>
<tr>
<th></th>
<th>A</th>
<th>B</th>
</tr>
</thead>
<tbody>
<tr>
<th>1</th>
<td>/test1/index.html</td>
<td>/test_after/abc.html</td>
</tr>
<tr>
<th>2</th>
<td>/test1/test1.html</td>
<td>/test_after/index.html</td>
</tr>
</tbody>
</table>


## オプション - Options

```php
$result = $px2moveContents->run('/path/to/move_list.csv', $options);
```

- `$options->php->bin` : PHPコマンドのパス
- `$options->php->ini` : php.ini のパス
- `$options->php->extension_dir` : PHPの extension_dir のパス
- `$options->stdout` : 標準出力先のコールバック
- `$options->stderr` : エラー出力先のコールバック



## 更新履歴 - Change log

### pickles2/px2-move-contents v0.0.1 (20??年??月??日)

- 初回リリース


## ライセンス - License

Copyright (c)2001-2017 Tomoya Koyanagi, and Pickles 2 Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
