<?php

	require('vendor/autoload.php');

	use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\ctrls\YShoppingController;
	use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\YConnectLib;
	use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\YShoppingLib;
	use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\FeLib;

	$pgval = array();
	$resp_dsc = array();
	$resp_axs = array();
	$resp_shp = array();

	$ctrl = new YShoppingController();
	$ctrl->main($_GET, $_POST, $pgval, $resp_dsc, $resp_axs, $resp_shp);

	$pgparts_selapi = array(
		"api-order" => "注文API",
		"api-item" => "商品API",
		"api-stock" => "在庫API",
		"api-image" => "画像API",
		"api-product" => "製品/カテゴリ/ブランドAPI",
		"api-inquiry" => "問い合わせ管理API"
	);

?>
<!DOCTYPE html>
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<style>
.tokentxt {
	width: 450px;
}
</style>
</head>
<body>

<h1>YConnect v2 + Y!SHP API<?php if (!empty($pgval["stage"])) echo " (" . ucfirst($pgval["stage"]) . ")"; ?></h1>

<h2>Procedure for YConnect</h2>

<ol>
	<li><a href="./">Reset this page</a></li>
	<li><a href="https://e.developer.yahoo.co.jp/dashboard/" target="_blank" rel="noopener noreferrer">Look at ids in YJDN Management Apps</a></li>
	<li><form method="post" name="frmid" action="./"><input type="hidden" name="mode" value="<?= YConnectLib::MODE_SETID ?>">Create Auth URL. <label for="text_clientid">Client id: </label><input type="text" name="clientid" id="text_clientid" value="<?= $pgval["client_id"] ?>"> & <label for="text_secret">Secret: </label><input type="text" name="secret" id="text_secret" value="<?= $pgval["secret"] ?>"><input type="submit"></form></li>
	<li><?php if (!FeLib::empty($pgval["authorization_url"])) : ?><a href="<?= $pgval["authorization_url"] ?>"><?php endif; ?>Request User Auth<?php if (!FeLib::empty($pgval["authorization_url"])) : ?></a><?php endif; ?></li>
	<li><?php if (!FeLib::empty($pgval["code"])) : ?><a href="javascript: submitSpecifedMode('<?= YConnectLib::MODE_ACTOKEN ?>');"><?php endif; ?>Request Access Token<?php if (!FeLib::empty($pgval["code"])) : ?></a><?php endif; ?></li>
	<li><?php if (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YConnectLib::MODE_REFRESH ?>');"><?php endif; ?>Refresh Access Token<?php if (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?></li>

</ol>

<form method="post" name="frmapi" action="./">

<h2>Procedure for SHP</h2>

<!-- {{{ input boxes -->
<table>
	<tr>
		<td><label>Select Stage: </label></td><td><input type="radio" name="stage" id="radio_sand" value="<?= YShoppingLib::STAGE_SANDBOX; ?>"<?php if ($pgval["stage"] !== YShoppingLib::STAGE_PRODUCT) echo "checked"; ?>><label for="radio_sand"><?= ucfirst(YShoppingLib::STAGE_SANDBOX); ?></label><input type="radio" name="stage" id="radio_prod" value="<?= YShoppingLib::STAGE_PRODUCT; ?>" <?php if ($pgval["stage"] === YShoppingLib::STAGE_PRODUCT) echo "checked"; ?>><label for="radio_prod"><?= ucfirst(YShoppingLib::STAGE_PRODUCT); ?></label></td>
		<td></td><td></td></tr>
	<tr>
		<td><label for="select_api">Select API: </label></td>
		<td><select id="select_api">
		<?php foreach ($pgparts_selapi as $key => $val) : ?>
			<option value="<?= $key ?>"<?php if ($key === $pgval["api"]) echo " selected"; ?>><?= $val ?></option>
		<?php endforeach; ?>
		</select></td>
		<td><label for="check_indent">Indent raw:</label></td>
		<td><input type="checkbox" name="indent" id="check_indent" value="1"<?php if (!empty($pgval["indent"])) echo " checked"; ?>></td></tr>
	<tr><td><label for="text_sellerid">ストアアカウント: </label></td><td><input type="text" name="sellerid" id="text_sellerid" value="<?= $pgval["sellerid"] ?>"></td>
		<td></td><td></td></tr>
	<tr><td><label for="text_item_code">商品コード: </label></td><td><input type="text" name="item_code" id="text_item_code" value="<?= $pgval["item_code"] ?>"></td>
		<td><label for="text_item_path">カテゴリパス: </label></td><td><input type="text" name="item_path" id="text_item_path" value="<?= $pgval["item_path"] ?>"></td></tr>
	<tr><td><label for="text_item_name">商品名: </label></td><td><input type="text" name="item_name" id="text_item_name" value="<?= $pgval["item_name"] ?>"></td>
		<td><label for="text_item_price">通常販売価格: </label></td><td><input type="text" name="item_price" id="text_item_price" value="<?= $pgval["item_price"] ?>"></td></tr>
	<tr><td><label for="text_stcat_key">カテゴリページのキー: </label></td><td><input type="text" name="stcat_key" id="text_stcat_key" value="<?= $pgval["stcat_key"] ?>"></td>
		<td><label for="text_query">検索文字列: </label></td><td><input type="text" name="query" id="text_query" value="<?= $pgval["query"] ?>"></td></tr>
	<tr><td><label for="text_orderid">注文ID: </label></td><td><input type="text" name="orderid" id="text_orderid" value="<?= $pgval["orderid"] ?>"></td>
		<td></td><td></td></tr>

	<tr><td><label for="text_topicid">トピックID: </label></td><td><input type="text" name="topicid" id="text_topicid" value="<?= $pgval["topicid"] ?>"></td>
		<td></td><td></td></tr>

</table>
<!-- }}}  -->

<!-- {{{ id="api-order" -->
<div class="apipanel" id="api-order">
	<ul>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ORD_STAT_COUNT ?>');"><?php endif; ?>注文ステータス別件数参照API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ORD_LIST ?>');"><?php endif; ?>注文検索API (in the past month)<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ORD_INFO ?>');"><?php endif; ?>注文詳細API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_orderid">注文ID</label>]（type of node：注文）</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ORD_INFO_SHIP ?>');"><?php endif; ?>注文詳細API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_orderid">注文ID</label>]（type of node：注文＋請求＋配送）</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ORD_INFO_DETAIL ?>');"><?php endif; ?>注文詳細API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_orderid">注文ID</label>]（type of node：注文＋明細＋商品＋セラー＋バイヤー）</li>
	</ul>
</div>
<!-- }}} -->

