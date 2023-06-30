<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\ctrls;

use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\YConnectLib;
use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\YShoppingLib;

class YShoppingController
{
	private const MAX_AGE = 60 * 60 * 24;

	// {{{ public function main($GET, $POST, $FILES, &$pgval, &$resp_dsc, &$resp_axs, &$resp_shp)
	public function main($GET, $POST, $FILES, &$pgval, &$resp_dsc, &$resp_axs, &$resp_shp)
	{
		$pgval = $this->createPageValue($GET, $POST, $FILES, $pgval);

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
			$status = $yshp->editItem($pgval["access_token"], $pgval["sellerid"], $pgval["item_code"], $pgval["item_path"], $pgval["item_name"], $pgval["item_pcat"], $pgval["item_price"], $resp_shp);
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
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_ADD) {
			$status = $yshp->externalTalkAdd($pgval["access_token"], $pgval["sellerid"], $pgval["topicid"], $pgval["body"], $pgval["objectkey"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_DETAIL) {
			$status = $yshp->externalTalkDetail($pgval["access_token"], $pgval["sellerid"], $pgval["topicid"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_LIST) {
			$status = $yshp->externalTalkList($pgval["access_token"], $pgval["sellerid"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_READ) {
			$status = $yshp->externalTalkRead($pgval["access_token"], $pgval["sellerid"], $pgval["topicid"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_COMPLETE) {
			$status = $yshp->externalTalkComplete($pgval["access_token"], $pgval["sellerid"], $pgval["topicid"], $pgval["completeid"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_PRIVATE) {
			$status = $yshp->externalTalkPrivate($pgval["access_token"], $pgval["sellerid"], $pgval["topicid"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_FILE_ADD) {
			$status = $yshp->externalTalkFileAdd($pgval["access_token"], $pgval["sellerid"], $pgval["topicid"], $pgval["file"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_FILE_DOWNLOAD) {
			$status = $yshp->externalTalkFileDownload($pgval["access_token"], $pgval["sellerid"], $pgval["objectkey"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_FILE_DELETE) {
			$status = $yshp->externalTalkFileDelete($pgval["access_token"], $pgval["sellerid"], $pgval["objectkey"], $resp_shp);
		} else
		if ($pgval["mode"] === YShoppingLib::MODE_TALK_NEW_TOPIC) {
			$status = $yshp->externalStoreTopic($pgval["access_token"], $pgval["sellerid"], $pgval["orderid"], $pgval["topic_cat"], $pgval["title"], $pgval["body"], $resp_shp);
		} else {
			session_start();
			if ($pgval["mode"] === YConnectLib::MODE_SETID) {
				$pgval["nonce"] = YConnectLib::generateNonce();
				$_SESSION["nonce"] = $pgval["nonce"];
				$_SESSION["clientid"] = $pgval["clientid"];
				$_SESSION["secret"] = $pgval["secret"];
			} else
			if ($pgval["mode"] === YConnectLib::MODE_ACTOKEN) {
				$status = $ycon->generateAccessToken($pgval["clientid"], $pgval["secret"], $pgval["code"], $resp_axs);
				$pgval["access_token"] = $resp_axs["res"]["body"]->access_token;
				$pgval["token_type"] = $resp_axs["res"]["body"]->token_type;
				$pgval["refresh_token"] = $resp_axs["res"]["body"]->refresh_token;
				$pgval["expires_in"] = $resp_axs["res"]["body"]->expires_in;
				$pgval["id_token"] = $resp_axs["res"]["body"]->id_token;
				$pgval["cmd_curl"] = $ycon->makeCurlCommand($pgval["access_token"]);
			} else
			if ($pgval["mode"] === YConnectLib::MODE_REFRESH) {
				$status = $ycon->refreshAccessToken($pgval["clientid"], $pgval["secret"], $pgval["refresh_token"], $resp_axs);
				$pgval["access_token"] = $resp_axs["res"]["body"]->access_token;
				$pgval["cmd_curl"] = $ycon->makeCurlCommand($pgval["access_token"]);
			} else
			if ($pgval["code"] !== null) {
				$pgval["nonce"] = $_SESSION["nonce"];
				$pgval["clientid"] = $_SESSION["clientid"];
				$pgval["secret"] = $_SESSION["secret"];
			}
		}

		$pgval["authorization_url"] = $ycon->makeAuthUrl($pgval["clientid"], $pgval["nonce"], self::MAX_AGE);

		return;
	}
	// }}}

	// {{{ private function createPageValue($GET, $POST, $FILES, $pgval)
	private function createPageValue($GET, $POST, $FILES, $pgval)
	{
		$pgval["stage"]		= $this->pickupParamVal("stage", $POST);
		$pgval["mode"]		= $this->pickupParamVal("mode", $POST);
		$pgval["indent"]	= $this->pickupParamVal("indent", $POST);
		$pgval["clientid"]	= $this->pickupParamVal("clientid", $POST);
		$pgval["secret"]	= $this->pickupParamVal("secret", $POST);
		$pgval["nonce"]		= null;
		$pgval["code"]		= $this->pickupParamVal("code", $GET);
		if (is_null($pgval["code"])) {
			$pgval["code"]	= $this->pickupParamVal("code", $POST);
		}

		$pgval["access_token"]	= $this->pickupParamVal("access_token", $POST);
		$pgval["token_type"]	= $this->pickupParamVal("token_type", $POST);
		$pgval["refresh_token"]	= $this->pickupParamVal("refresh_token", $POST);
		$pgval["expires_in"]	= $this->pickupParamVal("expires_in", $POST);
		$pgval["id_token"]		= $this->pickupParamVal("id_token", $POST);
		$pgval["cmd_curl"]		= $this->pickupParamVal("cmd_curl", $POST);

		$pgval["api"]			= $this->pickupParamVal("api", $POST);
		$pgval["sellerid"]		= $this->pickupParamVal("sellerid", $POST);
		$pgval["stcat_key"]		= $this->pickupParamVal("stcat_key", $POST);
		$pgval["query"]			= $this->pickupParamVal("query", $POST);
		$pgval["orderid"]		= $this->pickupParamVal("orderid", $POST);
		$pgval["topicid"]		= $this->pickupParamVal("topicid", $POST);
		$pgval["completeid"]	= $this->pickupParamVal("completeid", $POST);
		$pgval["topic_cat"]		= $this->pickupParamVal("topic_cat", $POST);
		$pgval["title"]			= $this->pickupParamVal("title", $POST);
		$pgval["body"]			= $this->pickupParamVal("body", $POST);
		$pgval["objectkey"]		= $this->pickupParamVal("objectkey", $POST);
		$pgval["file"]			= $this->pickupParamFile("file", $FILES);

		$pgval["item_code"]		= $this->pickupParamVal("item_code", $POST);
		$pgval["item_path"]		= $this->pickupParamVal("item_path", $POST);
		$pgval["item_name"]		= $this->pickupParamVal("item_name", $POST);
		$pgval["item_pcat"]		= $this->pickupParamVal("item_pcat", $POST);
		$pgval["item_price"]	= $this->pickupParamVal("item_price", $POST);

		return $pgval;
	}
	// }}}

	// {{{ private function pickupParamVal($key, $params)
	private function pickupParamVal($key, $params)
	{
//		$value = array_key_exists($key, $params) ? $params[$key] : null;
		$value = null;
		if (array_key_exists($key, $params) && ($params[$key] !== "")) {
			$value = $params[$key];
		}

		return $value;
	}
	// }}}

	// {{{ private function pickupParamFile($key, $params)
	private function pickupParamFile($key, $files)
	{
		$file = null;
		if (array_key_exists($key, $files)) {
			$file = curl_file_create($files[$key]["tmp_name"], $files[$key]["type"], $files[$key]["name"]);
		}

		return $file;
	}
	// }}}

}

