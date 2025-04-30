let crudBase; // CRUD支援オブジェクト
let csh; // 列表示切替機能
let rowExchange; // 行入替機能
let crudBaseData;
let data; // 一覧データ
let searches; // 検索データ
let csrf_token; // CSRFトークン
let baseXHelper; // 基本X
let crudBaseConfig; // CrudBase設定クラス
let cbBtnSizeChanger; // ボタンサイズ変更コンポーネント
let autoSave; // 自動保存
let pwms; // 一覧のチェックボックス複数選択による一括処理
let jqMain; // メインコンテンツ
let jqMainTbl; // 一覧テーブルのjQueryオブジェクト
let jqForm; // SPA型入力フォームのjQueryオブジェクト
let jqValidErrMsg; // バリデーションエラーメッセージ表示要素
let jqRegistMsg; // 登録成功メッセージ要素	←「登録中」、「登録しました」などのメッセージを表示する。
let jqCreateMode; // 新規入力モード ←新規入力モードのみ表示するセレクタ
let jqEditMode; // 編集モード ←編集モードのみ表示するセレクタ
let modalCat; // モーダル化ライブラリ

$(()=>{
    
	baseXHelper = new BaseXHelper();
    
    let crud_base_json = $('#crud_base_json').val();
    crudBaseData = JSON.parse(crud_base_json);
    data = crudBaseData.data;
    searches = crudBaseData.searches;

	// CRUD支援オブジェクト
	crudBase = new CrudBase4(crudBaseData,{
		'main_tbl_slt': '#main_tbl', // メイン一覧テーブル ←メイン一覧である<table>のセレクタ。
		'form_slt': '#form_spa', // 入力フォーム ← SPA型・入力フォーム。入力フォーム一つに、新規入力モード、編集モード、複製モードが含まれる。
		'create_tr_place': 0, // 新規登録後の行の挿入場所： 0:末尾 , 1:先頭
	});
	
	// 列の順番である列インデックスをフィールドデータにセットします。
	crudBaseData.fieldData = crudBase.setColumnIndex(crudBaseData.fieldData); 
	
	// 入力フォームのtag名やtype名などをフィールドデータにセットします。
	crudBaseData.fieldData = crudBase.setFormInfoToFileData(crudBaseData.fieldData); 
	
	// ファイルアップロード要素にカスタマイズを施します。← カスタマイズにより、画像プレビューやファイル情報を表示などができるようになります。
	crudBase.customizeFileUpload(crudBaseData.fieldData);
	
	// 入力フォーム要素内のテキストエリアの高さを自動調整する
	crudBase.automateTextareaHeight(crudBaseData.fieldData);

	csrf_token = $('#csrf_token').val();
	
	autoSave = new AutoSave('auto_save', csrf_token);

	chs = initClmShowHide(); // 列表示切替機能の設定と初期化
	
	
	// 行入替機能の初期化
	rowExchange = new RowExchange('main_tbl', data, null, (param)=>{
		// 行入替直後のコールバック
		
		// 行入替後、再び行入替しなければ3秒後に自動DB保存が実行される。
		let auto_save_url = 'service/auto_save';
		autoSave.saveRequest(param.data, auto_save_url, ()=>{
			// DB保存後のコールバック
			location.reload(true); // ブラウザをリロードする
		});
	});
	
	// 年月による日付範囲入力【拡張】 | RangeYmEx.js
	let rngYmEx = new RangeYmEx();
	rngYmEx.init();
	
	// 新しいバージョンになった場合
	if(searches.new_version == 1){
		chs.reset(); // 列表示切替機能内のローカルストレージをクリア
	}
	
    // 一覧中のサムネイル画像をクリックしたら画像をモーダル化しつつ大きく表示する。
    let showModalBigImg = new ShowModalBigImg('.js_show_modal_big_img');
	
	// CrudBase設定クラス
	crudBaseConfig = new CrudBaseConfig();
	crudBaseConfig.init(null, crudBaseData);
	
	// ボタンサイズ変更コンポーネント
	cbBtnSizeChanger = crudBaseConfig.cbBtnSizeChanger;
	
	// ボタン設定: 表示切替とボタンサイズ
	cbBtnSizeChanger.setCnfData([
			{'slt':'.row_detail_btn','wamei':'詳細ボタン','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
			{'slt':'.row_edit_btn','wamei':'編集ボタン','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
			{'slt':'.row_copy_btn','wamei':'複製ボタン','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
			{'slt':'.row_delete_btn','wamei':'削除ボタン','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
			{'slt':'.row_eliminate_btn','wamei':'抹消ボタン','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
			{'slt':'.row_exc_btn','wamei':'行入替ボタン(↑↓ボタン)','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
			{'slt':'.row_enabled_btn','wamei':'削除取消ボタン','visible':true ,'def_size':'btn-sm','size':'btn-sm'},
		]);
	
	// 新バージョンフラグがONならモジュール群のクリア処理を施す
	if(crudBaseData.new_version	){
		_clearOfModules(); // モジュール群のクリア
	}
	
	// 一覧のチェックボックス複数選択による一括処理
	pwms = new ProcessWithMultiSelection({
			tbl_slt:'main_tbl',
			id_slt:'js_pwms_id',
			ajax_url:'service/ajax_pwms',
			csrf_token:csrf_token,
	});
	
	
	// 検索・日時リストギミック
	let sdgCreatedAt = new SearchDatetimeGimmick('.sdg_created_at');
	let sdgUpdatedAt = new SearchDatetimeGimmick('.sdg_updated_at');
		
    
    jqMain =  $('main'); // メインコンテンツ
	jqMainTbl = $('#main_tbl'); // 一覧テーブル
	jqForm = $('#form_spa'); // SPA型・入力フォーム
	jqValidErrMsg = $('.js_valid_err_msg'); // バリデーションエラーメッセージ表示要素
	jqRegistMsg = $('.js_registering_msg'); // 登録成功メッセージ要素	←「登録中」、「登録しました」などのメッセージを表示する。
	jqCreateMode = $('.js_create_mode'); // 新規入力モードのみ表示する要素
	jqEditMode = $('.js_edit_mode'); // 編集モードのみ表示する要素
	modalCat = new ModalCat();
	modalCat.modalize('form_spa');
	
    
});



// 列表示切替機能の設定と初期化
function initClmShowHide(){

	// 一覧テーブルの列表示切替機能を設定する
	
	// 列毎に初期の列表示状態を設定する。
	// -1:列切替対象外,  0:初期時はこの列を非表示, 1:最初からこの列は表示
	let iniClmData = [
		-1, // ID
		// CBBXS-6036
		1, // 奉仕テンプレID（外部キー）
		1, // 週始め日（月曜日の日付）
		1, // 奉仕場所
		1, // 備考

		// CBBXE
		0, // 順番
		0, // 無効フラグ
		0, // 更新者
		0, // IPアドレス
		0, // 生成日時
		0, // 更新日
		-1 // ボタン列
	];
	
	csh = new ClmShowHide();
	
	csh.init('main_tbl', 'csh_div', iniClmData);
	
	return csh;
}


/**
 * 行入替機能のフォームを表示
 * @param btnElm ボタン要素
 */
function rowExchangeShowForm(btnElm){
	rowExchange.showForm(btnElm); // 行入替フォームを表示する
}

/**
 * 削除/削除取消ボタンのクリック
 * @param object btnElm 削除、または削除取消ボタン要素
 * @param int action_flg 0:削除取消, 1:削除
 */
function disabledBtn(btnElm, action_flg){

	if(action_flg == 1 && !window.confirm("削除してもよろしいですか")){
		return;
	}

	let jqBtn = $(btnElm);
	let id = jqBtn.attr('data-id');
	
	let data = {
		'id':id,
		'action_flg':action_flg,
		
	}
	
	let json_str = JSON.stringify(data);//データをJSON文字列にする。
	let url = 'service/disabled'; // Ajax通信先URL
	
	let fd = new FormData(); // 送信フォームデータ
	fd.append( "key1", json_str );
	
	// CSRFトークンを送信フォームデータにセットする。
	fd.append( "_token", csrf_token );
	
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

		}catch(e){
			jQuery("#err").html(str_json);
			return;
		}
		
		location.reload(true); // ブラウザをリロード
		
	})
	.fail((jqXHR, statusText, errorThrown) => {
		console.log(jqXHR);
		jQuery('#err').html(jqXHR.responseText);
	});
}


/**
 * 抹消ボタンのクリック
 * @param object btnElm 抹消ボタン要素
 */
function destroyBtn(btnElm){
	
	if(!window.confirm("元に戻せませんが抹消してもよろしいですか？")){
		return;
	}
	
	let jqBtn = $(btnElm);
	let id = jqBtn.attr('data-id');
	
	let data = {
		'id':id,
	}
	
	let json_str = JSON.stringify(data);//データをJSON文字列にする。
	let url = 'service/destroy'; // Ajax通信先URL
	
	let fd = new FormData(); // 送信フォームデータ
	fd.append( "key1", json_str );
	
	// CSRFトークンを送信フォームデータにセットする。
	fd.append( "_token", csrf_token );
	
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

		}catch(e){
			jQuery("#err").html(str_json);
			return;
		}
		
		location.reload(true); // ブラウザをリロード
		
	})
	.fail((jqXHR, statusText, errorThrown) => {
		console.log(jqXHR);
		jQuery('#err').html(jqXHR.responseText);
	});
}


