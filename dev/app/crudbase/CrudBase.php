<?php 
namespace App\CrudBase;

require_once 'FileUploadK/FileUploadK.php';
require_once 'CopyEx.php';


/**
 * CRUD support class
 * @since 2014-1-1 | 2024-8-27
 *
 */
class CrudBase{
    
    private static $version = "4.0.1"; /// バージョン
    private static $singleton;
	private static $fileUploadK;
	private static $copyEx;
	
	
	/**
	 * バージョンを取得する
	 * @return string バージョン
	 */
	public static function getVersion(){
		$version = self::$version;
		return $version;
	}
	
	
	/**
	 * 画像ファイルパスからサムネイル用のパスに変換作成
	 * 
	 * @desc
	 * 【当メソッドの目的】
	 * オリジナルの画像ファイルパスからサムネイル用の画像ファイルパスを作成したいときに活躍するメソッドである。
	 * 画像ファイルパス中の一部のディレクトリ名を「orig」から「thum」に変更する。
	 * 
	 * 【注意】
	 * 特定の画像ファイルである場合のみ変換する。対応する画像形式はjpg, jpeg, png, gif, jfif のみ。それ以外は変換作成を行わない。
	 * 
	 * 変換例
	 * 変換前→"/img/orig/12345/orig/test.jpg"
	 * 変換後→"/img/orig/12345/thum/test.jpg"
	 * @param string $orig_fp オリジナルの画像ファイルパス
	 * @param string $thum_dir サムネイル画像のディレクトリ名（省略可）
	 * @param string $orig_dir オリジナル画像のディレクトリ名（省略可）
	 * @return string サムネイル用画像パス
	 */
	public static function toThumnailPath($orig_fp, $thum_dir='/thum/', $orig_dir='/orig/'){
	    if(empty($orig_fp)) return $orig_fp;
	    
	    // 拡張子を取得する
	    $pi = pathinfo($orig_fp);
	    if(empty($pi['extension'])) return '';
	    $ext = mb_strtolower($pi['extension']);
	    
	    // 対象の画像形式でないなら、オリジナル画像ファイルパスを変換せずそのまま返す。
	    $exts = ['jpg', 'jpeg', 'png', 'gif','jfif'];
	    if(!in_array($ext, $exts)) return $orig_fp;

	    $fp_l = self::stringLeftRev($orig_fp, $orig_dir); // 文字列を右側から印文字を検索し、左側の文字を切り出す。
	    $fp_r = self::stringRightRev($orig_fp, $orig_dir); // 文字列を右側から印文字を検索し、右側の文字を切り出す。

	    $thum_fp = $fp_l . $thum_dir . $fp_r;

	    return $thum_fp;
	    
	}
	
	
	
	/**
	 * 文字列を左側から印文字を検索し、左側の文字を切り出す。
	 * @param string $s 対象文字列
	 * @param string $mark 印文字
	 * @return string 印文字から左側の文字列
	 */
	public static function stringLeft($s, $mark){
	    
	    if ($s==null || $s==""){
	        return $s;
	    }
	    $a=strpos($s,$mark);
	    if($a==null && $a!==0){
	        return "";
	    }
	    $s2=substr($s,0,$a);
	    return $s2;
	    
	}

	
	/**
	 * 文字列を左側から印文字を検索し、右側の文字を切り出す。
	 * @param string $s 対象文字列
	 * @param string $mark 印文字
	 * @return string 印文字から右側の文字列
	 */
	public static function stringRight($s,$mark){
	    if ($s==null || $s==""){
	        return $s;
	    }
	    
	    $a=strpos($s,$mark);
	    if($a==null && $a!==0){
	        return "";
	    }
	    $s2=substr($s,$a + strlen($mark),strlen($s));
	    return $s2;
	}
	
	
	/**
	 * 文字列を右側から印文字を検索し、左側の文字を切り出す。
	 * @param string $s 対象文字列
	 * @param string $mark 印文字
	 * @return string 印文字から左側の文字列
	 */
	public static function stringLeftRev($s,$mark){
	    
	    if ($s==null || $s==""){
	        return $s;
	    }
	    $a = strrpos($s,$mark);
	    if($a==null && $a!==0){
	        return "";
	    }
	    $s2=substr($s,0,$a);
	    return $s2;
	    
	}
	
	
	/*
	* 文字列を右側から印文字を検索し、右側の文字を切り出す。
	* @param string $s 対象文字列
	* @param string $mark 印文字
	* @return string 印文字から右側の文字列
	*/
	public static function stringRightRev($s,$mark){
	    if ($s==null || $s==""){
	        return $s;
	    }
	    
	    $a = strrpos($s,$mark);
	    if($a==null && $a!==0){
	        return "";
	    }
	    $s2=substr($s,$a + strlen($mark),strlen($s));
	    
	    return $s2;
	}
	
