<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * User account controller.
 * 
 * The workflow for a new user is as follows:
 *
 *   1. Enter the 'register' method.  This will first display the 'account_register'
 *      template for entering the needed information.  If the information is
 *      incomplete or faulty, the 'account_register_invalid' will be shown.  Else,
 *      a preliminary account with the role {@see \addventure\User::AwaitApproval} will be
 *      created an E-mail will be sent to the user which contains encrypted data
 *      for validation.
 * 
 *   2. The user receives his E-mail with the activation link, which points to
 *      the 'verify' method.  Here, the security token passed in the URL will
 *      be verified against the stored information, and if everything is OK,
 *      the account role will be set to {@see \addventure\User::Registered}.  But if
 *      something goes wrong, the 'account_register_invalid' will be shown.
 * 
 *   3. Now, the user has to login to create his session cookie and to store
 *      the session data in the database.  This could be done in the 'verify'
 *      step, but it ensures that the E-mail account isn't hijacked, because
 *      the user has to enter his password again.
 */
class Account extends CI_Controller {

    private static function _getVerificationCode($email) {
        return sha1($email . sha1(getAddventureConfigValue('encryptionKey')));
    }

    private static function _encodePassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function _createMessage($to, $subject) {
        $message = Swift_Message::newInstance();
        $message->setFrom(getAddventureConfigValue('email', 'senderAddress'), getAddventureConfigValue('email', 'senderName'));
        $message->setTo($to);
        $message->setSubject($subject);
        return $message;
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
            $smarty->display('account_register_invalid.tpl');
            return;
        }

        try {
            $username = simplifyWhitespace($this->input->post('username', TRUE), 1000, false);
        }
        catch(\InvalidArgumentException $ex) {
            $smarty->display('account_register_invalid.tpl');
            return;
        }
        $user = new \addventure\User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRole(\addventure\UserRole::AwaitApproval);
        $this->load->library('encrypt');
        $user->setPassword(self::_encodePassword($password));
        $user->setRegisteredSince(new \DateTime());
        try {
            $this->load->library('em');
            $this->em->persistAndFlush($user);
        }
        catch(Exception $e) {
            $smarty->display('account_register_invalid.tpl');
            return;
        }

        $verificationUrl = site_url(array('account', 'verify')) . '?';
        $verificationUrl .= 'a=' . rawurlencode(self::_getVerificationCode($email));
        $verificationUrl .= '&b=' . rawurlencode(base64_encode($this->encrypt->encode($email)));

        $message = $this->_createMessage($email, _('Addventure2 E-Mail Verification'));
        $message->setBody(sprintf(_(<<<'MSG'
Dear writer!

To verify your e-mail address, please open the following link in your browser:

%1$s

Happy writing!
MSG
        ), $verificationUrl));
        $transport = Swift_SendmailTransport::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        if($mailer->send($message, $failures)) {
            $smarty->display('account_register_mail_sent.tpl');
            return;
        }
        $this->load->library('log');
        $this->log->crit('Could not send verification e-mail: ' . print_r($failures, true));
        show_error(_('Sorry, something bad happened; it\'s not your fault. Our dozens of monkeys are probably working on it.'));
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
        
        if($user && $user->getRole()->get() === \addventure\UserRole::AwaitApproval) {
            $diff = abs( (new \DateTime())->getTimestamp() - $user->getRegisteredSince()->getTimestamp() );
            if( $diff > getAddventureConfigValue('maxAwaitingApprovalHours') * 60 * 60 ) {
                $this->em->getEntityManager()->remove($user);
                $this->em->getEntityManager()->flush();
                $smarty->display('account_verify_expired.tpl');
                return;
            }
        }

        if(!$user || $user->getRole()->get() !== \addventure\UserRole::AwaitApproval || $token !== self::_getVerificationCode($email)) {
            $smarty->display('account_verify_invalid.tpl');
            return;
        }

