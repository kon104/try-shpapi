<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs;

use myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs\ApiRequest;

class YConnectLib extends ApiRequest
{

	public const MODE_SETID = "ycon-setid";
	public const MODE_ACTOKEN = "ycon-actoken";
	public const MODE_REFRESH = "ycon-refresh";

	private const OPENID_CONFIGURATION_ENDPOINT = "https://auth.login.yahoo.co.jp/yconnect/v2/.well-known/openid-configuration";
	private $authorization_endpoint;
	private $token_endpoint;

	// {{{ public function discovery(&$resp)
	public function discovery(&$resp)
	{
		$url = self::OPENID_CONFIGURATION_ENDPOINT;
		$stat = parent::httpGet($url);
		$resp = parent::getResponse();

		$this->authorization_endpoint = $resp["res"]["body"]->authorization_endpoint;
		$this->token_endpoint = $resp["res"]["body"]->token_endpoint;

		return $stat;
	}
	// }}}

	// {{{ public function generateAccessToken($client_id, $secret, $code, &$resp)
	public function generateAccessToken($client_id, $secret, $code, &$resp)
	{
		$redirect = self::getOwnFullScriptName();
		$post_data = array(
			"grant_type" => "authorization_code",
			"redirect_uri" => $redirect,
			"code" => $code
		);

		$url = $this->token_endpoint;
		parent::setBasicAuth($client_id, $secret);
		$stat = parent::httpPost($url, $post_data);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function refreshAccessToken($client_id, $secret, $refresh_token, &$resp)
	public function refreshAccessToken($client_id, $secret, $refresh_token, &$resp)
	{
		$post_data = array(
			"grant_type" => "refresh_token",
			"refresh_token" => $refresh_token
		);

		$url = $this->token_endpoint;
		parent::setBasicAuth($client_id, $secret);
		$stat = parent::httpPost($url, $post_data);
		$resp = parent::getResponse();

		return $stat;
	}
	// }}}

	// {{{ public function makeAuthUrl($client_id, $nonce, $max_age)
	public function makeAuthUrl($client_id, $nonce, $max_age)
	{
		if (empty($client_id) === true) {
			return null;
		}
		$redirect = self::getOwnFullScriptName();
		$param = array(
			"response_type" => "code",
			"client_id" => $client_id,
			"redirect_uri" => $redirect,
			"scope" => "openid profile email address",
			"nonce" => $nonce,
			"max_age" => $max_age
		);
		$param = http_build_query($param);
		$url = $this->authorization_endpoint . "?" . $param;

		return $url;
	}
	// }}}

	// {{{ private function getOwnFullScriptName()
	private function getOwnFullScriptName()
	{
		$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://')
			. $_SERVER['HTTP_HOST']
			. $_SERVER['SCRIPT_NAME'];

		return $url;
	}
	// }}}

	// {{{ public static function generateNonce()
	public static function generateNonce()
	{
		$nonce = time();
		$nonce = hash("sha256", $nonce);
		return $nonce;
	}
	// }}}

}

