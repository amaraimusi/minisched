
let app; // vue.js

$(()=>{
	
	//let scheduleMatrix =
	
	const STORAGE_KEY = 'pub_sched_fQZ4hs8';

	// ユーザー情報を初期化（デフォルト値）
	let users = [{
		nickname: '',
		mail: '',
		pass_code: ''
	}];
	
		app = new Vue({
			el: '#app1',
			data: {
				scheduleMatrix: [],
				weekDates: [],
				users: users,
			}
		})

	// ローカルストレージからユーザー情報を読み込む
	users[0] = loadUserFromStorage(STORAGE_KEY);
	
	
	
	
	// パスコードを確認後画面を表示
	code_gete(users);
});


/**
 * ローカルストレージからユーザー情報を読み込む
 * @param {string} key ローカルストレージのキー
 * @returns {object} ユーザー情報オブジェクト（nickname, mail, pass_code）
 */
function loadUserFromStorage(key) {
	let defaultUser = {
		nickname: '',
		mail: '',
		pass_code: ''
	};

	let savedData = localStorage.getItem(key);
	if (savedData) {
		try {
			const parsed = JSON.parse(savedData);
			if (parsed && typeof parsed === 'object') {
				return {
					nickname: parsed.nickname || '',
					mail: parsed.mail || '',
					pass_code: parsed.pass_code || ''
				};
			}
		} catch (e) {
			console.warn('ローカルストレージの読み込みに失敗:', e);
		}
	}
	return defaultUser;
}

function code_gete(){

	console.log('test_ajax');
	let fd = new FormData(); // 送信フォームデータ
	let data = {id:123, name:'古いねこ', age:15}; // バックエンド側に送信するデータ
	let json = JSON.stringify(data);
	fd.append( "key1", json );
	
	// CSRFトークンを送信フォームデータにセットする。
	let token = jQuery('#csrf_token').val();
	fd.append( "_token", token );
	
	jQuery.ajax({
		type: "post",
		url: 'pub_sched/code_gete',
		data: fd,
		cache: false,
		dataType: "text",
		processData: false,
		contentType: false,

	}).done((str_json, status, xhr) => {
	
		// 419エラーならトークンの期限切れの可能性のためリロードする（トークンの期限は2時間）
		if(xhr.status == 419)  location.reload(true);

		let res = null;
		try{
			res =jQuery.parseJSON(str_json); //パース
		}catch(e){
			alert('バックエンド側のエラー');
			console.log(str_json);
			$('#err').html(str_json);
			return;
		}
		
		if(res.err_msg == 'logout') location.reload(true); // すでにログアウトになっているならブラウザをリロードする。
		if(res.err_msg) {
			console.log(res.err_msg);//■■■□□□■■■□□□
			return;
		}
			
		console.log(res);//■■■□□□■■■□□□
		
		// TODO:: 表を作成
		app.scheduleMatrix = Object.values(res.schedule_matrix);
		app.weekDates = res.week_dates;
		

	}).fail((xhr, status, errorThrown) => {
	
		// 419エラーならトークンの期限切れの可能性のためリロードする（トークンの期限は2時間）
		if(xhr.status == 419)  location.reload(true);
		alert('通信エラー');
		console.log(status);
		console.log(xhr.responseText);
		$('#err').html(xhr.responseText);
		
	});
}


