<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs;

use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\ApiRequest;

class YShoppingLib extends ApiRequest
{

	public const STAGE_SANDBOX = "sandbox";
	public const STAGE_PRODUCT = "production";

	public const MODE_ORD_STAT_COUNT = "shp-ord-statcnt";
	public const MODE_ORD_LIST = "shp-ord-list";
	public const MODE_ORD_INFO = "shp-ord-info";
	public const MODE_ORD_INFO_SHIP = "shp-ord-info-ship";
	public const MODE_ORD_INFO_DETAIL = "shp-ord-info-detail";
	public const MODE_ITEM_EDIT = "shp-item-edit";
	public const MODE_ITEM_GET = "shp-item-get";
	public const MODE_ITEM_SUBMIT = "shp-item-submit";
	public const MODE_MY_ITEM_LIST = "shp-my-item-list";
	public const MODE_STOCK_GET = "shp-stock-get";
	public const MODE_IMAGE_LIST = "shp-image-list";
	public const MODE_PROD_BRAND_LIST = "shp-prod-brand-list";
	public const MODE_PROD_CATEGORY_LIST = "shp-prod-category-list";
	public const MODE_TALK_NEW_TOPIC = "shp-talk-new-topic";

	private const HOST_SANDBOX = "https://test.circus.shopping.yahooapis.jp";
	private const HOST_PRODUCT = "https://circus.shopping.yahooapis.jp";

	private const PATH_ORDER_COUNT			= "/ShoppingWebService/V1/orderCount";
	private const PATH_ORDER_LIST			= "/ShoppingWebService/V1/orderList";
	private const PATH_ORDER_INFO			= "/ShoppingWebService/V1/orderInfo";
	private const PATH_ITEM_EDIT			= "/ShoppingWebService/V1/editItem";
	private const PATH_ITEM_GET				= "/ShoppingWebService/V1/getItem";
	private const PATH_ITEM_SUBMIT			= "/ShoppingWebService/V1/submitItem";
	private const PATH_MY_ITEM_LIST			= "/ShoppingWebService/V1/myItemList";
	private const PATH_STOCK_GET			= "/ShoppingWebService/V1/getStock";
	private const PATH_IMAGE_LIST			= "/ShoppingWebService/V1/itemImageList";
	private const PATH_PROD_BRAND_LIST		= "/ShoppingWebService/V1/getShopBrandList";
	private const PATH_PROD_CATEGORY_LIST	= "/ShoppingWebService/V1/getShopCategoryList";
	private const PATH_TALK_NEW_TOPIC		= "/ShoppingWebService/V1/externalStoreTopic";

	private $stage = null;

	// {{{ public function setStage($stage)
	public function setStage($stage)
	{
		$this->stage = $stage;
	}
	// }}}

	// {{{ private function provideApiUrl($path)
	private function provideApiUrl($path)
	{
		$url = self::HOST_SANDBOX;
		if ($this->stage === self::STAGE_PRODUCT) {
			$url = self::HOST_PRODUCT;
		}
		$url .= $path;
		return $url;
	}
	// }}}

