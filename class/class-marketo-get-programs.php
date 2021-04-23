<?php

/* Class for Get Progams from marketo API */

class GFMarketoGetMarketoPrograms {

    public $status; //status filter for Engagement and Email programs, can be used to filter on "on", "off", or "unlocked"
    public $offset; //integer offset for paging
    public $maxReturn; //number of results to return, default 20, max 200

    public function getProgramsData($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key) {
        $url = $rest_api_endpoint_url . "/rest/asset/v1/programs.json?";
        if (isset($status)) {
            $url .= "status=" . $status . "&";
        }
        if (isset($offset)) {
            $url .= "offset=" . $offset . "&";
        }
        if (isset($this->maxReturn)) {
            $url .= "maxReturn=" . $this->maxReturn;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json', "Authorization: Bearer " . $this->getMarketoToken($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key)));
        $response = curl_exec($ch);
        return $response;
    }

    private function getMarketoToken($token_endpoint_url, $token_client_id, $token_secret_key) {
        $ch = curl_init($token_endpoint_url . "/identity/oauth/token?grant_type=client_credentials&client_id=" . $token_client_id . "&client_secret=" . $token_secret_key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        $token = $response->access_token;
        return $token;
    }

}
