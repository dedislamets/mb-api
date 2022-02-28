<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
header('Content-Type: application/json');

defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Api extends RestController  {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin');
        $this->load->model('Pengaturan');
    }

    public function koneksi_get()
    {   

        $this->response([
                'status' => true,
            ], 200 );
    }
    public function login_post()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);

        $email = $this->input->post('email');           
        $password = $this->input->post('password');  

        $query = $this->db->get_where('t_registrasi',array('email' => $params['email'], 'pass' => $params['password'], 'verified' => 1));       
        if ($query->num_rows() > 0) {
            $this->response([
                'status' => true,
                'data' => $query->result()
            ], 200 );
            
        }else{
            $this->response( [
                'status' => false,
                'message' => 'Incorrect username or password !!'
            ], 200 );
        }   
    }

    public function notifikasi_get()
    {
        $shift = $this->admin->api_array('tb_notifikasi',array("sent_to"=> $this->get('id') ));

        if ($shift != FALSE) {
            $this->response([
                'status' => true,
                'data' => $shift
            ], 200 );
        }else{

            $this->response( [
                'status' => false,
                'data' => array(),
                'message' => 'No users were found'
            ], 200 );
        }
    }

    public function serial_get()
    {
        $sql = "SELECT serial,nama_ruangan as ruangan,jenis FROM barang INNER JOIN jenis_barang ON barang.`id_jenis`=jenis_barang.`id`";
        $shift = $this->db->query($sql)->result_array();

        if ($shift != FALSE) {
            foreach ($shift as $key => $value) {

                $sql = "select * FROM (
                        select no_transaksi,current_insert,epc,'kotor' as STATUS from linen_kotor_detail WHERE epc='". $value['serial'] ."'
                        UNION  
                        SELECT no_transaksi,current_insert,epc,'bersih' AS STATUS FROM linen_bersih_detail WHERE epc='". $value['serial'] ."'
                        UNION  
                        SELECT no_transaksi,current_insert,epc,'keluar' AS STATUS FROM linen_keluar_detail WHERE epc='". $value['serial'] ."'
                        UNION  
                        SELECT no_transaksi,current_insert,epc,'rusak' AS STATUS FROM linen_rusak_detail WHERE epc='". $value['serial'] ."'
                    )history ORDER BY current_insert DESC limit 1";

                $history = $this->db->query($sql)->row_array();
                $shift[$key]['status'] = (empty($history['STATUS']) ? "" : $history['STATUS']);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($shift));
        }else{

            $this->response( [
                'status' => false,
                'message' => 'No data were found'
            ], 404 );
        }
    }

    public function front_kapal_get()
    {
        $live = $this->db->select('t_iklan.*,nama_foto,nama_foto_thumb,vessel_nama,place_build,year_build,construction')
                ->from('t_iklan')
                ->join('t_foto_kapal', 't_foto_kapal.clasification_no=t_iklan.clasification_no','left')
                ->join('t_kapal', 't_kapal.clasification_no=t_iklan.clasification_no','left')
                ->where(array('status' => 1,'active' => 1,'service' => 'Trading'))
                ->group_by('t_iklan.id')
                ->order_by('date_iklan','desc')  
                ->limit(5)           
                ->get()
                ->result_array();

        if ($live != FALSE) {
            $this->response([
                'status' => true,
                'title' => 'Kapal Terbaru',
                'horizontal' => true,
                'data' => $live
            ], 200 );
        }else{

            $this->response( [
                'status' => false,
                'message' => 'No data were found'
            ], 404 );
        }
    }

    

    public function room_byid_get()
    {
        $data =array(
            "id"=>$this->get('id'),
        );
        $shift = $this->admin->get_row('tb_ruangan',$data);

        if ($shift != FALSE) {
            $this->response([
                'id' => $shift->id,
                'ruangan' => $shift->ruangan
            ], 200 );
        }else{

            $this->response( [
                'status' => false,
                'message' => 'No users were found'
            ], 404 );
        }
    }

    public function kirim_email_get()
    {
        $this->Pengaturan->sendMail('dedi.slamets@gmail.com', "<h1>Ini adalah tes kirim email</h1>", "Tes kirim email");  
    }

    
    public function register_post()
    {
        $token = $this->Pengaturan->generateTokenApps();
        $data = array(
                'nama' => $this->input->post('nama'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'pass' => $this->input->post('pass'),
                'token' => $token
        );  

        $body = '<div bgcolor="#f0f0f0" style="margin:0;padding:0">
                    <table cellpadding="0" cellspacing="0" style="min-width:320px" width="100%">
                        <tbody>
                            <tr>
                                <td bgcolor="#f0f0f0" style="padding:0 0 50px">
                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto" width="600">
                                        <tbody><tr>
                                            <td>
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                    <tr>
                                                        <td bgcolor="#ffffff" style="padding:50px 30px;border:1px solid #e0e0e0;border-radius:0 0 3px 3px;font-size:14px">
                                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                                <tbody><tr>
                                                                    <td style="padding:0 0 30px;font-size:18px;color:#404448">Hi '. $this->input->post('nama') .',</td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:0 0 30px;font-size:14px;color:#404448">
                                                                        Selamat! Selangkah lagi untuk mengaktifkan Account anda. Aktifkan akun Anda dengan mengklik link ini <a href="https://marinebusiness.co.id/activation/sf/'. $token .'" target="_blank">Klik disini</a>
                                                                        <br>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="center" colspan="2">
                                                                        <a href="https://marinebusiness.co.id/activation/sf/'. $token .'" style="display:inline-block;padding-top:12px;padding-left:24px;padding-bottom:12px;padding-right:24px;text-decoration:none;background-color:#47bbe4;border-radius:5px;font-weight:bold;color:#ffffff;text-transform:uppercase;font-size:16px" target="_blank">Aktivasi Sekarang!</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" height="25"></td>
                                                                </tr>                                                               
                                                                <tr>
                                                                    <td colspan="2" height="25">&nbsp;</td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="font-size:14px;color:#404448">
                                                                        Terima kasih.<br>
                                                                        <strong>Tim Marine Business</strong>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        </td>
                                                    </tr>
                                                </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>        
                        </tbody>
                    </table>
                </div>';
            
        $query = $this->db->get_where('t_registrasi',array('email' => $this->input->post('email')));        
        if ($query->num_rows() > 0) {           
            foreach ($query->result() as $key => $value) {
                $response['error'] = TRUE;
                if($value->verified == 1){
                    $response['message'] = "Email already registered.";
                }else{
                    $response['message'] = "Email already registered and verify your account now.. Please check your email";    
                    $this->Pengaturan->sendMail($this->input->post('email'), $body, "Verify your account now..");   

                }   

                $this->response($response, 200 );            
            }
        }else{
            $insert = $this->db->insert('t_registrasi', $data);   
            $this->Pengaturan->sendMail($this->input->post('email'), $body, "Verify your account now..");  

            if($insert){
                $this->response([
                    'status' => true,
                    'message' => 'Berhasil disimpan'
                ], 200 );
            }else{

                $this->response( [
                    'status' => false,
                    'message' => 'Terjadi Kesalahan..!!'
                ], 404 );
            } 
        }    
    }
    function Acak($varMsg,$strKey) {
        try {
            $Msg = $varMsg;
            $char_replace="";
            $intLength = strlen($Msg);
            $intKeyLength = strlen($strKey);
            $intKeyOffset = $intKeyLength;
            $intKeyChar = ord(substr($strKey, -1));
            for ($n=0; $n < $intLength ; $n++) { 
                $intKeyOffset = $intKeyOffset + 1;

                if($intKeyOffset > $intKeyLength) {
                    $intKeyOffset = 1;
                }
                $intAsc = ord(substr($Msg,$n, 1));

                if($intAsc > 32 && $intAsc < 127){
                    $intAsc = $intAsc - 32;
                    $intAsc = $intAsc + $intKeyChar;

                    while ( $intAsc > 94) {
                       $intAsc = $intAsc - 94;
                    }

                    $intSkip = $n+1 % 94;
                    $intAsc = $intAsc + $intSkip;
                    if($intAsc > 94){
                        $intAsc = $intAsc - 94;
                    }

                    $char_replace .= chr($intAsc + 32);
                    
                    $Msg = $char_replace . substr($varMsg, $n+1) ;
                }

                $intKeyChar = ord(substr($strKey, $intKeyOffset-1));
            }
            return $Msg;
        } catch (Exception $e) {
            
        }
    }

    public function linen_kotor_all_get()
    {
        $linen_kotor = $this->admin->api_array('linen_kotor');
        $linen_kotor_detail = $this->admin->api_array('linen_kotor_detail');

        foreach ($linen_kotor_detail as $key => $value) {
            $this->db->from('barang');
            $this->db->join('jenis_barang','barang.id_jenis=jenis_barang.id');
            $this->db->where(array( 'serial' => $value['epc']));
            $data_exist = $this->db->get()->row();
            if(!empty($data_exist)){
                $linen_kotor_detail[$key]['item'] = $data_exist->jenis;
            }
        }

        if ($linen_kotor != FALSE) {
            $this->response([
                'status' => true,
                'data' => $linen_kotor,
                'data_detail' => $linen_kotor_detail
            ], 200 );
        }else{

            $this->response( [
                'status' => false,
                'message' => 'No users were found'
            ], 404 );
        }
    }

    
    public function hapus_token_post()
    {
        $del = $this->admin->deleteTable('id_user', $this->post('id_user') ,'tb_token_push');

        if ($del) {
            $this->response([
                'status' => 200,
                'message' => "Berhasil dihapus"
            ], 200 );
        }else{

            $this->response( [
                'status' => 502,
                'message' => 'Gagal menghapus data'
            ], 404 );
        }
    }

    public function hapus_linen_get()
    {
        // echo $this->get('id');exit();
        $del = $this->admin->deleteTable('serial', $this->get('serial') ,'barang');

        if ($del) {
            $this->response([
                'status' => 200,
                'message' => "Berhasil dihapus"
            ], 200 );
        }else{

            $this->response( [
                'status' => 502,
                'message' => 'Gagal menghapus data'
            ], 404 );
        }
    }

    public function room_post()
    {
        // print("<pre>".print_r($this->post(),true)."</pre>");exit();
        $data =array(
            "ruangan"=>$this->post('ruangan'),
            
        );
        $insert = $this->db->insert("tb_ruangan", $data);
        if($insert){
            $response['status']=200;
            $response['error']=false;
            $response['message']='Data berhasil ditambahkan.';
        }else{
            // $response['status']=502;
            $response['error']=true;
            $response['message']='Data gagal ditambahkan.';

        }
        $this->response($response);
    }

    public function token_post()
    {
        $data =array(
            "id_user"=>$this->post('id_user'),
            "token"=>$this->post('token'),
            
        );
        $insert = $this->db->insert("tb_token_push", $data);
        if($insert){
            $response['status']=200;
            $response['error']=false;
            $response['message']='Data berhasil ditambahkan.';
        }else{
            // $response['status']=502;
            $response['error']=true;
            $response['message']='Data gagal ditambahkan.';

        }
        $this->response($response);
    }

    public function linen_kotor_post()
    {
        $arr_date = explode("/", $this->post('tanggal'));
        $data =array(
            "NO_TRANSAKSI"  => $this->post('no_transaksi'),
            "TANGGAL"       => $arr_date[2] . "-" . $arr_date[1]. "-". $arr_date[0],
            "PIC"           => $this->post('pic'),
            "STATUS"        => 'CUCI',
            "KATEGORI"        => $this->post('kategori'),
            "F_INFEKSIUS"        => $this->post('infeksius'),
            "TOTAL_BERAT"        => $this->post('total_berat'),
            "TOTAL_QTY"        => $this->post('total_qty'),
        );

        $response['error']=true;
        $response['message']='Data gagal ditambahkan.';

        $data_exist = $this->admin->get_array('linen_kotor',array( 'NO_TRANSAKSI' => $this->post('no_transaksi')));
        if(empty($data_exist)){
            $insert = $this->db->insert("linen_kotor", $data);
            if($insert){
                $response['status']=200;
                $response['error']=false;
                $response['message']='Data berhasil ditambahkan.';
            }
        }
        
        $this->response($response);
    }
    public function linen_kotor_detail_post()
    {
        $data =array(
            "no_transaksi"  => $this->post('no_transaksi'),
            "epc"           => $this->post('epc'),
            "ruangan"        => $this->post('room')
        );

        $response['error']=true;
        $response['message']='Data gagal ditambahkan.';

        $data_exist = $this->admin->get_array('linen_kotor_detail',array( 'no_transaksi' => $this->post('no_transaksi'), 'epc' => $this->post('epc') ));
        if(empty($data_exist)){
            $insert = $this->db->insert("linen_kotor_detail", $data);
            if($insert){

                $this->db->set(array("kotor" => 1));
                $this->db->where(array( "epc" => $this->post('epc'), "kotor" => 0 ));
                $this->db->update('linen_keluar_detail');

                $this->db->set(array("nama_ruangan" => $this->post('room')));
                $this->db->where(array( "serial" => $this->post('epc') ));
                $this->db->update('barang');

                $response['status']=200;
                $response['error']=false;
                $response['message']='Data berhasil ditambahkan.';
            }

        }
        
        $this->response($response);
    }
    
    public function send_notif_app_get(){
        error_reporting(-1);
        ini_set('display_errors', 'On');

 
        $type = isset($_GET['type']) ? $_GET['type'] : 'single';
        
        $fields = NULL;
        $token = isset($_GET['token']) ? $_GET['token'] : 'cUzamsGi3pA:APA91bFUmb-zNoPWXvn8RgVtDhExlX8d6yPDMMWBOXVTaLjEZuOWiViZRT_h63qWJ0StNPv3bwUR6FikfSNua89gH7GlRS5wiZrifcriljsB9BIs3frmfad1Xo7-mzqxOYtc_xk23D2Y';
        
        if($type == "single") {
        // echo $token; exit();
            
            $message = isset($_GET['message']) ? $_GET['message'] : '';
            
            $res = array();
            $res['body'] = $message;
            
            $fields = array(
                'to' => $token,
                'notification' => $res,
            );
            echo json_encode($fields);
            // echo 'FCM Reg Id : '. $token . '<br/>Message : ' . $message;
        }else if($type == "topics") {
            $topics = isset($_GET['topics']) ? $_GET['topics'] : '';
            $message = isset($_GET['message']) ? $_GET['message'] : '';
            
            $res = array();
            $res['body'] = $message;
            
            $fields = array(
                'to' => '/topics/' . $topics,
                'notification' => $res,
            );
            
            echo json_encode($fields);
            echo 'Topics : '. $topics . '<br/>Message : ' . $message . '<br>';
        }
        
        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';
        $server_key = "AAAA-XXzNh4:APA91bFtdWD6MfsRH3PeYz62vYQdCNFNoXZdi5BaOyZ6AiEdIqQpYjuBplob5baO7RCU6iw-ElrX6GH60g95fTE6ltK2ejbC9XXPcfFOby4BMuVTSi2LEnPMHAxgMforeOFnJN_gCu7l";
        
        $headers = array(
            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            echo 'Curl failed: ' . curl_error($ch);
        }else{
            echo "<br>Curl Berhasil";
        }
 
        // Close connection
        curl_close($ch);
    }
       
}