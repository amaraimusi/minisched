

$(()=>{
	
	console.log('ねこねこさま');//■■■□□□■■■□□□
});


function test1(){
	console.log('キャインガーｚ');//■■■□□□■■■□□□
	test_ajax();
}

function test_ajax(){

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

	}).fail((xhr, status, errorThrown) => {
	
		// 419エラーならトークンの期限切れの可能性のためリロードする（トークンの期限は2時間）
		if(xhr.status == 419)  location.reload(true);
		alert('通信エラー');
		console.log(status);
		console.log(xhr.responseText);
		$('#err').html(xhr.responseText);
		
	});
}