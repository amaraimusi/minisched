<?php
//require_once 'crud_base_config.php';
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Consts;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use CrudBase\CrudBase;
use App\Consts\ConstCrudBase;

/**
 * 基本コントローラ
 *
 */
class CrudBaseController extends Controller{

	/**
	 * デフォルトページ情報を取得する
	 * @param [] $crudBaseData
	 * @return [] デフォルトページ情報
	 */
	private function getDefPages(&$crudBaseData){
		
		$defPages = [];
		if(!empty($crudBaseData['defPages'])){
			$defPages = $crudBaseData['defPages'];
		}
		
		if(empty($defPages['page_no'])) $defPages['page_no'] = 0;
		if(empty($defPages['row_limit'])) $defPages['row_limit'] = 50;
		
		$def_sort_feild =  $crudBaseData['def_sort_feild']; // デフォルトソートフィールド
		$def_sort_type =  $crudBaseData['def_sort_type']; // デフォルトソートタイプ 0:昇順 1:降順
		if(empty($defPages['sort_field'])) $defPages['sort_field'] = $def_sort_feild;
		if(empty($defPages['sort_desc'])) $defPages['sort_desc'] = $def_sort_type;
		
		return $defPages;
	}
	
	
	/**
	 * ユーザー情報を取得する
	 *
	 * @return [] <mixied> ユーザー情報
	 */
	public function getUserInfo($param=[]){

		// ユーザー情報の構造
		$userInfo = [
			'id'=> 0,
			'user_id'=> 0,
			'name' => '',
			'username' => '',
			'user_name' => '',
			'update_user' => '',
			'ip_addr' => '',
			'user_agent' => '',
			'email'=>'',
			'role' => 'oparator',
			'delete_flg' => 0,
			'nickname' => '',
			'authority_wamei'=>'',
			'authority_name'=>'',
			'authority_level'=>0, // 権限レベル(権限が強いほど大きな数値）
		];
		
		if(\Auth::id()){// idは未ログインである場合、nullになる。
			$userInfo['id'] = \Auth::id(); // ユーザーID
			$userInfo['user_id'] = $userInfo['id'];
			$userInfo['name'] = \Auth::user()->name; // ユーザー名
			$userInfo['username'] = $userInfo['name'] ;
			$userInfo['user_name'] = $userInfo['name'];
			$userInfo['update_user'] = $userInfo['name'];
			$userInfo['email'] = \Auth::user()->email; // メールアドレス
			$userInfo['role'] = \Auth::user()->role; // 権限
			$userInfo['nickname'] = \Auth::user()->nickname ?? $userInfo['name']; // ニックネーム
			
		}
		
		$userInfo['ip_addr'] = $_SERVER["REMOTE_ADDR"];// IPアドレス
		$userInfo['user_agent'] = $_SERVER['HTTP_USER_AGENT']; // ユーザーエージェント
		
		if(!empty($userInfo['id'])){
			$users = \DB::select("SELECT * FROM users WHERE id={$userInfo['id']}");
			$users = $users[0];
			$userInfo['role'] = $users->role;
			$userInfo['delete_flg'] = $users->delete_flg;
			
		}
		
		// 権限が空であるならオペレータ扱いにする
		if(empty($userInfo['role'])){
			$userInfo['role'] = 'oparator';
		}
		
		// 権限まわり
		$role = $userInfo['role'];
 		$userInfo['authority'] = $this->getAuthority($role);
 		$userInfo['authority_wamei'] = $userInfo['authority']['wamei'];
 		$userInfo['authority_name'] = $userInfo['authority']['name'];
 		$userInfo['authority_level'] = $userInfo['authority']['level'];
 		
		return $userInfo;
	}
	
	
	/**
	 *  レビューモード用ユーザー情報を取得
	 * @param [] $userInfo
	 * @return [] $userInfo
	 */
	public function getUserInfoForReviewMode(){
		
		$userInfo = $this->getUserInfo();
		
		$userInfo['id'] = -1;
		$userInfo['user_id'] = $userInfo['id'];
		$userInfo['update_user'] = 'dummy';
		$userInfo['username'] = $userInfo['update_user'];
		$userInfo['update_user'] = $userInfo['update_user'];
		$userInfo['ip_addr'] = 'dummy_ip';
		$userInfo['user_agent'] = 'dummy_user_agent';
		$userInfo['email'] = 'dummy@example.com';
		$userInfo['role'] = 'admin';
		$userInfo['delete_flg'] = 0;
		$userInfo['nickname'] = '見本ユーザー';
		$userInfo['review_mode'] = 1; // 見本モードON;
		
		$userInfo['authority'] = $this->getAuthority($role);
		$userInfo['authority_wamei'] = $userInfo['authority']['wamei'];
		$userInfo['authority_name'] = $userInfo['authority']['name'];
		$userInfo['authority_level'] = $userInfo['authority']['level'];
		
		return $userInfo;
	}
	
