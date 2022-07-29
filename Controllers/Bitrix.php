<?php
require_once('/home/bbdevely/eot/vendor/autoload.php');

class Bitrix{
    protected $_url;
    
    public function __construct($url){
        $this->_url = $url;
    }
    
    public function requestGet($method, $params, $totals = null, $toggle = 'json'){
        $response = $this->request($method, $params, $toggle);
        if ($toggle === 'xml') return $response;
        if (!isset($totals)) {
            $fullResponse = $response['result'];
        } else {
            $fullResponse = $response['total'];
        }
        while (isset($response['next'])) {
            sleep(0.1);
            $params["start"] = $response['next'];
            $response = $this->request($url, $method, $params, $toggle);
            if (!isset($totals)) {
                $fullResponse = array_merge($response['result'], $fullResponse);
            } else {
                $fullResponse = array_merge($response['total'], $fullResponse);
            }
        }
        if (!$fullResponse) {
            return;
        }
        return $fullResponse;
    }
    
    public function request($method, $params, $toggle = 'json'){
        $params = http_build_query($params);
        $url = $this->_url . $method . '.' . $toggle . '?' . $params;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($toggle == 'xml') {
            $response = curl_exec($ch);
        } else {
            $response = json_decode(curl_exec($ch), true);
        }
        curl_close($ch);
        return $response;
    }
    
    public function requestUpdate($method, $params, $totals=null , $toggle = 'json' ) {
        $curl = curl_init();
        $url = $this->_url . $method . '.' . $toggle;
        // echo json_encode($params, JSON_UNESCAPED_UNICODE) . "<br>" . $url;
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }
}