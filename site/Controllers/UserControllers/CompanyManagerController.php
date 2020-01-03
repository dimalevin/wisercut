<?php

/*
 * Company manager controller
 * 
 * Provides company manager logics
 */
class CompanyManagerController extends UserController {
    
    // CONSTRUCTOR
    public function __construct(User $user) {
      
        $this->_model = new CompanyManagerModel($user);
        $this->_view = new View($this->_model->getUserMenuItems(), $this->_model->getUserContentFileNames());
    }
 
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">
    
    // Specific user type command handler
    protected function _specificUserTypeCommandHandler(string $command, array $data_array) {
        
        switch ($command) {

            case 'addUser':
                $this->_doAddUser($data_array);
                break;
            
            case 'blockUser':
                $this->_doBlockUser($data_array);
                break;
            
            case 'unblockUserRequest':
                $this->_doUnblockUserRequest($data_array);
                break;
            
            default:
                die(Notifications::ACCESS_DENIED);
        }
    }
    
    // <editor-fold defaultstate="collapsed" desc="'DO' COMMANDS">
    
    // Add user
    private function _doAddUser(array $data_array) {
        
        $result = false;
        
        /* @var $user_to_add User */
        $user_to_add = $this->_model->findUserByEmail($data_array['email']);   // find user with provided email
        
        // user is exists, approved and not blocked
        if ($user_to_add && $user_to_add->isApproved() && !$user_to_add->isBlocked() && $user_to_add->getType() === 'advising') {
            
            // user not belongs to another company
            if ($this->_model->getCompanyIdByUserId($user_to_add->getId()) == 0) {
                
                $request_added = $this->_model->addUserToCompanyRequest($this->_model->getCurrentCompany()->getId(), $user_to_add->getId());
                
                if ($request_added) {
                    
                    $link = $this->_model->companyActivationLink($user_to_add->getUsername());   // create activation link

                    $current_user = $this->_model->getCurrentUser();
                    $company_name = $this->_model->getCurrentCompany()->getName();
                    $link_sended = MailHandler::SendNewCompanyUserActivationLink($user_to_add, $current_user, $company_name, $link);  // send email with account activation link
                }
                
                // request_added and link sended
                if ($request_added && $link_sended) {
                    
                    $result = true;
                    $message = Notifications::COMPANY_ADDUSER_LINK_SENDED;
                    $message_type = Notifications::TYPE_NOTICE;
                } else {
                    $this->_model->removeUserToCompanyRequest($this->_model->getCurrentCompany()->getId(), $user_to_add->getId());
                    $message = Notifications::DATABASE_ERROR;
                    $message_type = Notifications::TYPE_ERROR;
                }

            } else {
                
                $message = Notifications::COMPANY_ADDUSER_BELONGS_TO_ANOTHER_COMPANY;
                $message_type = Notifications::TYPE_WARNING;
            }
            
        } else {
            
            $message = Notifications::COMPANY_ADDUSER_ERROR;
            $message_type = Notifications::TYPE_WARNING;
        }
        
        $this->_view::SendResponse($result, $message, $message_type);
    }
    
    // Block user
    private function _doBlockUser(array $data_array) {
        
        $user_id = $data_array['user_id'];
        
        $user_is_blocked = $this->_model->blockUser($user_id);
        
        // user is blocked
        if($user_is_blocked) {
            
            $status = true;
            $message = Notifications::USER_STATUS_CHANGED;
            $message_type = Notifications::TYPE_NOTICE;
            
            MailHandler::SendUserStatus($this->_model->findUserByUserId($user_id), true);    // notify user by mail
            
        } else {
            
            $message = Notifications::DATABASE_ERROR;
            $message_type = Notifications::TYPE_ERROR;
        }
        
        $this->_view::SendResponse($status, $message, $message_type);
    }
    
