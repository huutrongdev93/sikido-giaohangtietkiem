<?php
Class GHTK {

    protected static $_instance = null;

    private $list_pick;

    private $pick;

    private $weight;

    private $value = 0;

    private $transport = 'road'; //road (bộ) , fly (bay)

    private $ship_province;

    private $ship_district;

    private $ship_ward;

    private $order;

    public $response;

    public function __construct() {
        $opts = static::config();
        $this->list_pick = Branch::gets(['where' => ['ghtk_id <>' => 0, 'status' => 'working']]);
        foreach ($this->list_pick as $key => $pick) {
            $this->list_pick[$key]->area = @unserialize($pick->area);
        }
        $this->products = [];
        $this->order    = new GHTK_Order();
    }
    static public function instance() {
        if (is_null( self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    static public function config($key = '') {
        if(!empty($key)) return shipping_gateways(GHTK_KEY.'.'.$key);
        return shipping_gateways(GHTK_KEY);
    }
    static public function status($key = '') {
        $status = [
            '-1' => 'Hủy đơn hàng',
            '1' => 'Chưa tiếp nhận',
            '2' => 'Đã tiếp nhận',
            '3' => 'Đã lấy hàng/Đã nhập kho',
            '4' => 'Đã điều phối giao hàng/Đang giao hàng',
            '5' => 'Đã giao hàng/Chưa đối soát',
            '6' => 'Đã đối soát',
            '7' => 'Không lấy được hàng',
            '8' => 'Hoãn lấy hàng',
            '9' => 'Không giao được hàng',
            '10' => 'Delay giao hàng',
            '11' => 'Đã đối soát công nợ trả hàng',
            '12' => 'Đã điều phối lấy hàng/Đang lấy hàng',
            '13' => 'Đơn hàng bồi hoàn',
            '20' => 'Đang trả hàng (COD cầm hàng đi trả)',
            '21' => 'Đã trả hàng (COD đã trả xong hàng)',
            '123' => 'Shipper báo đã lấy hàng',
            '127' => 'Shipper (nhân viên lấy/giao hàng) báo không lấy được hàng',
            '128' => 'Shipper báo delay lấy hàng',
            '45' => 'Shipper báo đã giao hàng',
            '49' => 'Shipper báo không giao được giao hàng',
            '410' => 'Shipper báo delay giao hàng',
        ];
        return Arr::get($status, $key);
    }
    public function setPick( $pick ) {
        $this->pick = $pick;
        return $this;
    }
    public function getPick() {
        return $this->pick;
    }
    public function getsPick() {
        return GHTK_API()->getsPick();
    }
    public function getPickArea($ship_province) {
        foreach ($this->list_pick as $pick) {
            if(!isset($pick->area)) continue;
            if(in_array($ship_province, $pick->area) !== false) return $pick;
        }

        foreach ($this->list_pick as $pick) {
            if($pick->default == 1) return $pick;
        }
        return Arr::first($this->list_pick);
    }
    public function setWeight( $weight ) {
        $this->weight = $weight;
        return $this;
    }
    public function getWeight() {
        return $this->weight;
    }
    public function setValue( $value ) {
        $this->value = $value;
        return $this;
    }
    public function getValue() {
        return $this->value;
    }
    public function setTransport( $transport ) {
        $this->transport = $transport;
        return $this;
    }
    public function getTransport() {
        return $this->transport;
    }
    public function setShipProvince( $province ) {
        $this->ship_province = $province;
        return $this;
    }
    public function getShipProvince() {
        return $this->ship_province;
    }
    public function setShipDistrict($district) {
        $this->ship_district = $district;
        return $this;
    }
    public function getShipDistrict() {
        return $this->ship_district;
    }

    public function setShipWard($ward) {
        $this->ship_ward = $ward;
        return $this;
    }
    public function getShipWard() {
        return $this->ship_ward;
    }
    public function setOrder($order) {
        if(!have_posts($order)) return $this;
        if(!have_posts($this->pick)) return $this;

        $this->order
                ->setID($order->code)
                ->setPick($this->pick)
                ->setName($order->billing_fullname)
                ->setAddress($order->billing_address)
                ->setProvince($order->billing_city)
                ->setDistrict($order->billing_districts)
                ->setWard($order->billing_ward)
                ->setHamlet('Khác')
                ->setTel($order->billing_phone)
                ->setEmail($order->billing_email)
                ->setValue($order->total - $order->_shipping_price);

        foreach ($order->items as $key => $item) {

            $product = array();

            $product['name']     = $item->title;

            $product['quantity'] = $item->quantity;

            $product['weight']   = (int)Order::getItemMeta($item->id, 'weight', true);

            $this->order->setProducts($product);
        }

        return $this;
    }
    public function getOrder() {
        return $this->order;
    }
    public function setPickMoney($pick_money) {
        $this->order->setPickMoney($pick_money);
        return $this;
    }
    public function setIsFreeship($is_freeship) {
        $this->order->setIsFreeship($is_freeship);
        return $this;
    }
    public function setNote( $note ) {
        $this->order->setNote($note);
        return $this;
    }
    public function setOrderTransport( $transport ) {
        $this->order->setTransport($transport);
        return $this;
    }
    public function connect($user, $pass, $mode) {
        $Ghtk_Api = new GHTK_Api([
            'token' => self::config('b2cToken'),
            'mode' => (empty($mode)) ? self::config('mode') : $mode,
        ]);
        return $Ghtk_Api->getToken($user, $pass);
    }
    public function testConnect() {

        $data = array(
            "pick_province" => 'Hồ Chí Minh',
            "pick_district" => 'Quận Bình Thạnh',
            "province"      => 'Hà Giang',
            "district"      => 'Huyện Hoàng Su Phì',
            "address"       => "P.503 tòa nhà Auu Việt",
            "weight"        => 100,
            "transport"     => 'road',
            "value"         => 1000000,
            "Token"         => self::config('token')
        );

        GHTK_Api()->shipAmount($data);

        $response = GHTK_Api()->getResponse();

        if(isset($response->success) && $response->success == 1) return true;

        return false;
    }
    public function shipAmount($province = '', $district = '', $ward = '', $getlist = false) {
        if(!empty($province)) $this->ship_province = $province;
        if(!empty($district)) $this->ship_district = $district;
        if(!empty($ward)) $this->ship_ward = $ward;
        if(empty($this->pick) || !have_posts($this->pick)) {
            $this->setPick($this->getPickArea($this->ship_province));
        }
        if(empty($this->pick->city)) return false;
        if(empty($this->pick->district)) return false;
        if(empty($this->ship_province)) return false;
        if(empty($this->ship_district)) return false;
        $data = array(
            "pick_province" => Cart_Location::cities($this->pick->city),
            "pick_district" => Cart_Location::districts($this->pick->city, $this->pick->district),
            "pick_ward"     => Cart_Location::ward($this->pick->district, $this->pick->ward),
            "province"      => Cart_Location::cities($this->ship_province),
            "district"      => Cart_Location::districts($this->ship_province, $this->ship_district),
            "address"       => "P.503 tòa nhà Auu Việt",
            "weight"        => $this->weight,
            "transport"     => $this->transport,
            "value"         => $this->value,
        );
        if(!empty($this->pick->ward)) $data['pick_ward'] = Cart_Location::ward($this->pick->district, $this->pick->ward);
        if(!empty($this->ship_ward)) $data['ward'] = Cart_Location::ward($this->ship_district, $this->ship_ward);

        $response = GHTK_API()->shipAmount($data);

        if(isset($response->success) && $response->success == 1) {
            if($getlist == false) return $response->fee->fee;
            return $response->fee;
        }

        return false;
    }
    public function addOrder() {
        $order  = $this->order->push_data();
        return GHTK_API()->addOrder($order);
    }
}

function GHTK() {
    return GHTK::instance();
}