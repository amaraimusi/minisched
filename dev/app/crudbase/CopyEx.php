<?php
namespace App\CrudBase;

/**
 * 拡張ファイルコピー。
 * 日本語ファイル名のファイルコピーとディレクトリ作成コピーができる。
 *
 * ディレクトリ存在チェックメソッドを備える。
 * ディレクトリ内のファイルをすべて削除するメソッドを備える。
 *
 * @version 2.3
 * ★履歴
 * 2010/10/22	新規作成
 * 2015/8/6		リニューアル
 * 2015/8/10	dirClearメソッドを追加
 * 2016/10/27	copyにコピー成功可否レスポンスを追加する
 * 2017/2/21	パーミッションに対応
 *
 * @author uehara
 */
class CopyEx{

	public  function __construct(){
		
	}

	/**
	 * 拡張コピー　存在しないディテクトリも自動生成
	 * 日本語ファイルに対応
	 * @param string $sourceFn コピー元ファイル名
	 * @param string $copyFn コピー先ファイル名
	 * @param int $permission ディレクトリまたはファイルのパーミッション
	 * @return true:コピー成功  false:コピー失敗
	 */
	public function copy($sourceFn,$copyFn,$permission = 0777){

		$res = null;
		
		//フルファイル名からパスを取得する。
		$di=dirname($copyFn);

		//コピー先ファイル名とコピー元ファイル名が同名であれば、Nullを返して処理を終了
		if($sourceFn==$copyFn){
			return null;
		}

		//ディレクトリが存在するかチェック。
		if ($this->is_dir_ex($di)){

			//存在するならそのままコピー処理
			$sourceFn=mb_convert_encoding($sourceFn,'SJIS','UTF-8');
			$copyFn=mb_convert_encoding($copyFn,'SJIS','UTF-8');
			$res = @copy($sourceFn,$copyFn);
			if($res){
				chmod($copyFn,$permission);
			}

			
		}else{

			//存在しない場合。
			//パスを各ディレクトリに分解し、ディレクトリ配列をして取得する。
			$ary=explode('/', $di);
			//ディレクトリ配列の件数分以下の処理を繰り返す。
			$iniFlg=true;
			foreach ($ary as $key => $val){

				//作成したディレクトリが存在しない場合、ディレクトリを作成
				if ($iniFlg==true){
					$iniFlg=false;
					$dd=$val;
				}else{
					$dd.='/'.$val;
				}

				if (!($this->is_dir_ex($dd))){
					mkdir($dd,$permission);//ディレクトリを作成
					chmod($dd,$permission);
				}

			}

			$sourceFn=mb_convert_encoding($sourceFn,'SJIS','UTF-8');
			$copyFn=mb_convert_encoding($copyFn,'SJIS','UTF-8');
			$res = @copy($sourceFn,$copyFn);//ファイルをコピーする。
			chmod($copyFn,$permission);

		}
		
		return $res;
	}


	/**
	 * 日本語フォルダ名対応のディレクトリ存在チェック
	 * @param string $dn	ディレクトリ名
	 * @return boolean	true:存在	false:未存在
	 */
	public function is_dir_ex($dn){
		$dn=mb_convert_encoding($dn,'SJIS','UTF-8');
		if (is_dir($dn)){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * ディレクトリ内のファイルをまとめて削除する。
	 * @param string $dir_name 削除ファイル群のディレクトリ名
	 * @return boolean true
	 */
	public function dirClear($dir_name){
		//フォルダ内のファイルを列挙
		$files = scandir($dir_name);
		$files = array_filter($files, function ($file) {
			return !in_array($file, array('.', '..'));
		});

			foreach($files as $fn){
				$ffn=$dir_name.'/'.$fn;
				if($this->is_dir_ex($ffn)) continue;
				try {
					unlink($ffn);//削除
				} catch (Exception $e) {
					throw e;
				}
			}

			return true;
	}
	
	
	
	/**
	 * 指定したディレクトリを再帰的に削除するメソッド
	 *
	 * @param string $dir 削除対象のディレクトリのパス
	 * @throws Exception
	 */
	public function rmdirEx($dir)
	{
		if (!is_dir($dir)) {
			throw new Exception("$dir is not a directory");
		}
		
		$this->deleteDirectoryContents($dir);
		
		// 最後にディレクトリ自体を削除
		rmdir($dir);
	}
	
	/**
	 * ディレクトリの中身を再帰的に削除する
	 *
	 * @param string $dir
	 */
	private function deleteDirectoryContents($dir)
	{
		$items = scandir($dir);
		
		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}
			
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			
			if (is_dir($path)) {
				// サブディレクトリの場合、再帰的に削除
				$this->rmdirEx($path);
			} else {
				// パーミッションを変更してファイルを削除
				if (!is_writable($path)) {
					chmod($path, 0666);
				}
				unlink($path);
			}
		}
	}
	
	
	/**
	 * 再帰的にディレクトリをコピーするメソッド
	 *
	 * @param string $sourceDir コピー元のディレクトリパス
	 * @param string $destDir コピー先のディレクトリパス
	 * @return bool コピーが成功した場合は true、失敗した場合は false
	 */
	public function copyDirEx(string $sourceDir, string $destDir): bool
	{
		// コピー元のディレクトリが存在しない場合、falseを返す
		if (!is_dir($sourceDir)) {
			return false;
		}
		
		// コピー先のディレクトリが存在しない場合、作成する
		if (!is_dir($destDir)) {
			mkdir($destDir, 0755, true);
		}
		
		// ディレクトリハンドルを開く
		$dirHandle = opendir($sourceDir);
		if ($dirHandle === false) {
			return false;
		}
		
		// ディレクトリ内のファイルやフォルダをループ
		while (($file = readdir($dirHandle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			
			$sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $file;
			$destPath = $destDir . DIRECTORY_SEPARATOR . $file;
			
			if (is_dir($sourcePath)) {
				// 再帰的にディレクトリをコピー
				if (!$this->copyDirEx($sourcePath, $destPath)) {
					closedir($dirHandle);
					return false;
				}
			} else {
				// ファイルをコピー
				if (!copy($sourcePath, $destPath)) {
					closedir($dirHandle);
					return false;
				}
			}
		}
		
		// ディレクトリハンドルを閉じる
		closedir($dirHandle);
		return true;
	}

}