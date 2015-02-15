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
     * @param array[] $recv Array of email=>name mappings
     */
    public function setReceivers($recv)
    {
        $this->receivers = $recv;
    }

    public function setReceiver($mail, $name)
    {
        $this->receivers = array(array($mail=>$name));
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

}

/**
 * @return EmailSender
 */
function createMailSender()
{
    return new EmailSender();
}
