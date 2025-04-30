/**
 * 検索・日時リストギミック
 * @since 2023-8-24
 * @version 1.0.0
*/
class SearchDatetimeGimmick{
	
	/**
	* コンストラクタ
	* @param string parent_element_selector 親要素のセレクタ
	*  - ls_key ローカルストレージキー(省略可） 当クラスを一つの画面で複数生成する場合は必ずセットすること。
	*/
	constructor(parent_element_selector){
		
		this.jqParElm = jQuery(parent_element_selector);
		if(this.jqParElm[0] == null) throw new Error('システムエラー:230824A');

		this.jq_sdg_select = this.jqParElm.find('.sdg_select'); // セレクト要素
		this.jq_sdg_msg = this.jqParElm.find('.sdg_msg'); // メッセージ要素
		this.jq_sdg_value = this.jqParElm.find('.sdg_value'); // 値要素
		if(this.jq_sdg_select[0] == null) throw new Error('システムエラー:230824B');
		if(this.jq_sdg_msg[0] == null) throw new Error('システムエラー:230824C');
		if(this.jq_sdg_value[0] == null) throw new Error('システムエラー:230824D');

		// セレクト要素のチェンジイベント
		this.jq_sdg_select.change((evt) => {
			
			// セレクト要素から値を取得する
			let dt_value = this.jq_sdg_select.val();
			
			// メッセージを表示する
			this._showMessage(dt_value);
			
			// 値要素にセットする
			this.jq_sdg_value.val(dt_value);
			
		});

	}

	// メッセージを表示する
	_showMessage(dt_value){
		
		if(dt_value == null || dt_value == ''){
			this.jq_sdg_msg.html('');
		}else{
			this.jq_sdg_msg.html(`検索対象 ～ ${dt_value}`);
		}
	}
			
	
	
	
}