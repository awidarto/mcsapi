<?php

class Test extends Application
{
    public $ordermap = array(
        'ordertime'=>'',
        'buyerdeliveryzone'=>'',
        'buyerdeliverycity'=>'',
        'buyerdeliveryslot'=>1,
        'buyerdeliverytime'=>1,
        'assigntime'=>'',
        'timeslot'=>1,
        'assignment_zone'=>'',
        'assignment_city'=>'',
        'assignment_seq'=>'',
        'delivery_id'=>'',
        'delivery_cost'=>'',
        'cod_cost'=>'',
        'width'=>'',
        'height'=>'',
        'length'=>'',
        'weight'=>'',
        'actual_weight'=>'',
        'delivery_type'=>'',
        'currency'=>'IDR',
        'total_price'=>'',
        //'fixed_discount'=>'',
        //'total_discount'=>'',
        //'total_tax'=>'',
        'chargeable_amount'=>'',
        'delivery_bearer'=>'',
        'cod_bearer'=>'',
        'cod_method'=>'',
        'ccod_method'=>'',
        'application_id'=>'',
        'application_key'=>'',
        'buyer_id'=>'',
        'merchant_id'=>'',
        'merchant_trans_id'=>'',
        //'courier_id'=>'',
        //'device_id'=>'',
        'buyer_name'=>'',
        'email'=>'',
        'recipient_name'=>'',
        'shipping_address'=>'',
        'shipping_zip'=>'',
        'directions'=>'',
        //'dir_lat'=>'',
        //'dir_lon'=>'',
        'phone'=>'',
        'mobile1'=>'',
        'mobile2'=>'',
        'status'=>'pending',
        'laststatus'=>'pending',
        //'change_actor'=>'',
        //'actor_history'=>'',
        'delivery_note'=>'',
        //'reciever_name'=>'',
        //'reciever_picture'=>'',
        //'undersign'=>'',
        //'latitude'=>'',
        //'longitude'=>'',
        //'reschedule_ref'=>'',
        //'revoke_ref'=>'',
        //'reattemp'=>'',
        //'show_merchant'=>'',
        //'show_shop'=>'',
        'is_pickup'=>1,
        'is_import'=>0
    );

    public function __construct()
    {
        parent::__construct();
        //$this->ag_auth->restrict('admin'); // restrict this controller to admins only
        date_default_timezone_set('Asia/Jakarta');

        $this->accessor_ip = $_SERVER['REMOTE_ADDR'];
    }

    public function __destruct()
    {
        $this->db->close();
    }

    public function pickup($filename){

        $pu_dir = FCPATH.'json/pickup/';

        $in = file_get_contents($pu_dir.$filename.'.json');

        $dt = json_decode($in, true);

        $orders = json_decode($dt['orders'], true);

        foreach($orders as $k){

    /*
    1390521600
    1390462727801000
    [create_time] => 1390462727801000
    [merchant_id] => 4664
    [app_id] => 72
    [trx_id] => RURN5HKFWFJE
    [delivery_type] => COD
    [buyerdeliveryzone] => Bantar Gebang
    [buyerdeliverycity] => Bekasi Kota
    [weight] => 0 kg - 1 kg
    [actual_weight] => 1
    [unit_price] => 250000
    [deliverycost] => 6500
    [codsurcharge] => 7500
    [pic_address] =>
    [pic_1] =>
    [pic_2] =>
    [pic_3] =>
    */
            $order = $this->ordermap;

            foreach($k as $key=>$val){
                if( isset($order[$key]) ){
                    $order[$key] = $val;
                }
            }

            $order['ordertime'] = date( 'Y-m-d H:i:s', ($k['create_time'] / 1000000) ) ;
            $order['merchant_trans_id'] = $k['trx_id'] ;


            print_r($k);

            print_r($order);

            if(isset( $k['pic_address_body'] )){
                file_put_contents($pu_dir.$k['pic_address'], base64_decode( $k['pic_address_body']) );
            }
            if(isset( $k['pic_1_body'] )){
                file_put_contents($pu_dir.$k['pic_1'], base64_decode( $k['pic_1_body']) );
            }
            if(isset( $k['pic_2_body'] )){
                file_put_contents($pu_dir.$k['pic_2'], base64_decode( $k['pic_2_body']) );
            }
            if(isset( $k['pic_3_body'] )){
                file_put_contents($pu_dir.$k['pic_3'], base64_decode( $k['pic_3_body']) );
            }
        }

    }

    public function jt(){
        $pu_file = FCPATH.'json/pickup/incoming.json';
        $in = file_get_contents($pu_file);

        //$in = stripslashes($in);

        print $in;

        $data = json_decode($in, true);

        var_dump($data);

        $orders = json_decode($data['orders']);

        var_dump($orders);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                echo ' - No errors';
            break;
            case JSON_ERROR_DEPTH:
                echo ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                echo ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                echo ' - Unknown error';
            break;
        }
    }


}