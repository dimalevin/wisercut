<?php

/*
 * General controller
 * 
 * Used when the system loads till user login.
 * Provides appropriate logics.
 */
class GeneralController extends Controller {
    
    // PRIVATE PROPERTIES
    private $_login_attemps = 0;

    // CONSTRUCTOR
    public function __construct() {
        $this->_model = new GeneralModel();
        $this->_view = new View($this->_model->getUserMenuItems(), $this->_model->getUserContentFileNames());
    }

    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">

    // Command handler
    protected function _commandHandler(string $command, $data_array) {
        
        switch ($command) {

            case 'login':
                $this->_doLogin($data_array);
                break;

            case 'register':
                $this->_doRegister($data_array);
                break;
            
            case 'recoverPassword':
                $this->_doRecoverPassword($data_array);
                break;
            
            case 'sendContactUsForm':
                $this->_doSendContactUsForm($data_array);
                break;

            default:
                die(Notifications::ACCESS_DENIED);
        }
    }
    
    // <editor-fold defaultstate="collapsed" desc="DO COMMANDS">

    // Login
    private function _doLogin(array $data_array) {
        
        $login_status = false;
        $message = '';
        $message_type = Notifications::TYPE_WARNING;
        
        // login data
        $given_username = ($data_array['username'] ? trim($data_array['username']) : '');
        $given_psw = ($data_array['password'] ? trim($data_array['password']) : '');
                    
        $this->_login_attemps++; // count attempt
        
        // max attemps not reached
        if ($this->_login_attemps <= SystemConstants::MAX_LOGIN_ATTEMPTS) {
            
            /* @var $user_to_load User */
            
            // find user by username
            $user_to_load = $this->_model->findUserByUsername($given_username);
            
            // user found and the password is match
            if ($user_to_load && $this->_model::PasswordValidate($given_psw, $user_to_load->getPassword())) {
                
                // user is blocked or not approved
                if ($user_to_load->isBlocked() || !$user_to_load->isApproved()) { $message = ($user_to_load->isApproved() ? Notifications::BLOCKED_ACCOUNT : Notifications::NOT_ACTIVATED_ACCOUNT); }
                // user is advising user and doesn't belongs to company
                else if ($user_to_load->getType() === 'advising' && $this->_model->getCompanyIdByUserId($user_to_load->getId()) == 0) { $message = Notifications::ADVISING_USER_NO_COMPANY; } 
                // user of type company or advising and his company is blocked
                else if (($user_to_load->getType() === 'company' || $user_to_load->getType() === 'advising') && $this->_model->getCompanyBlockedStatusByUserId($user_to_load->getId())) { $message = Notifications::BLOCKED_COMPANY; }
                // user of type company or advising and his company is not approved
                else if (($user_to_load->getType() === 'company' || $user_to_load->getType() === 'advising') && !$this->_model->getCompanyApproveStatusByUserId($user_to_load->getId())) { $message = Notifications::COMPANY_NOT_APPROVED; }
                else {
                    
                    $user_controller_loaded = $this->_loadUserController($user_to_load);
                    
                    // user controller is loaded
                    if ($user_controller_loaded) {
                        
                        // response to view
                        $login_status = true;
                        $message = 'Welcome, '.$user_to_load->getFName().'!';
                        $message_type = Notifications::TYPE_NOTICE;
                    }
                }
                
            } else { $message = Notifications::LOGIN_ERROR; }
                
        } else { $message = Notifications::BLOCKED_FALSE_ATTEMPS; }    
                    
        $this->_view::SendResponse($login_status, $message, $message_type);     // response to view
    }
    
    // Register
    private function _doRegister(array $data_array) {
        
        $user_details_array = $data_array['user'];
        $company_details_array = $data_array['company'];
        
        $register_status = false;

        $user_to_register = $this->_model->createUserObject($user_details_array);   //create user object
        
        // username exists
        if ($this->_model->findUserByUsername($user_to_register->getUsername()) != null) {
            $message = Notifications::USERNAME_TAKEN;
            $message_type = Notifications::TYPE_WARNING;
        } 
        // email exists
        else if ($this->_model->findUserByEmail($user_to_register->getEmail()) != null) {
            $message = Notifications::EMAIL_TAKEN;
            $message_type = Notifications::TYPE_WARNING;
        } else {
            
            $register_status = $this->_registerUser($user_to_register);     // register user
            
            // user registered and his type is company
            if ($register_status && $user_to_register->getType() === 'company') {
                
                /* @var $registered_user User */
                $registered_user = $this->_model->findUserByUsername($user_to_register->getUsername());
                
                $company_to_register = $this->_model->createCompanyObject($company_details_array, $registered_user->getId());  //create company object
                
                $register_status = $this->_registerCompany($company_to_register, $registered_user);
                
                // company is added
                if ($register_status) {
                    $user_added_to_company_users = $this->_model->addManagerToCompanyUsers($registered_user->getId());
                }
                
                // company is not added or the user is not added to the company users
                if (!$register_status || !$user_added_to_company_users) {
                    $this->_model->removeUserByUsername($registered_user->getUsername());    // remove user
                    $this->_model->removeUserFromCompanyUsers($registered_user->getId());
                }
            }
            
            $message = $register_status ? Notifications::CONTINUE_REGISTRATION_NOTICE : Notifications::DATABASE_ERROR;
            $message_type = $register_status? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        }
        
        $this->_view::SendResponse($register_status, $message, $message_type);
    }
    