        $user->setRole(\addventure\UserRole::Registered);
        $user->setRegisteredSince(new \DateTime());
        $this->em->persistAndFlush($user);
        redirect(site_url());
    }

    public function login() {
        $this->load->library('encrypt');
        $name = $this->input->post('username', TRUE);
        $password = $this->input->post('password');
        $remember = $this->input->post('remember', TRUE);

        $this->load->library('em');
        $user = $this->em->findUserByName($name);

        $this->load->helper('smarty');
        $smarty = createSmarty();

        if(!$user || $user->getRole()->get() < \addventure\UserRole::Registered || !password_verify($password, $user->getPassword())) {
            if($user) {
                $user->setFailedLogins($user->getFailedLogins() + 1);
                $this->em->persistAndFlush($user);
                if($user->isLockedOut()) {
                    $smarty->display('account_locked.tpl');
                    return;
                }
            }
            $smarty->display('account_login_invalid.tpl');
            return;
        }

        // even a successful login cannot unlock a locked account
        if($user->isLockedOut()) {
            $smarty->display('account_locked.tpl');
            return;
        }
        
        $user->setFailedLogins(0);
        $this->em->persistAndFlush($user);
        
        $this->load->library('session');
        if(!isset($remember) || $remember !== 'yes') {
            $this->session->sess_expire_on_close = TRUE;
        }
        $this->session->set_userdata('userid', $user->getId());
        $this->load->helper('url');
        redirect(site_url());
    }

    public function logout() {
        $this->load->library('session');
        $this->session->sess_destroy();
        $this->load->helper('url');
        redirect(site_url());
    }

    public function changepassword() {
        $this->load->library('userinfo');
        if(!$this->userinfo->user) {
            show_error('Access denied.');
            return;
        }
        $oldPw = $this->input->post('oldpassword');
        $newPw1 = $this->input->post('newpassword1');
        $newPw2 = $this->input->post('newpassword2');
        $this->load->helper('smarty');
        $smarty = createSmarty();
        if(empty($oldPw) && empty($newPw1) && empty($newPw2)) {
            $smarty->display('account_changepassword.tpl');
            return;
        }
        $dataIsOk = !empty($oldPw) && !empty($newPw1) && !empty($newPw2) && $newPw1 == $newPw2 && password_verify($oldPw, $this->userinfo->user->getPassword());
        if(!$dataIsOk) {
            $smarty->display('account_changepassword_again.tpl');
            return;
        }
        $this->load->library('em');
        $this->userinfo->user->setPassword(self::_encodePassword($newPw1));
        $this->em->persistAndFlush($this->userinfo->user);
        $this->load->helper('url');
        redirect(site_url());
    }

    public function recover() {
        $this->load->library('userinfo');
        if($this->userinfo->user !== null) {
            // already logged in
            $this->load->helper('url');
            redirect(site_url());
            return;
        }

        $this->load->helper('smarty');
        $this->load->helper('email');
        $smarty = createSmarty();
        $email = $this->input->post('email', TRUE);
        if(!$email || empty($email) || !valid_email($email)) {
            $smarty->display('account_recover.tpl');
            return;
        }

        $this->load->library('em');
        $user = $this->em->findUserByMail($email);
        if(!$user) {
            redirect(site_url());
            return;
        }

        $this->load->helper('string');
        $generatedPw = random_string();

        $message = $this->_createMessage($user->getEmail(), _('Addventure2 Password Recovery'));

        $message->setBody(sprintf(_(<<<'MSG'
Dear writer!

Here is your new password:

%1$s

Happy writing!
MSG
        ), $generatedPw));
        $transport = Swift_SendmailTransport::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        if($mailer->send($message, $failures)) {
            $user->setPassword(self::_encodePassword($generatedPw));
            $this->em->persistAndFlush($user);
            $smarty->display('account_recover_mail_sent.tpl');
            return;
        }
        $this->load->library('log');
        $this->log->crit('Could not send recover e-mail: ' . print_r($failures, true));
        show_error(_('Sorry, something bad happened; it\'s not your fault. Our dozens of monkeys are probably working on it.'));
    }

}
