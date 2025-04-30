<?php 
namespace App\Helpers;
use App\CrudBase\CrudBase;
use App\CrudBase\PagenationEx3;

class CrudBaseHelper
{
    
    private $crudBaseData;
    private $searches; // 検索データ
    
    public function __construct(&$crudBaseData){
        $this->crudBaseData = $crudBaseData;
        $this->searches = $crudBaseData['searches'];
        $this->pagenationEx3 = new PagenationEx3();
    }

    /**
     * 新バージョン通知区分を表示
     */
    public function divNewPageVarsion(){
        
        $new_version = $this->crudBaseData['new_version'];
        $this_page_version = $this->crudBaseData['this_page_version'];
        
        if(empty($new_version)) return;
        $html = "
			<div style='padding:10px;background-color:#fac9cc'>
				<div>新バージョン：{$this_page_version}</div>
				<div class='text-danger'>当画面は新しいバージョンに変更されています。
				セッションクリアボタンを押してください。</div>
				<input type='button' class='btn btn-danger btn-sm' value='セッションクリア' onclick='sessionClear()' >
			</div>
		";
        echo $html;
    }

    /**
     * ソート機能付きのth要素を作成する
     * @return string
     */
    public function sortLink(&$searches, $table_name, $field, $display_name)
    {
        
        $now_sort_field = $searches['sort'] ?? ''; // 現在のソートフィールドを取得
        
        $query_param_str = ''; // クエリパラメータ文字列
        foreach ($searches as $prop => $value){
            if($prop == 'sort' || $prop == 'desc') continue;
            if($value === null) continue;
            $query_param_str .= "{$prop}={$value}&";
        }
        
        // クエリパラメータ文字列が空でないなら末尾の一文字「&」を除去
        if(!empty($query_param_str)) $query_param_str=mb_substr($query_param_str,0,mb_strlen($query_param_str)-1);

        $url = '';
        $arrow = '';
        $dire = 'asc'; // 並び向き
        if($now_sort_field == $field){
            $desc_flg = $searches['desc'] ?? 0;
            if(empty($desc_flg)){ // 並び向きが昇順である場合
                $arrow = '▲';
                $url = "?{$query_param_str}&sort={$field}&desc=1";
            }else{ // 並び向きが降順である場合
                $arrow = '▼';
                $url = "?{$query_param_str}&sort={$field}&desc=0";
            }
        }else{
            $url = "?{$query_param_str}&sort={$field}";
        }
        
        $html = "
			<a href='{$url}' data-field='{$field}'>{$arrow}{$display_name}</a>
		";

        return $html;
    }
    
    
    /**
     * 通常の文字列をサニタイズしながら表示する
     * @param mixed $value
     * @return string
     */
    public function tdStr($value){
    	if(is_numeric($value)){
    		return $value;
    	}
    	return h($value);
    }
    
    
    /**
     * ＩＤのTD要素を出力する
     * @param int $value
     * @param string $width 横幅（省略可）
     */
    public function tdId($value, $width='80px'){
    	
    	$html = "
			<div style='width:{$width}'>
				<input type='checkbox' name='pwms' class='form-check-input pwms' />
				<span class='text-success js_display_value'>{$value}</span>
				<input type='hidden' class='js_original_value js_pwms_id'  value='{$value}'>
			<div>
		";
    	
    	return $html;

    	
    }
    
    
    /**
     * フラグを「有効」、「無効」の形式で表記する
     * @param int $flg フラグ
     * @return string
     */
    public function tdDate($value){
        
        if(empty($value)) $value = '';
        if($value == '0000-00-00') $value = '';
        if($value == '0000-00-00 00:00') $value = '';
        if($value == '0000-00-00 00:00:00') $value = '';
        
        return $value;
    }
    
    /**
     * フラグを「有効」、「無効」の形式で表記する
     * @param int $flg フラグ
     * @return string
     */
    public function tdFlg($flg){
        $notation = "<span class='text-success js_display_value'>ON</span>";
        if(empty($flg)){
            $notation = "<span class='text-secondary js_display_value'>OFF</span>";
        }
        
        $notation .= "<input type='hidden' class='js_original_value'  value='{$flg}'>";
        
        return $notation;
    }
    
