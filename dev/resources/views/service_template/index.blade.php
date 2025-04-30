<?php
use App\Helpers\CrudBaseHelper;

$ver_str = '?v=' . $this_page_version;
$cbh = new CrudBaseHelper($crudBaseData);
?>
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="favicon.png" type="image/png">
	
	<script src="{{ asset('/js/app.js') }}" defer></script>
	<script src="{{ asset('/js/jquery-3.6.1.min.js') }}" defer></script>
	{!! $cbh->crudBaseJs(1, $this_page_version) !!}
	<script src="{{ asset('/js/ServiceTemplate/index.js')  . $ver_str}} }}" defer></script>
	
	<link href="{{ asset('/css/app.css')  . $ver_str}}" rel="stylesheet">
	<link href="{{ asset('/js/font/css/open-iconic.min.css') }}" rel="stylesheet">
	{!! $cbh->crudBaseCss(0, $this_page_version) !!}
	<link href="{{ asset('/css/common/common.css')  . $ver_str}}" rel="stylesheet">
	<link href="{{ asset('/css/ServiceTemplate/index.css')  . $ver_str}}" rel="stylesheet">
	
	<title>奉仕テンプレ管理画面</title>
	
</head>

<body>
@include('layouts.common_header')
<div class="container-fluid">

<div id="app"><!-- vue.jsの場所・未使用 --></div>



<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="{{ url('/') }}">ホーム</a></li>
	<li class="breadcrumb-item active" aria-current="page">奉仕テンプレ管理画面(見本版)</li>
  </ol>
</nav>

<!-- バリデーションエラーの表示 -->
@if ($errors->any())
	<div class="alert alert-danger">
		<ul>
			@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif
<div id="err" class="text-danger"></div>

<main>

<!-- 検索フォーム -->
<form method="GET" action="service_template">
		
	<div><?php echo $cbh->searchFormText('main_search', '検索', ['title'=>'奉仕テンプレ名、備考を部分検索します']); ?></div>
	
	<div style="display:inline-block;">
		<div id="search_dtl_div" style="display:none;">
		
			<div><?php echo $cbh->searchFormId(); ?></div>
			
			<!-- CBBXS-6030 -->
			<div><?php echo $cbh->searchFormText('service_name', '奉仕名（例：火曜日の5時からの奉仕）'); ?></div>
			<div><?php echo $cbh->searchFormSelect('weekday', '曜日（0=日曜〜6=土曜）', $weekdayList); ?></div>
			<div><?php echo $cbh->searchFormText('conductor_name', '担当司会者'); ?></div>
			<div><?php echo $cbh->searchFormText('sub_conductor_name', '補助担当（いない場合もあり）'); ?></div>
			<div><?php echo $cbh->searchFormDatetime('start_time', '開始時刻（例：17:00:00）'); ?></div>

			<!-- CBBXE -->
			<div><?php echo $cbh->searchFormInt('sort_no', '順番'); ?></div>
			<div><?php echo $cbh->searchFormText('ip_addr', 'IPアドレス'); ?></div>
			<div><?php echo $cbh->searchFormDelete(); ?></div>
			<div><?php echo $cbh->searchFormText('update_user', '更新者'); ?></div>
			<div><?php echo $cbh->searchFormCreated(); ?></div>
			<div><?php echo $cbh->searchFormUpdated(); ?></div>
			<div><?php echo $cbh->searchFormLimit(); ?></div>

			<div><button type="button" class ="btn btn-outline-secondary" onclick="$('#search_dtl_div').toggle(300);">＜ 閉じる</button></div>
		</div>
	</div>
	<div style="display:inline-block;">
		<button type="submit" class ="btn btn-outline-primary">検索</button>
		<button type="button" class ="btn btn-outline-secondary" onclick="$('#search_dtl_div').toggle(300);">詳細</button>
		<button type="button" class="btn btn-outline-secondary" onclick="clearA()">クリア</button>

	</div>
</form>

<div style="margin-top:0.4em;">

	<!-- CrudBase設定 -->
	<div class="tool_btn_w">
		<div id="crud_base_config"></div>
	</div>

	<div class="tool_btn_w">
		<a href="service_template/csv_download" class="btn btn-secondary">CSV</a>
	</div>
	
	<!-- 列表示切替機能 -->
	<div class="tool_btn_w">
		<button class="btn btn-secondary" onclick="$('#csh_div_w').toggle(300);">列表示切替</button>
		<div id="csh_div_w" style="width:100vw;" >
			<div id="csh_div" ></div><!-- 列表示切替機能の各種チェックボックスの表示場所 -->
		</div>
	</div>
	
	<div class="tool_btn_w">
		<a href="service_template/create" class="btn btn-success">新規登録・MPA型</a>
		<button type="button" class="btn btn-success" onclick="clickCreateBtn();">新規登録・SPA型</button>
	</div>
</div>

<div id="auto_save" class="text-success"></div><!-- 自動保存のメッセージ表示区分 -->