	/**
	 * キャメル記法に変換
	 * @param string $str スネーク記法のコード
	 * @return string キャメル記法のコード
	 */
	public static function camelize($str) {
	    $str = strtr($str, '_', ' ');
	    $str = ucwords($str);
	    return str_replace(' ', '', $str);
	}
	
	/**
	 * スネーク記法に変換
	 * @param string $str キャメル記法のコード
	 * @return string スネーク記法のコード
	 */
	public static function snakize($str) {
	    $str = preg_replace('/[A-Z]/', '_\0', $str);
	    $str = strtolower($str);
	    return ltrim($str, '_');
	}
	
	/**
	 * キャメル記法からテーブル名に変換 例）BigCat → bit_cats
	 * @param string $str キャメル記法のコード
	 * @return string テーブル名
	 */
	public static function camelToTableName($str) {
	    $str = preg_replace('/[A-Z]/', '_\0', $str);
	    $str = strtolower($str);
	    $str = ltrim($str, '_');
	    $str .= 's';
	    return $str;
	}
	
	/**
	 * ディレクトリごとファイルを削除する。（階層化のファイルまで削除可能）
	 * @param string $dir_name 削除対象ディレクトリ(絶対パスで指定する。セパレータはスラッシュ、バックスラッシュが混在しても良い）
	 */
	public static function removeDirectory($dir_name){
	    
	    $fileUploadK = self::factoryFileUploadK();
	    return $fileUploadK->removeDirectory($dir_name);
	}
	