    // Recover password
    private function _doRecoverPassword(array $data_array) {
        
        $recovery_status = false;
        $message = '';
        $message_type = Notifications::TYPE_WARNING;
        
        $username = $data_array['username'];
        $email = $data_array['email'];
        
        /* @var $user User */
        $user = $this->_model->findUserByUsername($username);   // find user with provided username
        
        // user found and provided email is match
        if ($user && $user->getEmail() === $email) {
            
            // user is not blocked and approved
            if (!$user->isBlocked() && $user->isApproved()) {
                
                $new_password = $this->_setGeneratedPasswordToUser($user->getId());
                
                // password changed successfuly
                if ($new_password) {
                    
                    $message_is_sent = MailHandler::SendNewPassword($user, $new_password);     // send email with the new password
                } 
                
                $recovery_status = $message_is_sent;
                $message = $message_is_sent? Notifications::PASSWORD_RECOVERY_NOTICE : Notifications::GENERAL_ERROR;
                $message_type = $message_is_sent? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
                
            } else { $message = Notifications::PASSWORD_RECOVERY_NOT_ACTIVE_ACCOUNT; }
            
        } else { $message = Notifications::PASSWORD_RECOVERY_WRONG_USERNAME_EMAIL; }

        $this->_view::SendResponse($recovery_status, $message, $message_type);
    }
            
    // Send contact us form
    private function _doSendContactUsForm(array $data_array) {
        
        $name = $data_array['name'];
        $email = $data_array['email'];
        $subject = $data_array['subject'];
        $content = $data_array['message'];
        
        $message_is_sent = MessagesHandler::SendContactUsForm($name, $email, $subject, $content);
        
        $message = $message_is_sent? Notifications::MESSAGE_SENT : Notifications::GENERAL_ERROR;
        $message_type = $message_is_sent? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        $this->_view::SendResponse($message_is_sent, $message, $message_type);
    }
    
    // </editor-fold>
    
    // Load user controller
    private function _loadUserController(User $user): bool {
        
        $user_controller = null;
        
        switch ($user->getType()) {
            
            case 'admin':
                $user_controller = new AdminUserController($user);
                break;
            
            case 'advising':
                $user_controller = new AdvisingUserController($user);
                break;
            
            case 'asking':
                $user_controller = new AskingUserController($user);
                break;
            
            case 'company':
                $user_controller = new CompanyManagerController($user);
                break;
        }
        
        // user controller init.
        if ($user_controller) {
            $_SESSION['controller'] = $user_controller;     // set as default controller
        } else {
            throw new Exception('Model init. error.');
        }
        
        return $user_controller != null;
    }
    
    // Set generated password to user
    private function _setGeneratedPasswordToUser(int $user_id) {
        
        $new_psw = Model::GeneratePassword();               // generate new password
        $new_psw_hashed = Model::PasswordHash($new_psw);    // hash password

        // set new password to user
        $status = $this->_model->updateUserPassword($user_id, $new_psw_hashed);
        
        return $status ? $new_psw : null;
    }
    
    // Register user
    private function _registerUser(User $user_to_register): bool {

        $add_new_user_result = $this->_model->addNewUser($user_to_register);    // add the user to the db

        // user added successfuly
        if ($add_new_user_result) {
            
            $link = Model::UserActivationLink($user_to_register->getUsername());   // create activation link
            
            $link_sended = MailHandler::SendNewUserActivationLink($user_to_register, $link);  // send email with account activation link
            
            // link not sended
            if (!$link_sended) {
                
                $this->_model->removeUserByUsername($user_to_register->getUsername());    // remove user
                $add_new_user_result = false;
            }
        }
        
        return $add_new_user_result;
    }
    
    // Register company
    private function _registerCompany(Company $company_to_register, User $manager): bool {

        $add_new_company_result = $this->_model->addNewCompany($company_to_register);   // add the company to the db

        // company added successfuly
        if ($add_new_company_result) {
            
            $admin_message = MessagesHandler::SendCompanyRegistrationAdminNotice($company_to_register, $manager);  // message to the admin
            
            // mail send fails
            if (!$admin_message) {
                $add_new_company_result = false;
                $this->_model->removeCompanyByManagerId($company_to_register->getManagerId());
            }
        }

        return $add_new_company_result;
    }
    // </editor-fold>
}

