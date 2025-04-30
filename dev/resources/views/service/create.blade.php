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
	
	<script src="{{ asset('/js/app.js') }}" defer></script>
	<script src="{{ asset('/js/common/jquery-3.6.0.min.js') }}" defer></script>
	{!! $cbh->crudBaseJs(1, $this_page_version) !!}
	<script src="{{ asset('/js/Service/create.js')  . $ver_str}} }}" defer></script>
	
	<link href="{{ asset('/css/app.css')  . $ver_str}}" rel="stylesheet">
	<link href="{{ asset('/js/font/css/open-iconic.min.css') }}" rel="stylesheet">
	<link href="{{ asset('/css/common/common.css')  . $ver_str}}" rel="stylesheet">
	{!! $cbh->crudBaseCss(0, $this_page_version) !!}
	<link href="{{ asset('/css/Service/create.css')  . $ver_str}}" rel="stylesheet">
	
	<title>奉仕管理・新規登録フォーム</title>
	
</head>

<body>
@include('layouts.common_header')
<div class="container-fluid">

<div id="app"><!-- vue.jsの場所・未使用 --></div>

<div class="d-flex flex-row m-1 m-sm-4 px-sm-5 px-1">

<main class="flex-fill mx-sm-2 px-sm-5 mx-1 px-1 w-100">

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="{{ url('/') }}">ホーム</a></li>
	<li class="breadcrumb-item"><a href="{{ url('service') }}">奉仕管理・一覧</a></li>
	<li class="breadcrumb-item active" aria-current="page">奉仕管理・新規登録フォーム</li>
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

<div>
	<div class="form_w" >
		<form id="form1" method="POST" action="{{ url('service/store') }}" onsubmit="return checkDoublePress()" enctype="multipart/form-data">
			@csrf
			
			<div class="row">
				<div class="col-12" style="text-align:right">
					<button  class="btn btn-success btn-lg js_submit_btn" onclick="return onSubmit1()">登録</button>
					<div class="text-danger js_valid_err_msg"></div>
					<div class="text-success js_submit_msg" style="display:none" >データベースに登録中です...</div>
				</div>
			</div>
			
			
			<!-- CBBXS-6090 -->
			<div class="row">
				<label for="service_template_id" class="col-12 col-md-5 col-form-label">奉仕テンプレID（外部キー）</label>
				<div class="col-12 col-md-7">
					<input name="service_template_id" type="number"  class="form-control form-control-lg" placeholder="service_template_id" value="{{old('service_template_id', $ent->service_template_id)}}">
				</div>
			</div>
			
			<div class="row">
				<label for="week_start_date" class="col-12 col-md-5 col-form-label">週始め日（月曜日の日付）</label>
				<div class="col-12 col-md-7">
					<input name="week_start_date" type="text"  class="form-control form-control-lg" placeholder="week_start_date" value="{{old('week_start_date', $ent->week_start_date)}}" pattern="[0-9]{4}(-|/)[0-9]{1,2}(-|/)[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}" title="日時（Y-m-d H:i:s)を入力してください。(例  2012-12-12 12:12:12)">
				</div>
			</div>
			
			<div class="row">
				<label for="location" class="col-12 col-md-5 col-form-label">奉仕場所</label>
				<div class="col-12 col-md-7">
					<input name="location" type="date"  class="form-control form-control-lg" placeholder="location" value="{{old('location', $ent->location)}}">
				</div>
			</div>
			
			<div class="row">
				<label for="notes" class="col-12 col-md-5 col-form-label">備考</label>
				<div class="col-12 col-md-7">
					<textarea name="notes" id="notes" class="form-control form-control-lg" placeholder="備考"  maxlength="2000">{{old('notes', $ent->notes)}}</textarea>
				</div>
			</div>


			<!-- CBBXE -->

			<div class="row">
				<div class="col-12" style="text-align:right">
					<button  class="btn btn-success btn-lg js_submit_btn" onclick="return onSubmit1()">登録</button>
					<div class="text-danger js_valid_err_msg"></div>
					<div class="text-success js_submit_msg" style="display:none" >データベースに登録中です...</div>
				</div>
			</div>
			
		</form>
		
	</div>
</div>

</main>
</div><!-- d-flex -->

</div><!-- container-fluid -->

@include('layouts.common_footer')

<!-- JSON埋め込み -->
<input type="hidden" id="csrf_token" value="{{ csrf_token() }}" >
{!! $cbh->embedJson('crud_base_json', $crudBaseData) !!}

</body>
</html>