<?php
namespace App\CrudBase;

/**
 * ページネーション Bootstrap5に対応
 * @version 3.0.0
 * @since 2010-4-1 | 2023-9-8
 * @author amaraimusi
 *
 */
class PagenationEx3{
	
	
	/**
	 * ページネーションを生成する
	 * @param int $page_no 現在ページ番号（1ページ～）
	 * @param int $per_page　制限行数 
	 * @param int $total_count 合計データ件数
	 * @return string
	 */
	public function pagenation($page_no, $per_page, $data_count){
		
		$page_no_field = 'page'; // ページ番号のフィールド名

		$base_url = $_SERVER["REQUEST_URI"]; // 基本URL 例→ /CrudBaseBulk2/public/neko/index?neko_type=2
		$url_path=parse_url($base_url, PHP_URL_PATH); // URLパス 例→/CrudBaseBulk2/public/neko/index
		$url_query=parse_url($base_url, PHP_URL_QUERY); // クエリ文字列を取得
		$queryList = []; // クエリリスト
		parse_str($url_query, $queryList); // クエリ文字列を分解してクエリリストにセットする

		$all_page_count = ceil($data_count / $per_page); // 全ページ数を取得する
		if($all_page_count == 0) return '';
		
		$last_page_no = $all_page_count; // // 最終ページ番号
		
		
		// ▼「最初ページへ」ボタンを生成する
		// 一ページ目（ページ番号=1）である場合
		$first_html = '';
		if($page_no == 1){
			$first_html = "
					<li class='page-item disabled' aria-disabled='true' aria-label='pagination.previous'> <span class='page-link' aria-hidden='true'><i class='bi bi-chevron-double-left'></i></span> </li>
			";
		}else{
			$queryList[$page_no_field] = 1; // クエリリストのページ番号に目次ページ番号をセットする
			$query_str = http_build_query($queryList); // クエリリストからWEBクエリ文字列を作成する
			$url = $url_path . '?' . $query_str; // 目次のURLを組み立てる
			$first_html = "
					<li class='page-item'> <a class='page-link' href='{$url}' rel='next' aria-label='pagination.previous'><i class='bi bi-chevron-double-left'></i></a> </li>
			";
		}
		
		
		// ▼「一つ前へ」ボタンを生成する
		// 一ページ目（ページ番号=1）である場合
		$prev_html = '';
		if($page_no == 1){
			$prev_html = "
					<li class='page-item disabled' aria-disabled='true' aria-label='pagination.previous'> <span class='page-link' aria-hidden='true'><i class='bi bi-chevron-left'></i></span> </li>
			";
		}else{
			$queryList[$page_no_field] = $page_no - 1; // クエリリストのページ番号に目次ページ番号をセットする
			$query_str = http_build_query($queryList); // クエリリストからWEBクエリ文字列を作成する
			$url = $url_path . '?' . $query_str; // 目次のURLを組み立てる
			$prev_html = "
					<li class='page-item'> <a class='page-link' href='{$url}' rel='next' aria-label='pagination.previous'><i class='bi bi-chevron-left'></i></a> </li>
			";
		}

		
		// ▼ 目次リストを作成する
		// ページ1から最終ページ番号までループする。インデックスは目次ページ番号
		$indexList = []; // 目次リスト
		for($i = 1; $i <= $last_page_no; $i++){
			// 現在ページ番号と目次ページ番号が同じである場合
			if($page_no == $i){
				$indexList[] = "<li class='page-item active' aria-current='page'><span class='page-link'>{$i}</span></li>";
			}
			// 異なる場合
			else{
				$queryList[$page_no_field] = $i; // クエリリストのページ番号に目次ページ番号をセットする
				$query_str = http_build_query($queryList); // クエリリストからWEBクエリ文字列を作成する
				$url = $url_path . '?' . $query_str; // 目次のURLを組み立てる
				$indexList[] = "<li class='page-item'><a class='page-link' href='{$url}'>{$i}</a></li>"; // li要素を組立て、目次リストに追加する。
			}
		}
		
		// 目次リストから目次文字列を生成する。
		$index_li_text= implode("\n", $indexList);
		
		
		// ▼「一つ先へ」ボタンを生成する
		// 最終ページである場合
		$next_html = '';
		if($page_no == $last_page_no){
			$next_html = "
					<li class='page-item disabled' aria-disabled='true' aria-label='pagination.next'> <span class='page-link' aria-hidden='true'><i class='bi bi-chevron-right'></i></span> </li>
			";
		}else{
			$queryList[$page_no_field] = $page_no + 1; // クエリリストのページ番号に目次ページ番号をセットする
			$query_str = http_build_query($queryList); // クエリリストからWEBクエリ文字列を作成する
			$url = $url_path . '?' . $query_str; // 目次のURLを組み立てる
			$next_html = "
					<li class='page-item'> <a class='page-link' href='{$url}' rel='next' aria-label='pagination.next'><i class='bi bi-chevron-right'></i></a> </li>
			";
		}
		
		
		// ▼「最終ページへ」ボタンを生成する
		// 最終ページである場合
		$last_html = '';
		if($page_no == $last_page_no){
			$last_html = "
					<li class='page-item disabled' aria-disabled='true' aria-label='pagination.next'> <span class='page-link' aria-hidden='true'><i class='bi bi-chevron-double-right'></i></span> </li>
			";
		}else{
			$queryList[$page_no_field] = $last_page_no; // クエリリストのページ番号に目次ページ番号をセットする
			$query_str = http_build_query($queryList); // クエリリストからWEBクエリ文字列を作成する
			$url = $url_path . '?' . $query_str; // 目次のURLを組み立てる
			$last_html = "
					<li class='page-item'> <a class='page-link' href='{$url}' rel='next' aria-label='pagination.next'><i class='bi bi-chevron-double-right'></i></i></a> </li>
			";
		}
		
		
		$html = "
			<div class='pagination_w'>
				
				<ul class='pagination'>
					{$first_html}
					{$prev_html}
					{$index_li_text}
					{$next_html}
					{$last_html}
				</ul>
			</div>
		";
		
		return $html;
		
	}
	
}