<?php
namespace WC\Controllers;
use WC\Models\{Users,EmailConfirmations};
use WC\UserSession;
/**
 * Handle Email Login Confirmation
 *
 * @author michael
 */
class ConfirmController extends BaseController {
    use \WC\Link\UserAdm;
    
    public function emailCodeAction($code, $email)
    {
        $confirmation = EmailConfirmations::findFirstByCode($code);

        if (!$confirmation) {
            $this->flash->notice('The email confirmation failed');
            return $this->dispatcher->forward(array(
                'controller' => 'error',
                'action' => 'block'
            ));
        }
        $userid = $confirmation->usersId;
        $user = Users::findFirstByid($userid);
        
        if ($confirmation->confirmed !== 'N' 
                &&  $user->mustChangePassword !== 'Y') {
            return $this->dispatcher->forward(array(
                'controller' => 'login',
                'action' => 'index'
            ));
        }

        $confirmation->confirmed = 'Y';
        $user->status = 'C';

        /**
         * Change the confirmation to 'confirmed' and update the user to 'active'
         */
        if (!$confirmation->update()) {

            foreach ($confirmation->getMessages() as $message) {
                $this->flash->error($message);
            }

            return $this->dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'index'
            ));
        }

        /**
         * Give the User a Session
         */
        
        
        $us = $this->user_session;
        $roles = $this->getRoleList($user);
        $us->setUser($user,$roles);
        

        /**
         * Check if the user must change his/her password
         */
        if ($user->mustChangePassword === 'Y') {

            $us->addFlash('The email was successfully confirmed. Now you must change your password');

            return $this->dispatcher->forward(array(
                'controller' => 'login',
                'action' => 'changePwd'
            ));
        }

        $us->addFlash('The email was successfully confirmed');

        return $this->dispatcher->forward(array(
            'controller' => 'myaccount',
            'action' => 'edit'
        ));
    }
    
    public function resetPasswordAction()
    {
        $code = $this->dispatcher->getParam('code');

        $resetPassword = ResetPasswords::findFirstByCode($code);

        if (!$resetPassword) {
            return $this->dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'index'
            ));
        }

        if ($resetPassword->reset != 'N') {
            return $this->dispatcher->forward(array(
                'controller' => 'session',
                'action' => 'login'
            ));
        }

        $resetPassword->reset = 'Y';

        /**
         * Change the confirmation to 'reset'
         */
        if (!$resetPassword->update()) {

            foreach ($resetPassword->getMessages() as $message) {
                $this->flash->error($message);
            }

            return $this->dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'index'
            ));
        }

        /**
         * Identify the user in the application
         */
        $this->auth->authUserById($resetPassword->usersId);

        $this->flash->success('Please reset your password');

        return $this->dispatcher->forward(array(
            'controller' => 'users',
            'action' => 'changePassword'
        ));
    }
}