	// {{{ public function orderCount($access_token, $sellerid, &$resp)
	public function orderCount($access_token, $sellerid, &$resp)
	{
		$query = array(
			"sellerId" => $sellerid
		);
		$query = http_build_query($query);
		$url = $this->provideApiUrl(self::PATH_ORDER_COUNT);
		$url .= "?" . $query;

		parent::setBearerAuth($access_token);
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function orderList($access_token, $sellerid, &$resp)
	// how to make xml: https://www.php.net/manual/ja/example.xmlwriter-simple.php
	public function orderList($access_token, $sellerid, &$resp)
	{
		$pastymd = date("Ymd000000", strtotime("-1 month"));

		$xw = xmlwriter_open_memory();
		xmlwriter_set_indent($xw, true);
		xmlwriter_start_document($xw, '1.0', 'UTF-8');

		xmlwriter_start_element($xw, "Req");
		xmlwriter_start_element($xw, "Search");

		xmlwriter_start_element($xw, "Sort");
		xmlwriter_text($xw, "-order_time");
		xmlwriter_end_element($xw);	// SellerId

		xmlwriter_start_element($xw, "Condition");

		xmlwriter_start_element($xw, "SellerId");
		xmlwriter_text($xw, $sellerid);
		xmlwriter_end_element($xw);	// SellerId

		xmlwriter_start_element($xw, "OrderTimeFrom");
		xmlwriter_text($xw, $pastymd);	// yyyymmddhhmmss
		xmlwriter_end_element($xw);	// OrderTimeFrom

		xmlwriter_end_element($xw);	// Condition
		xmlwriter_start_element($xw, "Field");
		xmlwriter_text($xw, "OrderId,Version,IsSeen,OrderTime,OrderStatus,PayStatus,SettleStatus,ShipStatus");
		xmlwriter_end_element($xw);	// Field
		xmlwriter_end_element($xw);	// Search

		xmlwriter_start_element($xw, "SellerId");
		xmlwriter_text($xw, $sellerid);
		xmlwriter_end_element($xw);	// SellerId

		xmlwriter_end_element($xw);	// Req

		xmlwriter_end_document($xw);
		$xml = xmlwriter_output_memory($xw);

//	var_dump($xml);

		$url = $this->provideApiUrl(self::PATH_ORDER_LIST);

		parent::setBearerAuth($access_token);
		$stat = parent::httpPost($url, $xml);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function orderInfo($access_token, $sellerid, $orderid, $mode, &$resp)
	public function orderInfo($access_token, $sellerid, $orderid, $mode, &$resp)
	{
		$fields = "";

		$fields = $this->orderInfoFieldOrder($fields);
		if ($mode === self::MODE_ORD_INFO_SHIP) {
			$fields = $this->orderInfoFieldPay($fields);
			$fields = $this->orderInfoFieldShip($fields);
		} else
		if ($mode === self::MODE_ORD_INFO_DETAIL) {
			$fields = $this->orderInfoFieldDetail($fields);
			$fields = $this->orderInfoFieldItem($fields);
			$fields = $this->orderInfoFieldSeller($fields);
			$fields = $this->orderInfoFieldBuyer($fields);
		}

		$debug = "node size of 'Field' in xml is: " . number_format(strlen($fields)) . "bytes";
		$this->addDebug($debug);

		$xw = xmlwriter_open_memory();
		xmlwriter_set_indent($xw, true);
		xmlwriter_start_document($xw, '1.0', 'UTF-8');

		xmlwriter_start_element($xw, "Req");

		xmlwriter_start_element($xw, "SellerId");
		xmlwriter_text($xw, $sellerid);
		xmlwriter_end_element($xw);	// SellerId

		xmlwriter_start_element($xw, "Target");

		xmlwriter_start_element($xw, "OrderId");
		xmlwriter_text($xw, $orderid);
		xmlwriter_end_element($xw);	// OrderId

		xmlwriter_start_element($xw, "Field");
		xmlwriter_text($xw, $fields);
		xmlwriter_end_element($xw);	// Field

		xmlwriter_end_element($xw);	// Target

		xmlwriter_end_element($xw);	// Req

		xmlwriter_end_document($xw);
		$xml = xmlwriter_output_memory($xw);

//	var_dump($xml);

		$url = $this->provideApiUrl(self::PATH_ORDER_INFO);

		parent::setBearerAuth($access_token);
		$stat = parent::httpPost($url, $xml);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ private function orderInfoFieldOrder($fields)
	private function orderInfoFieldOrder($fields)
	{
		// Order
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "OrderId,Version,ParentOrderId,ChildOrderId,DeviceType,"
		. "MobileCarrierName,IsSeen,IsSplit,CancelReason,CancelReasonDetail,"
		. "IsRoyalty,IsRoyaltyFix,IsSeller,IsAffiliate,IsRatingB2s,NeedSnl,"
		. "OrderTime,LastUpdateTime,Suspect,SuspectMessage,OrderStatus,"
		. "StoreStatus,RoyaltyFixTime,SendConfirmTime,SendPayTime,"
		. "PrintSlipTime,PrintDeliveryTime,PrintBillTime,BuyerComments,"
		. "SellerComments,Notes,OperationUser,Referer,EntryPoint,HistoryId,"
		. "UsageId,UseCouponData,TotalCouponDiscount,ShippingCouponFlg,"
		. "ShippingCouponDiscount,CampaignPoints,IsMultiShip,MultiShipId,"
		. "IsReadOnly,IsFirstClassDrugIncludes,IsFirstClassDrugAgreement,"
		. "IsWelcomeGiftIncludes,YamatoCoopStatus,FraudHoldStatus,"
		. "PublicationTime,IsYahooAuctionOrder,YahooAuctionMerchantId,"
		. "YahooAuctionId,IsYahooAuctionDeferred,YahooAuctionCategoryType,"
		. "YahooAuctionBidType";

		return $fields;
	}
	// }}}

	// {{{ private function orderInfoFieldPay($fields)
	private function orderInfoFieldPay($fields)
	{
		// Charging
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "PayStatus,SettleStatus,PayType,PayKind,PayMethod,PayMethodName,"
		. "SellerHandlingCharge,PayActionTime,PayDate,PayNotes,SettleId,"
		. "CardBrand,CardNumber,CardNumberLast4,CardExpireYear,CardExpireMonth,"
		. "CardPayType,CardHolderName,CardPayCount,CardBirthDay,UseYahooCard,"
		. "UseWallet,NeedBillSlip,NeedDetailedSlip,NeedReceipt,AgeConfirmField,"
		. "AgeConfirmValue,AgeConfirmCheck,BillAddressFrom,BillFirstName,"
		. "BillFirstNameKana,BillLastName,BillLastNameKana,BillZipCode,"
		. "BillPrefecture,BillPrefectureKana,BillCity,BillCityKana,"
		. "BillAddress1,BillAddress1Kana,BillAddress2,BillAddress2Kana,"
		. "BillPhoneNumber,BillEmgPhoneNumber,BillMailAddress,BillSection1Field,"
		. "BillSection1Value,BillSection2Field,BillSection2Value,PayNo,"
		. "PayNoIssueDate,ConfirmNumber,PaymentTerm,IsApplePay";

		return $fields;
	}
	// }}}

	// {{{ private function orderInfoFieldShip($fields)
	private function orderInfoFieldShip($fields)
	{
		// Shipment
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "ShipStatus,ShipMethod,ShipMethodName,ShipRequestDate,ShipRequestTime,"
		. "ShipNotes,ShipCompanyCode,ReceiveShopCode,ShipInvoiceNumber1,"
		. "ShipInvoiceNumber2,ShipInvoiceNumberEmptyReason,ShipUrl,"
		. "ArriveType,ShipDate,ArrivalDate,NeedGiftWrap,GiftWrapType,"
		. "GiftWrapMessage,NeedGiftWrapPaper,GiftWrapPaperType,GiftWrapName,"
		. "Option1Field,Option1Type,Option1Value,Option2Field,Option2Type,"
		. "Option2Value,ShipFirstName,ShipFirstNameKana,ShipLastName,"
		. "ShipLastNameKana,ShipZipCode,ShipPrefecture,ShipPrefectureKana,"
		. "ShipCity,ShipCityKana,ShipAddress1,ShipAddress1Kana,ShipAddress2,"
		. "ShipAddress2Kana,ShipPhoneNumber,ShipEmgPhoneNumber,ShipSection1Field,"
		. "ShipSection1Value,ShipSection2Field,ShipSection2Value,"
		. "ReceiveSatelliteType,ReceiveSatelliteSettleMethod,"
		. "ReceiveSatelliteMethod,ReceiveSatelliteCompanyName,"
		. "ReceiveSatelliteShopCode,ReceiveSatelliteShopName,"
		. "ReceiveSatelliteShipKind,ReceiveSatelliteYahooCode,"
		. "ReceiveSatelliteCertificationNumber,CollectionDate,CashOnDeliveryTax,"
		. "NumberUnitsShipped,ShipRequestTimeZoneCode,ShipInstructType,"
		. "ShipInstructStatus,ReceiveShopType,ReceiveShopName,ExcellentDelivery,"
		. "IsEazy";

		return $fields;
	}
	// }}}

	// {{{ private function orderInfoFieldDetail($fields)
	private function orderInfoFieldDetail($fields)
	{
		// Details
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "PayCharge,ShipCharge,GiftWrapCharge,Discount,Adjustments,"
		. "SettleAmount,UsePoint,TotalPrice,SettlePayAmount,IsGetPointFixAll,"
		. "TotalMallCouponDiscount,IsGetStoreBonusFixAll";

		return $fields;
	}
	// }}}

	// {{{ private function orderInfoFieldItem($fields)
	private function orderInfoFieldItem($fields)
	{
		// Items
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "LineId,ItemId,Title,SubCode,SubCodeOption,ItemOption,Inscription,"
		. "IsUsed,ImageId,IsTaxable,ItemTaxRatio,Jan,ProductId,CategoryId,"
		. "AffiliateRatio,UnitPrice,Quantity,PointAvailQuantity,ReleaseDate,"
		. "PointFspCode,PointRatioY,PointRatioSeller,UnitGetPoint,IsGetPointFix,"
		. "GetPointFixDate,CouponData,CouponDiscount,OriginalPrice,OriginalNum,"
		. "LeadTimeText,LeadTimeStart,LeadTimeEnd,PriceType,PickAndDeliveryCode,"
		. "PickAndDeliveryTransportRuleType,YamatoUndeliverableReason,"
		. "StoreBonusRatioSeller,UnitGetStoreBonus,IsGetStoreBonusFix,"
		. "GetStoreBonusFixDate";

		return $fields;
	}
	// }}}

	// {{{ private function orderInfoFieldSeller($fields)
	private function orderInfoFieldSeller($fields)
	{
		// Seller
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "SellerId";

		return $fields;
	}
	// }}}

	// {{{ private function orderInfoFieldBuyer($fields)
	private function orderInfoFieldBuyer($fields)
	{
		// Buyer
		$fields = ($fields . (empty($fields) ? "" : ","))
		. "IsLogin,FspLicenseCode,FspLicenseName,GuestAuthId";

		return $fields;
	}
	// }}}

	// {{{ public function editItem($access_token, $sellerid, &$resp)
	public function editItem($access_token, $sellerid, $item_code, $path, $name, $price, &$resp)
	{
		$query = array(
			"seller_id" => $sellerid,
			"item_code" => $item_code,
			"path" => $path,
			"name" => $name,
			"price" => $price
		);
		$query = http_build_query($query);
		$url = $this->provideApiUrl(self::PATH_ITEM_EDIT);

		parent::setBearerAuth($access_token);
		$stat = parent::httpPost($url, $query);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function getItem($access_token, $sellerid, $item_code, &$resp)
	public function getItem($access_token, $sellerid, $item_code, &$resp)
	{
		$query = array(
			"seller_id" => $sellerid,
			"item_code" => $item_code,
			"expand_spec" => 1
		);
		$query = http_build_query($query);
		$url = $this->provideApiUrl(self::PATH_ITEM_GET);
		$url .= "?" . $query;

		parent::setBearerAuth($access_token);
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function submitItem($access_token, $sellerid, $code, &$resp)
	public function submitItem($access_token, $sellerid, $item_code, &$resp)
	{
		$query = array(
			"seller_id" => $sellerid,
			"item_code" => $item_code
		);
		$query = http_build_query($query);
		$url = $this->provideApiUrl(self::PATH_ITEM_SUBMIT);

		parent::setBearerAuth($access_token);
		$stat = parent::httpPost($url, $query);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function myItemList($access_token, $sellerid, $stcat_key, $query, &$resp)
	public function myItemList($access_token, $sellerid, $stcat_key, $query, &$resp)
	{
		$param = array(
			"seller_id" => $sellerid,
		);
		if (is_null($stcat_key) != true) $param += array("stcat_key" => $stcat_key);
		if (is_null($query) != true) $param += array("query" => $query);

		$param = http_build_query($param);
		$url = $this->provideApiUrl(self::PATH_MY_ITEM_LIST);
		$url .= "?" . $param;

		parent::setBearerAuth($access_token);
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}
 
	// {{{ public function listImage($access_token, $sellerid, $stcat_key, $query, &$resp)
	public function listImage($access_token, $sellerid, $stcat_key, $query, &$resp)
	{
		$param = array(
			"seller_id" => $sellerid,
		);
		if (is_null($stcat_key) != true) $param += array("stcat_key" => $stcat_key);
		if (is_null($query) != true) $param += array("query" => $query);

		$param = http_build_query($param);
		$url = $this->provideApiUrl(self::PATH_IMAGE_LIST);
		$url .= "?" . $param;

		parent::setBearerAuth($access_token);
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function getStock($access_token, $sellerid, $item_code, &$resp)
	public function getStock($access_token, $sellerid, $item_code, &$resp)
	{
		$data = array(
			"seller_id" => $sellerid,
			"item_code" => $item_code
		);
		$data = http_build_query($data);

		$url = $this->provideApiUrl(self::PATH_STOCK_GET);

		parent::setBearerAuth($access_token);
		$stat = parent::httpPost($url, $data);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function prodBrandList($access_token, $sellerid, $query, &$resp)
	public function prodBrandList($access_token, $sellerid, $query, &$resp)
	{
		$param = array(
			"seller_id" => $sellerid,
		);
		if (is_null($query) != true) $param += array("query" => $query);

		$param = http_build_query($param);
		$url = $this->provideApiUrl(self::PATH_PROD_BRAND_LIST);
		$url .= "?" . $param;

		parent::setBearerAuth($access_token);
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function prodCategoryList($access_token, $sellerid, $query, &$resp)
	public function prodCategoryList($access_token, $sellerid, $query, &$resp)
	{
		$param = array(
			"seller_id" => $sellerid,
		);
		if (is_null($query) != true) $param += array("query" => $query);

		$param = http_build_query($param);
		$url = $this->provideApiUrl(self::PATH_PROD_CATEGORY_LIST);
		$url .= "?" . $param;

		parent::setBearerAuth($access_token);
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function externalStoreTopic($access_token, $sellerid, &$resp)
	public function externalStoreTopic($access_token, $sellerid, &$resp)
	{
		$now = date("c");
		$ar = array(
			"sellerId" => $sellerid,
			"categoryId" => 44,
			"orderId" => "snbx-cvovsibfi-10000003",
			"title" => "Title: " . $now,
			"body" => "Message: " . $now
		);
		$json = json_encode($ar);

		$url = $this->provideApiUrl(self::PATH_TALK_NEW_TOPIC);

		parent::setBearerAuth($access_token);
		$stat = parent::httpPost($url, $json);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

}

