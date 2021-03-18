<?php

class Curl {
	var $curl;
    var $manual_follow;
    var $redirect_url;

	function Curl() {
		$this->curl = curl_init();
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: "; 

		curl_setopt($this->curl, CURLOPT_USERAGENT, 'Firefox 0.9 (Mac OSX )');
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, 4000);
		curl_setopt($this->curl, CURLOPT_POSTREDIR, 3);
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		$cookie_file = uniqid().'.txt';
		curl_setopt($this->curl, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $cookie_file);
	
        $this->setRedirect();
	}

	function close() {
	 curl_close($this->curl);
	}
	

	function getInstance() {
		static $instance;
		if (!isset($instance)) {
			$curl = new Curl;
			$instance = array($curl);
		}
		return $instance[0];
	}

    function setTimeout($connect, $transfer) {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $connect);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $transfer);
    }

    function getError() {
        return curl_errno($this->curl) ? curl_error($this->curl) : false;
    }

    function disableRedirect() {
        $this->setRedirect(false);
    }

    function setRedirect($enable = true) {
        if ($enable) {
	        $this->manual_follow = !@curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        } else {
	        @curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
            $this->manual_follow = false;
        }
    }

    function getHttpCode() {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }

    function getResponse() {
        return curl_getinfo($this->curl);
    }

	function auth($user, $pass) {
		curl_setopt($this->curl, CURLOPT_USERPWD, "$user:$pass");
	}

	function makeQuery($data) { 
		if (is_array($data)) {
			$fields = array();
			foreach ($data as $key => $value) {
 				$fields[] = $key . '=' . urlencode($value);
			}
			$fields = implode('&', $fields);
		} else {
			$fields = $data;
		}

		return $fields;
	}
	
	function maybeFollow($page) {
        if (strpos($page, "\r\n\r\n") !== false) {
            list($headers, $page) = explode("\r\n\r\n", $page, 2);
        }

        $code = $this->getHttpCode();
        if ($code > 300 && $code < 310) {
            preg_match("#Location: ?(.*)#i", $headers, $match);
            $this->redirect_url = trim($match[1]);

	        if ($this->manual_follow) {
                return $this->get($this->redirect_url);
            }
        } else {
            $this->redirect_url = '';
        }
            
	    return $page;
	}
	
	function post($url, $data) {
		$fields = $this->makeQuery($data);
		$cookies= unserialize(file_get_contents('cookies/cookies.ser'));

			$cookie = '';
			$show = '';
			$head = '';
			$delim = '';
			foreach ($cookies as $k => $v){
			$cookie .= "$delim$k$v";
			$delim = '; ';
					}
					
					echo "Cookies";
			var_dump($cookie);
		curl_setopt($this->curl, CURLOPT_COOKIE, $cookie );
		
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		$page = curl_exec($this->curl);
		$html= $page;
		$skip = intval(curl_getinfo($this->curl, CURLINFO_HEADER_SIZE)); 
		$requestHeader= substr($html,0,$skip);
		$html = substr($html,$skip);
		$e = 0;
		while(true){
				$s = strpos($requestHeader,'Set-Cookie: ',$e);
				if (!$s){break;}
					$s += 12;
					$e = strpos($requestHeader,';',$s);
					$cookie = substr($requestHeader,$s,$e-$s) ;
					$s = strpos($cookie,'=');
					$key = substr($cookie,0,$s);
					$value = substr($cookie,$s);
					$cookies[$key] = $value;
				}

			$fp = fopen('cookies/cookies.ser' ,'w');
			fwrite($fp,serialize($cookies));
			fclose($fp);
			
			$info = curl_getinfo($this->curl);
			print_r($info);

		$error = curl_errno($this->curl);	
		if ($error != CURLE_OK || empty($page)) {
			  echo 'Curl error: ' . curl_error($this->curl);
			return false;
		}

		curl_setopt($this->curl, CURLOPT_POST, false);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, '');
		
		return $this->maybeFollow($page);
	}
	
	function get($url, $data = null) {
        if (!is_null($data)) {
            $fields = $this->makeQuery($data);
            $url .= '?' . $fields;
        }
		$fields = $this->makeQuery($data);
		$cookies= unserialize(file_get_contents('cookies/cookies.ser'));

			$cookie = '';
			$show = '';
			$head = '';
			$delim = '';
			foreach ($cookies as $k => $v){
			$cookie .= "$delim$k$v";
			$delim = '; ';
					}

		curl_setopt($this->curl, CURLOPT_COOKIE, $cookie );

		curl_setopt($this->curl, CURLOPT_URL, $url);
		$page = curl_exec($this->curl);
		$html= $page;
		$skip = intval(curl_getinfo($this->curl, CURLINFO_HEADER_SIZE)); 
		$requestHeader= substr($html,0,$skip);
		$html = substr($html,$skip);
		$e = 0;
		while(true){
				$s = strpos($requestHeader,'Set-Cookie: ',$e);
				if (!$s){break;}
					$s += 12;
					$e = strpos($requestHeader,';',$s);
					$cookie = substr($requestHeader,$s,$e-$s) ;
					$s = strpos($cookie,'=');
					$key = substr($cookie,0,$s);
					$value = substr($cookie,$s);
					$cookies[$key] = $value;
				}

			$fp = fopen('cookies/cookies.ser' ,'w');
			fwrite($fp,serialize($cookies));
			fclose($fp);
		$info = curl_getinfo($this->curl);
			print_r($info);
			
			
		$error = curl_errno($this->curl);

		if ($error != CURLE_OK || empty($page)) {
			return false;
		}
		
		return $this->maybeFollow($page);
	}
}

?>
