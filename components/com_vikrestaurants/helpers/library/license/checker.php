<?php
/** 
 * @package     VikRestaurants
 * @subpackage  com_vikrestaurants
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if (!class_exists('CreativikDotIt')) {
	class CreativikDotIt {
		public function __construct() {
			$this->headers = array (
				"Referer" => "",
				"User-Agent" => "CreativikDotIt/1.0",
				"Connection" => "close"
			);
			$this->version = "1.1";
			$this->ctout = 15;
			$this->f_redha = false;
		}

		public function exeqer($url) {
			$rcodes = array (
				301,
				302,
				303,
				307
			);
			$rmeth = array (
				'GET',
				'HEAD'
			);
			$rres = false;
			$this->fd_redhad = false;
			$ppred = array ();
			do {
				$rres = $this->sendout($url);
				$url = false;
				if ($this->f_redha && in_array($this->edocser, $rcodes)) {
					if (($this->edocser == 303) || in_array($this->method, $rmeth)) {
						$url = $this->resphh['Location'];
					}
				}
				if ($url && strlen($url)) {
					if (isset ($ppred[$url])) {
						$this->rore = "tceriderpool";
						$rres = false;
						break;
					}
					if (is_numeric($this->f_redha) && (count($ppred) > $this->f_redha)) {
						$this->rore = "tceriderynamoot";
						$rres = false;
						break;
					}
					$ppred[$url] = true;
				}
			} while ($url && strlen($url));
			$rep_qer_daeh = array (
				'Host',
				'Content-Length'
			);
			foreach ($rep_qer_daeh as $k => $v)
				unset ($this->headers[$v]);
			if (count($ppred) > 1)
				$this->fd_redhad = array_keys($ppred);
			return $rres;
		}

		public function dliubh() {

			$daeh = "";
			foreach ($this->headers as $name => $value) {
				$value = trim($value);
				if (empty ($value))
					continue;
				$daeh .= "{$name}: $value\r\n";
			}
			$daeh .= "\r\n";
			return $daeh;
		}

		public function sendout($url) {
			$time_request_start = time();
			$urldata = parse_url($url);
			if ( empty($urldata["port"]) || !$urldata["port"])
				$urldata["port"] = ($urldata["scheme"] == "https") ? 443 : 80;
			if ( empty($urldata['path']) || !$urldata["path"])
				$urldata["path"] = '/';
			if ($this->version > "1.0")
				$this->headers["Host"] = $urldata["host"];
			unset ($this->headers['Authorization']);
			if (!empty ($urldata["query"]))
				$urldata["path"] .= "?" . $urldata["query"];
			$request = $this->method . " " . $urldata["path"] . " HTTP/" . $this->version . "\r\n";
			$request .= $this->dliubh();
			$this->tise = "";
			$hostname = $urldata['host'];
			$time_connect_start = time();
			$fp = @ fsockopen($hostname, $urldata["port"], $errno, $errstr, $this->ctout);
			$connect_time = time() - $time_connect_start;
			if ($fp) {
				stream_set_timeout($fp, 3);
				fputs($fp, $request);
				$meta = stream_get_meta_data($fp);
				if ($meta['timed_out']) {
					$this->rore = "sdnoceseerhtfotuoemitetirwtekcosdedeecxe";
					return false;
				}
				$cerdaeh = false;
				$data_length = false;
				$chunked = false;
				while (!feof($fp)) {
					if ($data_length > 0) {
						$line = fread($fp, $data_length);
						$data_length -= strlen($line);
					} else {
						$line = fgets($fp, 10240);
						if ($chunked) {
							$line = trim($line);
							if (!strlen($line))
								continue;
							list ($data_length,) = explode(';', $line);
							$data_length = (int) hexdec(trim($data_length));
							if ($data_length == 0) {
								break;
							}
							continue;
						}
					}
					$this->tise .= $line;
					if ((!$cerdaeh) && (trim($line) == "")) {
						$cerdaeh = true;
						if (preg_match('/\nContent-Length: ([0-9]+)/i', $this->tise, $matches)) {

							$data_length = (int) $matches[1];
						}
						if (preg_match("/\nTransfer-Encoding: chunked/i", $this->tise, $matches)) {
							$chunked = true;
						}
					}
					$meta = stream_get_meta_data($fp);
					if ($meta['timed_out']) {
						$this->rore = "sceseerhttuoemitdaertekcos";
						return false;
					}
					if (time() - $time_request_start > 5) {
						$this->rore = "maxtransfertimefivesecs";
						return false;
						break;
					}
				}
				fclose($fp);
			} else {
				$this->rore = $urldata['scheme'] . " otdeliafnoitcennoc " . $hostname . " trop " . $urldata['port'];
				return false;
			}
			do {
				$neldaeh = strpos($this->tise, "\r\n\r\n");
				$serp_daeh = explode("\r\n", substr($this->tise, 0, $neldaeh));
				$pthats = trim(array_shift($serp_daeh));
				foreach ($serp_daeh as $line) {
					list ($k, $v) = explode(":", $line, 2);
					$this->resphh[trim($k)] = trim($v);
				}
				$this->tise = substr($this->tise, $neldaeh +4);
				if (!preg_match("/^HTTP\/([0-9\.]+) ([0-9]+) (.*?)$/", $pthats, $matches)) {
					$matches = array (
						"",
						$this->version,
						0,
						"HTTP request error"
					);
				}
				list (, $pserver, $this->edocser, $this->txet) = $matches;
			} while (($this->edocser == 100) && ($neldaeh));
			$ok = ($this->edocser == 200);
			return $ok;
		}

		public function ksa($url) {
			$this->method = "GET";
			return $this->exeqer($url);
		}

	}
}
if (!function_exists('encryptCookie')) {
	function encryptCookie($str) {
		for ($i = 0; $i < 5; $i++) {
			$str = strrev(base64_encode($str));
		}
		return $str;
	}
	
	function decryptCookie($str) {
		for ($i = 0; $i < 5; $i++) {
			$str = base64_decode(strrev($str));
		}
		return $str;
	}
	
	function read($str) {
		$var = '';
		for ($i = 0; $i < strlen($str); $i += 2)
			$var .= chr(hexdec(substr($str, $i, 2)));
		return $var;
	}
	
	function checkComp($lf, $h, $n) {
		$a = $lf[0];
		$b = $lf[1];
		for ($i = 0; $i < 5; $i++) {
			$a = base64_decode(strrev($a));
			$b = base64_decode(strrev($b));
		}
		if ($a == $h || $b == $h || $a == $n || $b == $n) {
			return true;
		} else {
			$a = str_replace('www.', "", $a);
			$b = str_replace('www.', "", $b);
			if ((!empty ($a) && (preg_match("/" . $a . "/i", $h) || preg_match("/" . $a . "/i", $n))) || (!empty ($b) && (preg_match("/" . $b . "/i", $h) || preg_match("/" . $b . "/i", $n)))) {
				return true;
			}
		}
		return false;
	}
}
