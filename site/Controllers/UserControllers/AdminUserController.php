<?php

/*
 * Admin user controller
 * 
 * Provides admin user logics
 */
class AdminUserController extends UserController {
    
    // CONSTRUCTOR
    public function __construct(User $user) {
        
        $this->_model = new AdminUserModel($user);
        $this->_view = new View($this->_model->getUserMenuItems(), $this->_model->getUserContentFileNames());
    }
    
   // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">
    
    // Specific user type command handler
    protected function _specificUserTypeCommandHandler(string $command, array $data_array) {
        
        switch ($command) {

            case 'blockUser':
                $this->_doBlockUser($data_array);
                break;
            
            case 'unblockUser':
                $this->_doUnblockUser($data_array);
                break;
            
            case 'blockCompany':
                $this->_doBlockCompany($data_array);
                break;
            
            case 'unblockCompany':
                $this->_doUnblockCompany($data_array);
                break;
            
            case 'approveCompany':
                $this->_doApproveCompany($data_array);
                break;
            
            case 'hideQuestion':
                $this->_doHideQuestion($data_array);
                break;
            
            case 'unhideQuestion':
                $this->_doUnhideQuestion($data_array);
                break;

            default:
                die(Notifications::ACCESS_DENIED);
        }
    }
    
    // <editor-fold defaultstate="collapsed" desc="'DO' COMMANDS">

    // Block user
    private function _doBlockUser(array $data_array) {
        
        $user_id = $data_array['user_id'];
        
        $this->_changeUserStatus($user_id, true);
    }
    
    // Unblock user
    private function _doUnblockUser(array $data_array) {
        
        $user_id = $data_array['user_id'];
        
        $this->_changeUserStatus($user_id, false);
    }
    
    // Block company
    private function _doBlockCompany(array $data_array) {
        
        $company_id = $data_array['company_id'];
        
        $this->_changeCompanyStatus($company_id, true);
    }
    
    // Unblock company
    private function _doUnblockCompany(array $data_array) {
        
        $company_id = $data_array['company_id'];
        
        $this->_changeCompanyStatus($company_id, false);
    }
    
    // Approve company
    private function _doApproveCompany(array $data_array) {
                
        $company_id = $data_array['company_id'];
        
        /* @var $company Company */
        $company = $this->_model->findCompanyById($company_id);
        
        $company_is_approved = $this->_model->approveCompany($company_id);

        // company approve status is changed
        if ($company_is_approved) {
            
            $company_manager = $this->_model->findUserByUserId($company->getManagerId());
            
            MailHandler::SendCompanyRegistrationStatus($company->getName(), $company_manager, true); // notify the approved company by email
        }
        
        $message = $company_is_approved ? Notifications::COMPANY_APPROVED : Notifications::DATABASE_ERROR;
        $message_type = $company_is_approved ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        $this->_view::SendResponse($company_is_approved, $message, $message_type);
    }
    
    // Hide question
    private function _doHideQuestion(array $data_array) {
        
        $question_id = $data_array['question_id'];
        
        $this->_changeQuestionStatus($question_id, false);
    }
    
    // Unhide question
    private function _doUnhideQuestion(array $data_array) {
        
        $question_id = $data_array['question_id'];
        
        $this->_changeQuestionStatus($question_id, true);
    }
    
    // Update settings
    protected function _doUpdateSettings(array $data_array) {
        
        $status = false;
        $message = null;
        $message_type = null;
        
        // user settings
        if (isset($data_array['user_settings'])) {
            $user_settings_update_status = $this->_updateUserSettings($data_array['user_settings']);
        }
        
        // user settings updated successfuly
        if ($user_settings_update_status['status'] ?? false) {
            
            // system settings
            if (isset($data_array['system_settings'])) {
                $status = SystemSettings::SetSettings($data_array['system_settings']);
            }
            
        } else {
            $message = $user_settings_update_status['message'];
            $message_type = $user_settings_update_status['message_type'];
        }
        
        // settings updated
        if ($status) {
            $result = true;
            $message = Notifications::SETTINGS_SAVED_SUCCESSFULY;
            $message_type = Notifications::TYPE_NOTICE;
        } else if (!$message) {
            $result = false;
            $message = Notifications::GENERAL_ERROR;
            $message_type = Notifications::TYPE_ERROR;
        }

        $this->_view::SendResponse($result, $message, $message_type);
    }
    
    // </editor-fold>
    
    // Change user status
    private function _changeUserStatus(int $user_id, bool $is_blocked) {
        
        $user_status_changed = $this->_model->setUserStatus($user_id, $is_blocked);
        
        $message = $user_status_changed ? Notifications::USER_STATUS_CHANGED : Notifications::DATABASE_ERROR;
        $message_type = $user_status_changed ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        // user status is changed
        if ($user_status_changed) {
            
            $user = $this->_model->findUserByUserId($user_id);
            
            MailHandler::SendUserStatus($user, $is_blocked);    // notify the user by email
        }
        
        $this->_view::SendResponse($user_status_changed, $message, $message_type);
    }
    
    // Change company status
    private function _changeCompanyStatus(int $company_id, bool $is_blocked) {
        
        $company_status_changed = $this->_model->setCompanyStatus($company_id, $is_blocked);
        
        $message = $company_status_changed ? Notifications::COMPANY_STATUS_CHANGED : Notifications::DATABASE_ERROR;
        $message_type = $company_status_changed ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        // company status is changed
        if ($company_status_changed) {

            /* @var $company Company */
            $company = $this->_model->findCompanyById($company_id);

            $company_manager = $this->_model->findUserByUserId($company->getManagerId());
            
            MailHandler::SendCompanyStatus($company->getName(), $company_manager, $is_blocked);    // notify the company manager by email
        }
        
        $this->_view::SendResponse($company_status_changed, $message, $message_type);
    }
    
    // Change question status
    private function _changeQuestionStatus(int $question_id, bool $is_visible) {
        
        $question_status_changed = $this->_model->setQuestionStatus($question_id, $is_visible);
        
        $message = $question_status_changed ? Notifications::QUESTION_STATUS_CHANGED : Notifications::DATABASE_ERROR;
        $message_type = $question_status_changed ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        // question status is changed
        if ($question_status_changed) {
            
            /* @var $question Question */
            $question = $this->_model->findQuestionById($question_id);
            $user = $this->_model->findUserByUserId($question->getUserId());        // find the asking user
            
            MessagesHandler::SendUserQuestionStatus($user, $question, $is_visible); // notify the user by message
        }
        
        $this->_view::SendResponse($question_status_changed, $message, $message_type);
    }
    
    // </editor-fold> 
}
