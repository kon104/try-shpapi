<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs;

class ApiRequest
{
	const CTYPE_XML = "application/xml";
	const CTYPE_JSON = "application/json";
	const CTYPE_MPART_FORMDATA = "multipart/form-data";

	private $ch;
	private $custom_headers = array();
	private $req_url;
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
		$this->custom_headers[] = "Authorization: Bearer $access_token";
	}
	// }}}

	// {{{ protected function httpGet($url)
	protected function httpGet($url)
	{
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		$status = self::httpExecute($url);

		return $status;
	}
	// }}}

	// {{{ protected function httpPost($url, $data, $ctype = null)
	protected function httpPost($url, $data, $ctype = null)
	{
		$data = $this->convertContentType($data, $ctype);

		$this->req_rawbody = $data;
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		$status = self::httpExecute($url);

		return $status;
	}
	// }}}

	// {{{ protected function httpPut($url, $data, $ctype = null)
	protected function httpPut($url, $data, $ctype = null)
	{
		$data = $this->convertContentType($data, $ctype);

		$this->req_rawbody = $data;
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		$status = self::httpExecute($url);

		return $status;
	}
	// }}}

	// {{{ protected function httpDelete($url)
	protected function httpDelete($url)
	{
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		$status = self::httpExecute($url);

		return $status;
	}
	// }}}

	// {{{ private function convertContentType($data, $ctype)
	private function convertContentType($data, $ctype)
	{
		if ($ctype === $this::CTYPE_XML) {
			$data = $this->array2xml($data);
			$this->custom_headers[] = "Content-Type: " . $this::CTYPE_XML;
		} else
		if ($ctype === $this::CTYPE_JSON) {
			$data = json_encode($data);
			$this->custom_headers[] = "Content-Type: application/json; charset=UTF-8";
		} else
		if ($ctype === $this::CTYPE_MPART_FORMDATA) {
		} else {
			$data = http_build_query($data);
		}
		return $data;
	}
	// }}}

	// {{{ protected function addDebug($text)
	protected function addDebug($text)
	{
		$this->debug[] = $text;
	}
	// }}}

	// {{{ private function httpExecute($url)
	private function httpExecute($url)
	{
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->custom_headers);
		curl_setopt($this->ch, CURLOPT_URL, $url);
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

		$this->req_url = $url;
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
			"url" => $this->req_url,
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

	// {{{ private function array2xml($array)
	// how to make xml: https://www.php.net/manual/ja/example.xmlwriter-simple.php
	private function array2xml($array)
	{
		$writer = xmlwriter_open_memory();
		xmlwriter_set_indent($writer, true);
		xmlwriter_start_document($writer, '1.0', 'UTF-8');
		$writer = $this->a2x($array, $writer);
		xmlwriter_end_document($writer);
		$xml = xmlwriter_output_memory($writer);
		return $xml;
	}
	// }}}

	// {{{ private function a2x($a, $w)
	private function a2x($a, $w)
	{
		foreach($a as $k => $v) {
			xmlwriter_start_element($w, $k);
			if (is_array($v)) {
				$w = $this->a2x($v, $w);
			} else {
				xmlwriter_text($w, $v);
			}
			xmlwriter_end_element($w);
		}
		return $w;
	}
	// }}}

	// {{{ public function __destruct()
	public function __destruct()
	{
		curl_close($this->ch);
	}
	// }}}

}

