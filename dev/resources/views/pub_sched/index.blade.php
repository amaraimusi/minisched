
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


<div id="app1"><!-- vueの場所 -->
	<table border="1" class="table table-bordered">
		<thead>
			<tr>
				<th>奉仕</th>
				<th v-for="date in weekDates" :key="date">@{{ date }}の週</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="row in scheduleMatrix" :key="row.service_template_id">
				<td>@{{ row.service_name }}</td>
				<td v-for="date in weekDates" :key="date">
					<div v-if="row[date] && Object.keys(row[date]).length > 0">
						<div>場所: @{{ row[date].location }}</div>
						<div>司会: @{{ row[date].conductor_name }}</div>
						<div>備考: @{{ row[date].notes }}</div>
					</div>
					<div v-else>-</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>


<div id="err" class="text-danger"></div>


</div><!-- container-fluid -->



<!-- JSON埋め込み -->
<input type="hidden" id="csrf_token" value="{{ csrf_token() }}" >




</body>
</html>