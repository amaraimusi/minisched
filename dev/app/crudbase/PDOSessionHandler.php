<?php 
namespace App\CrudBase;

/**
 * セッションをPDOでDB管理するためのクラス
 * @since 2023-9-2
 * @version 1.0.0
 * @license MIT
 * 
 * 【使い方】
 * まずはDBにsessionsテーブルを作成する。以下はsessionsテーブルを生成するSQL。
	CREATE TABLE sessions (
			id VARCHAR(128) NOT NULL PRIMARY KEY,
			data TEXT,
			timestamp TIMESTAMP NOT NULL
			);
			
 【実装方法】
 	session_start()関数を実行する前に、当クラスの設定処理を記述する。
 
	$pdo = new PDO("mysql:host=localhost;dbname=mydatabase", "username", "password");
	
	$handler = new PDOSessionHandler($pdo);
	
	session_set_save_handler(
	    [$handler, 'open'],
	    [$handler, 'close'],
	    [$handler, 'read'],
	    [$handler, 'write'],
	    [$handler, 'destroy'],
	    [$handler, 'gc']
	);
	
	// セッションを開始する
	session_start();
			
*/
class PDOSessionHandler
{
	private $pdo;
	
	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}
	
	public function open($savePath, $sessionName)
	{
		return true;
	}
	
	public function close()
	{
		return true;
	}
	
	public function read($id)
	{
		$stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			return $row['data'];
		}
		
		return '';
	}
	
	public function write($id, $data)
	{
		$timestamp = time();
		$stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (:id, :data, :timestamp)");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':data', $data);
		$stmt->bindParam(':timestamp', $timestamp, \PDO::PARAM_INT);
		return $stmt->execute();
	}
	
	public function destroy($id)
	{
		$stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
		$stmt->bindParam(':id', $id);
		return $stmt->execute();
	}
	
	public function gc($maxLifetime)
	{
		$old = time() - $maxLifetime;
		$stmt = $this->pdo->prepare("DELETE FROM sessions WHERE timestamp < :old");
		$stmt->bindParam(':old', $old, \PDO::PARAM_INT);
		return $stmt->execute();
	}
}