	/**
	 * 拡張コピー　存在しないディテクトリも自動生成
	 * 日本語ファイルに対応
	 * @param string $sourceFn コピー元ファイル名
	 * @param string $copyFn コピー先ファイル名
	 * @param int $permission ディレクトリまたはファイルのパーミッション
	 * @return true:コピー成功  false:コピー失敗
	 */
	public static function copyEx($sourceFn,$copyFn,$permission = 0777){
	    $copyEx = self::factoryCopyEx();
	    $copyEx->copy($sourceFn,$copyFn,$permission = 0777);
	    
	    return true;
	}
	
	
	/**
	 * 再帰的にディレクトリをコピーするメソッド
	 *
	 * @param string $sourceDir コピー元のディレクトリパス
	 * @param string $destDir コピー先のディレクトリパス
	 * @return bool コピーが成功した場合は true、失敗した場合は false
	 */
	public static function copyDirEx(string $sourceDir, string $destDir): bool
	{
		$copyEx = self::factoryCopyEx();
		return $copyEx->copyDirEx($sourceDir, $destDir);
	
	}
		
	
	/**
	 * ディレクトリ内のファイルをまとめて削除する。（2階層のファイル群のみ）
	 * @param string $dir_name 削除ファイルのディレクトリ名
	 * @return boolean true
	 */
	public static function dirClear($dn){
	    $copyEx = self::factoryCopyEx();
	    $copyEx->dirClear($dn);
	    
	    return true;
	}
	
	
	/**
	 * ディレクトリを中身のファイルやフォルダごと削除する（エイリアス）
	 * @param string $dir_name 削除ファイルのディレクトリ名
	 * @return boolean true
	 */
	public static function rmdir($dn){
		$copyEx = self::factoryCopyEx();
		$copyEx->rmdirEx($dn);
		
		return true;
	}
	
	
	/**
	 * ディレクトリを中身のファイルやフォルダごと削除する
	 * @param string $dir_name 削除ファイルのディレクトリ名
	 * @return boolean true
	 */
	public static function rmdirEx($dn){
		$copyEx = self::factoryCopyEx();
		$copyEx->rmdirEx($dn);
		
		return true;
	}
	
	
	
	
	/**
	 * 日本語フォルダ名対応のディレクトリ存在チェック
	 * @param string $dn	ディレクトリ名
	 * @return boolean	true:存在	false:未存在
	 */
	public static function isDirEx($dn){
	    $copyEx = self::factoryCopyEx();
	    $copyEx->is_dir_ex($dn);
	    
	    return true;
	}
	
	
	/**
	 * ファイルアップロードクラスのインスタンスを取得する
	 * @return CopyEx
	 */
	public static function factoryCopyEx(){
	    if(empty(self::$copyEx)){
	        self::$copyEx = new CopyEx();
	    }
	    
	    return self::$copyEx;
	}
	
	
	/**
	 * ファイルアップロードクラスのインスタンスを取得する
	 * @return FileUploadK
	 */
	public static function factoryFileUploadK(){
	    if(empty(self::$fileUploadK)){
	        self::$fileUploadK = new FileUploadK();
		}
		
		return self::$fileUploadK;
	}
	
	/**
	 * テンプレートからファイルパスを組み立てる
	 * @param array $FILES $_FILES
	 * @param string $path_tmpl ファイルパステンプレート
	 * @param array $ent エンティティ
	 * @param string $field　file要素のname属性
	 * @param string $date パスに含める日時 ← 「Y-m-d H:i:s」型で指定 ← 省略した場合は現在日時になる。
	 * @return string ファイルパス
	 */
	public static  function makeFilePath(&$FILES, $path_tmpl, $ent, $field, $date=null){
		
		// $_FILESにアップロードデータがなければ、既存ファイルパスを返す
		if(empty($FILES[$field])){
			return $ent[$field];
		}
		
		$fp = $path_tmpl;
		
		if(empty($date)){
			$date = date('Y-m-d H:i:s');
		}
		$u = strtotime($date);
		
		// ファイル名を置換
		$fn = $FILES[$field]['name']; // ファイル名を取得
		
		// ファイル名が半角英数字でなければ、日時をファイル名にする。（日本語ファイル名は不可）
		if (!preg_match("/^[a-zA-Z0-9-_.]+$/", $fn)) {
			
			// 拡張子を取得
			$pi = pathinfo($fn);
			$ext = $pi['extension'];
			if(empty($ext)) $ext = 'png';
			$fn = date('Y-m-d_his',$u) . '.' . $ext;// 日時ファイル名の組み立て
		}
		
		$fp = str_replace('%fn', $fn, $fp);
		
		// フィールドを置換
		$fp = str_replace('%field', $field, $fp);
		
		if(strpos($fp, '%unique')){
			$unique = uniqid(rand(1, 1000)); // ユニーク値を取得
			$fp = str_replace('%unique', $unique, $fp);
		}
		
		// 日付が空なら現在日時をセットする
		$Y = date('Y',$u);
		$m = date('m',$u);
		$d = date('d',$u);
		$H = date('H',$u);
		$i = date('i',$u);
		$s = date('s',$u);
		
		$fp = str_replace('%Y', $Y, $fp);
		$fp = str_replace('%m', $m, $fp);
		$fp = str_replace('%d', $d, $fp);
		$fp = str_replace('%H', $H, $fp);
		$fp = str_replace('%i', $i, $fp);
		$fp = str_replace('%s', $s, $fp);
		
		return $fp;
		
	}
	
