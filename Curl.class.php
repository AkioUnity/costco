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

		curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3236.0 Safari/537.36');
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
		
					
					echo "Cookies";
			//var_dump($cookie);
			
			$cookie="_abck=CAA5A3CA162DDC91D98DA92BDA578AC9B854F3949C660000C32C065A87B53334~0~CB2GJMwi+dMHl6lSVgkGS2sBD50aU+AfO2hwq9fAG88=~-1~-1; spid=E8EADC69-1075-4D73-B7BE-CA2ACC4C1427; bm_sz=8218F914881A02E79104323D0523D856~QAAQi/NUuOJrsm5fAQAA5Hnwr3fAeGPFY7F5LH38thwSmkWplvlQ7FIR3+GexoonQotpmswA1HkqVz4uZCNxEvPkYfKs55NN8vmA2U2aV+gx4RP65yyu9jsZ7TWp1W+o77aAEA5ZpDV8oou+sk9adBwxWvSUJ8iJNVvOgajl7127oIcz4o02esCnVJBJ5sI=; CRTOABE=0; capp_json=%7B%22coBrandedCreditMax%22%3Atrue%2C%22displayErrorForRenew%22%3Afalse%2C%22displayErrorForCancel%22%3Afalse%2C%22displayErrorForRenewGSHousehold%22%3Afalse%7D; akaas_AS01=2147483647~rv=84~id=7a5bafffcf610a7205e1ced7b393cf9c; AMCVS_97B21CFE5329614E0A490D45%40AdobeOrg=1; bm_mi=B099B383E603BB2B46A06EFCEF12B593~ikUpVS1OXyqEfbzzBMi+XE7EPGQYYwG6QKe1Nc/SJZ4RwEuPiorJWknHRiFXNeBCz/NLg2QWtanXJLthWDRq9yQWjwZ1LAxeOTfvSWrECZOyFiq6cqEXFNSABm2z6Fa8PUyvgjYd4NQRC24KbA8pmd7zhsWuNliQBUBIqwqluJiMAGXunrjTbQ47YJN9jr96I4NwON2dqClbYta12TzLwS+41jqrJelBbDOk2B2RGTc8H0s2Cp1hB3fGaBdeVM6lLq41CFY2EnVIv/jvBFFwzA==; ak_bmsc=058172A97191901A0DC370E927F3B252B854F3949C6600002E4D085AE1EB3168~pl3iGaPiLykmKJO1GRAsqeZ97zl3dbKBFA/nYT8/BwwLWp7j4n0kysMJ/quGAiteTJko1Hfh6dy0YOIgfXmaMOCHMlVpAbq+zeiLreQK4pxqfjKWRShAvEebwMkueeT8DPVe/5ZMDX2in+Y5NPX2Z5BlnT77PHV6TnA0fvzly7J6dIKCrzFkEv6h4HOKLUaajszG8ZNuYDLu8UxN6M1/+clOBsIUfrZu1KpqzP0WlTEg1iFOdiLjwW39eWgVll9EYH; hl_p=af122daa-b0cb-466f-ab44-b25282e5110f; WC_SESSION_ESTABLISHED=true; bvUserToken=b70e6fa6b3377afae9d6f950b61aaa0d646174653d323031372d31312d3132267573657269643d34373261333332642d326261332d346666652d613733322d633530313765323663346161; wcMember=d8689311e1aec6cb775afce942efd1ac%2C1%2CZ00020; tkSession=false; rrDisabledPref=\"\"; rrStoreFlag=\"\"; hashedUserId=\"\"; cartCountCookie=\"\"; cartTotalCookie=\"\"; amountToGoCookie=\"\"; grocSurchargeCookie=\"\"; WC_PATIENT_PROFILE_EXISTS=\"\"; PrescriptionCount=\"\"; WC_PERSISTENT=o%2FUpHPATdWRvSg59JH%2FiLI5%2B47M%3D%0A%3B2017-11-12+06%3A27%3A16.327_1506903764931-763_10301_-1002%2C-1%2CUSD_10301; WC_ACTIVEPOINTER=-1%2C10301; AMCV_97B21CFE5329614E0A490D45%40AdobeOrg=102365995%7CMCIDTS%7C17483%7CMCMID%7C34134803711313348191384826588342999499%7CMCAAMLH-1511101637%7C7%7CMCAAMB-1511101637%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1510504037s%7CNONE%7CMCSYNCSOP%7C411-17488%7CvVersion%7C2.2.0%7CMCAID%7CNONE; JSESSIONID=0000Ae6ZsAMibxvjRZUnBf09WlW:161b8g4i5; WC_AUTHENTICATION_-1002=-1002%2C5M9R2fZEDWOZ1d8MBwy40LOFIV0%3D; s_cc=true; WRIgnore=true; WRUID03232017=1479208422711295; __CT_Data=gpv=29&ckp=tld&dm=costco.com&apv_59_www33=29&cpv_59_www33=29&rpv_59_www33=29; WC_USERACTIVITY_-1002=-1002%2C10301%2C0%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C2BSxOhQF%2FA6v6FaDNojWA9y4onZR3wvVKYRgUvlBMDJPWgDOiAYPAtMQfbthACHEcggHD8QR4SSLIEeJ6Sm6L5naZeGrQWAkVnhQJntCY3OG%2FSPUGBW9bfnad78i1DVKxb88yv9TV9Cs5sLYPTnhsNQlN%2F7m4GxLqaYx6yFqU6M%2BGCGs8IJCUTFlrhI7N1pqXVRoCJxWHm%2BpofagHRv8eQ%3D%3D; WC_GENERIC_ACTIVITYDATA=[5981014349%3Atrue%3Afalse%3A0%3AYZWXYdgnihvmICoBm0cAXAYZfsg%3D][com.ibm.commerce.context.audit.AuditContext|1506903764931-763][com.ibm.commerce.store.facade.server.context.StoreGeoCodeContext|null%26null%26null%26null%26null%26null][CTXSETNAME|Store][com.ibm.commerce.context.globalization.GlobalizationContext|-1%26USD%26-1%26USD][com.ibm.commerce.catalog.businesscontext.CatalogContext|10701%26null%26false%26false%26false][com.ibm.commerce.context.base.BaseContext|10301%26-1002%26-1002%26-6][com.ibm.commerce.context.experiment.ExperimentContext|null][com.ibm.commerce.context.entitlement.EntitlementContext|4000000000000001002%264000000000000001002%26null%26-2000%26null%26null%26null][com.ibm.commerce.giftcenter.context.GiftCenterContext|null%26null%26null]; bm_sv=F84CB2655827D66C0EC860ABB5B856FA~a4Ebx5Okp0yvtmSb/FhGLpnuz1mmDqi/KbyRir0/6a5QtqaMS+Vf712TyPVkockBNG1XoUU1iwvv3AfwZz6oyKGLHhmLY01DZacc5YoB1zfPtcb66e6I68IsOUScpvV9T6n9tHZczqWoP7dYKfSAFdVsNFXK15N5TWQtT3sl688=; sp_ssid=1510497216837; mbox=PC#7c993979c65c4078a2c78bea3ed00a1f.17_75#1573742017|session#8201452af4174e499ced57c4bd488437#1510499077; s_sq=cwcostcocomprod%3D%2526c.%2526a.%2526activitymap.%2526page%253Dhttps%25253A%25252F%25252Fm.costco.com%25252FLogonForm%25253FcatalogId%25253D10701%252526langId%25253D-1%252526storeId%25253D10301%252526krypto%25253D26514sOhxa5d9RtavG5Xgl8AXn7dea%2525252FlBQu2WDvteM%2525252FPVA0yNRb4esPEVUE86wwOLGF8vTN4CX3Ub%2525252BLpoxwrDzUBSW5jQvr3WSSYY70Q8IH28NFq7n34tjzclDHim9WV6fIGQCSuV2kuzlPLJqSzoizdeMLvFf%2525252BTyU18V9uqL%2526link%253DSign%252520In%2526region%253DLogonForm%2526.activitymap%2526.a%2526.c; rememberedLogonId=null";
			
		curl_setopt($this->curl, CURLOPT_COOKIE, $cookie );
		curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);
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
			//print_r($info);

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
