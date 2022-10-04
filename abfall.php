<?php

const SERVICE_USERAGENT = "'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
	/**
     * Checks if a string starts with a given substring
     *
     * @param string $haystack The string to search in.
     * @param string $needle The substring to search for in the haystack.
     * @param bool Returns true if haystack begins with needle, false otherwise.
     */
	function StartsWith($haystack, $needle)
    {
        return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
	
function GetDocument($url, $request)
    {
        $response = ServiceRequest($url, $request);
        if ($response !== false) {
            // $this->SendDebug(__FUNCTION__, $response);
            $dom = new DOMDocument();
            // disable libxml errors
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));
            // remove errors for yucky html
            libxml_clear_errors();
            $xpath = new DOMXpath($dom);
            return $xpath;
        }
        return $response;
    }

	function ServiceRequest($url, $request, $method = 'GET')
    {
        // Return
        $ret = false;
        // CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, SERVICE_USERAGENT);
        if ($request != null) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        //$this->SendDebug(__FUNCTION__, $response);
        if ($response != null) {
            return $response;
        }
        return $ret;
    }















        // Build URL for token
        $url = 'https://api.abfall.io/?key=XXXXXYOURKEYXXXXXX&modus=d6c5855a62cf32a4dadbc2831f0f295f&waction=init';

        // Build GET data
        $request = null;

        // Request FORM (xpath)
        $res = GetDocument($url, $request);
        // Extract token
        $token = null;
        if ($res !== false) {
            $inputs = $res->query("//input[@type='hidden']");
            foreach ($inputs as $input) {
                $name = $input->getAttribute('name');
                $value = $input->getAttribute('value');
                if (!StartsWith($name, 'f_')) {
                    $token = $name . '=' . $value;
                }
            }
        }
		
		// print token
        //print $token;
		
		$params[] = $token;
		
		// build dates
		$heute = date("Ymd");  
		$ende=date_add(date_create(date('Y-m-d H:i:s')),date_interval_create_from_date_string("30 days"));
		$ende=date_format($ende,"Ymd");
		
		/*
		echo PHP_EOL;echo PHP_EOL;
		echo $heute;
		echo PHP_EOL;
		echo $ende;
		echo PHP_EOL;echo PHP_EOL; 
		*/
		
		// ---------------------------
		//Abfrage Werte
		// ---------------------------
		$io['f_id_kommune']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_bezirk']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_strasse']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_abfalltyp_0']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_abfalltyp_3']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_abfalltyp_4']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_abfalltyp_5']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_abfalltyp_6']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_id_abfalltyp_7']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_abfallarten_index_max']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_abfallarten']='XXXXXYOUR_SETTINGS_XXXXXX';
		$io['f_zeitraum']=$heute.'-'.$ende;


		
		foreach ($io as $key => $entry) {
                if (StartsWith($key, 'f_') && strlen($entry)) {
                    $params[] = $key . '=' . $entry;
                }
            }
			
		$request = implode('&', $params);
		$url='https://api.abfall.io/?key=XXXXXYOURKEYXXXXXX&modus=d6c5855a62cf32a4dadbc2831f0f295f&waction=export_ics';
		$res = ServiceRequest($url, $request);
		#echo $res
		file_put_contents('abfall.ics', $res);
?>