	/**
	 * CSRFトークンによるセキュリティチェック
	 * @return boolean true:無問題 , false:不正アクションを確認！
	 */
	public static function checkCsrfToken($page_code){
	    
	    // Ajaxによって送信されてきたCSRFトークンを取得。なければfalseを返す。
	    $csrf_token = null;
	    if(!empty($_POST['_token'])) $csrf_token = $_POST['_token'];
	    
	    if($csrf_token == null){
	        if(!empty($_POST['csrf_token'])) $csrf_token = $_POST['csrf_token'];
	    }
	    
	    if($csrf_token == null){
	        if(!empty($_GET['_token'])) $csrf_token = $_GET['_token'];
	    }
	    
	    if($csrf_token == null){
	        if(!empty($_GET['csrf_token'])) $csrf_token = $_GET['csrf_token'];
	    }
	    
	    if($csrf_token == null) return false;
	    
	    // セッションキーを組み立て
	    $ses_key = $page_code . '_csrf_token';
	    $ses_csrf_token = $_SESSION[$ses_key];
	    
	    if($csrf_token == $ses_csrf_token){
	        return true;
	    }
	    
	    return false;
	}
	
	
	/**
	 * CSRFトークンを取得
	 * セッションまわりの処理も行う。
	 * @return string CSRFトークン
	 */
	public static function getCsrfToken($page_code)
	{
	    
	    $ses_key = $page_code . '_csrf_token'; // セッションキーを組み立て
	    $csrf_token = self::random();
	    $_SESSION[$ses_key]  = $csrf_token;
	    
	    return $csrf_token;
	}
	
	
	/**
	 * ランダム文字列を作成
	 * @param number $length
	 * @return string
	 */
	public static function random($length = 8)
	{
	    return base_convert(mt_rand(pow(36, $length - 1), pow(36, $length) - 1), 10, 36);
	}
	
	
	/**
	 * ランダムハッシュコードを作成
	 * @param number $length
	 * @return string ハッシュコード
	 */
	public function randomHash($length = 8)
	{
	    $random =  substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, $length);
	    $hash = hash('sha256', MD5($random)); // ハッシュを作成
	    $hash = mb_substr($hash,0,$length);
	    
	    return $hash;
	}
	
	
	/**
	 * CrudBase設定データをhidden化して埋め込み
	 */
	public static function hiddenOfCrudBaseConfigJson(){
	    global $crudBaseConfig;
	    $json_str = json_encode($crudBaseConfig,JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS);
	    
	    echo "<input type='hidden' id='crudBaseConfig' value='{$json_str}' >";
	    
	}
	
	
	/**
	 * CrudBaseのインスタンスを取得する
	 * @return \CrudBase\CrudBase
	 */
	public static function getInstance(){
	    if (!isset(self::$singleton)) {
	        self::$singleton = new CrudBase();
	    }
	    return self::$singleton;
	}
	
	/**
	 * バージョンを表示
	 * @return string バージョン
	 */
	public static function version(){
	    var_dump(self::$version);
	    return self::$version;
	}

	
	/**
	 * SQLサニタイズ(※なるべくこの関数にたよらずプリペアド方式を用いること）
	 * @param string $text
	 * @return string SQLサニタイズ後のテキスト
	 */
	public function sqlSanitize($text) {
		$text = trim($text);
		
		// 文字列がUTF-8でない場合、UTF-8に変換する
		if(!mb_check_encoding($text, 'UTF-8')){
			$text = str_replace(['\\', '/', '\'', '"', '`',' OR '], '', $text);
			$text = mb_convert_encoding($text, 'UTF-8');
		}
		
		// SQLインジェクションのための特殊文字をエスケープする
		$search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a", "`");
		$replace = array("\\\\", "\\0", "\\n", "\\r", "\\'", "\\\"", "\\Z", "");
		
		$text = str_replace($search, $replace, $text);
		
		return $text;
	}
	
}