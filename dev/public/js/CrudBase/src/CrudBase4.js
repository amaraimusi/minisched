/**
 * CRUD支援クラス | CrudBase.js
 * 
 * @note
 * 当クラスはSPA型のCRUDを補助的にサポートするのが目的になります。
 * バージョン3までのCrudBase.jsにはブラックボックス化している部分が多く、保守性の問題がありました。
 * 現バージョンであるバージョン4からは保守性の問題を解決するため、よりシンプル化しています。
 * 他のJavaScriptライブラリとの競合問題を考え、ベースとなるライブラリはVue.jsではなくjQueryを採用しています。
 * 
 * @license MIT
 * @since 2016-9-21 | 2024-9-13
 * @version 4.0.6
 * @histroy
 * 2024-9-13 v4.0.6 入力フォーム内のテキストエリアの高さを入力文字に合わせて自動フィットするメソッドを作成
 * 2024-4-17 v4.0.0 保守性の問題解決のため、大幅なリニューアルをする。
 * 2019-6-28 v2.8.3 CSVフィールドデータ補助クラス | CsvFieldDataSupport.js
 * 2018-10-21 v2.8.0 ボタンサイズ変更機能にボタン表示切替機能を追加
 * 2018-10-21 v2.6.0 フォームをアコーディオン形式にする。
 * 2018-10-2 v2.5.0 フォームドラッグとリサイズ
 * 2018-9-18 v2.4.4 フォームフィット機能を追加
 * v2.0 CrudBase.jsに名称変更、およびES6に対応（IE11は非対応）
 * v1.7 WordPressに対応
 * 2016-9-21 v1.0.0
 * 
 */
class CrudBase4{
	
	/**
	* コンストラクタ
	* @param {} crudBaseData 
	* @param {} options オプションパラメータ　←省略可能
	*     - string main_tbl_slt メインテーブル一覧のセレクタ
	*     - string form_slt 入力フォームのセレクタ
	*     - string err_slt エラー表示場所のセレクタ
	*     - string create_tr_place	新規入力追加場所フラグ 0:末尾(デフォルト） , 1:先頭
	*/
	constructor(crudBaseData, options){
		
        this.crudBaseData = crudBaseData;
		
		if (options == null) options = {};
		//if(options.main_slt == null) options.mainl_slt= 'main'; // メイン区分 ←入力フォームが表示されている時に隠す範囲のセレクタ。
		if(options.main_tbl_slt == null) options.main_tbll_slt= '#main_tbl'; // メイン一覧テーブル ←メイン一覧である<table>のセレクタ。
		if(options.form_slt == null) options.forml_slt= '#form_spa'; // 入力フォーム ← SPA型・入力フォーム。入力フォーム一つに、新規入力モード、編集モード、複製モードが含まれる。
		if(options.err_slt == null) options.err_slt= '#err';
		if(options.create_tr_place == null) options.create_tr_place= 0; // 新規入力追加場所フラグ 0:末尾(デフォルト） , 1:先頭

		this.options = options;
		
		this.jqMainTbl = jQuery(options.main_tbl_slt); // メイン一覧テーブル
		this.jqForm = jQuery(options.form_slt); // 入力フォーム
		this.jqErr = jQuery(options.err_slt); // エラー表示要素

		
    }
    
	
	/**
	* フィールドデータに列インデックス情報をセットします。
	* @param {} fieldData フィールドデータ
	* @return {} フィールドデータ
	*/
    setColumnIndex(fieldData){
        if(fieldData==null) fieldData = this.crudBaseData.fieldData;
        
		let clmIndexList = this.getColumnIndexs(); // 列インデックス情報を取得します。
		for(let field in clmIndexList){
			let clm_index = clmIndexList[field];
			if(fieldData[field]){
				fieldData[field]['clm_index'] = clm_index;
			}
		}
		
		return fieldData;

    }
	
	/**
	* 列インデックス情報を取得します。
	* @return {} 列インデックス情報:キーはフィールド、値は列番号
	*/
	getColumnIndexs(){
		let clmIndexs = {}; // 列インデックス情報
		
		let ths = this.jqMainTbl.find('thead th');
		ths.each((i, th)=>{
			let jqTh = jQuery(th);
			let field = jqTh.attr('data-field');
			if(field != null){
				clmIndexs[field] = i;
			}
		});
		
		return clmIndexs;
		
	}
	
	
	/**
	* 入力フォームのtag名やtype名などをフィールドデータにセットします。
	* @param {} fieldData フィールドデータ
	* @return {} フィールドデータ
	*/
	setFormInfoToFileData(fieldData){
		for(let field in fieldData){
			let fildEnt = fieldData[field];
			fildEnt['form_tag'] = null;
			fildEnt['form_type'] = null;
			fildEnt['form_valid_ext'] = null;
			
			let jqInp = this._getInpFromForm(field); // フォームから入力要素を取得する
			
			if(jqInp[0] == null) continue;
			
			// 入力要素のタグ名を取得し、フィールドデータにセットします。
			let form_tag = jqInp.get(0).tagName; 
			form_tag = form_tag.toLowerCase(); // 小文字化
			fildEnt['form_tag'] = form_tag;
			
			// 入力要素のtype属性を取得して、フィールドデータにセットします。
			let form_type = jqInp.attr('type'); // type属性を取得
			if(form_type != null){
				form_type = form_type.toLowerCase();
				fildEnt['form_type'] = form_type;
			}
			
			// type属性がfile系なら、一般的によく使われる拡張子群を表すコードであるoften_useを指定します。
			if(form_type=='file'){
				fildEnt['form_valid_ext'] = 'often_use';
			}
			
		}
		
		return fieldData;
	}
	

