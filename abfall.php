<?php

const SERVICE_USERAGENT = "'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";



// build dates
$heute = date("Ymd");
$ende  = date_add(date_create(date('Y-m-d H:i:s')), date_interval_create_from_date_string("30 days"));
$ende  = date_format($ende, "Ymd");


// ---------------------------
//Abfrage Werte
// ---------------------------
const ABFALL_IO_KEY = "e2b5b0f129dde22e1993abc2aa4654f8";
$io['f_id_kommune']            = '2785';
$io['f_id_bezirk']             = '2331';
$io['f_id_strasse']            = '2332';
$io['f_id_abfalltyp_0']        = '20';
$io['f_id_abfalltyp_3']        = '279';
$io['f_id_abfalltyp_4']        = '19';
$io['f_id_abfalltyp_5']        = '60';
$io['f_id_abfalltyp_6']        = '31';
$io['f_id_abfalltyp_7']        = '8';
$io['f_abfallarten_index_max'] = '8';
$io['f_abfallarten']           = '31,19,0,279,60,275';
$io['f_zeitraum']              = $heute . '-' . $ende;



// Build URL for token
$url = 'https://api.abfall.io/?key='.ABFALL_IO_KEY.'&modus=d6c5855a62cf32a4dadbc2831f0f295f&waction=init';

// Build GET data
$request = null;

// Request FORM (xpath)
$res   = GetDocument($url, $request);
// Extract token
$token = null;
if ($res !== false) {
    $inputs = $res->query("//input[@type='hidden']");
    foreach ($inputs as $input) {
        $name  = $input->getAttribute('name');
        $value = $input->getAttribute('value');
        if (!StartsWith($name, 'f_')) {
            $token = $name . '=' . $value;
        }
    }
}

// print token
//print $token;

$params[] = $token;



foreach ($io as $key => $entry) {
    if (StartsWith($key, 'f_') && strlen($entry)) {
        $params[] = $key . '=' . $entry;
    }
}

$request = implode('&', $params);
$url     = 'https://api.abfall.io/?key=e2b5b0f129dde22e1993abc2aa4654f8&modus=d6c5855a62cf32a4dadbc2831f0f295f&waction=export_ics';
$res     = ServiceRequest($url, $request);
#echo $res;
file_put_contents('abfall.ics', $res);



















/*****************************************************
 ****** Functions
 *****************************************************/




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
/**
 * Sends the action url to extract the token pair
 *
 * @param string API key
 * @return string Token for Export.
 */
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

/**
 * Sends the API call
 *
 * @param string $url Rewquest URL
 * @param string $request If $request not null, we will send a POST request, else a GET request.
 * @param string $method Over the $method parameter can we force a POST or GET request!
 * @return mixed False if the response is null, otherwise the response
 */
function ServiceRequest($url, $request, $method = 'GET')
{
    // Return
    $ret  = false;
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
?>
