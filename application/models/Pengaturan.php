<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }
    function getProfil($email='')
    {        
        $query = $this->db->query("SELECT * FROM t_registrasi where email='".$email."'");
        return $query->result();        
    }
    function getFoto() {
        $email= $this->session->userdata('email');
        $query = $this->db->query("SELECT foto_profil,thumbnail FROM t_registrasi where email='".$email."'");
        return $query->result_array();
    }
    function getSlider() {        
        $query = $this->db->from('t_slider')                        
                ->get()
                ->result();       
        return $query;
    }
    function getCalendar($email='')
    {        
        $query = $this->db->query("SELECT * FROM t_calendar");
        return $query->result();        
    }
    function getPengajuan($email='')
    {        
        $query = $this->db->query("SELECT * FROM t_pengajuan where email='".$email."' and status='Verified'");
        return $query->result();        
    }
    function getNego()
    {        
        $email = $this->session->userdata('email'); 
        
        $this->db->select('t_iklan.*,t_nego.price_nego,date_nego,nama_foto_thumb,nama_foto,t_nego.id as id_nego, status_nego,note_nego,kirim,t_nego.email as email_nego, f_komisi,n_komisi');
        $this->db->from('t_nego');
        $this->db->join('t_iklan', 't_iklan.id=t_nego.id_iklan'); 
        $this->db->join('t_foto_kapal', 't_foto_kapal.clasification_no=t_iklan.clasification_no'); 
        $this->db->where('t_nego.email', $email);
        $this->db->where('t_foto_kapal.main',1);
        $this->db->order_by('date_nego',"DESC");
        $data = $this->db->get()->result();
        
        return $data;        
    }
    function getDNego($id)
    {        
        $email = $this->session->userdata('email'); 
        
        $this->db->select('t_iklan.*,t_nego.price_nego,date_nego,nama_foto_thumb,nama_foto,t_nego.id as id_nego, status_nego,note_nego,t_nego.email as email_nego, f_komisi,n_komisi');
        $this->db->from('t_nego');
        $this->db->join('t_iklan', 't_iklan.id=t_nego.id_iklan'); 
        $this->db->join('t_foto_kapal', 't_foto_kapal.clasification_no=t_iklan.clasification_no'); 
        $this->db->where('t_nego.id', $id);
        $this->db->where('t_foto_kapal.main',1);
        $data = $this->db->get()->result();
        
        return $data;        
    }
    function getHNego($id,$email)
    {        
        
        $this->db->select('t_iklan.*,t_nego.price_nego,date_nego,nama_foto_thumb,nama_foto,t_nego.id as id_nego, status_nego,note_nego, f_komisi,n_komisi');
        $this->db->from('t_nego');
        $this->db->join('t_iklan', 't_iklan.id=t_nego.id_iklan'); 
        $this->db->join('t_foto_kapal', 't_foto_kapal.clasification_no=t_iklan.clasification_no'); 
        $this->db->where('t_nego.id_iklan', $id);
        $this->db->where('t_nego.email', $email);
        $this->db->where('t_foto_kapal.main',1);
        $data = $this->db->get()->result();
        
        return $data;        
    }
    public function get_notification_global(){
        $email = $this->session->userdata('email'); 
        $result = [];  
        $data = $this->db->order_by('date_notif', 'DESC')->get_where('t_notification',array('email'=>$email,'status' => 0))->result(); 
        $data_all = $this->db->order_by('date_notif', 'DESC')->get_where('t_notification',array('email'=>$email))->result();
        $data_nego = $this->db->get_where('t_nego',array('email'=>$email,'status_nego' => 0))->result(); 

        $status = array('Cancel', 'Finish');
        $this->db->from('t_pengajuan');
        $this->db->where('email', $email);
        $data_submission = $this->db->where_not_in('status', $status)->get()->result();

        $status = array('Verified', 'Sign 1');
        $data_sign = $this->db->query("SELECT * FROM `t_pengajuan` WHERE (`id_seller` = '". $this->session->userdata('id')."' OR `id_buyer` = '".$this->session->userdata('id')."') AND `status` IN('Verified', 'Sign 1')")->result();           

        $data_meetup = $this->db->query("SELECT * FROM `t_pengajuan` WHERE (`id_seller` = '". $this->session->userdata('id')."' OR `id_buyer` = '".$this->session->userdata('id')."') AND `status` IN('Sign 2', 'On Schedule')")->result();        

        $result['count'] = count($data);
        $result['count_submission'] = count($data_submission);
        $result['count_sign'] = count($data_sign);
        $result['count_meetup'] = count($data_meetup);
        $result['count_nego'] = count($data_nego);
        $result['row'] = $data;
        $result['row_all'] = $data_all;
        return $result;
    }
    function getNoPengajuan($kode='')
    {        
        $query = $this->db->query("SELECT * FROM t_pengajuan where kode_pengajuan   ='".$kode."'");
        return $query->result();        
    }
    function getPengajuan_2()
    {        
        $email= $this->session->userdata('email');
        $query = $this->db->get_where('t_registrasi',array('email'=>$email))->result();

        $query = $this->db->query("SELECT * FROM t_pengajuan where id_seller='".$query[0]->id."' and status='Sign 1'");
        return $query->result();        
    }
    function getMeetSaya()
    {        
        $email= $this->session->userdata('email');
        $query = $this->db->query("SELECT * FROM t_pengajuan where email='".$email."' and status='Sign 2'");
        return $query->result();        
    }
    function getMeetClient()
    {        
        $email= $this->session->userdata('email');
        $query = $this->db->get_where('t_registrasi',array('email'=>$email))->result();

        $query = $this->db->query("SELECT * FROM t_pengajuan where id_seller='".$query[0]->id."' and status='Sign 2'");
        return $query->result();        
    }
    function getAllGroups()
    {        
        $query = $this->db->query('SELECT type FROM t_sertifikat_mst group by type order by type asc');
        return $query->result();        
    }
    function getAllKab()
    {        
        $query = $this->db->query('SELECT * FROM t_sertifikat_mst WHERE TYPE = (SELECT TYPE FROM t_sertifikat_mst GROUP BY TYPE ORDER BY TYPE ASC LIMIT 1)');
        return $query->result();        
    }

    function getDataIklan($id = '')
    {        
        $query = $this->db->query("SELECT * FROM t_iklan WHERE id= ".$id."");
        return $query->result();        
    }

    function getDataKapal($id = '')
    {        
        $query = $this->db->query("SELECT * FROM t_kapal WHERE clasification_no= ".$id."");
        return $query->result();        
    }

    function getImage($id = '')
    {        
        $query = $this->db->query("SELECT * FROM t_foto_kapal WHERE clasification_no= ".$id." order by main Desc");
        return $query->result();        
    }

    function getCountry()
    {        
        $query = $this->db->query('SELECT iso,nicename FROM country');
        return $query->result();        
    }
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    function sendMail($email,$body,$subject,$lampiran="") {
        $ci = get_instance();
        
        $config['protocol'] = "smtp";
        if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == '::1')
        {
            $config['smtp_host'] = "ssl://smtp.gmail.com";
            $config['smtp_user'] = "dedi.slamets@gmail.com";
            $config['smtp_pass'] = "wallpapers";
            $config['charset']   = 'iso-8859-1';

        }else{
            $config['smtp_host'] = "ssl://mail.marinebusiness.co.id";
            $config['smtp_user'] = "admin@marinebusiness.co.id";
            $config['smtp_pass'] = "admin123^";
            $config['charset'] = 'utf-8';
        }
        
        $config['smtp_port'] = "465";
        
        
        $config['mailtype'] = "html";
        $config['newline'] = "\r\n";
              
        $config['send_multipart'] = FALSE;
        
        //vdebug($config);
        $ci->email->initialize($config);
        if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == '::1')
        {
            $ci->email->from('dedi.slamets@gmail.com', 'Marine Business');
        }else{
            $ci->email->from('admin@marinebusiness.co.id', 'Marine Business');
        }
        
        $list = array($email);
        $ci->email->to($list);
        $ci->email->subject($subject);
        $ci->email->message($body);
        $this->email->set_newline("\r\n");
        $ci->email->attach($lampiran);
        if ($this->email->send()) {
            // echo 'Email sent.';
        } else {
            show_error($this->email->print_debugger());
        }
    }

    function Autonumber()   {
          $this->db->select('RIGHT(kode_pengajuan,4) as kode', FALSE);
          $this->db->order_by('id','DESC');    
          $this->db->limit(1);    
          $query = $this->db->get('t_pengajuan');      //cek dulu apakah ada sudah ada kode di tabel.    
          if($query->num_rows() <> 0){      
           //jika kode ternyata sudah ada.      
           $data = $query->row();      
           $kode = intval($data->kode) + 1;    
          }
          else {      
           //jika kode belum ada      
           $kode = 1;    
          }
          $kodemax = str_pad($kode, 4, "0", STR_PAD_LEFT); // angka 4 menunjukkan jumlah digit angka 0
          $kodejadi = "MB-2209-".$kodemax;    // hasilnya ODJ-9921-0001 dst.
          return $kodejadi;  
    }
}
