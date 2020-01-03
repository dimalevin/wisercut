<?php

/*
 * User controller
 * 
 * Provides logics for logged in users
 */
abstract class UserController extends Controller {

    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">
    // Command handler
    protected function _commandHandler(string $command, $data_array) {

        switch ($command) {

            case 'logout':
                $this->_doLogout();
                break;

            case 'deleteMessage':
                $this->_doDeleteMessage($data_array);
                break;

            case 'sendMessage':
                $this->_doSendMessages($data_array);
                break;

            case 'updateMessageInStatus':
                $this->_doUpdateMessageInStatus($data_array);
                break;

            case 'updateSettings':
                $this->_doUpdateSettings($data_array);
                break;
            
            case 'addComment':
                $this->_doAddComment($data_array);
                break;

            default:
                $this->_specificUserTypeCommandHandler($command, $data_array);
        }
    }

    // <editor-fold defaultstate="collapsed" desc="DO COMMANDS">
        
    // Logout
    protected function _doLogout() {
        session_destroy();
        $this->_view::SendResponse(true, Notifications::LOG_OUT, Notifications::TYPE_NOTICE);
    }

    // Send message
    private function _doSendMessages(array $data_array) {

        $result = false;
        $message = Notifications::GENERAL_ERROR;
        $message_type = Notifications::TYPE_ERROR;

        $data_array['message_details']['sender_id'] = $this->_model->getCurrentUser()->getId();  // add sender id

        $receiver_ids = $data_array['message_details']['receiver_ids'];

        // create messages objects
        foreach ($receiver_ids as $receiver_id) {

            $data_array['message_details']['receiver_id'] = $receiver_id;   // set receiver id

            $new_messages[] = $this->_model->createMessageObject($data_array['message_details']);   // create message
        }

        $messages_is_sent = MessagesHandler::Send($new_messages);   // send messages
        
        // response is added
        if ($messages_is_sent) {

            $result = true;
            $message = Notifications::MESSAGE_SENT;
            $message_type = Notifications::TYPE_NOTICE;
        }

        $this->_view::SendResponse($result, $message, $message_type);
    }

    // Delete message
    private function _doDeleteMessage(array $data_array) {

        $message = Notifications::DATABASE_ERROR;
        $message_type = Notifications::TYPE_ERROR;

        $mailbox_type = $data_array['mailbox_type'];
        $message_id = $data_array['message_id'];     // id of a message to delete

        if ($mailbox_type === 'inbox') {
            $result = MessagesHandler::DeleteInMessage($message_id);
        } else {
            $result = MessagesHandler::DeleteOutMessage($message_id);
        }

        // action performed
        if ($result) {

            $message = Notifications::MESSAGES_DELETED;
            $message_type = Notifications::TYPE_NOTICE;
        }

        $this->_view::SendResponse($result, $message, $message_type);
    }

    // Update message status
    private function _doUpdateMessageInStatus(array $data_array) {

        $message_id = $data_array['message_id'];     // id of the message

        $result = MessagesHandler::SetInMessageAsReaded($message_id);

        $this->_view::SendResponse($result);
    }
    
    // Add comment
    private function _doAddComment(array $data_array) {

        $advice_id = $data_array['advice_id'];
        $question_id = $data_array['question_id'];
        $comment = $data_array['comment'];
        
        $result = $this->_model->addComment($advice_id, $question_id, $comment);
        
        // add success
        if ($result['status']) {
            $this->_view::SendData(['comments' => $result['updated_comments']]);
        } else {
            $this->_view::SendResponse($result, Notifications::GENERAL_ERROR, Notifications::TYPE_ERROR);
        }
    }

    // </editor-fold>

    // Update user settings
    protected function _updateUserSettings(array $user_settings): array {

        $status = false;
        $message = null;
        $message_type = null;
        
        // change user picture
        if (isset($user_settings['picture'])) {
            $picture_update_result = $this->_updateUserPictureFile($user_settings['picture']);
        }
        
        // update picture filename
        if ($picture_update_result['status'] ?? false) { 
            $user_settings['picture_filename'] = $picture_update_result['picture_filename'];
        }
        else { $user_settings['picture_filename'] = $this->_model->getCurrentUser()->getPicture(); }
        
        // no need to change a picture OR picture changed successfuly
        if (!isset($user_settings['picture']) || $picture_update_result['status'] ?? false) {
            $status = $this->_model->updateUserSettings($user_settings);    // update user settings
        } else {
            $message = $picture_update_result['message'];
            $message_type = $picture_update_result['message_type'];
        }

        $result = [
            'status' => $status,
            'message' => $message,
            'message_type' => $message_type
        ];
        
        return $result;
    }
    
    // Update user picture file
    private function _updateUserPictureFile(array $data_array): array {
        
        $status = false;
        $picture_filename = null;
        $message = null;
        $message_type = null;
        
        $image_str = $data_array['picture_str'];    // picture as string
        $file_type = $data_array['picture_ext'];    // file format

        // check format
        if(in_array($file_type, SystemConstants::ALLOWABLE_IMG_FILE_TYPES)) {

            $image_size = strlen($image_str)/1367;
            
            // check size
            if ($image_size > 1 && $image_size <= SystemConstants::MAX_IMG_FILE_SIZE) {
                
                $new_image_file = $this->_model->saveImage($image_str, $file_type, SystemConstants::IMAGE_USER_PIC);  // save to folder

                // file saved
                if ($new_image_file != '') {
                    $status = true;
                    $picture_filename = $new_image_file;
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
            'picture_filename' => $picture_filename,
            'message' => $message,
            'message_type' => $message_type
        ];
        
        return $result;
    }


    /* Abstract */

    protected abstract function _specificUserTypeCommandHandler(string $command, array $data_array);    // specific user type command handler
    protected abstract function _doUpdateSettings(array $data_array);
    // </editor-fold>
}
