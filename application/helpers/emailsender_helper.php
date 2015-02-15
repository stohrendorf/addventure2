<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class EmailSender
{
    private $receivers = array();
    private $message = null;
    private $subject = null;

    /**
     * @param string[] $recv email=>name mappings
     */
    public function setReceivers($recv)
    {
        $this->receivers = $recv;
    }

    public function setReceiver($mail, $name)
    {
        $this->receivers = array($mail => $name);
    }

    public function addReceiver($mail, $name)
    {
        $this->receivers[$mail] = $name;
    }

    public function setMessage($msg)
    {
        $this->message = $msg;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function send(&$failures = null)
    {
        $type = getAddventureConfigValue('email', 'type');
        if($type == 'smtp') {
            return $this->sendSmtp($failures);
        }
        elseif($type == 'sendmail') {
            return $this->sendSendmail($failures);
        }
        elseif($type == 'mandrill') {
            return $this->sendMandrill($failures);
        }
        else {
            throw new \InvalidArgumentException('Invalid mail type');
        }
    }

    private function createSwiftMessage()
    {
        $message = Swift_Message::newInstance();
        $message->setFrom(getAddventureConfigValue('email', 'senderAddress'), getAddventureConfigValue('email', 'senderName'));
        $message->setSubject($this->subject);
        $message->setBody($this->message);
        return $message;
    }

    private function sendSmtp(&$failures = null)
    {
        $message = $this->createSwiftMessage();

        $transport = Swift_SmtpTransport::newInstance(getAddventureConfigValue('email', 'host'), getAddventureConfigValue('email', 'port'), getAddventureConfigValue('email', 'security'));
        $transport->setUsername(getAddventureConfigValue('email', 'username'));
        $transport->setUsername(getAddventureConfigValue('email', 'password'));

        $transport->setAuthMode(getAddventureConfigValue('email', 'authMode'));

        $mailer = Swift_Mailer::newInstance($transport);
        $hadErrors = false;
        foreach($this->receivers as $mail => $name) {
            $message->setTo($mail, $name);
            if(!$mailer->send($message, $failures)) {
                $hadErrors = true;
            }
        }
        return !$hadErrors;
    }

    private function sendSendmail(&$failures = null)
    {
        $message = $this->createSwiftMessage();

        $transport = Swift_SendmailTransport::newInstance();

        $mailer = Swift_Mailer::newInstance($transport);
        $hadErrors = false;
        foreach($this->receivers as $mail => $name) {
            $message->setTo($mail, $name);
            if(!$mailer->send($message, $failures)) {
                $hadErrors = true;
            }
        }
        return !$hadErrors;
    }

    private function sendMandrill(&$failures = null)
    {
        $to = array();
        foreach($this->receivers as $mail => $name) {
            $to[] = array(
                'email' => $mail,
                'name' => $name,
                'type' => 'to'
            );
        }
        $messageData = array(
            'text' => $this->message,
            'subject' => $this->subject,
            'from_email' => getAddventureConfigValue('email', 'senderAddress'),
            'from_name' => getAddventureConfigValue('email', 'senderName'),
            'to' => $to,
            'preserve_recipients' => false
        );
        
        $async = true;
        $ip_pool = 'Main Pool';
        try {
            $mandrill = new Mandrill(getAddventureConfigValue('email', 'apikey'));
            $mandrill->messages->send($messageData, $async, $ip_pool);
        }
        catch(Mandrill_Error $e) {
            if($failures != null && !is_array($failures)) {
                $failures = array();
            }
            if(is_array($failures)) {
                $failures[] = get_class($e) . ': ' . $e->getMessage();
            }
        }
        return true; // assume that everything is OK
    }

}

/**
 * @return EmailSender
 */
function createMailSender()
{
    return new EmailSender();
}
