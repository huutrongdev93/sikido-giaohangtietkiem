<?php
class GHTK_Order  {

    private $id;                //String - mã đơn hàng thuộc hệ thống của đối tác
    //Thông tin điểm lấy hàng
    private $pick_name;         //String - Tên người liên hệ lấy hàng hóa
    private $pick_money	;       //Integer - Số tiền cần thu hộ. Nếu bằng 0 thì không thu hộ tiền. Tính theo VNĐ
    private $pick_address_id;   //String - ID địa điểm lấy hàng của shop trong trang quản lý đơn hàng dành cho khách hàng. Nếu trường này khác rỗng sẽ được ưu tiên sử dụng
    private $pick_address; //	String - Địa chỉ ngắn gọn để lấy nhận hàng hóa. Ví dụ: nhà số 5, tổ 3, ngách 11, ngõ 45
    private $pick_province; //	String - Tên tỉnh/thành phố nơi lấy hàng hóa
    private $pick_district; //	String - Tên quận/huyện nơi lấy hàng hóa
    private $pick_ward; //	String - Tên phường/xã nơi lấy hàng hóa
    private $pick_street; //	String - Tên đường/phố nơi lấy hàng hóa
    private $pick_tel; //	String - Số điện thoại liên hệ nơi lấy hàng hóa
    private $pick_email; //	String - Email liên hệ nơi lấy hàng hóa
    //Thông tin điểm giao hàng
    private $name; //	String - tên người nhận hàng
    private $address; //	String - Địa chỉ chi tiết của người nhận hàng, ví dụ: Chung cư CT1, ngõ 58, đường Trần Bình
    private $province; //	String - Tên tỉnh/thành phố của người nhận hàng hóa
    private $district; //	String - Tên quận/huyện của người nhận hàng hóa
    private $ward; //	String - Tên phường/xã của người nhận hàng hóa
    private $hamlet; //Tên thôn/ấp/xóm/tổ
    private $street; //	String - Tên đường/phố của người nhận hàng hóa
    private $tel; //	String - Số điện thoại người nhận hàng hóa
    private $note; //	String - Ghi chú đơn hàng. Vd: Khối lượng tính cước tối đa: 1.00 kg
    private $email; //	String - Email người nhận hàng hóa
    //Thông tin điểm trả hàng		
    private $use_return_address; //	Integer - mặc định là 0. Field này có thể truyền vào một trong hai giá trị 0 hoặc 1. Bằng 0 nghĩa là địa chỉ trả hàng giống địa chỉ lấy hàng nên các field địa chỉ trả hàng không cần truyền qua. Bằng 1 nghĩa là sử dụng địa chỉ trả hàng khác địa chỉ lấy hàng và cần truyền vào giá trị cho các field tiếp theo
    private $return_name; //	String - tên người nhận hàng trả
    private $return_address; //	String - Địa chỉ chi tiết của người nhận hàng, ví dụ: nhà A, ngõ 100
    private $return_province; //	String - Tên tỉnh/thành phố của người nhận hàng hóa
    private $return_district; //	String - Tên quận/huyện của người nhận hàng hóa
    private $return_ward; //	String - Tên phường/xã của người nhận hàng hóa
    private $return_street; //	String - Tên đường/phố của người nhận hàng hóa
    private $return_tel; //	String - Số điện thoại người nhận hàng hóa
    private $return_email; //	String - Email người nhận hàng hóa
    //Các thông tin thêm		
    private $is_freeship; //	Integer - Freeship cho người nhận hàng. Nếu bằng 1 COD sẽ chỉ thu người nhận hàng số tiền bằng pick_money, nếu bằng 0 COD sẽ thu tiền người nhận số tiền bằng pick_money + phí ship của đơn hàng, giá trị mặc định bằng 0
    private $weight_option; //	String - nhận một trong hai giá trị gram và kilogram, mặc định là kilogram, đơn vị khối lượng của các sản phẩm có trong gói hàng
    private $total_weight; //	Double - Tổng khối lượng của đơn hàng, mặc định sẽ tính theo products.weight nếu không truyền giá trị này.
    private $pick_work_shift; //	Integer - Nếu set bằng 3 đơn hàng sẽ lấy vào buổi tối. 2: buồi chiều. 1: buổi sáng. Giá trị mặc định GHTK set theo ca tự tính.
    private $deliver_work_shift; //	Integer - Nếu set bằng 3 đơn hàng sẽ được giao vào buổi tối. 2: buồi chiều. 1: buổi sáng. Giá trị mặc định GHTK set theo ca tự tính.
    private $label_id; //	String - Mã vận đơn được cấp trước cho đối tác - mặc định không sử dụng được field này, cấu hình riêng cho từng gói dịch vụ
    private $pick_date; //	String YYYY/MM/DD - Hẹn ngày lấy hàng - mặc định không sử dụng được field này, cấu hình riêng cho từng gói dịch vụ
    private $deliver_date; //	String YYYY/MM/DD - Hẹn ngày giao hàng - mặc định không sử dụng được field này, cấu hình riêng cho từng gói dịch vụ
    private $expired; //	String YYYY/MM/DD hh:mm:ss - thời gian tự động - mặc định không sử dụng được field này, cấu hình riêng cho từng gói dịch vụ
    private $value; //	Interger (VNĐ) - Giá trị đóng bảo hiểm, là căn cứ để tính phí bảo hiểm và bồi thường khi có sự cố.
    private $opm; //	Interger (VNĐ) - 1. đơn chỉ thu tiền, 0. default
    private $pick_option; //	String - Nhận một trong hai giá trị cod và post, mặc định là cod, biểu thị lấy hàng bởi COD hoặc Shop sẽ gửi tại bưu cục
    private $actual_transfer_method; //	String - Trường này lưu đường vận chuyển của đơn hàng, mặc định là đường bay (fly). Nếu đơn hàng được chuyển bằng đường bộ (road), bạn sẽ nhận được thông báo của GHTK.
    private $transport; //	String - Phương thức vâng chuyển road ( bộ ) , fly (bay). Nếu phương thức vận chuyển không hợp lệ thì GHTK sẽ tự động nhảy về PTVC mặc định
    
