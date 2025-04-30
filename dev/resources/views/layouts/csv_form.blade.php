
<button class="btn btn-secondary" onclick="$('#csv_form').toggle(300);">CSV</button>
<div id="csv_form" style="border:solid 4px #60bbf9;border-radius:5px;padding:0.5em;margin-top:0.3em;display:none;">
	<a href="{{$path_a}}/csv_download" class="btn btn-success btn-sm">CSVをダウンロード</a>
	<a href="{{$path_a}}/csv_download?str_code=shiftjis" class="btn btn-success btn-sm">Shift-JIS版CSVをダウンロード(旧Excel用)</a>
	<button type="button" class="btn btn-outline-secondary btn-sm" onclick="$('#csv_form').toggle();">閉じる</button>
	
	<div id="csv_import_w" style="margin-top:2em">
		<div class="h4 text-primary">CSVインポート</div>
		<div id="csv_exin"></div><!-- CSVインポート機能 -->
	</div>
</div>