	/**
	* ファイルアップロード要素にカスタマイズを施します。
	* @param {} fieldData フィールドデータ
	* @return ｛｝ FileUploadKオブジェクトの連想配列
	*/
	customizeFileUpload(fieldData){
		this.fileUploadKList = {};
		
		for(let field in fieldData){
			let fieldEnt = fieldData[field];
			if(fieldEnt.form_type != 'file') continue;
			
			let fileUploadK = new FileUploadK();
			fileUploadK.addEvent(field, {'valid_ext':fieldEnt.form_valid_ext});

			this.fileUploadKList[field] = fileUploadK;
			
		}
		
		return this.fileUploadKList;
	}

	/**
	* FileUploadKオブジェクトの連想配列を取得する。連想配列のキーはフィールド。
	* @return {} FileUploadKオブジェクトの連想配列
	*/
	getFileUploadKList(){
		return this.fileUploadKList;
	}
	
	
	/**
	* 現在のボタンの位置から行インデックスを取得します。
	* @param object btn ボタン要素 ← jQueryオブジェクトも指定化
	* @return int row_index 行インデックス
	*/
	getRowIndexFromButtonPosition(btn){
		let jqBtn = null;
		if (btn instanceof jQuery) {
			jqBtn = btn;
		}else{
			jqBtn = jQuery(btn);
		}
		
		let tr = jqBtn.parents('tr');
		let row_index = tr.index();

		return row_index;
	}
	
	
	/**
	* メイン一覧テーブルの行インデックスに紐づく行からエンティティを取得する。→crudDataBaseの一覧データにマージしてから取得
	* @param int row_index 行インデックス
	* @return {} エンティティ
	*/
	getEntityByRowIndex(row_index){

		// メイン一覧テーブルの行インデックスに紐づく行からエンティティを取得する。
		let rowEnt = this._getEntityByRowIndex(row_index);
		
		// CrudBaseDataからidに紐づくエンティティを取得する
		let ent = this.getEntityFromCrudBaseData(rowEnt.id);

		// 行要素から取得したエンティティを一覧データのエンティティにマージする。
		for(let field in rowEnt){
			ent[field] = rowEnt[field];
		}
		
		// CrudBaseDataへエンティティをセットする。
		this.setEntityToCrudBaseData(ent);

		return ent;

	}
	
	
	/**
	* メイン一覧テーブルの行インデックスに紐づく行からエンティティを取得する
	* @param int row_index 行インデックス
	* @return {} エンティティ
	*/
	_getEntityByRowIndex(row_index){
		
		let ent = {}; // エンティティ
		
		// メイン一覧テーブルから行要素を取得する
		let tr = this.jqMainTbl.find('tr').eq(row_index + 1);
		
		let tds = tr.find('td'); // セル要素のリストを取得
		
		tds.each((clm_index,elm) => {

			// フィールドデータから列インデックスに紐づくフィールドエンティティを取得する。
			let fieldEnt = this._getFieldEntByClmIndex(clm_index);
			if(fieldEnt == null) return;
			
			let value = null;
			let td = $(elm);
			
			let origElm = td.find('.js_original_value');
			if(origElm[0]){
				let tag_name = origElm.get(0).tagName;
				tag_name = tag_name.toLowerCase();
				if(tag_name == 'input' || tag_name == 'select'){
					value = origElm.val();
				}
				else{
					value = origElm.html();
					value = value.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g,''); // 文字列からタグを除去
				}
			}else{
				value = td.html();
				value = value.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g,''); // 文字列からタグを除去
			}

			ent[fieldEnt.Field] = value; // エンティティへtd要素内から取得した値をセットする。

		});


		return ent;
		
		
	}
	

	/**
	* CrudBaseDataからidに紐づくエンティティを取得する
	* @param int id ID
	* @return {} エンティティ
	*/
	getEntityFromCrudBaseData(id){
		let data = this._getData();

		for(let i in data){
			let ent = data[i];
			if(ent.id == id){
				return ent;
			}
		}
		
		return null;
		
	}
	

	/**
	* CrudBaseDataへエンティティをセットする
	* @param {} pEnt エンティティ
	*/
	setEntityToCrudBaseData(pEnt){
		
		let data = this._getData();

		for(let i in data){
			let ent = data[i];
			if(ent.id == pEnt.id){
				for(let field in pEnt){
					ent[field] = pEnt[field];
				}
				return;
			}
		}
	}
		
	
	/**
	* フィールドデータから列インデックスに紐づくフィールドエンティティを取得する。
	* @param int clm_index 列インデックス
	* @return {} フィールドエンティティ
	*/
	_getFieldEntByClmIndex(clm_index){
		
		let fieldEnt = null; // フィールドエンティティ
		let fieldData = this.crudBaseData.fieldData;
		
		for(let field in fieldData){
			let fieldEnt = fieldData[field]; // フィールドエンティティ
			if(fieldEnt.clm_index == clm_index){
				return fieldEnt;
			}
		}
		
		return null;
	}
	
	
	/**
	* デフォルトエンティティを取得する
	* @return {} デフォルトエンティティ
	*/
	getDefaultEntity(){
		let ent = {};
		let fieldData = this.crudBaseData.fieldData;
		for(let field in fieldData){
			let fieldEnt = fieldData[field];
			ent[field] = fieldEnt.Default;
		}
		return ent;
	}
	
	
	/**
	* 入力フォームにエンティティを反映する
	* @param {} ent エンティティ
	* @param int row_index 行インデックス ← メイン一覧テーブルの行番
	* @param string inp_mode 入力モード: create:新規入力モード, edit:編集モード, copy:複製モード
	*/
	setEntToForm(ent, row_index, inp_mode){

		for(let field in ent){
			let value = ent[field];
			
			if(field == 'id'){
				if(inp_mode == 'create' || inp_mode == 'copy'){
					value = null;
				}
			}
			
			let jqInp = this._getInpFromForm(field); // フォームから入力要素を取得する
			if(jqInp[0] != null){
				this.setValueToElement(jqInp, field, value); // 様々なタイプの要素へ値をセットする
			}
			
			// data-display属性を持つ要素のインナーへ表示する。
			let jqDisplay = this._getDisplayFromForm(field); // フォームから表示要素を取得する
			if(jqDisplay[0] != null){
				let value2 = this._xssSanitize(value);
				jqDisplay.html(value2);
			}
		}
		
		this.row_index = row_index;
		this.inp_mode = inp_mode;

	}
	
	
	/**
	* フォームから表示要素を取得する
	* @param string field フィールド名
	* @return object 表示要素オブジェクト
	*/
	_getDisplayFromForm(field){
		let jqDisplay = this.jqForm.find(`[data-display='${field}']`);
		return jqDisplay;
	}
	
	
	
	/**
	 * 様々なタイプの要素へ値をセットする
	 * @param inp(string or jQuery object) 要素オブジェクト、またはセレクタ
	 * @param field フィールド
	 * @param val1 要素にセットする値
	 * @param options
	 *  - form_type フォーム種別
	 *  - xss サニタイズフラグ 0:サニタイズしない , 1:xssサニタイズを施す（デフォルト）
	 *  - disFilData object[フィールド]{フィルタータイプ,オプション} 表示フィルターデータ
	 *  - dis_fil_flg 表示フィルター適用フラグ 0:OFF(デフォルト) , 1:ON
	 */
	setValueToElement(inp,field,val1,options){
		
		// 要素がjQueryオブジェクトでなければ、jQueryオブジェクトに変換。
		if(!(inp instanceof jQuery)) inp = jQuery(inp);

		// 入力要素のタグ名を取得する
		let tag_name = inp.get(0).tagName; 
		tag_name = tag_name.toLowerCase(); // 小文字化

		// input要素へのセット
		if(tag_name == 'input'){

			let typ = inp.attr('type'); // type属性を取得

			// チェックボックス要素へのセット
			if(typ=='checkbox'){
				if(val1 == 0 || val1 == null || val1 == ''){
					inp.prop("checked",false);
				}else{
					inp.prop("checked",true);
				}

			}

			// ラジオボタン要素へのセット
			else if(typ=='radio'){

				let radioParent = inp.parent();
				let opElm = radioParent.find("[value='" + val1 + "']");
				if(opElm[0]){
					opElm.prop("checked",true);
				}else{

					// ラジオボックスの選択肢に存在しない場合、すべてのチェックを外す。
					let radios = radioParent.find("[name='" + field + "']");
					radios.prop("checked",false);
	
				}

			}
			
			// file要素へのセット
			else if(typ=='file'){
				let fileUploadK = this.fileUploadKList[field];
				let midway_dp = crudBaseData.paths.public_url + '/';
				let fps = [val1];
				fileUploadK.setFilePaths(field, fps, {'midway_dp':midway_dp,});
				
			}

			// type属性がtext,hidden,date,numberなど。
			else{
				inp.val(val1);
			}

		}
		
		// SELECTへのセット
		else if(tag_name == 'select'){
			inp.val(val1);
		}

		// テキストエリア用のセット
		else if(tag_name == 'textarea'){
			inp.val(val1);
		}
		
		
	}
	
	/**
	* 入力フォーム要素内のテキストエリアの高さを自動調整する
	* @param ｛｝ fieldData フィールドデータ
	*/
	automateTextareaHeight(fieldData){
		for(let field in fieldData){
			let fieldEnt = fieldData[field];
			if(fieldEnt.form_tag == 'textarea'){
				let jqInp = this._getInpFromForm(field); // フォームから入力要素を取得する
				this._automateTextareaHeight(jqInp);　// テキストエリアの高さを自動調整する。
				
			}
		}
	}
	

	/**
	* テキストエリアの高さを自動調整する。
	* @param elm object テキストエリア要素
	*/
	_automateTextareaHeight(elm){
		let jqElm = null;
		if (elm instanceof jQuery) {
			jqElm = elm;
		}else{
			jqElm = jQuery(elm);
		}
		
		// 文字入力した時に高さ自動調整
		jqElm.attr("rows", 1).on("input", e => {
			$(e.target).height(0).innerHeight(e.target.scrollHeight);
		});
		
		// クリックしたときに自動調整
		jqElm.attr("rows", 1).click("input", e => {
			$(e.target).height(0).innerHeight(e.target.scrollHeight);
		});
	}
	
	
	/**
	* 入力フォームからエンティティを取得する
	* @param {} フィールドデータ←省略可
	* @return {} エンティティ
	*/
	getEntByForm(fieldData){
		
		if(fieldData == null) fieldData = this.crudBaseData.fieldData;
		
		let fEnt = {};
		for(let field in fieldData){

			let fieldInfo = fieldData[field];

			let jqInp = this._getInpFromForm(field); // フォームから入力要素を取得する
			
			if(jqInp[0]){
				fEnt[field] = this._getValueFromForm(jqInp, field);// 入力要素から値を取得する
			}
			
		}
		
		// CrudBaseDataの一覧データから取得したエンティティに入力フォームから取得したエンティティをマージする。
		let ent = {};
		if(fEnt.id){
			ent = this.getEntityFromCrudBaseData(fEnt.id);
			for(let field in fEnt){
				ent[field] = fEnt[field];
			}
		}else{
			ent = fEnt;
		}
		
		return ent;
	}

	
	/**
	* 入力要素から値を取得する
	* @param string field フィールド
	* @param object jqInp 入力要素オブジェクト
	* @return mixed 入力要素から取得した値
	*/
	_getValueFromForm(jqInp, field){

		let tag_name = jqInp.get(0).tagName; // 入力要素のタグ名を取得する
		tag_name = tag_name.toLowerCase(); // 小文字化

		// input要素へのセット
		if(tag_name == 'input'){

			let typ = jqInp.attr('type'); // type属性を取得

			// チェックボックス要素へのセット
			if(typ=='checkbox'){
				
				if(jqInp.prop('checked')){
					return 1;
				}else{
					return 0;
				}

			}

			// ラジオボタン要素へのセット
			else if(typ=='radio'){
				
				let jqInp2 = this.jqForm.find(`[name='${field}']:checked`);
				
				if(jqInp2[0]){
					return jqInp2.val();
				}
				
				return null;

			}
			
			// file要素へのセット 
			//※注意→ ファイル名にはパスが含まれないので注意すること。バックエンド側でパスを生成するためである。
			else if(typ=='file'){
				let fileUploadK = this.fileUploadKList[field];
				let fileNameList = fileUploadK.getFileNames(field);

				if(fileNameList[0]){
					return fileNameList[0];
				}
				return null;

			}

			// type属性がtext,hidden,date,numberなど。
			else{
				return jqInp.val();
			}

		}
		
		// SELECTへのセット
		else if(tag_name == 'select'){
			return jqInp.val();
		}

		// テキストエリア用のセット
		else if(tag_name == 'textarea'){
			return jqInp.val();
		}
		
		
	}
	
	
	/**
	* バリデーション
	* @param {} fieldData フィールドデータ←省略可
	* @param {} origMsgList オリジナルメッセージリスト(省略可)→キーはフィールド
	* @return string エラーメッセージ群テキスト
	*/
	validation(fieldData, origMsgList){
		
		if(fieldData == null) fieldData = this.crudBaseData.fieldData;
		if(origMsgList == null) origMsgList = {};
		
		let err_msgs_text = '' // エラーメッセージ群テキスト
		
		for(let field in fieldData){
			
			let jqInp = this._getInpFromForm(field); // 入力要素を取得する 
			if(jqInp[0] == null) continue;
			
			let jqErr = this._getErrFromForm(field); // 入力エラー表示要素を取得する

			// バリデーションチェックをする。
			if(jqInp[0].checkValidity()){
				// 正常
				
				if(jqErr[0]) jqErr.html(''); // エラー表示要素に空文字をセット
				
			}else{
				// 入力エラーあり
				
				let err_msg;
				if(origMsgList[field]){
					err_msg = origMsgList[field];
				}else{
					err_msg = jqInp.attr('title'); // 入力要素のtitle属性からエラーメッセージを取得する
				}
				
				if(jqErr[0]) jqErr.html(err_msg); // エラー表示要素にエラーメッセージをセットする。
				err_msgs_text += `<div>${err_msg}</div>`; // エラーメッセージ群テキストにエラーメッセージを追記
				
			}
				
		}
		
		return err_msgs_text;

	}
	
	
	/**
	* 入力エラー表示要素を取得する
	* @param string field フィールド名
	* @return object 入力エラー表示要素
	*/
	_getErrFromForm(field){
		
		if(this.jqErrs == null) this.jqErrs = {};
		if(this.jqErrs[field] === undefined){
			let jqErr = this.jqForm.find(`[data-valid-err='${field}']`);
			if(jqErr[0] == null){
				this.jqErrs[field] = 0;
			}else{
				this.jqErrs[field] = jqErr;
			}
		}
		
		return this.jqErrs[field]
		 
	}
	
	
	// Check empty.
	_empty(v){
		if(v == null || v == '' || v=='0'){
			return true;
		}else{
			if(typeof v == 'object'){
				if(Object.keys(v).length == 0){
					return true;
				}
			}
			return false;
		}
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
	_xssSanitize(data){
		if(typeof data == 'object'){
			for(var i in data){
				data[i] = this._xssSanitize(data[i]);
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
	* フォームから入力要素を取得する
	* @param string field フィールド名
	* @return object 入力要素オブジェクト
	*/
	_getInpFromForm(field){
		
		if(this.jqInps == null) this.jqInps = {};
		if(this.jqInps[field] === undefined){
			let jqInp = this.jqForm.find(`[name='${field}']`);
			if(jqInp[0] == null){
				this.jqInps[field] = 0;
			}else{
				this.jqInps[field] = jqInp;
			}
		}
		
		return this.jqInps[field]
		 
	}
	
	
	/**
	* SPA型・登録アクション
	* @param {} ent エンティティ
	* @param {} options オプション
	*    - string csrf_token CSRFトークン
	*    - function callback 登録後に実行するコールバック関数
	* @param string csrf_token CSRFトークン（省略可）
	* @return object 入力要素オブジェクト
	*/	
	regAction(ent, ajax_url, options){

		if(options == null) options = {};
		if(options.csrf_token == null) options.csrf_token = this._getCsrfToken();
		let csrf_token = options.csrf_token

		let fd = new FormData(); // 送信フォームデータ
		let data = {'ent':ent}; // バックエンド側に送信するデータ
		let json = JSON.stringify(data);
		fd.append( "key1", json );
		
		for(let fu_field in this.fileUploadKList){
			fd = this._setFilesToFd(fd, fu_field);
		}

		// CSRFトークンを送信フォームデータにセットする。
		fd.append( "_token", csrf_token );
		
		fetch(ajax_url, {
			method: 'POST',
			body: fd,
		})
		.then(response => {
			
			return response.text()
				.then(text => {
					try {
						return JSON.parse(text);
					} catch (e) {
						this.jqErr.html(`<strong>バックエンド側でエラーが起きました。</strong><br>${text}`);
					}
				});

		})
		.then(data => {

			// メイン一覧テーブルの行数を取得する
			let row_count = this._getRowCountFromMainTable();
			if(row_count == 0) location.reload(true); // メイン一覧テーブルの行数が0件ならブラウザリロードする。
			this.row_count = row_count;
			
			this.setEntToRow(data); // メイン一覧の行にエンティティをセットする。
			if(options.callback){
				options.callback(); // コールバックを実行する
			}
		})
		.catch(error => {

			console.log(error.message);
			alert('エラー');
			
		});
	}
	
				// 
	/**
	 * メイン一覧テーブルの行数を取得する
	 * @return int メイン一覧テーブルの行数
	 */
	_getRowCountFromMainTable(){
		
		let row_count = this.jqMainTbl.find('tbody tr').length;
		return row_count;
		
	}
	
	
	/**
	 * FDにファイルオブジェクトをセットする
	 * @param FileData fd FD
	 * @param string field フィールド
	 * @return FileData FD
	 */
	_setFilesToFd(fd, field){
		
		let fileUploadK = this.fileUploadKList[field];
		let box = fileUploadK.box;

		for(let fu_id in box){

			let files = box[fu_id]['files']; // FDにセット予定のファイルオブジェクトを取得する

			if(files == null) continue;
			if(files[0] == null) continue;
			
			let fileData = box[fu_id]['fileData']; // エラーチェックのためにフィールドデータを取得 （フィールドデータにはFU要素やDnD由来のMIME,サイズ、ファイル名がセットされている。）
			if(fileData[0] == null) continue;
			let fEnt = fileData[0]; // フィールドエンティティを取得 (単一アップロードなので一行目のみ取得)
			if(fEnt.err_flg == false){ // エラーでない場合
				fd.append(field, files[0]); // FDにファイルオブジェクトをセットする
			}

		}

		return fd;

	}
	
	
	/**
	* CSRFトークンを取得する
	* @return CSRFトークン
	* @throw CSRFトークン取得失敗
	*/
	_getCsrfToken(){

		if(this.crudBaseData.csrf_token) return this.crudBaseData.csrf_token;
		
		let csrfTokenElm = $('#csrf_token');
		if(csrfTokenElm[0]){
			return csrfTokenElm.val();
		}

		throw Error('システムエラー23042515A:CSRFトークンが取得できませんでした。');
		
	}
	
	/**
	* メイン一覧の行にエンティティをセットする。
	* @param {} ent エンティティ
	*/
	setEntToRow(ent){

		let fieldData = this.crudBaseData.fieldData;

		let targetTr = null; // 対象行: 新規追加した行オブジェクトか、編集中の行オブジェクト。
		let row_index = null; // 対象行の行インデックス
		
		// 新規入力モードまたは複製モード、もしくはidが空である場合、メイン一覧テーブルに新しい行を作成する。
		if(this.inp_mode == 'create' || this.inp_mode == 'copy' || this._empty(ent.id)){
			let res = this._createNewRow(); // メイン一覧テーブルに新しい行を作成する。
			row_index = res.row_index;
			targetTr = res.newTr;

		}else{
			row_index = this.row_index; // 編集中の行インデックス
			targetTr = this._getTrFromMainTable(row_index); // 編集中の行
			targetTr.addClass('edit_tr'); // class属性を追加。編集した行の背景色を変える
			
		}
		
		// デフォルトエンティティを取得し、引数のエンティティをマージする。
		let ent2 = this.getDefaultEntity();
		for(let field in ent){
			ent2[field] = ent[field];
		}

		let clmIndexList = this.getColumnIndexs(); // 列インデックス情報を取得します。
		for(let field in clmIndexList){
			
			// 列番号を取得する
			let clm_index = clmIndexList[field] ?? null;
			if(clm_index == null) continue;

			// 行へフィールドに該当する値をセットする
			this._setValueToRow(targetTr, ent2, field);

			let fdEnt = fieldData[field];
			
			if(fdEnt == null){
				continue;
			}
			
		}
		
		// ボタン群の各ボタンのURLのidを書き換える
		this._replaceButtonIdsInUrls(targetTr, ent.id);
		
		// 削除ボタンのdata-id属性にidをセットする
		let deleteBtnElm = targetTr.find('.row_delete_btn');
		if(deleteBtnElm[0]) deleteBtnElm.attr('data-id', ent.id);

		// CrudBaseData内で保持するデータにも反映すること。
		this._setEntityToCrudBaseData(ent2);
		
	}
	
	
	/** データを取得する
	 */
	_getData(){
		if(this.crudBaseData.list_data){
			return this.crudBaseData.list_data.data;
		}else{
			return this.crudBaseData.data;
		}
		 
	}
	
	
	/**
	* CrudBaseData内で保持するデータに反映
	* @param jQuery targetTr 行オブジェクト
	* @param int id_prefix ID
	*/
	_setEntityToCrudBaseData(ent){
		let data = this._getData();
		
		// 編集時の反映
		for(let i in data){
			let ent0 = data[i];
			if(ent0.id == ent.id){
				for(let field in ent){
					let value = ent[field];
					ent0[field] = value;
				}
				return;
			}
		}
		
		// 新規入力時の反映
		data.push(ent);
		

	}
			
	
	/**
	* ボタン群の各ボタンのURLのidを書き換える
	* @param jQuery targetTr 行オブジェクト
	* @param int id_prefix ID
	*/
	_replaceButtonIdsInUrls(targetTr, id_prefix){
		
		let ankers = targetTr.find('a');
		ankers.each((i, elm)=>{
			let anker = $(elm);
			let url = anker.attr('href');
			
			// URLのid部分を書き換える。
			url = url.replace(/(id=)\d+/, '$1' + id_prefix);
			
			anker.attr('href', url);
		});
		
	}
		
	
	
	/**
	* 行へフィールドに該当する値をセットする
	* @param jQuery jqTr TR要素オブジェクト
	* @param {} ent エンティティ
	* @param string field フィールド
	*/
	_setValueToRow(jqTr, ent, field){

		let fieldInfo = this.crudBaseData.fieldData[field]; // フィールド情報
		if(fieldInfo==null) return;
		
		let clm_index = fieldInfo.clm_index; // 列インデックス
		let jqTd = jqTr.find('td').eq(clm_index); // 列インデックスに紐づくTD要素を取得
		
		// TD要素内は画像系である場合
		let tdImgDivElm = jqTd.find('.js_td_img_div');
		if(tdImgDivElm[0]){
			this._setValueToTdImg(tdImgDivElm, ent, field); // TD画像系要素に画像パスをセットする。
			return;
		}
		
		let value = ent[field] ?? '';
		let value2 = this._xssSanitize(value); // XSSサイニタイズ
		
		// TD要素内にjs_display_valueのclass属性を持っている表示要素を取得する。
		let displayElm = jqTd.find('.js_display_value');
		
		// TD要素内にjs_original_valueのclass属性を持っている元値要素を取得する。
		let originalElm = jqTd.find('.js_original_value');
		
		// 表示要素および元値要素が空である場合、TD要素内に値をセットする。
		if(displayElm[0] == null && originalElm[0] == null){
			jqTd.html(value2);
		}
		

		// 表示要素が存在している場合
		if(displayElm[0]){
			// フィールド情報と値から表示値を作成する。
			let display_value = this._createDisplayValue(fieldInfo, value2);
			displayElm.html(display_value);
			
			// 表示要素の値のclass属性を変更して色を変える。
			this._changeDisplayElmColor(displayElm, value);
			
			
			
		}
			
		// 元値要素が存在している場合
		if(originalElm[0]){
			originalElm.val(value);// 元値要素に値をセットする。
		}

	}
	
	/**
	* TD画像系要素に画像パスをセットする
	* @param jQuery tdImgDivElm TD画像系要素のラッパー要素
	* @param {} ent 
	* @param string field フィールド
	*/	
	_setValueToTdImg(tdImgDivElm, ent, field){

		let public_url = this.crudBaseData.paths.public_url;

		let value = ent[field];
		let orig_fp = '';
		let thum_fp = '';	
		
		if(value == '' || value == null){
			value = '';
			orig_fp = 'img/icon/none.gif';
			thum_fp = orig_fp;
		}else{
			orig_fp = value;
			thum_fp = orig_fp.replace('/orig/', '/thum/');
			orig_fp = public_url + '/' + orig_fp;
			thum_fp = public_url + '/' + thum_fp;
		}
		
		let jqImgA = tdImgDivElm.find('.js_show_modal_big_img');
		let jqThumImg = jqImgA.find('img');
		let jqOrig = tdImgDivElm.find('.js_original_value');
		
		jqImgA.attr('href', orig_fp);
		jqThumImg.attr('src', thum_fp);
		jqOrig.val(value);
		
	}
	
	
	/**
	* 表示要素の値のclass属性を変更して色を変える。
	* @param jQuery displayElm 表示要素のjQueryオブジェクト
	* @param mixed value 値
	*/	
	_changeDisplayElmColor(displayElm, value){
		if(this._empty(value)){
			if(displayElm.hasClass('text-success')){
				displayElm.removeClass('text-success');
				displayElm.addClass('text-secondary');
			}
		}else{
			if(displayElm.hasClass('text-secondary')){
				displayElm.removeClass('text-secondary');
				displayElm.addClass('text-success');
			}
		}
	}
	
	
	/**
	* フィールド情報と値から表示値を作成する。
	* @param {} fieldInfo フィールド情報
	* @param mixed value2 値（サニタイズ済み）
	* @return mixed 表示値
	*/		
	_createDisplayValue(fieldInfo, value2){

		//value_typeが「フラグ」である場合
		if(fieldInfo.value_type == 'flg'){
			if(this._empty(value2)){
				value2 = 'OFF';
			}else{
				value2 = 'ON';
			}
			return value2;
		}
		
		//value_typeが「削除フラグ」である場合
		if(fieldInfo.value_type == 'delete_flg'){
			if(this._empty(value2)){
				value2 = '有効';
			}else{
				value2 = '無効';
			}
			return value2;
		}
		
		
		//outer_listがセットされている場合
		if(fieldInfo.outer_list != null){
			let outerList = this.crudBaseData[fieldInfo.outer_list];
			if(outerList == null) return '';
			for(let outer_value in outerList){
				if(outer_value == value2){
					return outerList[outer_value];
				}
			}
			return '';
		}
		
		return value2;
	}
			
			
	
	/**
	* メイン一覧テーブルに新しい行を作成する。
	* @return {}
	*  - jQuery newTr 新しい行のjQueryオブジェクト
	*  - int row_index 新しい行の行インデックス
	*/
	_createNewRow(){
		
		// メイン一覧テーブルから1行目のTR要素を取得します。
		let jqTr1 = this._getTrFromMainTable(0);
		
		let tr_html = jqTr1[0].outerHTML;
		
		let newTr = null; // 新行要素
		let row_index = null; // 行インデックス
		
		// 新規入力追加場所フラグが先頭を示している場合( 0:末尾 , 1:先頭)
		if(this.options.create_tr_place == 1){
			jqTr1.before(tr_html); // 先頭行の前に新行を挿入する。
			newTr = this._getTrFromMainTable(0);
			row_index = 0;
		}
		
		// 新規入力追加場所フラグが末尾を示している場合
		else{
			// メイン一覧テーブルの行数を取得する
			let tr_len = this._getRowCountFromMainTable();
			let jqTrLast = this._getTrFromMainTable(tr_len - 1);
			jqTrLast.after(tr_html);
			row_index = tr_len;
			newTr = this._getTrFromMainTable(row_index);
			
			
		}
		
		newTr.addClass('create_tr'); // class属性を追加。背景色の色を変えるため。
		
		return {
			'newTr': newTr,
			'row_idex': row_index,
		};

	}
	
			// メイン一覧テーブルから1行目のTR要素を取得します。
	
	/**
	*  メイン一覧テーブルから指定行番号にひもづくTR要素を取得します。
	* @param int row_index 行番号 0から始まりす。すなわち1行目が0です。
	*/
	_getTrFromMainTable(row_index){
		let tr = this.jqMainTbl.find('tbody tr').eq(row_index);
		return tr;
	}


	/**
	 * 検索実行
	 * @param {} params 
	 *    - string form_slt 検索フォームのセレクタ
	 *    - string inp_slt 検索入力要素のセレクタ
	 *    - string page_no_field ページ番号フィールド
	 */
	searchAction(params){
		if(params==null) params = {};
		let form_slt = params.form_slt ?? '#searchForm';
		let inp_slt = params.inp_slt ?? '.js_search_inp';
		let page_no_field = params.page_no_field ?? 'page';
		
		// 検索フォーム要素を取得する
		let jqForm = jQuery(form_slt);
		if(jqForm[0] == null) throw Error('システムエラー20230918A');
		
		let jqInps = jqForm.find(inp_slt); // 検索フォームから入力要素群を取得する
		
		// バリデーションによる入力チェックを行う。
		let errs_flg = this._validationForForm(jqInps);

		// バリデーションによる入力エラーなっていれば、処理を中断する。
		if(errs_flg == true) return;

		// URLからWEBクエリリストを取得する
		let webQuerys = this._getUrlQuery();

		// form要素内の入力要素群をループする。
		let searchList = {}; // 検索条件情報
		let removes = ['clear']; // 除去リスト→入力が空の要素のフィールド。「clear」も不要なので除去対象。
		
		jqInps.each((i,elm)=>{

			// 要素から選択値を取得する
			let inpElm = jQuery(elm);
			
			let type = inpElm.attr('type');
			
			let val = '';
			if(type == 'checkbox'){
				if(inpElm.prop('checked')){
					val = 1;
				}else{
					val = 0;
				}
			}else{
				 val = inpElm.val();
			}

			// 選択値が空でない場合（0も空ではない扱い）
			let field = inpElm.attr('name'); // 要素のID属性から検索条件フィールドを取得する
			if(!this._emptyNotZero(val)){ // 空判定 | ただし「0」はfalseを返す
				val = encodeURIComponent(val); // 要素の値をURLエンコードする
				searchList[field] = val; // 検索条件情報にセットする
			}else{
				removes.push(field);
			}
		});

		// WEBクエリに検索条件情報をマージする
		for(let field in searchList){
			let value = searchList[field];
			webQuerys[field] = value;
		}

		// パラメータから除去リストのフィールドを削除する。
		for(let i in removes){
			let rem_field = removes[i];
			delete webQuerys[rem_field];
		}

		webQuerys[page_no_field] = 1; // 1ページ目をセットする

		// パラメータからURLクエリを組み立てる
		let query = '';
		for(let field in webQuerys){
			let val = webQuerys[field];
			query += field + '=' + val + '&';
		}

		// URLの組み立て
		let url;
		if(query != ''){
			query = query.substr(0,query.length-1); // 末尾の一文字を除去する
			url = '?' + query;
		}

		window.location.href = url; // URLへ遷移
	}
	
	
	/**
	 * バリデーションによる入力チェックを行う
	 * @param jQuery jqInps 入力要素群
	 * @return boolean errs_flg
	 */
	_validationForForm(jqInps){
		
		let errs_flg = false;
		
		// 検索入力のバリデーション
		jqInps.each((i,elm)=>{
			
			let inpElm = jQuery(elm); // 入力要素

			// 入力要素に付属する入力エラー要素を非表示にする。
			let parElm = inpElm.parent(); // 親要素を取得
			let errElm = parElm.find('.searche_err');
			errElm.hide();
				
			// バリデーションチェックを行う。
			let valid=inpElm[0].checkValidity(); // バリデーション検知
			
			// バリデーションにひっかかる、すなわり入力エラーになっている場合。
			if(valid == false){
				errs_flg = true;
				errElm.show(); // 入力エラー要素を表示する。
			}

		});
		
		return errs_flg;
	}
		
		
	
	/**
	 * 空判定 | ただし「0」はfalseを返す
	 * @param v
	 */
	_emptyNotZero(v){
		var res = this._empty(v);
		if(res){
			if(v===0 || v==='0'){
				res = false;
			}
		}
		return res;
	}
	
	
	/**
	 * URLクエリデータを取得する
	 * 
	 * @return object URLクエリデータ
	 */
	_getUrlQuery(){
		query = window.location.search;

		if(query =='' || query==null){
			return {};
		}
		var query = query.substring(1,query.length);
		var ary = query.split('&');
		var data = {};
		for(var i=0 ; i<ary.length ; i++){
			var s = ary[i];
			var prop = s.split('=');

			data[prop[0]]=prop[1];

		}	
		return data;
	}
	

	/**
	 * 入力フォーム内のテキストエリアの高さを文字に合わせてフィットさせる
    *  注意：display:noneなどでテキストエリアが非表示になっているとき（親要素ごとテキストエリアが隠れているときも含む）は自動フィットできない。高さが0になる。
	 * 
	 */
	fitTextareaHeightForInpForm(){

		this.jqForm.find('textarea').each((index, elm) => {
			 let taElm = $(elm);
			taElm.height(0).innerHeight(taElm[0].scrollHeight);
		});
	}
	

	
}