    private $products;

    public function __construct() {

        $this->transport = 'road';
    }

    public function setID( $id ) {
        $this->id = $id;
        return $this;
    }

    public function getID() {
        return $this->id;
    }

    public function setPickMoney( $pick_money ) {
        $this->pick_money = $pick_money;
        return $this;
    }

    public function setPick( $pick ) {
        $this->pick_name     = $pick->ghtk_name;
        $this->pick_address  = $pick->address;
        $this->pick_province = Cart_Location::cities($pick->city);
        $this->pick_district = Cart_Location::districts($pick->city, $pick->district);
        $this->pick_ward     = Cart_Location::ward($pick->district, $pick->ward);
        $this->pick_tel      = $pick->phone;
        $this->pick_email    = $pick->email;
        return $this;
    }

    //Thông tin giao hàng
    public function setName( $name ) {
        $this->name = $name;
        return $this;
    }

    public function setAddress( $address ) {
        $this->address = $address;
        return $this;
    }

    public function setProvince( $province ) {
        $this->province = $province;
        return $this;
    }

    public function setDistrict( $district ) {
        $this->district = $district;
        return $this;
    }

    public function setWard( $ward ) {
        $this->ward = $ward;
        return $this;
    }

    public function setStreet( $street ) {
        $this->street = $street;
        return $this;
    }

    public function setHamlet( $hamlet ) {
        $this->hamlet = $hamlet;
        return $this;
    }

    public function setTel( $tel ) {
        $this->tel = $tel;
        return $this;
    }

    public function setNote( $note ) {
        $this->note = $note;
        return $this;
    }

    public function setEmail( $email ) {
        $this->email = $email;
        return $this;
    }

    public function setValue( $value ) {
        $this->value = $value;
        return $this;
    }

    //Thông tin điểm trả hàng
    //Các thông tin thêm
    public function setIsFreeship( $is_freeship ) {
        $this->is_freeship = $is_freeship;
        return $this;
    }

    public function setTotalWeight( $total_weight ) {
        $this->total_weight = $total_weight;
        return $this;
    }

    public function setProducts( $products ) {

        // Kiểm tra xem item được thêm vào là một hay một
        // mảng các item. Nếu chỉ là 1 item, thì chúng ta sẽ
        // cho nó thành một mảng item rồi foreach. Việc này giúp
        // chúng ta có thể thêm một hay nhiều item cùng lúc
        if (count($products) === count($products, COUNT_RECURSIVE)) {
            $products = [$products];
        }

        $total_weight = 0;

        // Duyệt danh sách các item
        foreach ($products as $data) {
            // Thêm item vào danh sách
            $data['weight'] = $data['weight']/1000;

            $this->products[] = $data;

            $total_weight += $data['weight']*$data['quantity'];
        }

        $this->setTotalWeight($total_weight);

        return $this;
    }

    public function setTransport( $transport ) {
        $this->transport = $transport;
        return $this;
    }

    public function push_data() {

        $data = array();

        $data['products'] = $this->products;
        
        $data['order'] = [
            'id'            => $this->id,
            'pick_name'     => $this->pick_name,
            'pick_address'  => $this->pick_address,
            'pick_province' => $this->pick_province,
            'pick_district' => $this->pick_district,
            'pick_ward'     => $this->pick_ward,
            'pick_tel'      => $this->pick_tel,
            'weight'        => $this->total_weight,
            'tel'           => $this->tel,
            'name'          => $this->name,
            'address'       => $this->address,
            'province'      => Cart_Location::cities($this->province),
            'district'      => Cart_Location::districts($this->province, $this->district),
            'ward'          => Cart_Location::ward($this->district, $this->ward),
            'hamlet'        => $this->hamlet,
            'is_freeship'   => $this->is_freeship,
            'pick_money'    => $this->pick_money,
            'note'          => $this->note,
            'value'         => $this->value,
            'transport'     => $this->transport,
        ];

        return $data;
    }
}