<!-- {{{ id="api-item" -->
<div class="apipanel" id="api-item">
	<ul>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ITEM_EDIT ?>');"><?php endif; ?>商品登録API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_item_code">商品コード</label>]＋[<label for="text_item_path">カテゴリパス</label>]＋[<label for="text_item_name">商品名</label>]＋[<label for="text_item_price">通常販売価格</label>]</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ITEM_GET ?>');"><?php endif; ?>商品参照API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_item_code">商品コード</label>]</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_ITEM_SUBMIT ?>');"><?php endif; ?>商品個別反映API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_item_code">商品コード</label>]</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_MY_ITEM_LIST ?>');"><?php endif; ?>商品リストAPI<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋ ( [<label for="text_stcat_key">カテゴリページのキー</label>] or [<label for="text_query">検索文字列</label>] )</li>
	</ul>
</div>
<!-- }}} -->

<!-- {{{ id="api-stock" -->
<div class="apipanel" id="api-stock">
	<ul>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_STOCK_GET ?>');"><?php endif; ?>在庫参照API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_item_code">商品コード</label>]</li>
	</ul>
</div>
<!-- }}} -->

<!-- {{{ id="api-image" -->
<div class="apipanel" id="api-image">
	<ul>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_IMAGE_LIST ?>');"><?php endif; ?>商品画像一覧API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋ ( [<label for="text_stcat_key">カテゴリページのキー</label>] or [<label for="text_query">検索文字列</label>] )</li>
	</ul>
</div>
<!-- }}} -->

<!-- {{{ id="api-product" -->
<div class="apipanel" id="api-product">
	<ul>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_PROD_BRAND_LIST ?>');"><?php endif; ?>SHPブランドコード検索API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_query">検索文字列</label>]</li>

		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_PROD_CATEGORY_LIST ?>');"><?php endif; ?>SHPカテゴリ検索API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_query">検索文字列</label>]</li>

	</ul>
</div>
<!-- }}} -->

<!-- {{{ id="api-inquiry" -->
<div class="apipanel" id="api-inquiry">
	<ul>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_TALK_LIST ?>');"><?php endif; ?>質問一覧API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]</li>
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_TALK_DETAIL ?>');"><?php endif; ?>質問詳細API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]＋[<label for="text_topicid">トピックID</label>]</li>
<!--
		<li><?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?><a href="javascript: submitSpecifedMode('<?= YShoppingLib::MODE_TALK_NEW_TOPIC ?>');"><?php endif; ?>セラー新規問い合わせ投稿API<?php if (true) : // (!FeLib::empty($pgval["access_token"])) : ?></a><?php endif; ?>：[<label for="text_sellerid">ストア</label>]</li>
-->
	</ul>
</div>
<!-- }}} -->