/**
 * ノート詳細を開く
 * @param btnElm 詳細ボタン要素
 */
function openNoteDetail(btnElm,field){
	return baseXHelper.openNoteDetail(btnElm,field);
}









/////////// 以下はSPA型・入力フォーム関連

/**
 * SPA型・新規入力ボタン押下時の処理
 */
function clickCreateBtn(btn){
	
	// SPA型・入力フォーム画面を開く
	_showForm(null, 'create');
}

/**
 * SPA型・詳細 ボタン押下時の処理  SPA型詳細画面を開く
 */
function clickDetailBtn(btn){
	
    // 現在のボタンの位置から行インデックスを取得します。
    let row_index = crudBase.getRowIndexFromButtonPosition(btn);
	

	// メイン一覧テーブルの行インデックスに紐づく行からエンティティを取得する
	let ent = crudBase.getEntityByRowIndex(row_index);
	
	/*
	// SPA型詳細画面を開く■■■□□□■■■□□□
	// 入力フォームにエンティティを反映する
	crudBase.setEntToForm(ent, row_index, inp_mode); 
	
	// 新規入力モード、編集モードのそれぞれの表示切替。 複製は新規入力モード扱い
	if(inp_mode=='create' || inp_mode=='copy'){
		jqCreateMode.show();
		jqEditMode.hide();
	}else{
		jqCreateMode.hide();
		jqEditMode.show();
	}
	
	jqValidErrMsg .html(''); // エラーメッセージをクリア
	jqRegistMsg.html(''); // 登録中のメッセージをクリア
	

	modalCat.open(); // 入力フォームをモーダル表示する
	
	crudBase.fitTextareaHeightForInpForm(); //  入力フォーム内のテキストエリアの高さを文字に合わせてフィットさせる
	*/
}

