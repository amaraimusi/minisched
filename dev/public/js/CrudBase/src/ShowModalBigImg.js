/**
 * モーダル大画像表示モジュール
 * @since 2022-8-31 | 2024-12-8
 * @version 1.1.0
*/
class ShowModalBigImg{
	
	constructor(slt){

		this.modalCat = this._createModalDiv();
		let content = this.modalCat.getContent();
		this.mdlImgElm = content.find('img'); // モーダル区分要素内の<img>要素を取得する。
		
		this.jqElm = jQuery(slt);
		this.jqElm.click((evt)=>{
			
			let aElm = $(evt.currentTarget);
			this._onClick(aElm);

			return false;
		});
		
		
	}
	
	// モーダル区分を生成する
	_createModalDiv(){
		
		// モーダル区分
		let html = `<div id="show_modal_big_img_mdl" ><img src="" alt="" style="max-width:100%;max-height:70vh" /></div>`;
		jQuery('body').prepend(html);
		
		// モーダル化
		let modalCat = new ModalCat_smbi();
		modalCat.modalize('show_modal_big_img_mdl');
		
		return modalCat;
		
	}
	
	_onClick(aElm){
		
		let orig_img_url = aElm.attr('href');
		
		let xhr = new XMLHttpRequest();
		xhr.open('GET', orig_img_url, true);
		xhr.responseType = 'blob';
		xhr.onload = (e) => {
			
			// Blobを取得する
			var blob = e.target.response;
	
			// BlobをBlobURLスキームに変換して、img要素にセットする。
			var blob_url = window.URL.createObjectURL(blob);
			this.mdlImgElm.attr('src',blob_url);
			
			this.modalCat.open();
			console.log(orig_img_url);
			
	
		};
		xhr.send();
	}
	
	
}


/**
 * モーダル化クラス
 * @since 2022-1-21
 * @version 1.0.0
 * @auther amaraimusi
 * @license MIT
 */
class ModalCat_smbi{
	
	modalize(xid){
		
		let main_xid = xid + '_js_main'; // xidで指定した要素のラップ要素（指定要素の親要素）
		let close_xid = xid + '_js_modal_close';// id属性名：背景クリックによる閉じる

		let content = jQuery('#' + xid);
		content.wrap(`<div id='${main_xid}'></div>`); // モーダル化制御のためmain要素でラッピング
		let main = jQuery('#' + main_xid);
		
		let bg_close_html = `<div id='${close_xid}'></div>`;
		main.prepend(bg_close_html);
		
		let bgClose = jQuery(main.find('#' + close_xid)); // 背景クリック閉じる用要素
		
		main.css({
			display: 'none',
			height: '100vh',
			position: 'fixed',
			top: '0',
			left: '0',
			width: '100vw',
		});
		
		bgClose.css({
			background: 'rgba(0,0,0,0.8)',
			height: '100vh',
			position: 'absolute',
			width: '100vw',
		});
		
		content.css({
			background: '#fff',
			left: '50%',
			padding: '10px',
			position: 'absolute',
			top: '50%',
			transform: 'translate(-50%,-50%)',
			'max-width': '60%',
		});

		this.main = main;
		this.content = content;

		bgClose.on('click',()=>{
			this.main.fadeOut();
			return false;
		});
		
	}
	
	open(){
		this.main.fadeIn();
	}
	
	close(){
		this.main.fadeOut();
	}
	
	getMainDiv(){
		return this.main;
	}
	
	getContent(){
		return this.content;
	}
}