    // Unblock user request
    private function _doUnblockUserRequest(array $data_array) {
        
        $username_to_unblock = $data_array['username'];
        $info = $data_array['info'];

        $self_id = $this->_model->getCurrentUser()->getId();
        $company_name = $this->_model->getCurrentCompany()->getName();
        
        $result = MessagesHandler::SendCompanyUserUnblockRequest($self_id, $company_name, $username_to_unblock, $info);    // send request to the admin
        
        $message = $result ? Notifications::REQUEST_IS_SENT : Notifications::DATABASE_ERROR;
        $message_type = $result ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        $this->_view::SendResponse($result, $message, $message_type);
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
            
            // company settings
            if (isset($data_array['company_settings'])) {
                $company_settings_update_status = $this->_updateCompanySettings($data_array['company_settings']);
            }
            
            // company settings updated successfuly
            if ($company_settings_update_status['status'] ?? false) {
                $status = true;
            } else {
                $message = $company_settings_update_status['message'];
                $message_type = $company_settings_update_status['message_type'];
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
            $message = Notifications::GENERAL_ERROR;
            $message_type = Notifications::TYPE_ERROR;
        }

        $this->_view::SendResponse($result, $message, $message_type);
    }
    // </editor-fold>
    
    // Update company settings
    private function _updateCompanySettings(array $company_settings): array {
        
        $status = false;
        $message = null;
        $message_type = null;
        
        // change company logo
        if (isset($company_settings['logo'])) {
            $logo_update_result = $this->_updateCompanyLogoFile($company_settings['logo']);
        }
        
        // update logo filename
        if ($logo_update_result['status'] ?? false) { 
            $company_settings['logo_filename'] = $logo_update_result['logo_filename'];
        }
        else { $company_settings['logo_filename'] = $this->_model->getCurrentCompany()->getLogo(); }
        
        // no need to change a logo OR logo changed successfuly
        if (!isset($company_settings['logo']) || $logo_update_result['status'] ?? false) {
            $status = $this->_model->updateCompanySettings($company_settings);    // update company settings
        } else {
            $message = $logo_update_result['message'];
            $message_type = $logo_update_result['message_type'];
        }

        $result = [
            'status' => $status,
            'message' => $message,
            'message_type' => $message_type
        ];
        
        return $result;
    }
    
    // Update company logo file
    private function _updateCompanyLogoFile(array $data_array): array {
        
        $status = false;
        $logo_filename = null;
        $message = null;
        $message_type = null;
        
        $image_str = $data_array['logo_str'];    // logo as string
        $file_type = $data_array['logo_ext'];    // file format

        // check format
        if(in_array($file_type, SystemConstants::ALLOWABLE_IMG_FILE_TYPES)) {

            $image_size = strlen($image_str)/1367;
            
            // check size
            if ($image_size > 1 && $image_size <= SystemConstants::MAX_IMG_FILE_SIZE) {
                
                $new_image_file = $this->_model->saveImage($image_str, $file_type, SystemConstants::IMAGE_COMPANY_LOGO);  // save to folder

                // file saved
                if ($new_image_file != '') {
                    $status = true;
                    $logo_filename = $new_image_file;
                }
                
            } else if ($image_size > SystemConstants::MAX_IMG_FILE_SIZE) {
                $message = Notifications::IMAGE_MAX_FILE_SIZE_EXCEEDED;
                $message_type = Notifications::TYPE_WARNING;
            } else {
                $message = Notifications::IMAGE_FILE_CORRUPTED;
                $message_type = Notifications::TYPE_WARNING;
            }

        } else {
            $message = Notifications::IMAGE_FILE_TYPE_NOT_SUPPORTED;
            $message_type = Notifications::TYPE_WARNING;
        }
        
        $result = [
            'status' => $status,
            'logo_filename' => $logo_filename,
            'message' => $message,
            'message_type' => $message_type
        ];
        
        return $result;
    }
    
    // </editor-fold>
}
