<?php

if (!function_exists('sendCurl_request')) {

    function sendCurl_request($json_request, $endUrl, $provider = null)
	{

		if($provider == 'Signzy')
		{
			$url = URL_SIGNZY.'api/v3/' . $endUrl ;
			$token = array(
				'Authorization: '.TOKEN_SIGNZY,
				'Content-Type: application/json'
			);
		}
		else
		{
            $url = URL_NP.'api/generateNetBankingRequest';
			$tokenValue = trim(TOKEN_NP);
			$token = array(
				'Content-Type: application/json',
				'auth-token: '.$tokenValue
			);
		}
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$json_request,
			CURLOPT_HTTPHEADER => $token,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		//echo "<pre>"; print_r($token);
		//echo $url."<br>".$json_request."<br>".$response; exit;
		return $response;
    }

}
