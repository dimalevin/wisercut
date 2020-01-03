<?php

/*
 * Advising user controller
 * 
 * Provides advising user logics
 */
class AdvisingUserController extends UserController {
    
    // CONSTRUCTOR
    public function __construct(User $user) {
    
        $this->_model = new AdvisingUserModel($user);
        $this->_view = new View($this->_model->getUserMenuItems(), $this->_model->getUserContentFileNames());                
    }
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">
    
    // Specific user type command handler
    protected function _specificUserTypeCommandHandler(string $command, array $data_array) {
        
        switch ($command) {

            case 'giveAdvice':
                $this->_doGiveAdvice($data_array);
                break;
            
            default:
                die(Notifications::ACCESS_DENIED);
        }
    }
    
    // <editor-fold defaultstate="collapsed" desc="'DO' COMMANDS">
    
    // Give advice
    private function _doGiveAdvice(array $data_array) {
        
        $result = false;
        $message = Notifications::DATABASE_ERROR;
        $message_type = Notifications::TYPE_ERROR;
        $question_id = $data_array['question_id'];

        // advice is not previously given
        if (!$this->_model->isAdviceGiven($question_id)) {

            // question is open
            if (!$this->_model->isQuestionClosed($question_id)) {

                $new_advice = $this->_model->createAdviceObject($data_array['advice']);    // create advice object

                $advice_is_added = $this->_model->addAdvice($new_advice);    // save to the db

                // the advice is added
                if ($advice_is_added) {

                    /* @var $added_advice Advice */
                    $added_advice = $this->_model->currentUserLastAdvice();  // load the advice

                    $advice_is_linked = $this->_model->linkAdviceToQuestion($added_advice->getId(), $question_id);    // link the advice to question

                    // advice is linked
                    if ($advice_is_linked) {

                        $asking_user = $this->_model->findUserByUserId($data_array['asking_user_id']);
                        MessagesHandler::NotifyAskingUserAboutNewAdvice($asking_user, $question_id);

                        $result = true;
                        $message = Notifications::ADVICE_ADDED;
                        $message_type = Notifications::TYPE_NOTICE;

                    } else {

                        $this->_model->removeAdvice($added_advice->getId());    // remove advice from the db
                    }
                }

            } else {
                $message = Notifications::QUESTION_IS_CLOSED;
                $message_type = Notifications::TYPE_WARNING;
            }
        } else {
            $message = Notifications::ADVICE_ALREADY_GIVEN;
            $message_type = Notifications::TYPE_WARNING;
        }
            
        $this->_view::SendResponse($result, $message, $message_type);
    }
    
    // Update settings
    protected function _doUpdateSettings(array $data_array) {
        
        $result = false;
        $message = null;
        $message_type = null;
        
        // user settings
        if (isset($data_array['user_settings'])) {
            
            $settings_update_status = $this->_updateUserSettings($data_array['user_settings']);
            
            // settings updated
            if ($settings_update_status['status']) {
                $result = true;
                $message = Notifications::SETTINGS_SAVED_SUCCESSFULY;
                $message_type = Notifications::TYPE_NOTICE;
            } else {
                $message = $settings_update_status['message'];
                $message_type = $settings_update_status['message_type'];
            }
        }
        
        // result is false and message is not set
        if (!$result && !$message) {
            $message = Notifications::GENERAL_ERROR;
            $message_type = Notifications::TYPE_ERROR;
        }
        
        $this->_view::SendResponse($result, $message, $message_type);
    }

    // </editor-fold>
    
    // </editor-fold>
}