    /**
     * 無効フラグを「有効」、「無効」の形式で表記する
     * @param int $delete_flg 無効フラグ
     * @return string
     */
    public function tdDeleteFlg($delete_flg){
        $notation = "<span class='js_display_value text-success'>有効</span>";
        if(!empty($delete_flg)){
            $notation = "<span class='js_display_value text-secondary'>無効</span>";
        }
        
        $notation .= "<input type='hidden' class='js_original_value'  value='{$delete_flg}'>";
        
        return $notation;
    }
    
    
    /**
     * 長文を折りたたみ式にする
     * @param array $ent データのエンティティ
     * @param string $field フィールド名
     * @param int $strLen 表示文字数（バイト）(省略時は無制限に文字表示）
     */
    public function tdNote($v, $field,$str_len = null){
        
        $v2="";
        $long_over_flg = 0; // 制限文字数オーバーフラグ
        if(!empty($v)){
            $v = str_replace(array('<','>'),array('&lt;','&gt;'), $v); // XSSサニタイズ
            if($str_len === null){
                $v2 = $v;
            }else{
                if(mb_strlen($v) > $str_len){
                    $v2=mb_strimwidth($v, 0, $str_len * 2);
                    $long_over_flg = 1;
                }else{
                    $v2 = $v;
                }
            }
            $v2= str_replace('\\r\\n', ' ', $v2);
            $v2= str_replace('\\', '', $v2);
        }
        
        // ノート詳細開きボタンのHTMLを作成
        $note_detail_open_html = '';
        if($long_over_flg) {
            $note_detail_open_html = "<input type='button' class='btn btn-secondary btn-sm note_detail_open_btn' value='...' onclick=\"openNoteDetail(this, '{$field}')\" />";
        }
        
        $td = "
			<div>
				<input type='hidden' name='{$field}' value='{$v}' class='js_original_value' />
				<div class='{$field} js_display_value' style='white-space:pre-wrap; word-wrap:break-word;'>{$v2}</div>
                {$note_detail_open_html}
			</div>";
        return $td;
    }
    
    
    /**
     * TD要素用の画像表示
     * @param [] $ent
     * @param string $field
     * @return string html
     */
    public function tdImg($ent, $field){

        $fp = $ent->$field ?? null;
        
        if(empty($fp)){
            $none_fp = 'img/icon/none.gif';
            return "
				<div class='js_td_img_div'>
		            <a href='{$none_fp}' class='js_show_modal_big_img'>
		                <img src='{$none_fp}' />
		            </a>
					<input type='hidden' class='js_original_value' value='' >
				</div>
			";
        }
        
        // サニタイズ
        $fp = h($fp);
        
        $thum_fp = CrudBase::toThumnailPath($fp);

        $html = "
			<div class='js_td_img_div'>
	            <a href='{$fp}' class='js_show_modal_big_img'>
	                <img src='{$thum_fp}' />
	            </a>
				<input type='hidden' class='js_original_value' value='{$fp}' >
			</div>
        ";
        return $html;
    }
    
    /**
     * リスト系の表示
     *
     */
    public function tdList($value, &$list){
    	
    	$text = $list[$value] ?? '';
    	$value2 = h($value);
    	$html = "<span class='js_display_value'>{$text}</span><input type='hidden' class='js_original_value' value='$value2'>";
    	
    	return $html;
    }
    
    
    /**
     * 単位付の表示
     * @param string $value 値
     * @param string $field フィールド
     * @param string $unit_f 単位（前）
     * @param string $unit_b 単位（後）
     * @param array $option オプション
     *     - boolean no_comma 3桁区切りなし
     * @return string
     */
    public function tdUnit($value, $field, $unit_f='', $unit_b='', $option=[]){
    	

    	// 数値でない
    	$display_value='';
    	if(is_numeric($value)){
    		if(empty($option['comma'])){
    			$display_value = number_format($value);
    		}else{
    			$display_value = $value;
    		}
    	}else{
    		$value = h($value);
    	}
    	
    	$html = "
			<span>{$unit_f}</span>
			<span class='js_display_value'>{$display_value}</span>
			<span>{$unit_b}</span>
			<span class='js_original_value' style='display:none'>{$value}</span>
		";
    	
    	return $html;
    }
    
    
    
    /**
     * 行入替ボタンを表示する
     * @param [] $searches 検索データ
     */
    public function rowExchangeBtn(&$searches){
        $html = '';

        // ソートフィールドが「順番」もしくは空である場合のみ、行入替ボタンを表示する。他のフィールドの並びであると「順番」に関して倫理障害が発生するため。
        if($searches['sort'] == 'sort_no' || empty($searches['sort'])){
            $html = "<input type='button' value='↑↓' onclick='rowExchangeShowForm(this)' class='row_exc_btn btn btn-info btn-sm text-light' />";
        }
       return $html;
    }
    
    
    /**
     * 削除/削除取消ボタン（無効/有効ボタン）を表示する
     * @param [] $searches 検索データ
     */
    public function disabledBtn(&$searches, $id){
        $html = '';
        
        if(empty($searches['delete_flg'])){
            // 削除ボタンを作成
            $html = "<input type='button' data-id='{$id}' onclick='disabledBtn(this, 1)' class='row_delete_btn btn btn-danger btn-sm text-light'  value='削除'>";
        }else{
            // 削除取消ボタンを作成
            $html = "<input type='button' data-id='{$id}' onclick='disabledBtn(this, 0)' class='row_enabled_btn btn btn-success btn-sm text-light' value='削除取消'>";
        }
        return $html;
    }
    
    
    /**
     * 抹消ボタン
     * @param [] $searches 検索データ
     */
    public function destroyBtn(&$searches, $id){
        $html = '';
        
        // 削除フラグONの時のみ、抹消ボタンを表示する
        if(!empty($searches['delete_flg'])){
            // 抹消ボタンを作成
            $html = "<input type='button' data-id='{$id}' onclick='destroyBtn(this)' class='row_eliminate_btn btn btn-danger btn-sm text-light' value='抹消'>";
        }
        return $html;
    }
    

