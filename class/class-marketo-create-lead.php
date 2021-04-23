<?php

/* Class for push lead to marketo API */

class GFMarketoPushLeads {

    public $input; //an array of lead records as objects (required)
    public $programName; // program that activity is attributed to (required)
    public $lookupField; //field used for deduplication
    public $reason; // activity metadata
    public $source; // activity metadata

    /* Post Lead data */

    public function postMarketoData($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key) {

        $url = $rest_api_endpoint_url . "/rest/v1/leads/push.json?access_token=" . $this->getMarketoToken($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key);
        $ch = curl_init($url);
        $requestBody = $this->MarketoBodyBuilder();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_getinfo($ch);
        $response = curl_exec($ch);
        return $response;
    }

    /* Get token from Marketo */

    private function getMarketoToken($token_endpoint_url, $token_client_id, $token_secret_key) {
        $ch = curl_init($token_endpoint_url . "/identity/oauth/token?grant_type=client_credentials&client_id=" . $token_client_id . "&client_secret=" . $token_secret_key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        $token = $response->access_token;
        return $token;
    }

    /* Set Request Body parametes */

    private function MarketoBodyBuilder() {
        $body = new stdClass();
        if (isset($this->programName)) {
            $body->programName = $this->programName;
        }
        if (isset($this->reason)) {
            $body->reason = $this->reason;
        }
        if (isset($this->source)) {
            $body->source = $this->source;
        }
        if (isset($this->lookupField)) {
            $body->lookupField = $this->lookupField;
        }
        $body->input = $this->input;
        $json = json_encode($body);
        return $json;
    }

}