	/**
	 * 権限情報を取得する
	 * @return [] 権限情報
	 */
	public function getAuthorityInfo(){
		return \App\Consts\ConstCrudBase::AUTHORITY_INFO;
	}
	

	/**
	 * 権限に紐づく権限エンティティを取得する
	 * @param string $role 権限
	 * @return array 権限エンティティ
	 */
	private function getAuthority($role){

		// 権限情報を取得する
		$authorityData = $this->getAuthorityInfo();
		
		$authority = [];
		if(!empty($authorityData[$role])){
			$authority = $authorityData[$role];
		}
		
		return $authority;
	}
	

	/**
	 * ユーザーをアプリケーションからログアウトさせる
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request)
	{
		\Auth::logout();
		
		$request->session()->invalidate();
		
		$request->session()->regenerateToken();
		
		return redirect('/');
	}
	
	
	/**
	 * 新バージョン判定
	 * 
	 * 	旧画面バージョンと現在の画面バージョンが一致するなら新バージョンフラグをOFFにする。
	 * 	旧画面バージョンと現在の画面バージョンが不一致なら新バージョンフラグをONにする。
	 * @param [] $sesSearches セッション検索データ
	 * @param string $this_page_version 画面バージョン
	 * @return int 新バージョンフラグ  0:バージョン変更なし（通常）, 1:新しいバージョン
	 */
	public function judgeNewVersion($sesSearches, $this_page_version){
		
		$old_page_version = $sesSearches['this_page_version'] ?? '';
		$new_version = 0;
		if($old_page_version != $this_page_version){
			$new_version = 1;
		}
		return $new_version;
	}
	
