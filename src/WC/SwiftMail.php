<?php
namespace WC;

    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use WC\App;
/**
 * Pcan\Mail\Mail
 * Sends e-mails based on templates
 */
class SwiftMail 
{
    protected $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    /**
     * @param array $msg
     * 
     * @return array['success' => boolean, 'errors' => *]
     */
    public function send( array $msg ) : array
    {
        // Settings
        $mailSettings = $this->app->getSecrets('mail');
        $smtp = $this->app->getSecrets('smtp');
        
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
