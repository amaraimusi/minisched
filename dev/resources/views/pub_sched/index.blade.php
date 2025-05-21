<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="favicon.png" type="image/png">
	
	<script src="{{ asset('/js/app.js') }}" defer></script>
	<script src="{{ asset('/js/jquery-3.6.1.min.js') }}" defer></script>
	<script src="{{ asset('/js/PubSched/index.js?v=')  . $app_version}} }}" defer></script>
	
	<link href="{{ asset('/css/app.css?v=')  . $app_version}}" rel="stylesheet">
	<link href="{{ asset('/js/font/css/open-iconic.min.css?v=') }}" rel="stylesheet">
	
	<link href="{{ asset('/css/common/common.css?v=')  . $app_version}}" rel="stylesheet">
	<link href="{{ asset('/css/PubSched/index.css?v=')  . $app_version}}" rel="stylesheet">
	
	<title>奉仕場所</title>
	
</head>

<body>

<div class="container-fluid">

<div id="app"></div><!-- vue.jsの場所・未使用 -->
<div id="app1"><!-- vue.jsの場所 -->
	  <input v-model="message1">
	  <div v-bind:title="text1">アマミノクロウサギ:title属性にセット</div>
	  <div v-bind:class="class1">サキシマハブ:クラス属性にセット</div>
</div>


<button type="button" onclick="test1()">テスト</button>

<div id="err" class="text-danger"></div>


</div><!-- container-fluid -->



<!-- JSON埋め込み -->
<input type="hidden" id="csrf_token" value="{{ csrf_token() }}" >




</body>
</html>