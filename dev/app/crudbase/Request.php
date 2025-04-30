<?php 
namespace App\CrudBase;

/**
 * リクエストクラス
 * 
 * @note
 * LaravelのRequestクラスを模倣したシンプル版クラス。
 * フレームワークなしのプロジェクトで利用するとよい。
 * 
 * @since 2023-9-2
 * @version 1.0.0
 * @licencse MIT
 */
class Request {
	private $data = [];
	
	public function __construct() {
		$this->data = $_GET + $_POST;  // 簡単のため、GETとPOSTデータを単純にマージしています。
	}
	
	public function __get($name) {
		return $this->data[$name] ?? null;
	}
	
	public function all(){
		return $this->data;
	}
	
}

