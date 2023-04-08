<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\ctrls;

use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\YConnectLib;
use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\YShoppingLib;

class YShoppingController
{
	private const MAX_AGE = 60 * 60 * 24;

	// {{{ public function main($GET, $POST, &$pgval, &$resp_dsc, &$resp_axs, &$resp_shp)
	public function main($GET, $POST, &$pgval, &$resp_dsc, &$resp_axs, &$resp_shp)
	{
		$pgval["stage"]		= array_key_exists("stage", $POST) ? $POST["stage"] : null;
		$pgval["mode"]		= array_key_exists("mode", $POST) ? $POST["mode"] : null;
		$pgval["indent"]	= array_key_exists("indent", $POST) ? $POST["indent"] : null;
		$pgval["client_id"]	= array_key_exists("clientid", $POST) ? $POST["clientid"] : null;
		$pgval["secret"]	= array_key_exists("secret", $POST) ? $POST["secret"] : null;
		$pgval["nonce"]		= null;
		$pgval["code"]		= array_key_exists("code", $GET) ? $GET["code"] : null;
		if (is_null($pgval["code"])) {
			$pgval["code"]	= array_key_exists("code", $POST) ? $POST["code"] : null;
		}
		$pgval["access_token"]	= array_key_exists("access_token", $POST) ? $POST["access_token"] : null;
		$pgval["token_type"]	= array_key_exists("token_type", $POST) ? $POST["token_type"] : null;
		$pgval["refresh_token"]	= array_key_exists("refresh_token", $POST) ? $POST["refresh_token"] : null;
		$pgval["expires_in"]	= array_key_exists("expires_in", $POST) ? $POST["expires_in"] : null;
		$pgval["id_token"]		= array_key_exists("id_token", $POST) ? $POST["id_token"] : null;
		$pgval["api"]			= array_key_exists("api", $POST) ? $POST["api"] : null;
		$pgval["sellerid"]		= array_key_exists("sellerid", $POST) ? $POST["sellerid"] : null;
		$pgval["item_code"]		= array_key_exists("item_code", $POST) ? $POST["item_code"] : null;
		$pgval["stcat_key"]		= array_key_exists("stcat_key", $POST) ? $POST["stcat_key"] : null;
		$pgval["query"]			= array_key_exists("query", $POST) ? $POST["query"] : null;
		$pgval["orderid"]		= array_key_exists("orderid", $POST) ? $POST["orderid"] : null;
		$pgval["item_path"]		= array_key_exists("item_path", $POST) ? $POST["item_path"] : null;
		$pgval["item_name"]		= array_key_exists("item_name", $POST) ? $POST["item_name"] : null;
		$pgval["item_price"]	= array_key_exists("item_price", $POST) ? $POST["item_price"] : null;

		$ycon = new YConnectLib($pgval["indent"]);
		$yshp = new YShoppingLib($pgval["indent"]);
		$yshp->setStage($pgval["stage"]);

		$status = $ycon->discovery($resp_dsc);

		if ($pgval["mode"] === YShoppingLib::MODE_ORD_STAT_COUNT) {
			$status = $yshp->orderCount($pgval["access_token"], $pgval["sellerid"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_ORD_LIST) {
			$status = $yshp->orderList($pgval["access_token"], $pgval["sellerid"], $resp_shp);
		} else
		if (($pgval["mode"] === YShoppingLib::MODE_ORD_INFO) ||
		    ($pgval["mode"] === YShoppingLib::MODE_ORD_INFO_SHIP) ||
		    ($pgval["mode"] === YShoppingLib::MODE_ORD_INFO_DETAIL)) {
			$status = $yshp->orderInfo($pgval["access_token"], $pgval["sellerid"], $pgval["orderid"], $pgval["mode"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_ITEM_EDIT) {
			$status = $yshp->editItem($pgval["access_token"], $pgval["sellerid"], $pgval["item_code"], $pgval["item_path"], $pgval["item_name"], $pgval["item_price"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_ITEM_GET) {
			$status = $yshp->getItem($pgval["access_token"], $pgval["sellerid"], $pgval["item_code"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_ITEM_SUBMIT) {
			$status = $yshp->submitItem($pgval["access_token"], $pgval["sellerid"], $pgval["item_code"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_MY_ITEM_LIST) {
			$status = $yshp->myItemList($pgval["access_token"], $pgval["sellerid"], $pgval["stcat_key"], $pgval["query"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_IMAGE_LIST) {
			$status = $yshp->listImage($pgval["access_token"], $pgval["sellerid"], $pgval["stcat_key"], $pgval["query"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_STOCK_GET) {
			$status = $yshp->getStock($pgval["access_token"], $pgval["sellerid"], $pgval["item_code"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_PROD_BRAND_LIST) {
			$status = $yshp->prodBrandList($pgval["access_token"], $pgval["sellerid"], $pgval["query"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_PROD_CATEGORY_LIST) {
			$status = $yshp->prodCategoryList($pgval["access_token"], $pgval["sellerid"], $pgval["query"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_NEW_TOPIC) {
			$status = $yshp->externalStoreTopic($pgval["access_token"], $pgval["sellerid"], $resp_shp);
		} else {
			session_start();
			if ($pgval["mode"] === YConnectLib::MODE_SETID) {
				$pgval["nonce"] = YConnectLib::generateNonce();
				$_SESSION["nonce"] = $pgval["nonce"];
				$_SESSION["client_id"] = $pgval["client_id"];
				$_SESSION["secret"] = $pgval["secret"];
			} else
			if ($pgval["mode"] === YConnectLib::MODE_ACTOKEN) {
				$status = $ycon->generateAccessToken($pgval["client_id"], $pgval["secret"], $pgval["code"], $resp_axs);
				$pgval["access_token"] = $resp_axs["res"]["body"]->access_token;
				$pgval["token_type"] = $resp_axs["res"]["body"]->token_type;
				$pgval["refresh_token"] = $resp_axs["res"]["body"]->refresh_token;
				$pgval["expires_in"] = $resp_axs["res"]["body"]->expires_in;
				$pgval["id_token"] = $resp_axs["res"]["body"]->id_token;

			} else
			if ($pgval["mode"] === YConnectLib::MODE_REFRESH) {
				$status = $ycon->refreshAccessToken($pgval["client_id"], $pgval["secret"], $pgval["refresh_token"], $resp_axs);
				$pgval["access_token"] = $resp_axs["res"]["body"]->access_token;
			} else
			if ($pgval["code"] !== null) {
				$pgval["nonce"] = $_SESSION["nonce"];
				$pgval["client_id"] = $_SESSION["client_id"];
				$pgval["secret"] = $_SESSION["secret"];
			}
		}

		$pgval["authorization_url"] = $ycon->makeAuthUrl($pgval["client_id"], $pgval["nonce"], self::MAX_AGE);

		return;
	}
	// }}}

}

