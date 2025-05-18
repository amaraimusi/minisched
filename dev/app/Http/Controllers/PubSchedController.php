<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Models\PubSched;

/**
 * 公開スケジュール画面
 * @since 2025-5-5
 * @version 1.0.0
 * @author amaraimusi
 *
 */
class PubSchedController extends CrudBaseController{
	
	/**
	 * indexページのアクション
	 *
	 * @param  Request  $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		
		// バージョンを .env から取得
		$app_version = env('APP_VERSION', '1.0.0');
		
		// TODO::データ取得処理
		$data = [];
		

		return view('pub_sched.index', [
				'app_version' => $app_version,
				'data' => $data,
				
	   ]);
		
	}
	
	
	/**
	 * パスコードを確認後画面を表示
	 */
	public function code_gete(){
		

		
		$res = ['hogehoge'=>'テスト'];
		$json_str = json_encode($res);//JSONに変換
		
		return $json_str;
	}
	
	
	


}