/**
 * SPA型・編集ボタン押下時の処理
 */
function clickEditBtn(btn){
	
    // 現在のボタンの位置から行インデックスを取得します。
    let row_index = crudBase.getRowIndexFromButtonPosition(btn);
	
	// SPA型・入力フォーム画面を開く
	_showForm(row_index, 'edit');
}

/**
 * SPA型・複製ボタン押下時の処理
 */
function clickCopyBtn(btn){
	
    // 現在のボタンの位置から行インデックスを取得します。
    let row_index = crudBase.getRowIndexFromButtonPosition(btn);
	
	// SPA型・入力フォーム画面を開く
	_showForm(row_index, 'copy');
}

/**
 * SPA型・入力フォーム画面を開く
 * @note フォーム画面はSPA型であり、新規入力と編集に対応する
 * @param int row_index 行インデックス ← メイン一覧テーブルの行番 ← 未セットなら新規入力、セットすれば編集という扱いになる。
 * @param string inp_mode 入力モード create:新規入力モード, edit:編集モード, copy:複製モード
 */
function _showForm(row_index, inp_mode){
	
	let ent = {};
	if(inp_mode == 'create'){
		// デフォルトエンティティを取得する
		ent = crudBase.getDefaultEntity();
	}else if(inp_mode == 'edit' || inp_mode == 'copy'){
		// メイン一覧テーブルの行インデックスに紐づく行からエンティティを取得する
		ent = crudBase.getEntityByRowIndex(row_index);

		
	}else{
		throw new Error('システムエラー23051109A');
	}
	
	// 入力フォームにエンティティを反映する
	crudBase.setEntToForm(ent, row_index, inp_mode); 
	
	// 新規入力モード、編集モードのそれぞれの表示切替。 複製は新規入力モード扱い
	if(inp_mode=='create' || inp_mode=='copy'){
		jqCreateMode.show();
		jqEditMode.hide();
	}else{
		jqCreateMode.hide();
		jqEditMode.show();
	}
	
	jqValidErrMsg .html(''); // エラーメッセージをクリア
	jqRegistMsg.html(''); // 登録中のメッセージをクリア
	

	modalCat.open(); // 入力フォームをモーダル表示する
	
	crudBase.fitTextareaHeightForInpForm(); //  入力フォーム内のテキストエリアの高さを文字に合わせてフィットさせる
	
}


