<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use App\Models\Service;
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
	
	
	public function code_gete(){
		
		// 今日の日付
		$today = new DateTime();
		
		// 今週の月曜日を取得
		$monday = clone $today;
		$monday->modify('this week monday');
		
		// 今週の月曜日から4週間後の月曜日を取得
		$after4Weeks = clone $monday;
		$after4Weeks->modify('+4 weeks');
		
		// 指定日付範囲の奉仕データを取得する
		$serviceModel = new Service();
		$services = $serviceModel->fetchWithTemplateByRange(
				$monday->format('Y-m-d'),
				$after4Weeks->format('Y-m-d')
				);
		
		// $servicesを連想配列に変換する。
		$serviceMap = [];
		foreach ($services as $srv) {
			$templateId = $srv->service_template_id;
			$weekDate = $srv->week_start_date;
			
			$serviceMap[$templateId][$weekDate] = (array)$srv;
		}
		
		// service_templatesテーブルからservice_nameのリストを取得（delete_flg = 0 限定）
		$serviceNameList = $serviceModel->getServiceNameList();
		
		// 表データの初期化（奉仕 × 4週分）
		$scheduleMatrix = [];
		$weekDates = [];
		
		for ($i = 0; $i < 4; $i++) {
			$weekDates[] = (clone $monday)->modify("+{$i} weeks")->format('Y-m-d');
		}
		
		foreach ($serviceNameList as $id => $serviceName) {
			$row = [
					'service_template_id' => $id,
					'service_name' => $serviceName,
			];
			foreach ($weekDates as $date) {
				$row[$date] = []; // 詳細情報は空で初期化
			}
			$scheduleMatrix[] = $row;
		}
		
		
		// TODO :: $serviceMap のデータを $scheduleMatrix にマッピングしてください。
		foreach ($services as $srv) {
			$templateId = $srv->service_template_id;
			$weekDate = $srv->week_start_date;
			
			// 行を探す（縦：service_template_id）
			foreach ($scheduleMatrix as &$row) {
				if ($row['service_template_id'] == $templateId) {
					// 列が存在するか確認（横：week_start_date）
					if (isset($row[$weekDate])) {
						$row[$weekDate] = (array)$srv; // サービスデータを格納（オブジェクト→配列）
					}
					break;
				}
			}
			unset($row); // foreach の参照を解除
		}
		
		dump($scheduleMatrix);//■■■□□□■■■□□□)
		
		$res = [
				'services' => $services,
				'service_name_list' => $serviceNameList,
				'schedule_matrix' => $scheduleMatrix,
				'week_dates' => $weekDates,
		];
		
		return json_encode($res);
	}
	
	
	


}