    /**
     * JSONに変換して埋め込み
     * @param [] $data
     */
    public function embedJson($xid, $data){
        
        $jData = [];
        if(gettype($data) == 'object'){
            foreach($data as $ent){
                $jData[] = (array)$ent;
            }
            
        }elseif(gettype($data) == 'array'){
            $jData = $data;
        }else{
            throw new Exception('220709A');
        }
        
        $json = json_encode($jData, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS);
        $html = "<input type='hidden' id='{$xid}' value='{$json}'>";
        return $html;
    }
    
    
    /**
     * 金額などの数値を3桁区切り表記に変換する
     * @param int $number 任意の数値
     * @throws Exception
     * @return string 3桁区切り表記文字列
     */
    public function amount($number){
        if($number === '' || $number === null) return null;
        if(!is_numeric($number)) throw new Exception('220711A CrudBaseHelper:amount:');
        return number_format($number);
        
        
    }
    
    
    /**
     * 複数有効/削除の区分を表示する
     * @param [] $delete_flg 
     * - help_flg string ヘルプフラグ 0:ヘルプ表示しない, 1:ヘルプを表示（デフォルト）$this
     * - help_msg string ヘルプメッセージ
     */
    public function divPwms($delete_flg){
        
        $help_msg = "※ID列の左側にあるチェックボックスにチェックを入れてから「削除」ボタンを押すと、まとめて削除されます。<br>";
        
  		$help_html = "<aside>{$help_msg}</aside>";
        
  		$undelete_display = '';
  		$delete_display = '';
  		
  		if($delete_flg ==='0' || $delete_flg ===0){
  			$undelete_display = 'display:none;';
  		}else if($delete_flg ==='1' || $delete_flg ===1){
  			$delete_display = 'display:none;';
  		}
  		
  		
        $html = "
			<div style='margin-top:10px;margin-bottom:10px'>
				<label for='pwms_all_select'>すべてチェックする <input type='checkbox' name='pwms_all_select' onclick='pwmsSwitchAll(this);' /></label>
				<button type='button' onclick='pwmsAction(10)' class='btn btn-success btn-sm' style='{$undelete_display}'>削除取消</button>
				<button type='button' onclick='pwmsAction(11)' class='btn btn-danger btn-sm' style='{$delete_display}'>削除</button>
				{$help_html}
			</div>
		";
				echo $html;
    }
    
    
    /**
     * シンプルなSELECT要素を作成
     * @param string $name SELECTのname属性
     * @param string $value 初期値
     * @param array $list 選択肢
     * @param array $option オプション  要素の属性情報
     * @param array $empty 未選択状態に表示する選択肢名。nullをセットすると未選択項目は表示しない
     *
     */
    public function selectX($name,$value,$list,$option=null,$empty=null){
        
        // オプションから各種属性文字を作成する。
        $optionStr = "";
        if(!empty($option)){
            foreach($option as $attr_name => $v){
                $str = $attr_name.'="'.$v.'" ';
                $optionStr.= $str;
            }
        }
        
        
        $def_op_name = '';
        
        echo "<select  name='{$name}' {$optionStr} class='form-control search_btn_x'>";
        
        if($empty!==null){
            $selected = '';
            if($value===null){
                $selected='selected';
            }
            echo "<option value='' {$selected}>{$empty}</option>";
        }
        
        foreach($list as $v=>$n){
            $selected = '';
            if($value==$v){
                $selected='selected';
            }
            
            $n = str_replace(array('<','>'),array('&lt;','&gt;'),$n);
            
            echo "<option value='{$v}' {$selected}>{$n}</option>";
            
        }
        
        echo "</select>";
    }
    
    
    /**
     * 入力フォーム用のセレクトボックスを生成
     * @param string $field フィールド
     * @param [] $list
     * @param string $empty
     * @return string HTML
     */
    public function selectForInpForm($field, $list, $empty=null){
    	
    	$options = [];
    	
    	if(!empty($empty)){
    		$options[] = "<option>{$empty}</option>";
    	}
    	
    	foreach($list as $value => $name){
    		$name = h($name);
    		$options[] = "<option value='{$value}'>{$name}</option>";
    	}
    	
    	$option_html = implode("\n", $options);
    	
    	$html = "
	    	<select name='{$field}' class='form-control form-control-lg'>
				{$option_html}
	    	</select>
		";
    	
    	return $html;

    }
    
    
    /**
     * CrudBase.jsまたは、関連スクリプト群の読み込み部分HTMLコードを作成する
     * @param string $mode モード 0:CrudBase.min.jsを読み込む   1:CrudBaseを構成するスクリプトを別個で読み込む
     * @param string $this_page_version バージョン
     * @return string HTMLコード → <script>～
     */
    public function crudBaseJs($mode, $this_page_version){

    	if($mode == 0){
    		return $this->crudBaseJsDist($this_page_version);
    	}else{
			if (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
				// 開発環境の場合
				return $this->crudBaseJsDev($this_page_version);
			}else{
				// 本番環境用はCrudBase.min.jsを読み込む
				return $this->crudBaseJsDist($this_page_version);
			}
    	}

    }
    
    
    /**
     * CrudBase.min.jsを読み込むHTMLコードを作成する
     * @param string $this_page_version バージョン
     * @return string HTMLコード → <script>～
     */
    public function crudBaseJsDist($this_page_version){
    	$url = url('js/CrudBase/dist/CrudBase.min.js') ;
    	$ver_str = '?v=' . $this_page_version;
    	$html = "<script src='{$url}{$ver_str}' defer></script>";
    	return $html;
    }
    
    
    /**
     * CrudBase関連スクリプト群の読み込み部分HTMLコードを作成する（スクリプト別個読込版）
     * @param string $this_page_version バージョン
     * @return string HTMLコード → <script>～
     */
    public function crudBaseJsDev($this_page_version){
    	$path = public_path('js/CrudBase/src') ;
    	$jsPaths = glob($path . '/*.js'); // ディレクトリ内のすべてのjsファイルを取得
    	
    	$jsFiles = [];
    	foreach($jsPaths as $js_path){
    		$jsFiles[] = basename($js_path);
    	}
    	
    	$jsUrls = [];
    	foreach($jsFiles as $fn){
    		$jsUrls[] = url('js/CrudBase/src/' . $fn);
    	}
    	
    	$ver_str = '?v=' . $this_page_version;
    	
    	$readScripts = [];
    	foreach($jsUrls as $js_url){
    		$readScripts[] = "<script src='{$js_url}{$ver_str}' defer></script>";
    	}
    	
    	$html = implode('', $readScripts);
    	return  $html;
    	
    }
    
    
    /**
     * CrudBase.cssまたは、関連スクリプト群の読み込み部分HTMLコードを作成する
     * @param string $mode モード 0:CrudBase.min.cssを読み込む   1:CrudBaseを構成するスクリプトを別個で読み込む
     * @param string $this_page_version バージョン
     * @return string HTMLコード → <script>～
     */
    public function crudBaseCss($mode, $this_page_version){
    	
    	if($mode == 0){
    		return $this->crudBaseCssDist($this_page_version);
    	}else{
    		return $this->crudBaseCssDev($this_page_version);
    	}
    	
    }
    
    
    /**
     * CrudBase.min.cssを読み込むHTMLコードを作成する
     * @param string $this_page_version バージョン
     * @return string HTMLコード → <script>～
     */
    public function crudBaseCssDist($this_page_version){
    	$url = url('css/CrudBase/dist/CrudBase.min.css') ;
    	$ver_str = '?v=' . $this_page_version;
    	$html = "<link href='{$url}{$ver_str}' rel='stylesheet'>";
    	return $html;
    }
    
    
    /**
     * CrudBase.css関連スクリプト群の読み込み部分HTMLコードを作成する（スクリプト別個読込版）
     * @param string $this_page_version バージョン
     * @return string HTMLコード → <script>～
     */
    public function crudBaseCssDev($this_page_version){
    	$path = public_path('css/CrudBase/src') ;
    	$jsPaths = glob($path . '/*.css'); // ディレクトリ内のすべてのjsファイルを取得
    	
    	$jsFiles = [];
    	foreach($jsPaths as $css_path){
    		$jsFiles[] = basename($css_path);
    	}
    	
    	$jsUrls = [];
    	foreach($jsFiles as $fn){
    		$jsUrls[] = url('css/CrudBase/src/' . $fn);
    	}
    	
    	$ver_str = '?v=' . $this_page_version;
    	
    	$readScripts = [];
    	foreach($jsUrls as $url){
    		$readScripts[] = "<link href='{$url}{$ver_str}' rel='stylesheet'>";
    	}
    	
    	$html = implode('', $readScripts);
    	return  $html;
    	
    }
    
    
    /**
     * 画像アップロード要素を作成する
     * @param string $xid ファイル要素のid属性
     * @param string $name ファイル要素のname属性→省略可：省略時は$xidがセットされる。
     */
    public function imgInput($xid, $name = ''){
    	
    	if(empty($name)) $name = $xid;
    	
    	$html = "
			<div class='cbf_input' style='width:100%;height:auto;'>
			
				<label for='img_fn' class='fuk_label' >
					<input type='file' id='{$xid}' name='{$name}' class='img_fn' style='display:none' accept='image/*' title='画像ファイルをドラッグ＆ドロップ(複数可)' data-inp-ex='image_fuk' data-fp='' />
					<span class='fuk_msg' style='padding:20%'>画像ファイルをドラッグ＆ドロップ(複数可)</span>
				</label>
				
			</div>
		";
    	
    	return $html;
    }
    
    
    /**
     * ページネーションを生成
     * @param int $current_page 現在ページ
     * @param int $per_page 制限行数
     * @param int $total_count 全件数
     */
    public function pagenation(){
    	
    	$page_no = $this->searches['page'];
    	$per_page = $this->searches['per_page'];
    	$total_count = $this->crudBaseData['data_count'];

    	return $this->pagenationEx3->pagenation($page_no, $per_page, $total_count);
    	
    }
    
    
    /**
     * 検索フォーム：ID
     * @return string
     */
    public function searchFormId(){
    	
    	$value = h($this->searches['id']);
    	
    	$html = "
			<div>
				<span class='search_form_label' style='display:none'>ID</span>
				<input type='search' placeholder='ID' name='id' value='{$value}' class='form-control search_btn_x js_search_inp' title='検索するIDを入力してください。' style='width:7em' pattern='^[0-9]+$'>
				<span class='searche_err text-danger' style='display:none'>👈IDに入力エラーがあります。自然数で入力してください。</span>
			</div>
		";
    	
    	return $html;
    }
    
    
    /**
     * 検索フォーム：テキスト
     * @param string $field フィールド
     * @param string $display_name 表示名
     * @param [] $options
     *     int $maxlength 最大入力文字数
     *     string width 横幅の長さ（CSSによる幅指定。 例→200px)
     *     string pattern バリデーションの正規表現
     *     string err_msg エラーメッセージ（置換指定可）     例→「%display_nameはカタカナで入力してください」
     *     string title ツールチップ
     * @param int $maxlength 最大入力文字数
     * @param string $pattern バリデーションの正規表現
     * @return string
     */
    public function searchFormText($field, $display_name, $option = []){
    	
    	$maxlength = $option['maxlength'] ?? 2000;
    	$width = $option['width'] ?? '20em';
    	$pattern = $option['pattern'] ?? '';
    	$err_msg = $option['err_msg'] ?? '';
    	$title = $option['title'] ?? '';
    	
    	// エラーメッセージの作成および加工処理。
    	if(empty($err_msg)){
    		$err_msg = "👈「%display_name」に入力エラーがあります。";
    	}
    	$err_msg = str_replace('%display_name', $display_name, $err_msg);
    	
    	// ツールチップの作成および加工処理。
    	if(empty($title)){
    		$title = "「%display_name」で検索します。";
    	}
    	$title = str_replace('%display_name', $display_name, $title);

    	
    	$value = h($this->searches[$field] ?? '');
    	
    	if(!empty($pattern)){
    		$pattern = "pattern='{$pattern}'";
    	}
    	
    	$html = "
			<div>
				<span class='search_form_label' style='display:none'>{$display_name}</span>
				<input type='search' placeholder='{$display_name}' 
					name='{$field}' 
					value='{$value}' 
					class='form-control search_btn_x js_search_inp' 
					maxlength = '{$maxlength}'
					title='{$title}' 
					style='width:{$width}' 
					{$pattern}
				>
				<span class='searche_err text-danger' style='display:none'>{$err_msg}</span>
			</div>
		";
    	
    	return $html;
    }
    
    
    /**
     * 検索フォーム：自然数
     * @param string $field フィールド
     * @param string $display_name 表示名
     * @param [] $options
     *     int $maxlength 最大入力文字数
     *     string width 横幅の長さ（CSSによる幅指定。 例→200px)
     *     string pattern バリデーションの正規表現
     *     string err_msg エラーメッセージ（置換指定可）     例→「%display_nameはカタカナで入力してください」
     *     string title ツールチップ
     * @param int $maxlength 最大入力文字数
     * @param string $pattern バリデーションの正規表現
     * @return string
     */
    public function searchFormInt($field, $display_name, $option = []){
    	
    	$maxlength = $option['maxlength'] ?? 2000;
    	$width = $option['width'] ?? '8em';
    	$pattern = $option['pattern'] ?? '\d+';
    	$err_msg = $option['err_msg'] ?? '';
    	$title = $option['title'] ?? '';
    	
    	// エラーメッセージの作成および加工処理。
    	if(empty($err_msg)){
    		$err_msg = "👈「%display_name」に入力エラーがあります。";
    	}
    	$err_msg = str_replace('%display_name', $display_name, $err_msg);
    	
    	// ツールチップの作成および加工処理。
    	if(empty($title)){
    		$title = "「%display_name」で検索します。";
    	}
    	$title = str_replace('%display_name', $display_name, $title);
    	
    	
    	$value = h($this->searches[$field] ?? '');
    	
    	if(!empty($pattern)){
    		$pattern = "pattern='{$pattern}'";
    	}
    	
    	$html = "
			<div>
				<span class='search_form_label' style='display:none'>{$display_name}</span>
				<input type='number' placeholder='{$display_name}'
					name='{$field}'
					value='{$value}'
					class='form-control search_btn_x js_search_inp'
					maxlength = '{$maxlength}'
					title='{$title}'
					style='width:{$width}'
					{$pattern}
				>
				<span class='searche_err text-danger' style='display:none'>{$err_msg}</span>
			</div>
		";
					
					return $html;
    }
    
    
    /**
     * 検索フォーム： 月・日付範囲検索
     *
     * @param string $field フィールド
     * @param string $display_name 表示名
     * @param [] $options 未使用
     * @param int $maxlength 最大入力文字数
     * @param string $pattern バリデーションの正規表現
     * @return string
     */
    public function searchFormDateRng($field, $display_name, $option = []){
    	
    	// 年月を取得
    	$field_ym = $field . '_ym';
    	$ym = $this->searches[$field_ym];
    	
    	$field1 = $field . '1';
    	$date1 =  $this->searches[$field1];
    	
    	$field2 = $field . '2';
    	$date2 =  $this->searches[$field2];
    	
    	return "<div id='{$field}' class='range_ym_ex' data-display_name='{$display_name}' data-def-ym='{$ym}' data-def1='{$date1}' data-def2='{$date2}' style='margin-right:40px;display:inline-block'></div>";
    	
    }
    
    
    /**
     * 検索フォーム：SELECT
     * @param string $field フィールド
     * @param string $display_name 表示名
     * @param [] $list 選択リスト
     * @param [] $options
     *     string width 横幅の長さ（CSSによる幅指定。 例→200px)
     *     string title ツールチップ
     *     boolean not_empty_flg 空無フラグ 0:空選択あり（デフォ）, 1:空選択なし
     * @param int $maxlength 最大入力文字数
     * @param string $pattern バリデーションの正規表現
     * @return string
     */
    public function searchFormSelect($field, $display_name, $list, $option = []){
    	
    	$width = $option['width'] ?? 'auto';
    	$title = $option['title'] ?? '';
    	$not_empty_flg = $option['not_empty_flg'] ?? false;
    	
    	// ツールチップの作成および加工処理。
    	if(empty($title)){
    		$title = "「%display_name」で検索します。";
    	}
    	$title = str_replace('%display_name', $display_name, $title);
    	
		$optionList = []; // 選択肢リスト
		
		// 空選択を選択肢リストに追加
		if($not_empty_flg == false){
			$optionList[] = "<option value=''> - {$display_name}(未選択) - </option>";
		}
		
		// 選択肢を作成する
		$select_value = h($this->searches[$field] ?? '');
		foreach($list as $value => $name){
			$name = h($name);
			
			$selected = '';
			if($value == $select_value) $selected = 'selected';
			
			$optionList[] = "<option value='{$value}' {$selected}>{$name}</option>";
		}
		
		
		$option_html = implode("\n", $optionList);
    	
    	$html = "
			<div>
				<span class='search_form_label' style='display:none'>{$display_name}</span>
				<select name='{$field}' class='form-control js_search_inp' title='{$title}' style='width:{$width}'>
					{$option_html}
				</select>
			</div>
		";
					
		return $html;
    }
    
    
    /**
     * 検索フォーム：フラグ
     * @param string $field フィールド
     * @param string $display_name 表示名
     * @param [] $list 選択リスト
     * @param [] $options
     *     - [] list 選択肢リスト
     *     - string width 横幅の長さ（CSSによる幅指定。 例→200px)
     *     - string title ツールチップ
     *     - boolean not_empty_flg 空無フラグ 0:空選択あり（デフォ）, 1:空選択なし
     * @param int $maxlength 最大入力文字数
     * @param string $pattern バリデーションの正規表現
     * @return string
     */
    public function searchFormFlg($field, $display_name,  $option = []){
    	
    	$width = $option['width'] ?? 'auto';
    	$title = $option['title'] ?? '';
    	$not_empty_flg = $option['not_empty_flg'] ?? false;
    	
    	// ツールチップの作成および加工処理。
    	if(empty($title)){
    		$title = "「%display_name」で検索します。";
    	}
    	$title = str_replace('%display_name', $display_name, $title);
    	
    	$optionList = $option['list'] ?? []; // 選択肢リスト
    	if(empty($optionList)){
    		if($not_empty_flg == false){
    			$optionList[-1] = " - {$display_name}(未選択) -";
    		}
    		$optionList[0] = 'OFF';
    		$optionList[1] = 'ON';
    	}
    	
    	// 選択肢を作成する
    	$select_value = h($this->searches[$field] ?? '-1');
    	foreach($optionList as $value => $name){

    		$selected = '';
    		if($value == $select_value) $selected = 'selected';
    
    		$optionList[] = "<option value='{$value}' {$selected}>{$name}</option>";
    	}
    	
    	$option_html = implode("\n", $optionList);
    
    	// 未選択の選択肢が存在する場合はラベルを非表示。未選択の選択肢が存在する場合、ラベルを表示する。
    	$label_display_style = '';
    	if($not_empty_flg == false) $label_display_style = 'display:none;';
    	
    	$html = "
			<div>
				<span class='search_form_label' style='{$label_display_style}'>{$display_name}</span>
				<select name='{$field}' class='form-control js_search_inp' title='{$title}' style='width:{$width}'>
					{$option_html}
				</select>
			</div>
		";
					
		return $html;
    }
    
    
    /**
     *
     * 検索フォーム:自然数入力フォームを生成
     *
     * @param string $field フィールド名（ kj_ を付けないこと）
     * @param string $display_name フィールド表示名
     * @param [] $options
     *     - int maxlength 入力文字数
     *     - string width 横幅の長さ（CSSによる幅指定。 例→200px)
     *     - string pattern バリデーションの正規表現
     *     - string err_msg エラーメッセージ
     *     - double step ステップ
     */
    public function searchFormIntRange($field, $display_name, $option=[]){
    	
    	$maxlength = $option['maxlength'] ?? 64;
    	$width = $option['width'] ?? '8em';
    	$pattern = $option['pattern'] ?? '\d+';
    	$err_msg = $option['err_msg'] ?? '';
    	$step = $option['err_msg'] ?? 1;
    	
    	// エラーメッセージの作成および加工処理。
    	if(empty($err_msg)){
    		$err_msg = "%display_nameは自然数で入力してください。";
    	}
    	$err_msg = str_replace('%display_name', $display_name, $err_msg);
    	
    	$field1 = "{$field}1";
    	$field2 = "{$field}2";
    	$value1 = h($this->searches[$field1]);
    	$value2 = h($this->searches[$field2]);
    	
    	echo "
			<div style='margin:0px 12px;'>
				<div  style='display:inline-block'>
					<input name='{$field1}'
						value='{$value1}'
						class='kjs_inp form-control js_search_inp'
						placeholder='{$display_name}～'
						title='{$display_name}～'
						type='number'
						style='width:{$width}'
						pattern='{$pattern}'
						step='{$step}'
						maxlength = '{$maxlength}'
						>
						<span id='{$field}1_err' class='text-danger searche_err'  style='display:none' >{$err_msg}</span>
				</div>
				<span>～</span>
				<div class='input number' style='display:inline-block'>
					<input name='{$field2}'
						value='{$value2}'
						class='kjs_inp form-control js_search_inp'
						placeholder='～{$display_name}'
						title='～{$display_name}'
						type='number'
						style='width:{$width}'
						pattern='{$pattern}'
						step='{$step}'
						maxlength = '{$maxlength}'
						>
					<span id='{$field}2_err' class='text-danger searche_err' style='display:none'>{$err_msg}</span>
				</div>
			</div>
		";
    	
    }
    
    	
    /**
     *
     * 検索フォーム:浮動小数範囲入力フォームを生成
     *
     * @param string $field フィールド名（ kj_ を付けないこと）
     * @param string $display_name フィールド表示名
     * @param [] $options
     *     - int maxlength 入力文字数
     *     - string width 横幅の長さ（CSSによる幅指定。 例→200px)
     *     - string pattern バリデーションの正規表現
     *     - string err_msg エラーメッセージ
     *     - double step ステップ
     */
    public function searchFormDoubleRange($field, $display_name, $option=[]){
    	
    	$maxlength = $option['maxlength'] ?? 64;
    	$width = $option['width'] ?? '8em';
    	$pattern = $option['pattern'] ?? '[0-9]+([\.,][0-9]+)?';
    	$err_msg = $option['err_msg'] ?? '';
    	$step = $option['step'] ?? 0.01;
    		
    	// エラーメッセージの作成および加工処理。
    	if(empty($err_msg)){
    		$err_msg = "%display_nameは数値で入力してください。";
    	}
    	$err_msg = str_replace('%display_name', $display_name, $err_msg);

    	$field1 = "{$field}1";
    	$field2 = "{$field}2";
    	$value1 = h($this->searches[$field1]);
    	$value2 = h($this->searches[$field2]);
    	
    	echo "
			<div style='margin:0px 12px;'>
				<div  style='display:inline-block'>
					<input name='{$field1}' 
						value='{$value1}'
						class='kjs_inp form-control js_search_inp' 
						placeholder='{$display_name}～' 
						title='{$display_name}～'
						type='number' 
						style='width:{$width}' 
						pattern='{$pattern}' 
						step='{$step}' 
						maxlength = '{$maxlength}'
						>
						<span id='{$field}1_err' class='text-danger searche_err'  style='display:none' >{$err_msg}</span>
				</div>
				<span>～</span>
				<div class='input number' style='display:inline-block'>
					<input name='{$field2}' 
						value='{$value2}'
						class='kjs_inp form-control js_search_inp' 
						placeholder='～{$display_name}' 
						title='～{$display_name}'
						type='number' 
						style='width:{$width}' 
						pattern='{$pattern}' 
						step='{$step}' 
						maxlength = '{$maxlength}'
						>
					<span id='{$field}2_err' class='text-danger searche_err' style='display:none'>{$err_msg}</span>
				</div>
			</div>
		";
    	
    }
    
    
    /**
     * 検索用の生成日時セレクトフォームを作成
     */
    public function searchFormCreated($field='created_at', $display_name='生成日時'){
    	
    	return $this->searchFormDatetime($field, $display_name);
    }
    
    
    /**
     * 検索用の更新日時セレクトフォームを作成
     */
    public function searchFormUpdated($field='updated_at', $display_name='更新日時'){
    	
    	return $this->searchFormDatetime($field, $display_name);
    }
    
    
    /**
     * 検索フォーム：日時
     *
     * @param string $field フィールド名
     * @param string $display_name 表示名
     * @param [] $options 
     *     - [] list 選択肢リスト
     *     - string width 入力フォームの横幅
     *     - string title ツールチップメッセージ
     */
    public function searchFormDatetime($field, $display_name, $option = []){
    	
    	$width = $option['width'] ?? 'auto';
    	$title = $option['title'] ?? '';
    	$list = $option['list'] ?? [];
    	
    	if(empty($title)) $title = $display_name . "で範囲検索";

    	if(empty($list)) $list = $this->getDateTimeList();
    	
    	$searches = $this->searches;
    	
    	$d1 = $searches[$field] ?? '';
    	$u1 = strtotime($d1);
    	
    	// option要素群
    	$options_str = ''; // option要素群文字列
    	foreach($list as $d2 => $name){
    		
    		$selected = '';
    		$u2 = strtotime($d2);
    		if(!empty($u1)){
    			if($u1 == $u2) $selected = 'selected';
    		}
    		
    		$name = h($name); // XSSサニタイズ
    		$options_str .= "<option value='{$d2}' $selected>{$name}</option>";
    	}

		$msg = '';
		if(!empty($d1)){
			$msg = "検索対象 ～{$d1}";
		}
				
		$parent_element_selector = "sdg_{$field}";
				
		$html = "
			<div class='{$parent_element_selector}' data-field='{$field}' style='display:inline-block'>
				<div class='input select'>
					<select name='{$field}' id='{$field}' style='width:{$width}' class='kjs_inp form-control sdg_select js_search_inp' title='{$title}'>
						<option value=''> - {$display_name}(未選択) - </option>
						{$options_str}
					</select>
				</div>
				<div class='text-danger sdg_msg'>{$msg}</div>
				<input type='hidden' class='sdg_value' value='{$d1}' >
				
			</div>";
				
		return $html;
				
    }
    
    
    /**
     * 検索入力フォーム：削除SELECTボックス
     * @param string $field フィールド
     * @param string $display_name 表示名
     * @param array $option
     */
    public function searchFormDelete($field = 'delete_flg', $display_name = '有効/削除', $option=[]){
    	
    	$delete_flg = $this->searches[$field];
    	$selected_0 = '';
    	$selected_1 = '';
    	if($delete_flg == 1){
    		$selected_1 = 'selected';
    	}else{
    		$selected_0 = 'selected';
    	}
    	
    	$html = "
    	<select name='delete_flg' class='form-control search_btn_x js_search_inp'>
	    	<option value=''> - 有効/削除 - </option>
	    	<option value='0' {$selected_0}>有効</option>
	    	<option value='1' {$selected_1}>削除</option>
    	</select>
    	";
    	
    	return $html;
    }
    
    
    /**
     * 日時選択肢リストを取得する
     *
     * @return array 日時選択肢リスト
     */
    private function getDateTimeList(){
    	
    	
    	if(!empty($this->_dateTimeList)){
    		return $this->_dateTimeList;
    	}
    	
    	$d1=date('Y-m-d');//本日
    	$d2=$this->getBeginningWeekDate($d1);//週初め日付を取得する。
    	$d3 = date('Y-m-d', strtotime("-10 day"));//10日前
    	$d4 = $this->getBeginningMonthDate($d1);//今月一日を取得する。
    	$d5 = date('Y-m-d', strtotime("-30 day"));//30日前
    	$d6 = date('Y-m-d', strtotime("-50 day"));//50日前
    	$d7 = date('Y-m-d', strtotime("-100 day"));//100日前
    	$d8 = date('Y-m-d', strtotime("-180 day"));//180日前
    	$d9 = $this->getBeginningYearDate($d1);//今年元旦を取得する
    	$d10 = date('Y-m-d', strtotime("-365 day"));//365日前
    	
    	$list= [
    			$d1=>'本日',
    			$d2=>'今週（日曜日から～）',
    			$d3=>'10日以内',
    			$d4=>'今月（今月一日から～）',
    			$d5=>'30日以内',
    			$d6=>'50日以内',
    			$d7=>'100日以内',
    			$d8=>'半年以内（180日以内）',
    			$d9=>'今年（今年の元旦から～）',
    			$d10=>'1年以内（365日以内）',
    	];
    	
    	$this->_dateTimeList = $list;
    	
    	return $list;
    	
    }
    
