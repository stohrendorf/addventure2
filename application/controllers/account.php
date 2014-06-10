<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * User account controller.
 * 
 * The workflow for a new user is as follows:
 *
 *   1) Enter the 'register' method.  This will first display the 'account_register'
 *      template for entering the needed information.  If the information is
 *      incomplete or faulty, the 'account_register_invalid' will be shown.  Else,
 *      a preliminary account with the role {@see \addventure\User::AwaitApproval} will be
 *      created an E-mail will be sent to the user which contains encrypted data
 *      for validation.
 * 
 *   2) The user receives his E-mail with the activation link, which points to
 *      the 'verify' method.  Here, the security token passed in the URL will
 *      be verified against the stored information, and if everything is OK,
 *      the account role will be set to {@see \addventure\User::Registered}.  But if
 *      something goes wrong, the 'account_register_invalid' will be shown.
 * 
 *   3) Now, the user has to login to create his session cookie and to store
 *      the session data in the database.  This could be done in the 'verify'
 *      step, but it ensures that the E-mail account isn't hijacked, because
 *      the user has to enter his password again.
 */
class Account extends CI_Controller {

    private static function getVerificationCode($email) {
        return sha1($email . sha1(ADDVENTURE_KEY));
    }
    
    private static function encodePassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function register() {
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password');
        if($email === FALSE || empty($email) || $password === FALSE || empty($password)) {
            $smarty->display('account_register.tpl');
            return;
        }
        $this->load->helper('email');
        if(!valid_email($email)) {
            echo 'A';
            $smarty->display('account_register_invalid.tpl');
            return;
        }

        try {
            $username = simplifyWhitespace($this->input->post('username', TRUE), 1000, false);
        }
        catch(\InvalidArgumentException $ex) {
            echo 'B';
            $smarty->display('account_register_invalid.tpl');
            return;
        }
        $user = new \addventure\User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRole(\addventure\UserRole::AwaitApproval);
        $this->load->library('encrypt');
        $user->setPassword(self::encodePassword($password));
        try {
            $this->load->library('em');
            $this->em->persistAndFlush($user);
        echo "ROLE=", $user->getRole();
        }
        catch(Exception $e) {
            echo 'C: ', $e->getMessage();
            $smarty->display('account_register_invalid.tpl');
            return;
        }

        $transport = Swift_SendmailTransport::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance();
        $message->setFrom(ADDVENTURE_EMAIL_ADDRESS, ADDVENTURE_EMAIL_NAME);
        $message->setTo($email);
        $message->setSubject('Addventure2 E-Mail Verification');

        $verify = site_url(array('account', 'verify')) . '?';
        $verify .= 'a=' . rawurlencode(self::getVerificationCode($email));
        $verify .= '&b=' . rawurlencode(base64_encode($this->encrypt->encode($email)));
        /**
         * @todo Make it a smarty template and support HTML mails.
         */
        $message->setBody(<<<"MSG"
Dear writer!

To verify your e-mail address, please open the following link in your browser:

{$verify}

Happy writing!
MSG
);
        if(!$mailer->send($message, $failures)) {
            $this->load->library('log');
            $this->log->crit('Could not send verification e-mail: ' . print_r($failures,true));
            show_error('Sorry, something bad happened; it\'s not your fault. Our dozens of monkey are probably working on it.');
        }
        else {
            $smarty->display('account_register_mail_sent.tpl');
        }
    }

    public function verify() {
        $token = rawurldecode($this->input->get('a'));
        $email = rawurldecode($this->input->get('b'));
        
        $this->load->library('encrypt');
        $email = $this->encrypt->decode(base64_decode($email));
        
        $this->load->library('em');
        $user = $this->em->findUserByMail($email);
        
        $this->load->helper('smarty');
        $smarty = createSmarty();
        /**
         * @todo Remove!
         */
        print_r($token);
        echo '<br/>';
        print_r($email);
        echo '<br/>';
        print_r(self::getVerificationCode($email));
        
        if(!$user || $user->getRole()->get() !== \addventure\UserRole::AwaitApproval || $token !== self::getVerificationCode($email)) {
            $smarty->display('account_verify_invalid.tpl');
        }
        else {
            $user->setRole(\addventure\UserRole::Registered);
            $this->em->persistAndFlush($user);
            redirect(site_url());
        }
    }

    public function login() {
        $this->load->library('encrypt');
        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password');
        $remember = $this->input->post('remember', TRUE);

        $this->load->library('em');
        $user = $this->em->findUserByMail($email);

        $this->load->helper('smarty');
        $smarty = createSmarty();
        if(!$user || $user->getRole() < \addventure\UserRole::Registered || !password_verify($password, $user->getPassword())) {
            $smarty->display('account_login_invalid.tpl');
        }
        else {
            $this->load->library('session');
            if(!isset($remember) || $remember !== 'yes') {
                $this->session->sess_expire_on_close = TRUE;
            }
            $this->session->set_userdata('userid', $user->getId());
            $this->load->helper('url');
            redirect(site_url());
        }
    }

    public function logout() {
        $this->load->library('session');
        $this->session->sess_destroy();
        $this->load->helper('url');
        redirect(site_url());
    }
}