<h2>Authorization & Access Token</h2>
<table>
	<tr><td>nonce</td><td><?= $pgval["nonce"] ?></td></tr>
	<tr><td>code</td><td><?= $pgval["code"] ?></td></tr>
	<tr><td><label for="text_access_token">access_token</label></td><td><input type="text" name="access_token" id="text_access_token" class="tokentxt" value="<?= $pgval["access_token"] ?>"></td></tr>
	<tr><td>token_type</td><td><?= $pgval["token_type"] ?></td></tr>
	<tr><td><label for="text_refresh_token">refresh_token</label></td><td><input type="text" name="refresh_token" id="text_refresh_token" class="tokentxt" value="<?= $pgval["refresh_token"] ?>"></td></tr>
	<tr><td>expires_in</td><td><?= $pgval["expires_in"] ?></td></tr>
	<tr><td><label for="text_id_token">id_token</label></td><td><input type="text" name="id_token" id="text_id_token" class="tokentxt" value="<?= $pgval["id_token"] ?>"></td></tr>
<!--
	<tr><td>curl&nbsp;<button type="button" id="copy_curl">copy</button></td><td><input type="text" name="cmd_curl" id="text_cmd_curl" value="<?= $pgval["cmd_curl"] ?>" readonly></td></tr>
-->
	<tr><td>curl&nbsp;<button type="button" id="copy_curl">copy</button></td><td><textarea name="cmd_curl" id="text_cmd_curl" class="tokentxt"><?= $pgval["cmd_curl"] ?></textarea></td></tr>
</table>

<input type="hidden" name="mode" id="mode" value="">
<input type="hidden" name="clientid" value="<?= $pgval["client_id"] ?>">
<input type="hidden" name="secret" value="<?= $pgval["secret"] ?>">
<input type="hidden" name="nonce" value="<?= $pgval["nonce"] ?>">
<input type="hidden" name="code" value="<?= $pgval["code"] ?>">
<input type="hidden" name="token_type" value="<?= $pgval["token_type"] ?>">
<input type="hidden" name="expires_in" value="<?= $pgval["expires_in"] ?>">
<input type="hidden" name="api" id="api" value="<?= $pgval["api"] ?>">

</form>

<h2>Dump variables</h2>
<?php
//	echo "<pre>" . PHP_EOL;
//	var_dump($_SESSION);
//	var_dump($_REQUEST);
//	var_dump($_GET);
//	var_dump($_POST);
//	echo "</pre>" . PHP_EOL;
?>
<?php
	if (!empty($pgval)) FeLib::dumpVar($pgval, "variables in this page");
//	if (!empty($resp_dsc)) FeLib::dumpVar($resp_dsc, "response of discovery");
	if (!empty($resp_axs)) FeLib::dumpVar($resp_axs, "response of access token");
	if (!empty($resp_shp)) FeLib::dumpVar($resp_shp, "response of shp api");
?>

<script type="text/javascript">

var selectApi = document.getElementById('select_api');
var copyCurl = document.getElementById('copy_curl');

selectApi.addEventListener('change', function(e) {

	var index = this.selectedIndex;
	if (index < 0) {
		return;
	}

	// send selected value to form
	var opts1 = document.querySelectorAll("#select_api option");
	var value1 = opts1[index].value;
	document.getElementById('api').value = value1;

	// change displaying the api panel
	changeDisplayingApiPanel(index);
});

copyCurl.addEventListener('click', function(e) {
	var target = document.getElementById('text_cmd_curl');
	target.select();
	document.execCommand("Copy");
	console.log("clicked copy curl");
});


window.onload = function() {
	var index = selectApi.selectedIndex;
	changeDisplayingApiPanel(index);
}

function changeDisplayingApiPanel(index)
{
	if (index < 0) {
		return;
	}
	var opts = document.querySelectorAll("#select_api option");
	var elems = document.getElementsByClassName("apipanel");
	var value = opts[index].value;
	for(var i = 0; i < elems.length; i++){
		var elem = elems[i];
		if (elem.id == value) {
			elem.style.display = "";
		} else {
			elem.style.display = "none";
		}
	}
}

function submitSpecifedMode(mode)
{
	document.getElementById('mode').value = mode;
	frmapi.submit();
}

</script>

</body>
</html>
