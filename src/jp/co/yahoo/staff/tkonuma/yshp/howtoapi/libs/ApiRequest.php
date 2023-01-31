<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs;

class ApiRequest
{
	private $ch;
	private $req_header;
	private $req_rawbody = null;
	private $res_status;
	private $res_header;
	private $res_body;
	private $res_rawbody;
	private $debug = null;

    // {{{ public function __construct($indent){
    public function __construct($indent){
		$this->indent = $indent;
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
    }
	// }}}

	// {{{ protected function setBasicAuth($client_id, $secret)
	protected function setBasicAuth($client_id, $secret)
	{
		curl_setopt($this->ch, CURLOPT_USERPWD, $client_id . ":" . $secret);
	}
	// }}}

	// {{{ protected function setBearerAuth($access_token)
	protected function setBearerAuth($access_token)
	{
//		curl_setopt($this->ch, CURLOPT_XOAUTH2_BEARER, $access_token);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
	}
	// }}}

	// {{{ protected function httpGet($url)
	protected function httpGet($url)
	{
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$status = self::httpExecute();

		return $status;
	}
	// }}}

	// {{{ protected function httpPost($url, $post_data)
	protected function httpPost($url, $post_data)
	{
		if (gettype($post_data) === "array") {
			$post_data = http_build_query($post_data);
		}
		$this->req_rawbody = $post_data;
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$status = self::httpExecute();

		return $status;
	}
	// }}}

	// {{{ protected function addDebug($text)
	protected function addDebug($text)
	{
		$this->debug[] = $text;
	}
	// }}}

	// {{{ private function httpExecute()
	private function httpExecute()
	{
		$response = curl_exec($this->ch);
		$res_info = curl_getinfo($this->ch);
		$req_head = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
		$req_head = self::divideResponseHeader($req_head, strlen($req_head));
		$req_head = self::makeHeaderList($req_head);

		$res_head = self::divideResponseHeader($response, $res_info["header_size"]);
		$res_rawbody = self::divideResponseBody($response, $res_info["header_size"]);

		$res_stat = self::extractStatusCode($res_head);
		$res_head = self::makeHeaderList($res_head);
		$res_body = self::alterBodyList($res_head, $res_rawbody);

		$this->req_header = $req_head;
		$this->res_status = $res_stat;
		$this->res_header = $res_head;
		$this->res_body = $res_body;
		$this->res_rawbody = $res_rawbody;

		return $res_stat;
	}
	// }}}

	// {{{ protected function getResponse()
	protected function getResponse()
	{
		$res = array();
		$res["req"] = array(
			"head" => $this->req_header
		);
		if (is_null($this->req_rawbody) === false) {
			$res["req"]["raw-body"] = $this->req_rawbody;
		}
		$res["res"] = array(
			"stat" => $this->res_status,
			"head" => $this->res_header,
			"body" => $this->res_body,
			"raw" => $this->res_rawbody
		);
		if (is_array($this->debug)) {
			$res["debug"] = $this->debug;
		}

		return $res;
	}
	// }}}

	// {{{ private function divideResponseHeader($resp, $header_size)
	private function divideResponseHeader($resp, $header_size)
	{
		$head = substr($resp, 0, $header_size);
		$head = str_replace(array("\r\n", "\r", "\n"), "\n", $head);
		$head = explode("\n", $head);
		return $head;
	}
	// }}}

	// {{{ private function divideResponseBody($resp, $header_size)
	private function divideResponseBody($resp, $header_size)
	{
		$body = substr ($resp, $header_size);
		return $body;
	}
	// }}}

	// {{{ private function extractStatusCode(&$head)
	private function extractStatusCode(&$head)
	{
		$line = explode(" ", $head[0]);
		array_shift($head);
		$stat = $line[1];
		return $stat;
	}
	// }}}

	// {{{ private function makeHeaderList($lines)
	private function makeHeaderList($lines)
	{
		$head = array();
		foreach ($lines as $line) {
			$kv = explode(":", $line);
			$k = $kv[0];
			if (count($kv) > 1) {
				$head[strtolower($k)] = trim($kv[1]);
			} else {
				if ($k === "") continue;
				array_push($head, $k);
			}
		}

		return $head;
	}
	// }}}

	// {{{ private function alterBodyList($header, &$rawbody)
	private function alterBodyList($header, &$rawbody)
	{
		$type = $header["content-type"];

		if (strpos($type, '/json') !== false) {
			$body = json_decode($rawbody, false);
			$rawbody = $this->indentJSON($rawbody, $body);
		} else
		if (strpos($type, '/xml') !== false) {
			$body =	simplexml_load_string($rawbody, 'SimpleXMLElement', LIBXML_NOCDATA);
			$rawbody = $this->indentXML($rawbody);
		}

		return $body;
	}
	// }}}

	// {{{ private function indentJSON($string, $object)
	private function indentJSON($string, $object)
	{
		if (empty($this->indent) === true) {
			return $string;
		}

		$string = json_encode($object, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		return $string;
	}
	// }}}

	// {{{ private function indentXML($string)
	private function indentXML($string)
	{
		if (empty($this->indent) === true) {
			return $string;
		}

		$string = preg_replace("/>\s*</", ">\n<", $string);
		$lines  = explode("\n", $string);
		$string = '';
		$indent = 0;

		foreach ($lines as $line) {
			$increment = false;
			$decrement = false;

			if (preg_match('#<\?xml.+\?>#', $line) == true) {
				// <?xml … 
			} else
			if (preg_match('#<[^/].+>.*</.+>#', $line) == true) {
				// Open Tag & Close Tag
			} else
			if (preg_match('#<.+/>#', $line) == true) {
				// Self-closing Tag
			} else
			if (preg_match('#<!.*>#', $line) == true) {
				// Comments and CDATA
			} else
			if (preg_match('#<[^/].+>#', $line) == true) {
				// Open Tag
				$increment = true;
			} else
			if (preg_match('#</.+>#', $line) == true) {
				// Close Tag
				$decrement = true;
			} else {
				// Others
			}

			if ($decrement === true) {
				$indent -= 1;
			}
			$string .= str_repeat("\t", $indent).$line."\n";
			if ($increment === true) {
				$indent += 1;
			}
		}
		return $string;
	}
	// }}}

	// {{{ public function __destruct()
	public function __destruct()
	{
		curl_close($this->ch);
	}
	// }}}

}