<div class="d-flex" style="margin-top:12px;">{{$listData->appends(request()->query())->links('layouts.pagenatoin_b5')}} </div><!-- ページネーション -->

<table id="main_tbl" class="table table-striped table-bordered table-condensed">
	<thead>
		<tr>
			<th data-field='id'><?php echo $cbh->sortLink($searches, 'service_template', 'id', 'ID'); ?></th>
			<!-- CBBXS-6035 -->
			<th data-field='service_name'><?php echo $cbh->sortLink($searches, 'service_template', 'service_name', '奉仕名（例：火曜日の5時からの奉仕）'); ?></th>
			<th data-field='weekday'><?php echo $cbh->sortLink($searches, 'service_template', 'weekday', '曜日（0=日曜〜6=土曜）'); ?></th>
			<th data-field='conductor_name'><?php echo $cbh->sortLink($searches, 'service_template', 'conductor_name', '担当司会者'); ?></th>
			<th data-field='sub_conductor_name'><?php echo $cbh->sortLink($searches, 'service_template', 'sub_conductor_name', '補助担当（いない場合もあり）'); ?></th>
			<th data-field='start_time'><?php echo $cbh->sortLink($searches, 'service_template', 'start_time', '開始時刻（例：17:00:00）'); ?></th>

			<!-- CBBXE -->
			<th data-field='sort_no'><?php echo $cbh->sortLink($searches, 'service_template', 'sort_no', '順番'); ?></th>
			<th data-field='delete_flg'><?php echo $cbh->sortLink($searches, 'service_template', 'delete_flg', '無効フラグ'); ?></th>
			<th data-field='update_user_id'><?php echo $cbh->sortLink($searches, 'service_template', 'update_user_id', '更新者'); ?></th>
			<th data-field='ip_addr'><?php echo $cbh->sortLink($searches, 'service_template', 'ip_addr', 'IPアドレス'); ?></th>
			<th data-field='created_at'><?php echo $cbh->sortLink($searches, 'service_template', 'created_at', '生成日時'); ?></th>
			<th data-field='updated_at'><?php echo $cbh->sortLink($searches, 'service_template', 'updated_at', '更新日'); ?></th>

			<th class='js_btns' 'style="width:280px"></th>
		</tr>
	</thead>
	<tbody>
		@foreach ($listData as $ent)
			<tr>
				<td>{!! $cbh->tdId($ent->id) !!}</td>
				<!-- CBBXS-6040 -->
				<td>{{$ent->service_name}}</td>
				<td>{!! $cbh->tdList($ent->weekday, $weekdayList) !!}</td>
				<td>{{$ent->conductor_name}}</td>
				<td>{{$ent->sub_conductor_name}}</td>
				<td>{{$ent->start_time}}</td>

				<!-- CBBXE -->
				<td>{{$ent->sort_no}}</td>
				<td>{!! $cbh->tdDeleteFlg($ent->delete_flg) !!}</td>
				<td>{{$ent->update_user}}</td>
				<td>{{$ent->ip_addr}}</td>
				<td>{{$ent->created_at}}</td>
				<td>{{$ent->updated_at}}</td>

				<td>

					{!! $cbh->rowExchangeBtn($searches) !!}<!-- 行入替ボタン -->
					<button type="button" class="row_detail_btn btn btn-info btn-sm text-light " onclick="clickDetailBtn(this)">詳細</button>
					<button type="button" class="row_edit_btn btn btn-primary btn-sm" onclick="clickEditBtn(this)">編集</button>
					<button type="button" class="row_copy_btn btn btn-success btn-sm" onclick="clickCopyBtn(this)">複製</button>
					<a href="service_template/show?id={{$ent->id}}" class="row_detail_btn btn btn-info btn-sm text-light ">詳細・MPA型</a>
					<a href="service_template/edit?id={{$ent->id}}" class="row_edit_btn btn btn-primary btn-sm">編集・MPA型</a>
					<a href="service_template/create?id={{$ent->id}}" class="row_copy_btn btn btn-success btn-sm">複製・MPA型</a>
					{!! $cbh->disabledBtn($searches, $ent->id) !!}<!-- 削除/削除取消ボタン（無効/有効ボタン） -->
					{!! $cbh->destroyBtn($searches, $ent->id) !!}<!-- 抹消ボタン -->
					
					
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

<div class="d-flex" style="margin-top:12px;">{{$listData->appends(request()->query())->links('layouts.pagenatoin_b5')}} </div><!-- ページネーション -->

<?php $cbh->divPwms($searches['delete_flg']); // 複数有効/削除の区分を表示する ?>


</main>


@include('service_template.form_spa')




</div><!-- container-fluid -->

@include('layouts.common_footer')

<!-- JSON埋め込み -->
<input type="hidden" id="csrf_token" value="{{ csrf_token() }}" >
{!! $cbh->embedJson('crud_base_json', $crudBaseData) !!}

</body>
</html>