	/**
	 * データをCSVファイルとしてダウンロードする。(UTF-8）
	 *
	 * @param string $csv_file CSVファイル名
	 * @param array  $data データ		エンティティ配列型
	 * @param bool $bom_flg BOMフラグ  0:BOMなし（デフォ）,  1:BOM有
	 */
	protected function csvOutput($csv_file, $data, $bom_flg=0){
		
		$buf = "";
		
		// BOM付きutf-8のファイルである場合
		if(!empty($bom_flg)){
			$buf = "¥xEF¥xBB¥xBF";
		}
		
		// CSVデータの作成
		if(!empty($data)){
			$i=0;
			foreach($data as $ent){
				foreach($ent as $v){
					$cell[$i][] = $v;
				}
				$buf .= implode(",",$cell[$i])."\n";
				$i++;
			}
		}
		
		// CSVファイルのヘッダーを書き出す
		header ("Content-disposition: attachment; filename=" . $csv_file);
		header ("Content-type: application/octet-stream; name=" . $csv_file);
		
		print($buf); // CSVデータの書き出し
		
	}
	
	
	/**
	 * パス情報を取得する
	 * @return [] パス情報
	 */
	protected function getPaths(){
		$public_url = url('/');
		$current_path = url()->current();
		$current_url_full = url()->full();
		
		return [
				'public_url' => $public_url,
				'current_path' => $current_path,
				'current_url_full' => $current_url_full,
		];
	}
	
	
	/**
	 * テンプレートからファイルパスを組み立てる
	 * @param array $FILES $_FILES
	 * @param string $path_tmpl ファイルパステンプレート
	 * @param array $ent エンティティ
	 * @param string $field
	 * @param string $date
	 * @return string ファイルパス
	 */
	protected function makeFilePath(&$FILES, $path_tmpl, $ent, $field, $date=null){
		
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
	 * DBテーブルからDBフィールドデータを取得します。
	 * @param string $tbl_name DBテーブル名
	 * @return [] 各フィールドの詳細情報
	 */
	public function getDbFieldData($tbl_name){
		
		$dbFieldData0 = $this->getFieldDataFromDb($tbl_name);
		$dbFieldData = [];
		foreach($dbFieldData0 as $ent){
			$field = $ent->Field;
			$dbFieldData[$field] = (array)$ent;
		}
		
		// 型長とデータ型を取得する
		foreach($dbFieldData as &$fEnt){
			if(empty($fEnt['Type'])) continue;
			$data_type = $fEnt['Type'];
			
			// 型長を取得する
			$matches = null;
			preg_match('/\d+/', $data_type, $matches);
			$fEnt['long'] = $matches[0] ?? null;
			
			// データ型を取得する
			$fEnt['type'] = preg_replace('/\([^)]+\)/', '', $data_type); // カッコとカッコ内の文字列を削除した文字列を取得する
			
		}
		unset($fEnt);
		
		return $dbFieldData;
	}
	
	/**
	 * // 値が空であればデフォルトをセットします。
	 * @param [] $ent エンティティ
	 * @param [] $dbFieldData DBフィールドデータ→getDbFieldDataメソッドで取得したフィールドデータ
	 */
	public function setDefalutToEmpty($ent, $dbFieldData){
		foreach($ent as $field=>$value){
			if(empty($dbFieldData[$field])) continue;
			$fEnt = $dbFieldData[$field];
			$type = $fEnt['type'];
			$default = $fEnt['Default'];
			if($type == 'int' || $type="float" || $type='double'){
				if(empty($value)){
					$ent[$field] = $default;
				}
			}
			
			if($type == 'date'){
				if($value=='0000-00-00' || $value == '0000/00/00'){
					$ent[$field] = $default;
				}
			}
		}
		
		return $ent;
	}
	
	
	/**
	 * DBテーブルから各フィールドの詳細情報を取得します。
	 * @param string $tbl_name DBテーブル名
	 * @return [] 各フィールドの詳細情報
	 */
	private function getFieldDataFromDb($tbl_name){
		$sql="SHOW FULL COLUMNS FROM {$tbl_name}";
		$res = DB::select($sql);
		
		return $res;
	}

	/**
	 * 次のソート番号を取得する
	 * @param string $tbl_name DBテーブル名
	 * @param string $order 方向タイプ asc:昇順用（最大数値）, desc:降順用（最小数値）
	 * @return int ソート番号
	 */
	public function getNextSortNo($tbl_name, $order = 'asc'){
		
		$sort_no = 0;
		
		if($order == 'asc'){
			
			$sql="SELECT  MAX(sort_no) AS next_sort_no FROM {$tbl_name};";
			$res = DB::select($sql);
			
			if($res){
				$sort_no = $res[0]->next_sort_no;
				$sort_no++;
			}
		}else{
			$sql="SELECT  MIN(sort_no) AS next_sort_no FROM {$tbl_name};";
			$res = DB::select($sql);
			
			if($res){
				$sort_no = $res[0]->next_sort_no;
				$sort_no--;
			}
		}
		
		return $sort_no;
	}
	
	

}