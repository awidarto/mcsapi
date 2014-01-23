<?php

class Test extends Application
{

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



}