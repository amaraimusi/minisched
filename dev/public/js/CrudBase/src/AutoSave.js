/**
 * 自動保存機能
 * @version 2.0.0
 * @date 2018-3-2 | 2022-7-9
 */
class AutoSave{
	
	
	/**
	 * コンストラクタ
	 * @param string msg_xid 自動保存メッセージ要素のID属性
	 * @param string csrf_token CSRFトークン(Ajaxのセキュリティ)
	 */
	constructor(msg_xid, csrf_token){

		this.msgElm = jQuery('#' + msg_xid); // 自動保存メッセージ要素
		this.data; // 保存するデータを宣言
		this.set_timeout_hdl; // setTimeout関数のハンドラ
		this.csrf_token = csrf_token;
	}
	
	/**
	 * 自動保存の依頼をする
	 *
	 * @param []　data 保存対象データ
	 * @param string　auto_save_url 自動保存先URL
	 * @param function afterCallBack　自動保存後に実行するコールバック 
	 * @param {}　option オプション
	 *   - int interval 自動保存依頼から実際に自動保存するまでの間隔時間
	 */
	saveRequest(data, auto_save_url, afterCallBack, option){
		
		this.data = data;
		this.auto_save_url = auto_save_url;
		this.afterCallBack = afterCallBack;

		// オプションの初期化
		if(option==null) option = {};
		if(option['interval']==null) option['interval'] = 3000;
		this.option = option;
		
		// setTimeoutの処理を一旦キャンセルする。
		if(this.set_timeout_hdl != null){
			clearTimeout(this.set_timeout_hdl);
		}
		
		// バックグラウンドで自動保存を実行する。(数秒後の遊びを設ける）
		this.set_timeout_hdl = setTimeout(()=>{
			this._autoSave(this.data);// 自動保存
		}, option.interval);

	}
	
	/**
	 * 自動保存処理
	 * 
	 * @param data 保存対象データ   省略した場合、HTMLテーブルのデータを保存する。
	 */
	_autoSave(data){
	
		this.msgElm.html('保存中...');

		data = this._escapeForAjax(data); // Ajax送信データ用エスケープ。実体参照（&lt; &gt; &amp; &）を記号に戻す。
		let json_str = JSON.stringify(data);//データをJSON文字列にする。
		let url = this.auto_save_url; // 自動保存先URL
		
		let fd = new FormData(); // 送信フォームデータ
		fd.append( "key1", json_str );
		
		// CSRFトークンを送信フォームデータにセットする。
		fd.append( "_token", this.csrf_token );
		
		// AJAX
		jQuery.ajax({
			type: "post",
			url: url,
			data: fd,
			cache: false,
			dataType: "text",
			processData: false,
			contentType: false,
		})
		.done((str_json, type) => {
			let res;
			try{
				res =jQuery.parseJSON(str_json);
				this.msgElm.html('');

			}catch(e){
				this.msgElm.html('自動保存のエラー1');
				jQuery("#err").html(str_json);
				return;
			}
			
			// 自動保存後コールバックを実行する
			if(this.afterCallBack){
				this.afterCallBack();
			}
			
		})
		.fail((jqXHR, statusText, errorThrown) => {
			this.msgElm.html('自動保存のエラー');
			console.log(jqXHR);
			jQuery('#err').html(jqXHR.responseText);
		});
		
	}
	
	
	/**
	 * Ajax送信データ用エスケープ。実体参照（&lt; &gt; &amp; &）を記号に戻す。
	 * 
	 * @param any data エスケープ対象 :文字列、オブジェクト、配列を指定可
	 * @returns エスケープ後
	 */
	_escapeForAjax(data){
		if (typeof data == 'string'){
			if ( data.indexOf('&') != -1) {
				data = data.replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
				return encodeURIComponent(data);
			}else{
				return data;
			}
		}else if (typeof data == 'object'){
			for(var i in data){
				data[i] = this._escapeForAjax(data[i]);
			}
			return data;
		}else{
			return data;
		}
	}
	
}