<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
//require '../vendor/autoload.php';
class Email extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }    
    function sendMail($email, $subject, $body) {
        $ci = get_instance();
        
        $config['protocol'] = "smtp";
        $config['smtp_host'] = "ssl://mail.marinebusiness.co.id";
        $config['smtp_port'] = "465";
        $config['smtp_user'] = "cs@marinebusiness.co.id";
        $config['smtp_pass'] = "admin123^";
        $config['charset'] = 'utf-8';
        //$config['charset'] = 'iso-8859-1';
        $config['mailtype'] = "html";
        $config['newline'] = "\r\n";
              
        $config['send_multipart'] = FALSE;
        
        
        $ci->email->initialize($config);
 
        $ci->email->from('dedi.slamets@gmail.com', 'Marine Business');
        $list = array($email);
        $ci->email->to($list);
        $ci->email->subject($subject);
        $ci->email->message($body);
        if ($this->email->send()) {
            echo 'Email sent.';
        } else {
            show_error($this->email->print_debugger());
        }
    }

    function sendPhpMail($email, $subject, $body, $attachments = null){
        $mail = new PHPMailer();                              // Passing `true` enables exceptions
        try {
            //Server settings
            //$mail->SMTPDebug = 3;                                 // Enable verbose debug output
            $mail->isSMTP();  
            $mail->SMTPAuth = true; 
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465; 
            if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == '::1')
            {
                $mail->Host = 'ssl://smtp.gmail.com';  // Specify main and backup SMTP servers
                $mail->Username = 'dedi.slamets@gmail.com';                 // SMTP username
                $mail->Password = 'wallpapers'; 
                $mail->setFrom('dedi.slamets@gmail.com', 'Marine Business');                         
            }else{               
                $mail->Host = 'ssl://mail.marinebusiness.co.id';  // Specify main and backup SMTP servers
                $mail->Username = 'admin@marinebusiness.co.id';                 // SMTP username
                $mail->Password = 'admin123^'; 
                $mail->setFrom('admin@marinebusiness.co.id', 'Marine Business');
            }                                  

            //Recipients
            
            $mail->addAddress($email);     // Add a recipient                        
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            if(isset($attachments)){
                $mail->addAttachment($attachments);
            }                                
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}