    /**
     * 引数日付の週の週初め日付を取得する。
     * 週初めは日曜日とした場合。
     * @param $ymd
     * @return DateTime 週初め
     */
    private function getBeginningWeekDate($ymd) {
    	
    	$w = date("w",strtotime($ymd));
    	$bwDate = date('Y-m-d', strtotime("-{$w} day", strtotime($ymd)));
    	return $bwDate;
    	
    }
    
    /**
     * 引数日付から月初めの日付を取得する。
     * @param $ymd
     */
    private function getBeginningMonthDate($ymd) {
    	
    	$ym = date("Y-m",strtotime($ymd));
    	$d=$ym.'-01';
    	
    	return $d;
    	
    }
    
    /**
     * 引数日付から元旦日を取得する。
     * @param $ymd
     */
    private function getBeginningYearDate($ymd) {
    	
    	$y = date("Y",strtotime($ymd));
    	$d=$y.'-01-01';
    	
    	return $d;
    	
    }
    
    
    /**
     * スネークケースにキャメルケースから変換
     * @param string $str キャメルケース
     * @return string スネークケース
     */
    private function snakize($str) {
    	$str = preg_replace('/[A-Z]/', '_\0', $str);
    	$str = strtolower($str);
    	return ltrim($str, '_');
    }
    
    
    /**
     * 検索用の表示件数セレクトを作成
     */
    public function searchFormLimit(){

    	$list = [
    			'5' =>"5件表示",
    			'10' =>"10件表示",
    			'20' =>"20件表示",
    			'50' =>"50件表示",
    			'100' =>"100件表示",
    			'200' =>"200件表示",
    			'500' =>"500件表示",
    	];
    	
    	// SELECT選択肢の組み立て
    	$exist_value = $this->crudBaseData['searches']['per_page'];
    	if(empty($exist_value)){
    		$exist_value = $this->crudBaseData['def_per_page'];
    	}
    	$option_html = '';
    	foreach($list as $key => $value){
    		$selected = '';
    		if($key == $exist_value) $selected = " selected='selected'";
    		$option_html .= "<option value='{$key}' {$selected}>{$value}</option>";
    	}
    	
    	$html = "
			<div  data-field='row_limit' >
				<select name='per_page' id='row_limit'  class='form-control search_btn_x js_search_inpl' style='width:8em'>
					{$option_html}
				</select>
			</div>
		";
						
		return $html;
						
    }
    
    
    
    
}

    



// 「 h()関数」
if (!function_exists('h')) {
	
	function h($text) {
		$double = true;
		$charset = null;
		
		if (is_string($text)) {
			
		} elseif (is_array($text)) {
			$texts = array();
			foreach ($text as $k => $t) {
				$texts[$k] = h($t, $double, $charset);
			}
			return $texts;
		} elseif (is_object($text)) {
			if (method_exists($text, '__toString')) {
				$text = (string)$text;
			} else {
				$text = '(object)' . get_class($text);
			}
		} elseif (is_bool($text)) {
			return $text;
		}
		
		$defaultCharset = 'UTF-8';
		
		if (is_string($double)) {
			$charset = $double;
			$double = true;
		}
		return htmlspecialchars($text, ENT_QUOTES, ($charset) ? $charset : $defaultCharset, $double);
	}
	
	
	
}







