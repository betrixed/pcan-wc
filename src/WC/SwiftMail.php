<?php
namespace WC;

    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
    
use Base;

/**
 * Pcan\Mail\Mail
 * Sends e-mails based on pre-defined templates
 */
class SwiftMail 
{
    protected $transport;
    protected $f3;
    
    public function __construct($f3, $transport = null) {
        $this->f3 = $f3;
        $this->transport = $transport;
    }
    // return [ $email => $name ] from [ 'email'=> 'x', 'name' => 'x' ]
    static public function EmailName(&$ref) {
        return [ $ref['email'] => $ref['name']];
    }
    /**
     * Sends e-mails via AmazonSES based on predefined templates
     *
     * @param array $msg
     * 
     * @return array['success' => boolean, 'errors' => *]
     */
    public function send( &$msg )
    {
        // Settings
        $dir = $this->f3->get('php');
        $cfg = &$this->f3->ref("secrets");
        $mailSettings = &$cfg['mail'];
        
        $from = isset($msg['from']) ? $msg['from'] :  $mailSettings['from'];
        $to = isset($msg['to']) ? $msg['to'] :  $mailSettings['to'];
        
        // Create the message
        $subject = $msg['subject'];
        
        $result = ['success' => false];
        $mailer = new PHPMailer();
        try {
            $mailer->setFrom($from['email'], $from['name']);
            $mailer->addAddress($to['email'], $to['name']);
            $mailer->Subject = $subject;
            if (isset($msg['html'])) {
                $mailer->isHTML(true);
                $mailer->Body = $msg['html'];
                if (isset($msg['text'])) {
                    $mailer->AltBody = $msg['text'];
                }
            }
            else {
                $mailer->Body = $msg['text'];
            }

            $mailer->isSMTP();
            $smtp = &$cfg['smtp'];

            $mailer->Host = $smtp['server'];
            $mailer->SMTPAuth = true;
            $mailer->SMTPSecure = $smtp['security'];
            $mailer->Username = $smtp['username'];
            $mailer->Password = $smtp['password'];
            $mailer->Port = $smtp['port'];

            $ok = $mailer->send();
            $result['success'] = $ok;
            if (!$ok) {
                $result['errors'] = $mailer->ErrorInfo;
            }
            
        }
        catch( Exception $e) // PHPMailer
        {
            $result['errors'] = $e->getMessage();
        }
        catch(\Exception $e)
        {
             $result['errors'] = $e->getMessage();
        }
        return $result;
    }
}
