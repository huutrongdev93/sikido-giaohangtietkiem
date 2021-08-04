<?php
Class GHTK_Api {

    protected static $_instance = null;

    private $token;

    private $b2c_token;

    private $mode = 'sandbox';

    private $response;

    protected $baseURL = array(
        'test'   => 'https://services.ghtklab.com',
        'prod'   => 'https://services.giaohangtietkiem.vn'
    );

    public function __construct($opts = []) {

        if (!function_exists('curl_init')) {
            throw new Exception('GHTK needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new Exception('GHTK needs the JSON PHP extension.');
        }

        if(!have_posts($opts)) $opts = GHTK::config();

        if(isset($opts) && !empty($opts["token"])) {
            $this->token = $opts["token"];
        }

        if (isset($opts) && !empty($opts["mode"])) {
            $this->mode = $opts["mode"];
        }

        $this->b2c_token = 'e4c4659C04dd309628c0a830E9878d2B1Ad8fa4b';
    }

    public static function instance($opts) {
        if (is_null( self::$_instance)) {
            self::$_instance = new self($opts);
        }
        return self::$_instance;
    }

    public function getToken($email, $pass) {
        $data   = ['email' => Str::clear($email), 'password' => Str::clear($pass)];
        $this->post(GHTK_Endpoint::SHOP_TOKEN, $data);
        return $this->response;
    }

    public function shipAmount($data = []) {
        return $this->get(GHTK_Endpoint::ESTIMATE_SHIPPING, $data);
    }

    public function getsPick($data = []) {
        return $this->get(GHTK_Endpoint::GETS_PICK, $data);
    }

    public function addOrder($order) {
        return $this->post(GHTK_Endpoint::SYNC_ORDER, $order);
    }

    public function getOrderStatus(string $trackingLabel) {
        return $this->get(GHTK_Endpoint::GET_ORDER_STATUS, $trackingLabel);
    }

    public function cancelOrder(string $trackingLabel) {
        $response = $this->post(GHTK_Endpoint::CANCEL_ORDER.'/'.$trackingLabel);
        if(isset($response->success)) return $response->success;
        return false;
    }

    public function printOrder(string $trackingLabel) {

        try {

            $api = $this->getUrl(GHTK_Endpoint::PRINT_ORDER.$trackingLabel);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $api,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/json",
                    "token: ".$this->token,
                ),
            ));

            curl_setopt( $curl, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] . "/".SPATH."uploads/cacert.pem");

            $response = curl_exec($curl);

            curl_close($curl);

            header('Content-type: ' . 'application/octet-stream');

            header('Content-Disposition: attachment; filename="GHTK_ORDER_"'.$trackingLabel.'.pdf');

            echo $response;

        } catch (\Exception $e) {

            $result = [];
        }
    }

    public function getResponse() {
        return $this->response;
    }

    public function getUrl($endpoint) {
        return $this->baseURL[$this->mode] . $endpoint;
    }

    public function get($api, $data = []) {

        $query = '';

        if(have_posts($data)) {
            $query = '?'.http_build_query($data);
        }
        else if(!empty($data)) {
            $query = '/'.$data;
        }

        $api = $this->getUrl($api).$query;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "token: ".$this->token,
            ),
        ));

        curl_setopt( $curl, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] . "/".SPATH."uploads/cacert.pem");

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        $this->response = json_decode($response);

        if ($err) {
            return $err;
        } else {
            return (is_string($response)) ? json_decode($response) : $response;
        }
    }

    public function post($api, $data = []) {
        $api    = $this->GetUrl($api);
        if(!empty($this->token)) $data['token'] = $this->token;
        $data = json_encode($data);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Token: ".$this->token,
                "X-Refer-Token: ".$this->b2c_token,
                "Content-Length: " . strlen($data),
            ),
        ]);
        curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt( $curl, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] . "/".SPATH."uploads/cacert.pem");
        $response = curl_exec($curl);
        $this->response = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return json_decode($response);
        }
    }
}

function GHTK_API() {
    return GHTK_API::instance(GHTK::config());
}