/**
 * SPA型・入力フォーム画面を閉じる
 */
function closeForm(){

	modalCat.close();// 入力フォームを閉じる
}

/**
 * SPA型・入力フォーム画面の登録ボタン、または変更ボタン押下アクション
 * @note 新規入力、編集、複製に関わらず当メソッドを呼び出す。
 */
function regAction(){
	
	// バリデーションによる入力チェック
	let err_msg = crudBase.validation(null);
	jqValidErrMsg.html(err_msg);
	
	if(err_msg != '') return;
	
	// 入力フォームからエンティティを取得する
	let ent = crudBase.getEntByForm();
	
	jqRegistMsg.html('登録中です...');
	
	// SPA型・登録アクション
	crudBase.regAction(ent,'service/reg_action', {
		'callback': (param)=>{
			// DB登録後のコールバック処理内容
			jqRegistMsg.html('登録しました。'); 
				modalCat.close();// 入力フォームを閉じる
		}
	});
	
	
}

/**
* クリアボタン押下処理
*/
function clearA(){
	
	_clearOfModules(); // モジュール群のクリア

	location.href = 'service?clear=1';
}

/**
* モジュール群のクリア
*/
function _clearOfModules(){
	
	// 列表示切替機能を初期化
	csh.reset();
	
	// CrudBase設定をリセット
	crudBaseConfig.reset();
}

/**
 * 一括選択削除/有効機能：一括アクション
 * @param kind_no アクション種別番号 10:有効化,  11:無効化
 */
function pwmsAction(kind_no){
	pwms.action(kind_no);
}

/**
* 一括選択削除/有効機能：全選択の切替
* @param object checkbox チェックボックスオブジェクト
*/
function pwmsSwitchAll(checkbox){
	pwms.switchAllSelection(checkbox);
}




