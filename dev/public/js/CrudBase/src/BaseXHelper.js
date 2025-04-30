/**
 * 基本ヘルパー
 * @since 2022-8-18
 * @version 1.0.0
*/
class BaseXHelper{
	
	/**
	* コンストラクタ
	*/
	constructor(){

	}
	
	/**
	 * ノート詳細を開く
	 * @param btnElm 詳細ボタン要素
	 */
	openNoteDetail(btnElm,field){
		
		if(!(btnElm instanceof jQuery)){
			btnElm = jQuery(btnElm);
		}
		
		if(field == null) field = 'note';
		
		// 親要素であるTD要素を取得する
		var td = btnElm.parents('td');
		
		// フィールド要素を取得する
		var fieldElm = td.find("[name='" + field + "']");
		
		// 短文要素を取得する
		var shortElm = td.find('.' + field);
		
		// ノート詳細要素を作成および取得する
		var maked = 1; // 作成済みフラグ  0:未作成 , 1:作成済み
		var noteDetailElm = td.find('.note_detail');
		if(!noteDetailElm[0]){
			maked = 0;
			var note_detail_html = `<div class='note_detail' data-field='${field}'></div>`;
			td.append(note_detail_html);
			noteDetailElm = td.find('.note_detail');
		}
		
		// 短文要素が隠れている場合（ノート詳細が開かれている状態である場合）
		if(shortElm.css('display') == 'none'){
			shortElm.show();
			noteDetailElm.hide();
			btnElm.val('...');
			return;
		}
		
		// ノート詳細が作成済みである場合
		if(maked){
			shortElm.hide();
			noteDetailElm.show();
			btnElm.val('閉じる');
			return;
		}

		// ノートのフルテキストを取得する
		var text1 = td.find("[name='" + field + "']").val();
		if(text1 == null || text1 == '') return;
		
		// XSSサニタイズ
		text1 = this._xssSanitaizeEncode(text1);
		
		// 改行コードをBRタグに変換する
		text1 = text1.replace(/\r\n|\n\r|\r|\n/g,'<br>');
		
		// ノート詳細にテキストをセットする
		noteDetailElm.html(text1);
		
		// 短文要素を隠し、詳細ボタン名も変更する
		shortElm.hide();
		btnElm.val('閉じる');
		
	}

	/**
	 * XSSサニタイズ
	 * 
	 * @note
	 * 「<」と「>」のみサニタイズする
	 * 
	 * @param any data サニタイズ対象データ | 値および配列を指定
	 * @returns サニタイズ後のデータ
	 */
	_xssSanitaizeEncode(data){
		if(typeof data == 'object'){
			for(var i in data){
				data[i] = this._xssSanitaizeEncode(data[i]);
			}
			return data;
		}
		
		else if(typeof data == 'string'){
			return data.replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}
		
		else{
			return data;
		}
	}
	
	
	/**
	 * XSSサニタイズ・デコード
	 * 
	 * @note
	 * 「<」と「>」のサニタイズ化を元に戻す
	 * 
	 * @param any data サニタイズ対象データ | 値および配列を指定
	 * @returns サニタイズ後のデータ
	 */
	_xssSanitaizeDecode(data){
		if(typeof data == 'object'){
			for(var i in data){
				data[i] = this._xssSanitaizeDecode(data[i]);
			}
			return data;
		}
		
		else if(typeof data == 'string'){
			return data.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		}
		
		else{
			return data;
		}
	}
	
	
}