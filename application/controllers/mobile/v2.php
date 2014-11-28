<?php

require(APPPATH.'libraries/REST_Controller.php');

class V2 extends REST_Controller {

    public $pu_order_map = array(
        'ordertime'=>'',
        'buyerdeliveryzone'=>'',
        'buyerdeliverycity'=>'',
        'buyerdeliveryslot'=>1,
        'buyerdeliverytime'=>1,
        'assigntime'=>'',
        'assignment_timeslot'=>1,
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

    public function test_post($id = null){

        $body = file_get_contents('php://input');

        //$body = mb_convert_encoding($body, 'UTF-8', 'UCS-2LE');
        //$body = parse_str($body);

        $json = json_decode($body,true);

        //var_dump($json);

        $this->response(array('result'=>$json),200);

    }

    public function pustatus_get()
    {

        $args = '';

        $api_key = $this->get('key');
        $trx_id = $this->get('trx');
        $status = $this->get('status');
        $did = $this->get('did');

        $pu_stat = $this->config->item('pu_status_code');

        if(is_null($api_key)){
            //$this->response(array('status'=>'ERR:NOKEY','timestamp'=>now()),400);
                $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));

        }else if( in_array($status, array_keys($pu_stat)) == false ){
            //$this->response(array('status'=>'ERR:INVALIDSTATUS','timestamp'=>now()),400);
                $result = json_encode(array('status'=>'ERR:INVALIDSTATUS','timestamp'=>now()));

        }else{

            $pu_data = array( 'pickup_status'=>$pu_stat[$status] );

            if(isset($did) && is_null($did) == false && $did != ''){
                $did = base64_decode($did);
                $this->db->where('delivery_id',trim($did))->update($this->config->item('incoming_delivery_table'), $pu_data);
            }else if(isset($trx_id) && is_null($trx_id) == false && $trx_id != ''){
                $trx_id = base64_decode($trx_id);
                $this->db->where('merchant_trans_id',trim($trx_id))->update($this->config->item('incoming_delivery_table'), $pu_data);
            }

            if($this->db->affected_rows() > 0){
                //$this->response(array('status'=>'OK:STATUSUPDATED','timestamp'=>now(),'trx'=>$trx_id ),200);
                $result = json_encode(array('status'=>'OK:STATUSUPDATED','timestamp'=>now(),'trx'=>$trx_id));

            }else{
                //$this->response(array('status'=>'OK:STATUSUPDATEFAILED','timestamp'=>now(),'trx'=>$trx_id),200);
                $result = json_encode(array('status'=>'OK:STATUSUPDATEFAILED','timestamp'=>now(),'trx'=>$trx_id));
            }

        }
        header('Content-Type: application/json');
        print $result;
    }

    public function trxchange_get()
    {

        $args = '';

        $api_key = $this->get('key');
        $trx_id = $this->get('trx');
        $did = $this->get('did');

        if(is_null($api_key)){
            $this->response(array('status'=>'ERR:NOKEY','timestamp'=>now()),400);
        }else{

            if(isset($did) && is_null($did) == false && $did != ''){
                $did = base64_decode($did);
                if(isset($trx_id) && is_null($trx_id) == false && $trx_id != ''){
                    $trx_id = base64_decode($trx_id);
                    $pu_data = array( 'merchant_trans_id'=>$trx_id );
                    $this->db->where('delivery_id',trim($did))->update($this->config->item('incoming_delivery_table'), $pu_data);
                }else{
                    $this->response(array('status'=>'ERR:MISSINGPARAMS','timestamp'=>now(),'trx'=>$trx_id, 'did'=>$did ),200);
                }
            }else{
                $this->response(array('status'=>'ERR:MISSINGPARAMS','timestamp'=>now(),'trx'=>$trx_id, 'did'=>$did ),200);
            }

            if($this->db->affected_rows() > 0){
                //$result = json_encode(array('status'=>'OK:STATUSUPDATED','timestamp'=>now());
                //print $result;
                $this->response(array('status'=>'OK:TRXUPDATED','timestamp'=>now(),'trx'=>$trx_id ),200);
            }else{
                //$result = json_encode(array('status'=>'NOK:TRXUPDATEFAILED','timestamp'=>now()));
                //print $result;
                $this->response(array('status'=>'OK:TRXUPDATEFAILED','timestamp'=>now(),'trx'=>$trx_id),200);
            }

        }


    }

    public function order_post()
    {
        $args = '';

        $api_key = $this->get('key');
        $transaction_id = $this->get('trx');

        if(is_null($api_key)){
            $this->response(array('status'=>'ERR:NOKEY','timestamp'=>now()),400);
        }else{
            $app = $this->get_key_info(trim($api_key));

            if($app == false){
                $this->response(array('status'=>'ERR:INVALIDKEY','timestamp'=>now()),400);
            }else{
                //$in = $this->input->post('transaction_detail');
                $in = file_get_contents('php://input');

                $args = 'p='.$in;

                $in = json_decode($in);

                $is_new = false;

                $in->phone = ( isset( $in->phone ) && $in->phone != '')?normalphone( $in->phone ):'';
                $in->mobile1 = ( isset( $in->mobile1 ) && $in->mobile1 != '' )?normalphone( $in->mobile1 ):'';
                $in->mobile2 = ( isset( $in->mobile2 ) && $in->mobile2 != '' )?normalphone( $in->mobile2 ):'';


                if(isset($in->buyer_id) && $in->buyer_id != '' && $in->buyer_id > 1){

                    $buyer_id = $in->buyer_id;
                    $is_new = false;

                }else{

                    if($in->email == '' || !isset($in->email) || $in->email == 'noemail'){

                        $in->email = 'noemail';
                        $is_new = true;
                        if( trim($in->phone.$in->mobile1.$in->mobile2) != ''){
                            if($buyer = $this->check_phone($in->phone,$in->mobile1,$in->mobile2)){
                                $buyer_id = $buyer['id'];
                                $is_new = false;
                            }
                        }

                    }else if($buyer = $this->check_email($in->email)){

                        $buyer_id = $buyer['id'];
                        $is_new = false;

                    }else if($buyer = $this->check_phone($in->phone,$in->mobile1,$in->mobile2)){

                        $buyer_id = $buyer['id'];
                        $is_new = false;

                    }

                }

                if(isset($in->transaction_id) && $in->transaction_id != ""){
                    $transaction_id = $in->transaction_id;
                }


                if($is_new){
                    $buyer_username = substr(strtolower(str_replace(' ','',$in->buyer_name)),0,6).random_string('numeric', 4);
                    $dataset['username'] = $buyer_username;
                    $dataset['email'] = $in->email;
                    $dataset['phone'] = $in->phone;
                    $dataset['mobile1'] = $in->mobile1;
                    $dataset['mobile2'] = $in->mobile2;
                    $dataset['fullname'] = $in->buyer_name;
                    $password = random_string('alnum', 8);
                    $dataset['password'] = $this->ag_auth->salt($password);
                    $dataset['created'] = date('Y-m-d H:i:s',time());

                    /*
                    $dataset['province'] =
                    $dataset['mobile']
                    */

                    $dataset['street'] = $in->shipping_address;
                    $dataset['district'] = $in->buyerdeliveryzone;
                    $dataset['city'] = $in->buyerdeliverycity;
                    $dataset['country'] = 'Indonesia';
                    $dataset['zip'] = $in->zip;

                    $buyer_id = $this->register_buyer($dataset);
                    $is_new = true;
                }

                $order['created'] = date('Y-m-d H:i:s',time());
                $order['ordertime'] = date('Y-m-d H:i:s',time());
                $order['application_id'] = $app->id;
                $order['application_key'] = $app->key;
                $order['buyer_id'] = $buyer_id;
                $order['merchant_id'] = $app->merchant_id;
                $order['merchant_trans_id'] = trim($transaction_id);

                $order['buyer_name'] = $in->buyer_name;
                $order['recipient_name'] = $in->recipient_name;
                $order['email'] = $in->email;
                $order['directions'] = $in->directions;
                //$order['dir_lat'] = $in->dir_lat;
                //$order['dir_lon'] = $in->dir_lon;
                $order['buyerdeliverytime'] = $in->buyerdeliverytime;
                $order['buyerdeliveryslot'] = $in->buyerdeliveryslot;
                $order['buyerdeliveryzone'] = $in->buyerdeliveryzone;
                $order['buyerdeliverycity'] = (is_null($in->buyerdeliverycity) || $in->buyerdeliverycity == '')?'Jakarta':$in->buyerdeliverycity;

                $order['currency'] = $in->currency;
                $order['total_price'] = (isset($in->total_price))?$in->total_price:0;
                $order['total_discount'] = (isset($in->total_discount))?$in->total_discount:0;
                $order['total_tax'] = (isset($in->total_tax))?$in->total_tax:0;
                $order['cod_cost'] = $in->cod_cost;
                $order['chargeable_amount'] = (isset($in->chargeable_amount))?$in->chargeable_amount:0;

                $order['shipping_address'] = $in->shipping_address;
                $order['shipping_zip'] = $in->zip;
                $order['phone'] = $in->phone;
                $order['mobile1'] = $in->mobile1;
                $order['mobile2'] = $in->mobile2;
                $order['status'] = $in->status;

                $order['width'] = $in->width;
                $order['height'] = $in->height;
                $order['length'] = $in->length;
                $order['weight'] = (isset($in->weight))?$in->weight:0;
                $order['delivery_type'] = $in->delivery_type;
                $order['delivery_cost'] = (isset($in->delivery_cost))?$in->delivery_cost:0;

                $order['cod_bearer'] = (isset($in->cod_bearer))?$in->cod_bearer:'merchant';
                $order['delivery_bearer'] = (isset($in->delivery_bearer))?$in->delivery_bearer:'merchant';

                $order['cod_method'] = (isset($in->cod_method))?$in->cod_method:'cash';
                $order['ccod_method'] = (isset($in->ccod_method))?$in->ccod_method:'full';

                if(isset($in->show_shop)){
                    $order['show_shop'] = $in->show_shop;
                }

                if(isset($in->show_merchant)){
                    $order['show_merchant'] = $in->show_merchant;
                }


                if(isset($in->delivery_id) && $in->delivery_id != ""){
                    $delivery_id = $in->delivery_id;
                    $this->db->where('delivery_id',$delivery_id)->update($this->config->item('incoming_delivery_table'),$order);
                }else{
                    $inres = $this->db->insert($this->config->item('incoming_delivery_table'),$order);
                    $sequence = $this->db->insert_id();
                    $delivery_id = get_delivery_id($sequence,$app->merchant_id);
                }

                $nedata['fullname'] = $in->buyer_name;
                $nedata['merchant_trx_id'] = trim($transaction_id);
                $nedata['delivery_id'] = $delivery_id;
                $nedata['merchantname'] = $app->application_name;
                $nedata['app'] = $app;

                $this->db->where('id',$sequence)->update($this->config->item('incoming_delivery_table'),array('delivery_id'=>$delivery_id));

                    $this->table_tpl = array(
                        'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="dataTable">'
                    );
                    $this->table->set_template($this->table_tpl);


                    $this->table->set_heading(
                        'No.',
                        'Description',
                        'Quantity',
                        'Total'
                        ); // Setting headings for the table

                    $d = 0;
                    $gt = 0;


                if($in->trx_detail){
                    $seq = 0;

                    foreach($in->trx_detail as $it){
                        $item['ordertime'] = $order['ordertime'];
                        $item['delivery_id'] = $delivery_id;
                        $item['unit_sequence'] = $seq;
                        $item['unit_description'] = $it->unit_description;
                        $item['unit_price'] = $it->unit_price;
                        $item['unit_quantity'] = $it->unit_quantity;
                        $item['unit_total'] = $it->unit_total;
                        $item['unit_discount'] = $it->unit_discount;

                        $rs = $this->db->insert($this->config->item('delivery_details_table'),$item);

                        $this->table->add_row(
                            (int)$item['unit_sequence'] + 1,
                            $item['unit_description'],
                            $item['unit_quantity'],
                            $item['unit_total']
                        );

                        $u_total = str_replace(array(',','.'), '', $item['unit_total']);
                        $u_discount = str_replace(array(',','.'), '', $item['unit_discount']);
                        $gt += (int)$u_total;
                        $d += (int)$u_discount;

                        $seq++;
                    }

                    $total = (isset($in->total_price) && $in->total_price > 0)?$in->total_price:0;
                    $total = str_replace(array(',','.'), '', $total);
                    $total = (int)$total;
                    $gt = ($total < $gt)?$gt:$total;

                    $disc = (isset($in->total_discount))?$in->total_discount:0;
                    $tax = (isset($in->total_tax))?$in->total_tax:0;
                    $cod = (isset($in->cod_cost))?$in->cod_cost:'Paid by merchant';

                    $disc = str_replace(array(',','.'), '', $disc);
                    $tax = str_replace(array(',','.'), '',$tax);
                    $cod = str_replace(array(',','.'), '',$cod);

                    $disc = (int)$disc;
                    $tax = (int)$tax;
                    $cod = (int)$cod;

                    $chg = ($gt - $disc) + $tax + $cod;

                    $this->table->add_row(
                        '',
                        '',
                        'Total Price',
                        number_format($gt,2,',','.')
                    );

                    $this->table->add_row(
                        '',
                        '',
                        'Total Discount',
                        number_format($disc,2,',','.')
                    );

                    $this->table->add_row(
                        '',
                        '',
                        'Total Tax',
                        number_format($tax,2,',','.')
                    );


                    if($cod == 0){
                        $this->table->add_row(
                            '',
                            '',
                            'COD Charges',
                            'Paid by Merchant'
                        );
                    }else{
                        $this->table->add_row(
                            '',
                            '',
                            'COD Charges',
                            number_format($cod,2,',','.')
                        );
                    }


                    $this->table->add_row(
                        '',
                        '',
                        'Total Charges',
                        number_format($chg,2,',','.')
                    );

                    $nedata['detail'] = $this->table;

                    $result = json_encode(array('status'=>'OK:ORDERPOSTED','timestamp'=>now(),'delivery_id'=>$delivery_id,'buyer_id'=>$buyer_id));

                    print $result;
                }else{
                    $nedata['detail'] = false;

                    $result = json_encode(array('status'=>'OK:ORDERPOSTEDNODETAIL','timestamp'=>now(),'delivery_id'=>$delivery_id));

                    print $result;
                }

                //print_r($app);

                if($app->notify_on_new_order == 1){
                    send_notification('New Delivery Order - Jayon Express COD Service',$in->email,$app->cc_to,$app->reply_to,'order_submit',$nedata,null);
                }

                if($is_new == true){
                    $edata['fullname'] = $dataset['fullname'];
                    $edata['username'] = $buyer_username;
                    $edata['password'] = $password;
                    if($app->notify_on_new_member == 1 && $in->email != 'noemail'){
                        send_notification('New Member Registration - Jayon Express COD Service',$in->email,null,null,'new_member',$edata,null);
                    }

                }

            }
        }

        $this->log_access($api_key, __METHOD__ ,$result,$args);
    }

    public function order_get(){
        $args = '';

        $api_key = $this->get('key');
        $mobile_type = $this->get('type');
        $date = $this->get('date');

        if(is_null($api_key)){
            $this->response(array('status'=>'ERR:NOKEY','timestamp'=>now()),400);
        }else{


            if(isset($date)){
                $datestring = $date;
                $date = date_parse($date);
                if ($date['error_count'] == 0 && checkdate($date['month'], $date['day'], $date['year'])){
                    $onehourbefore = strtotime($datestring) - 60 * 60;
                }else{
                    $onehourbefore = time() - 60*60;
                }
            }

            if($type == 'pickup'){
                $orders = $this->db->where('toscan', 1)
                    ->where('created < ', date('Y-m-d H:i:s', $onehourbefore) )
                    ->where('created > ', date('Y-m-d 00:00:00', $onehourbefore) )
                    ->get( $this->config->item('incoming_delivery_table'));
            }else{

            }
        }

        $this->response(array('message'=>'Not Implemented'),400);
    }

    public function order_put(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    public function order_delete(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    /* Merchant end point */

    public function merchant_get()
    {
        $api_key = $this->get('key');
        $group_id = user_group_id('merchant');
        $last = $this->get('last');

        //print $last;

        if($last == 0 || $last == '' || is_null($last)){
            $last_created = date('Y-m-d H:i:s',0);
            $last_update = now();
        }else{
            $last_created = date('Y-m-d H:i:s',$last);
            $last_update = $last;
        }


        //print $last_created;

        if(is_null($api_key) || !isset($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            $this->response(array('status'=>'ERR:NOKEY','timestamp'=>now()),400);
        }else{
                /*
                // get new
                $new_merchants = $this->db
                    ->select(   'id,
                                street,
                                district,
                                province,
                                city,
                                country,
                                zip,
                                phone,
                                mobile,
                                mobile1,
                                mobile2,
                                merchantname,
                                mc_email,
                                mc_street,
                                mc_district,
                                mc_city,
                                mc_province,
                                mc_country,
                                mc_zip,
                                mc_phone,
                                mc_mobile')
                    ->from($this->config->item('jayon_members_table'))
                    ->where('group_id',$group_id)
                    ->where('created > ',$last_created)
                    ->where('updated > ',$last_update)
                    ->order_by('created','desc')
                    ->get();

                    //print $this->db->last_query();


                $updated_merchants = $this->db
                    ->select(   'id,
                                street,
                                district,
                                province,
                                city,
                                country,
                                zip,
                                phone,
                                mobile,
                                mobile1,
                                mobile2,
                                merchantname,
                                mc_email,
                                mc_street,
                                mc_district,
                                mc_city,
                                mc_province,
                                mc_country,
                                mc_zip,
                                mc_phone,
                                mc_mobile')
                    ->from($this->config->item('jayon_members_table'))
                    ->where('group_id',$group_id)
                    ->where('created < ',$last_created)
                    ->where('updated > ',$last_update)
                    ->order_by('created','desc')
                    ->get();

                $new_merchants = $new_merchants->result_array();
                $updated_merchants = $updated_merchants->result_array();

                $data = array('new'=>$new_merchants, 'updated'=>$updated_merchants);

                */

                $merchants = $this->db
                    ->select(   'id,
                                street,
                                district,
                                province,
                                city,
                                country,
                                zip,
                                phone,
                                mobile,
                                mobile1,
                                mobile2,
                                merchantname,
                                mc_email,
                                mc_street,
                                mc_district,
                                mc_city,
                                mc_province,
                                mc_country,
                                mc_zip,
                                mc_phone,
                                mc_mobile')
                    ->from($this->config->item('jayon_members_table'))
                    ->where('group_id',$group_id)
                    ->order_by('created','desc')
                    ->get()
                    ->result_array();





                $apps = $this->db
                    ->select(   'id,
                                merchant_id,
                                domain,
                                application_name,
                                key')

                    ->from($this->config->item('applications_table'))
                    ->order_by('created','desc')
                    ->get()
                    ->result_array();

                $cod = $this->db
                    ->select(   'id,
                                from_price,
                                to_price,
                                surcharge,
                                app_id')

                    ->from($this->config->item('jayon_cod_fee_table'))
                    ->get()
                    ->result_array();

                $do = $this->db
                    ->select(   'id,
                                kg_from,
                                kg_to,
                                calculated_kg,
                                tariff_kg,
                                total,
                                app_id')

                    ->from($this->config->item('jayon_delivery_fee_table'))
                    ->get()
                    ->result_array();

                $ps = $this->db
                    ->select(   'id,
                                kg_from,
                                kg_to,
                                calculated_kg,
                                tariff_kg,
                                total,
                                app_id')

                    ->from($this->config->item('jayon_pickup_fee_table'))
                    ->get()
                    ->result_array();

                $data = array(
                    'merchants'=>$merchants,
                    'apps'=>$apps,
                    'cod'=>$cod,
                    'do'=>$do,
                    'ps'=>$ps
                    );

            $this->response(array('status'=>'OK','data'=>$data,'timestamp'=>now()),200);
        }
    }

    public function merchant_post(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    public function merchant_put(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    public function merchant_delete(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    /* Zones */

    public function zone_get(){

        $city = $this->db
            ->distinct('city')
            ->select('city')
            ->from($this->config->item('jayon_zones_table'))
            ->get()
            ->result_array();

        $cities = array();
        foreach($city as $c){
            $cities[]['name'] = $c['city'];
        }


        $district = $this->db
            ->select('district,
                        city,
                        province,
                        country,
                        is_on')
            ->from($this->config->item('jayon_zones_table'))
            ->get()
            ->result_array();

        $data = array(
            'city'=>$cities,
            'district'=>$district
            );

        $this->response(array('status'=>'OK','data'=>$data,'timestamp'=>now()),200);
    }

    public function zone_post(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    public function zone_put(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    public function zone_delete(){
        $this->response(array('message'=>'Not Implemented'),400);
    }

    /* Synchronize mobile device */
    public function report_post(){
        $api_key = $this->get('key');

        if(is_null($api_key)){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{

            //sync steps :
            //post stored data from device local db
            //retrieve relevant data for next delivery assignment

            //sync in

            //sync out
            if($dev = $this->get_dev_info($api_key)){

                $in = file_get_contents('php://input');

                if($in){


                    $args = 'p='.$in;

                    $in = json_decode($in);

                    //$in = json_decode($_POST['trx']);

                    //file_put_contents('log_data.txt',print_r($in));

                    //set status based on reported

                    //$out = $orders->result_array();

                    foreach($in as $key=>$val){

                        $data = array(
                            'timestamp'=>date('Y-m-d H:i:s',strtotime($val->capture_time)),
                            'report_timestamp'=>date('Y-m-d H:i:s',time()),
                            'delivery_id'=>$val->delivery_id,
                            'device_id'=>$dev->id,
                            'courier_id'=>'',
                            'actor_type'=>'MB',
                            'actor_id'=>$dev->id,
                            'latitude'=>$val->latitude,
                            'longitude'=>$val->longitude,
                            'status'=>$val->status,
                            'api_event'=>'sync_report',
                            'notes'=>$val->delivery_note,
                            'sync_id'=>$val->sync_id
                        );
                        delivery_log($data,true);
                    }

                    //get slot for specified date
                    $result = json_encode(array('status'=>'OK:LOGSYNC','timestamp'=>now()));
                    print $result;
                }else{
                    $result = json_encode(array('status'=>'ERR:NODATA','timestamp'=>now()));
                    print $result;
                }
            }else{
                $result = json_encode(array('status'=>'NOK:DEVICENOTFOUND','timestamp'=>now()));
                print $result;
            }
        }

        $this->log_access($api_key, __METHOD__ ,$result);
    }

    public function data_get($api_key = null,$indate = null){

        $key = $this->get('key');
        $indate = $this->get('date');

        if(is_null($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{

            if($dev = $this->get_dev_info($api_key)){

                $indate = (is_null($indate))?date('Y-m-d',time()):$indate;

                $orders = $this->db
                    ->select('d.delivery_id as delivery_id,
                            d.assignment_date as as_date,
                            d.assignment_timeslot as as_timeslot,
                            d.assignment_zone as as_zone,
                            d.assignment_city as as_city,
                            m.merchantname as mc_name,
                            m.street as mc_street,
                            m.district as mc_district,
                            m.province as mc_province,
                            m.city as mc_city,
                            d.merchant_trans_id as mc_trans_id,
                            d.buyerdeliverytime as by_time,
                            d.buyerdeliveryzone as by_zone,
                            d.buyerdeliverycity as by_city,
                            d.buyer_name as by_name,
                            d.email as by_email,
                            d.phone as by_phone,
                            d.recipient_name as rec_name,
                            d.undersign as rec_sign,
                            d.total_price as tot_price,
                            d.total_discount as tot_disc,
                            d.total_tax as tot_tax,
                            d.chargeable_amount as chg_amt,
                            d.cod_cost as cod_cost,
                            d.currency as cod_curr,
                            d.shipping_address as ship_addr,
                            d.directions as ship_dir,
                            d.dir_lat as ship_lat,
                            d.dir_lon as ship_lon,
                            d.deliverytime as dl_time,
                            d.status as dl_status,
                            d.delivery_note as dl_note,
                            d.latitude as dl_lat,
                            d.longitude as dl_lon,
                            d.reschedule_ref as res_ref,
                            d.revoke_ref as rev_ref')
                    ->from($this->config->item('assigned_delivery_table').' as d')
                    ->join('members as m','d.merchant_id=m.id','left')
                    ->where('status',$this->config->item('trans_status_admin_courierassigned'))
                    ->where('assignment_date',$indate)
                    ->where('device_id',$dev->id)
                    ->get();

                    //print $this->db->last_query();

                $out = $orders->result_array();

                //print_r($out);

                $output = array();

                foreach($out as $o){
                    $details = $this->db->where('delivery_id',$o['delivery_id'])->order_by('unit_sequence','asc')->get($this->config->item('delivery_details_table'));

                    $details = $details->result_array();

                    $d = 0;
                    $gt = 0;

                    foreach($details as $value => $key)
                    {

                        $u_total = str_replace(array(',','.'), '', $key['unit_total']);
                        $u_discount = str_replace(array(',','.'), '', $key['unit_discount']);
                        $gt += (int)$u_total;
                        $d += (int)$u_discount;
                    }


                    $total = str_replace(array(',','.'), '', $o['tot_price']);
                    $total = (int)$total;
                    $gt = ($total < $gt)?$gt:$total;
                    $dsc = str_replace(array(',','.'), '', $o['tot_disc']);
                    $tax = str_replace(array(',','.'), '',$o['tot_tax']);
                    $cod = str_replace(array(',','.'), '',$o['cod_cost']);

                    $dsc = (int)$dsc;
                    $tax = (int)$tax;
                    $cod = (int)$cod;

                    $chg = ($gt - $dsc) + $tax + $cod;

                    //$o['tot_price'] =>
                    //$o['tot_disc'] =>
                    //$o['tot_tax'] =>
                    //$o['chg_amt'] =>
                    $o['cod_cost'] = number_format($chg,2,',','.');
                    $output[] = $o;
                }

                $data = array(
                    'timestamp'=>date('Y-m-d H:i:s',time()),
                    'report_timestamp'=>date('Y-m-d H:i:s',time()),
                    'delivery_id'=>'',
                    'device_id'=>$dev->id,
                    'actor_type'=>'MB',
                    'actor_id'=>$dev->id,
                    'status'=>'sync_data'
                );

                delivery_log($data);

                //get slot for specified date
                $result = json_encode(array('status'=>'OK:DEVSYNC','data'=>$output ,'timestamp'=>now()));
                print $result;
            }else{
                $result = json_encode(array('status'=>'NOK:DEVICENOTFOUND','timestamp'=>now()));
                print $result;
            }
        }

        $this->log_access($api_key, __METHOD__ ,$result);
    }

    public function mobkey_post(){

        $api_key = $this->get('key');

        if(is_null($api_key) || $key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{
            if($api_key == $this->config->item('master_key')){

                $in = file_get_contents('php://input');

                if($in != ''){

                    $args = 'p='.$in;

                    $in = json_decode($in);

                    if($this->admin_auth($in->user,$in->pass)){


                        //file_put_contents('posted_status.txt', $_POST['trx'] );


                        if($dev = $this->get_dev_info_by_id($in->identifier)){

                            $data = array(
                                'timestamp'=>date('Y-m-d H:i:s',time()),
                                'report_timestamp'=>date('Y-m-d H:i:s',time()),
                                'delivery_id'=>'',
                                'device_id'=>$dev->id,
                                'courier_id'=>'',
                                'actor_type'=>'MB',
                                'actor_id'=>'',
                                //'latitude'=>$in->lat,
                                //'longitude'=>$in->lon,
                                'status'=>$this->config->item('trans_status_mobile_keyrequest'),
                                //'notes'=>$in->notes
                            );

                            delivery_log($data);
                            $result = json_encode(array('status'=>'OK:NEWKEY',
                                'keydata' => $dev->key,
                                'identifier'=>$in->identifier,
                                'timestamp'=>now()));
                            print $result;
                        }else{
                            $result = json_encode(array('status'=>'NOK:DEVICENOTFOUND','timestamp'=>now()));
                            print $result;
                        }

                    }else{
                        //full calendar time series for current month
                        $result = json_encode(array('status'=>'NOK:AUTHFAILED','timestamp'=>now()));
                        print $result;
                    }


                }else{
                    $result = json_encode(array('status'=>'NOK:NODATASENT','timestamp'=>now()));
                    print $result;
                }

            }else{
                $result = json_encode(array('status'=>'NOK:INVALIDKEY','timestamp'=>now()));
                print $result;
            }

        }

        $this->log_access($api_key, __METHOD__ ,$result);
    }

    public function pickup_post(){

        $in = file_get_contents('php://input');

        $filename = random_string('alnum', 8);

        $pu_dir = FCPATH.'json/pickup/';

        $pu_pic_dir = FCPATH.'public/pickup/';

        $dt = json_decode($in, true);

        $pickup_person = (isset($dt['courier_name']))?$dt['courier_name']:'no name';
        $pickup_device = (isset($dt['dev_id']))?$dt['dev_id']:'JY-PICKUPDEV';

        $orders = json_decode($dt['orders'], true);

        file_put_contents( $pu_dir.'incoming.json' , $in);

        $sorders = array();

        foreach($orders as $k){

            //print_r($k);

            $orderitem = $this->pu_order_map;

            $app = $this->get_key_info_id($k['app_id']);

            $orderitem['created'] = date('Y-m-d H:i:s',time());
            //$orderitem['ordertime'] = date( 'Y-m-d H:i:s', ($k['create_time'] / 1000000) ) ;
            $orderitem['ordertime'] = $k['create_datetime'];
            $orderitem['application_id'] = $app->id;
            $orderitem['application_key'] = $app->key;
            $orderitem['merchant_trans_id'] = $k['trx_id'] ;
            $orderitem['merchant_id'] = $k['merchant_id'] ;
            $orderitem['delivery_type'] = $k['delivery_type'];
            $orderitem['actual_weight'] = $k['actual_weight'];
            $orderitem['weight'] = $k['weight'];
            $orderitem['delivery_cost'] = $k['deliverycost'];
            $orderitem['buyerdeliverycity'] = $k['buyerdeliverycity'];
            $orderitem['buyerdeliveryzone'] = $k['buyerdeliveryzone'];
            $orderitem['recipient_name'] = (isset($k['recipient_name']))?$k['recipient_name']:'';
            $orderitem['buyer_name'] = (isset($k['buyer_name']))?$k['buyer_name']:'';

            $orderitem['cod_cost'] = $k['codsurcharge'];
            $orderitem['total_price'] = $k['unit_price'];

            $orderitem['pic_address'] = (isset($k['pic_address']))?$k['pic_address']:'';
            $orderitem['pic_1'] = (isset($k['pic_1']))?$k['pic_1']:'';
            $orderitem['pic_2'] = (isset($k['pic_2']))?$k['pic_2']:'';
            $orderitem['pic_3'] = (isset($k['pic_3']))?$k['pic_3']:'';

            $orderitem['pickup_dev_id'] = $pickup_device;
            $orderitem['pickup_person'] = $pickup_person;
            if( isset($k['pickup_status']) &&  $k['pickup_status'] != '' ){
                $orderitem['pickup_status'] = $k['pickup_status'];
            }else{
                $orderitem['pickup_status'] = $this->config->item('trans_status_pickup');
            }


            $orderitem['is_pickup'] = 1;

            $sorders[] = $k['trx_id'];

            //print_r($orderitem);

            $use_did = false;

            if( isset($k['delivery_id']) &&  $k['delivery_id'] != '' ){

                $item = array('pickup_status'=>$k['pickup_status']);
                $rs = $this->db->where('delivery_id', $k['delivery_id'])->update($this->config->item('incoming_delivery_table'),$item);

                $use_did = true;

            }

            if($use_did == false){

                if($this->record_exists($this->config->item('incoming_delivery_table'), 'merchant_trans_id' , $k['trx_id']) == false){


                    $inres = $this->db->insert($this->config->item('incoming_delivery_table'),$orderitem);
                    $sequence = $this->db->insert_id();
                    $delivery_id = get_delivery_id($sequence,$app->merchant_id);

                    $this->db->where('id',$sequence)->update($this->config->item('incoming_delivery_table'),array('delivery_id'=>$delivery_id));

                    $item = array();

                    $item['ordertime'] = $orderitem['ordertime'];
                    $item['delivery_id'] = $delivery_id;
                    $item['unit_sequence'] = 1;
                    $item['unit_description'] = '[PU] '.$k['recipient_name'];
                    $item['unit_price'] = $k['unit_price'];
                    $item['unit_quantity'] = 1;

                    $item['unit_total'] = $item['unit_quantity'] * $k['unit_price'];

                    $rs = $this->db->insert($this->config->item('delivery_details_table'),$item);
                }else{
                    $item = array('pickup_status'=>$k['pickup_status']);
                    $rs = $this->db->where('merchant_trans_id', $k['trx_id'])->update($this->config->item('incoming_delivery_table'),$item);
                }

            }

            file_put_contents( $pu_dir.$k['trx_id'].'.json' , json_encode($k));

            if(isset( $k['pic_address_body'] )){
                file_put_contents($pu_pic_dir.$k['pic_address'], base64_decode( $k['pic_address_body']) );
            }
            if(isset( $k['pic_1_body'] )){
                file_put_contents($pu_pic_dir.$k['pic_1'], base64_decode( $k['pic_1_body']) );
            }
            if(isset( $k['pic_2_body'] )){
                file_put_contents($pu_pic_dir.$k['pic_2'], base64_decode( $k['pic_2_body']) );
            }
            if(isset( $k['pic_3_body'] )){
                file_put_contents($pu_pic_dir.$k['pic_3'], base64_decode( $k['pic_3_body']) );
            }
        }


        $result = json_encode(array('status'=>'OK:DATASENT','orders'=>$sorders,'timestamp'=>now()));
        header('Content-Type: application/json');
        print $result;
    }

    public function pickup_get(){
        $api_key = $this->get('key');
        $device = $this->get('did');
        $merchant = $this->get('mid');
        $date = $this->get('date');

        if(is_null($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{
            /*
            if(is_null($device) || $device == ''){
                $result = json_encode(array('status'=>'ERR:NODEVID','timestamp'=>now()));
                print $result;
            }else{
            */
                $orders = $this->db
                    //->where('toscan',1)
                    //->where('pickup_dev_id',$device)
                    //->where('pickup_status',$this->config->item('trans_status_tobepickup'))
                    ->where('merchant_id',$merchant)
                    ->and_()
                    ->group_start()
                            ->like('ordertime', $date, 'after' )
                            ->or_like('pickuptime', $date, 'after' )
                        ->or_()
                        ->group_start()
                            ->where('status = ',$this->config->item('trans_status_confirmed'))
                            ->where('pickup_status = ',$this->config->item('trans_status_tobepickup'))
                        ->group_end()
                    ->group_end()
                    ->and_()
                        ->group_start()
                            ->where('status != ',$this->config->item('trans_status_canceled'))
                            ->where('pickup_status != ',$this->config->item('trans_status_canceled'))
                        ->group_end()
                    ->get($this->config->item('incoming_delivery_table') )->result_array();

                    //print $this->db->last_query();

                for($i = 0; $i < count($orders);$i++){
                    $orders[$i]['actual_weight'] = (is_null($orders[$i]['actual_weight']))?0:$orders[$i]['actual_weight'];
                    $orders[$i]['total_price'] = (is_null($orders[$i]['total_price']))?0:(double)$orders[$i]['total_price'];
                    $orders[$i]['cod_cost'] = (is_null($orders[$i]['cod_cost']))?0:(double)$orders[$i]['cod_cost'];
                    $orders[$i]['delivery_cost'] = (is_null($orders[$i]['delivery_cost']))?0:(double)$orders[$i]['delivery_cost'];
                    $orders[$i]['chargeable_amount'] = (is_null($orders[$i]['chargeable_amount']))?0:(double)$orders[$i]['chargeable_amount'];
                    $orders[$i]['total_tax'] = (is_null($orders[$i]['total_tax']))?0:(double)$orders[$i]['total_tax'];
                    $orders[$i]['total_discount'] = (is_null($orders[$i]['total_discount']))?0:(double)$orders[$i]['total_discount'];
                }

                $result = json_encode(array('status'=>'OK:DATASENT','orders'=>$orders,'timestamp'=>now()));
                print $result;
            //}
        }


    }

    public function uploadpic_post(){

        $api_key = $this->get('key');

        if(is_null($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{
            $delivery_id = $this->input->post('delivery_id');

            $target_path = $this->config->item('picture_path').$delivery_id.'.jpg';

            if(move_uploaded_file($_FILES['receiverpic']['tmp_name'], $target_path)) {

                $config['image_library'] = 'gd2';
                $config['source_image'] = $target_path;
                $config['new_image'] = $this->config->item('thumbnail_path').'th_'.$delivery_id.'.jpg';
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = TRUE;
                $config['width']     = 100;
                $config['height']   = 75;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();

                $result = json_encode(array('status'=>'OK:PICUPLOAD','timestamp'=>now()));
                print $result;
            } else{
                $result = json_encode(array('status'=>'ERR:UPLOADFAILED','timestamp'=>now()));
                print $result;
            }
        }
    }

    public function pickuppic_post(){

        $api_key = $this->get('key');

        if(is_null($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{
            $delivery_id = $this->input->post('delivery_id');

            $target_path = $this->config->item('picture_path').$delivery_id.'.jpg';

            if(move_uploaded_file($_FILES['receiverpic']['tmp_name'], $target_path)) {

                $config['image_library'] = 'gd2';
                $config['source_image'] = $target_path;
                $config['new_image'] = $this->config->item('thumbnail_path').'th_'.$delivery_id.'.jpg';
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = TRUE;
                $config['width']     = 100;
                $config['height']   = 75;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();

                $result = json_encode(array('status'=>'OK:PICUPLOAD','timestamp'=>now()));
                print $result;
            } else{
                $result = json_encode(array('status'=>'ERR:UPLOADFAILED','timestamp'=>now()));
                print $result;
            }
        }
    }

    public function sign_post(){

        $api_key = $this->get('key');

        if(is_null($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{
            $delivery_id = $this->input->post('delivery_id');

            $imagefilename = $this->post('imagefilename');
            $imagetimestamp = $this->post('imagetimestamp');

            $target_path = $this->config->item('pusign_path').$imagefilename;

            $s = explode('_', str_replace('.jpg','', $imagefilename));

            $sign = array();
            $sign['merchant_id'] = $s[0];
            $sign['application_id'] = $s[1];
            $sign['signature_date'] = $s[2];
            $sign['signature_filename'] = $imagefilename;
            $sign['photo_timestamp'] = $imagetimestamp;

            $this->db->insert('pickup_signatures',$sign);


            if(move_uploaded_file($_FILES['imagefile']['tmp_name'], $target_path)) {
                /*
                $config['image_library'] = 'gd2';
                $config['source_image'] = $target_path;
                $config['new_image'] = $this->config->item('thumbnail_path').'th_'.$delivery_id.'.jpg';
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = TRUE;
                $config['width']     = 100;
                $config['height']   = 75;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();
                */
                $result = json_encode(array('status'=>'OK:PICUPLOAD','timestamp'=>now()));
                print $result;
            } else{
                $result = json_encode(array('status'=>'ERR:UPLOADFAILED','timestamp'=>now()));
                print $result;
            }
        }
    }

    public function pickupphoto_post(){

        $api_key = $this->get('key');

        if(is_null($api_key) || $api_key == ''){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            print $result;
        }else{
            $delivery_id = $this->input->post('delivery_id');

            $imagefilename = $this->post('imagefilename');
            $imagetimestamp = $this->post('imagetimestamp');

            $pu_pic_dir = FCPATH.'public/pickup/';

            $target_path = $pu_pic_dir.$imagefilename;

            $s = explode('_', str_replace('.jpg','', $imagefilename));

            $sign = array();
            $sign['merchant_id'] = $s[0];
            $sign['application_id'] = $s[1];
            $sign['signature_date'] = $s[2];
            $sign['signature_filename'] = $imagefilename;
            $sign['photo_timestamp'] = $imagetimestamp;

            $this->db->insert('pickup_photos',$sign);


            if(move_uploaded_file($_FILES['imagefile']['tmp_name'], $target_path)) {
                /*
                $config['image_library'] = 'gd2';
                $config['source_image'] = $target_path;
                $config['new_image'] = $this->config->item('thumbnail_path').'th_'.$delivery_id.'.jpg';
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = TRUE;
                $config['width']     = 100;
                $config['height']   = 75;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();
                */
                $result = json_encode(array('status'=>'OK:PICUPLOAD','timestamp'=>now()));
                print $result;
            } else{
                $result = json_encode(array('status'=>'ERR:UPLOADFAILED','timestamp'=>now()));
                print $result;
            }
        }
    }

    //private supporting functions

    private function get_key_info($key){
        if(!is_null($key)){
            $this->db->where('key',$key);
            $result = $this->db->get($this->config->item('applications_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    private function get_key_info_id($id){
        if(!is_null($id)){
            $this->db->where('id',$id);
            $result = $this->db->get($this->config->item('applications_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    private function get_dev_info($key){
        if(!is_null($key)){
            $this->db->where('key',$key);
            $result = $this->db->get($this->config->item('jayon_devices_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    private function get_dev_info_by_id($identifier){
        if(!is_null($identifier)){
            $this->db->where('identifier',$identifier);
            $result = $this->db->get($this->config->item('jayon_devices_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    private function check_email($email){
        $em = $this->db->where('email',$email)->get($this->config->item('jayon_members_table'));
        if($em->num_rows() > 0){
            return $em->row_array();
        }else{
            return false;
        }
    }

    private function register_buyer($dataset){
        $dataset['group_id'] = 5;

        if($this->db->insert($this->config->item('jayon_members_table'),$dataset)){
            return $this->db->insert_id();
        }else{
            return 0;
        }
    }

    private function get_device($key){
        $dev = $this->db->where('key',$key)->get($this->config->item('jayon_mobile_table'));
        print_r($dev);
        print $this->db->last_query();
        return $dev->row_array();
    }

    private function get_group(){
        $this->db->select('id,description');
        $result = $this->db->get($this->ag_auth->config['auth_group_table']);
        foreach($result->result_array() as $row){
            $res[$row['id']] = $row['description'];
        }
        return $res;
    }

    private function log_access($api_key,$query,$result,$args = null){
        $data['timestamp'] = date('Y-m-d H:i:s',time());
        $data['accessor_ip'] = $this->accessor_ip;
        $data['api_key'] = (is_null($api_key))?'':$api_key;
        $data['query'] = $query;
        $data['result'] = $result;
        $data['args'] = (is_null($args))?'':$args;

        access_log($data);
    }

    private function admin_auth($username = null,$password = null){
        if(is_null($username) || is_null($password)){
            return false;
        }

        $password = $this->ag_auth->salt($password);
        $result = $this->db->where('username',$username)->where('password',$password)->get($this->ag_auth->config['auth_user_table']);

        if($result->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    private function record_exists($table, $key, $value)
    {
        $this->db->where($key,$value);
        $query = $this->db->get($table);
        if ($query->num_rows() > 0){
            return true;
        }
        else{
            return false;
        }
    }


}


?>