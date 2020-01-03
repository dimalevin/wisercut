<?php

/*
 * Asking user controller
 * 
 * Provides asking user logics
 */
class AskingUserController extends UserController {
    
    // CONSTRUCTOR
    public function __construct(User $user) {
      
        $this->_model = new AskingUserModel($user);
        $this->_view = new View($this->_model->getUserMenuItems(), $this->_model->getUserContentFileNames());
    }
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">
    
    // Specific user type command handler
    protected function _specificUserTypeCommandHandler(string $command, array $data_array) {
        
        switch ($command) {

            case 'postQuestion':
                $this->_doPostQuestion($data_array);
                break;
            
            case 'closeQuestion':
                $this->_doCloseQuestion($data_array);
                break;
            
            case 'giveResponse':
                $this->_doGiveResponse($data_array);
                break;
            
            case 'blockCompany':
                $this->_doBlockCompany($data_array);
                break;
            
            case 'unblockCompany':
                $this->_doUnblockCompany($data_array);
                break;
            
            case 'updateAdviceStatus':
                $this->_doUpdateAdviceStatus($data_array);
                break;
            
            case 'addSuggestedAdvice':
                $this->_doAddSuggestedAdvice($data_array);
                break;

            default:
                die(Notifications::ACCESS_DENIED);
        }
    }
    
    // <editor-fold defaultstate="collapsed" desc="'DO' COMMANDS">
    
    // Post question
    private function _doPostQuestion(array $data_array) {
        
        // current user is not exceeded daily questions limit
        if ($this->_model->countUserDailyQuestions() < SystemSettings::GetDailyQuestionsLimit()) {
        
            $new_question = $this->_model->createQuestionObject($data_array['question']);   // create question object

            $question_is_added = $this->_model->addQuestion($new_question);    // add to the db

            // the question is added
            if ($question_is_added) {

                $added_question = $this->_model->getCurrentUserLastQuestion();                  // load the question

                $companies_ids = $this->_model->idsOfQuestionSuitedCompanies($added_question);  // get list of companies id's to post the question

                $this->_linkQuestionToCompanies($companies_ids, $added_question->getId());      // link the question to the suited companies

                MessagesHandler::NotifyCompaniesOnNewQuestion($companies_ids, $added_question);              // notify companies by message

                $suggested_advices = $this->_model->suggestedAdvices($added_question);          // best advices of similiar questions

                $result['advices'] = $suggested_advices;

                $this->_view::SendData($result);

            } else { $this->_view::SendResponse(false, Notifications::GENERAL_ERROR, Notifications::TYPE_ERROR); }
            
        } else { $this->_view::SendResponse(false, Notifications::DAILY_QUESTIONS_LIMIT_REACHED, Notifications::TYPE_WARNING); }
    }
    
    // Close question
    private function _doCloseQuestion(array $data_array) {
        
        $result = false;
        $message = Notifications::DATABASE_ERROR;
        $message_type = Notifications::TYPE_ERROR;
        
        $question_closed = $this->_model->closeQuestion($data_array['question_id']);
        
        // question is closed
        if ($question_closed) {
            
            $result = true;
            $message = Notifications::QUESTION_CLOSED_SUCCESSFULY;
            $message_type = Notifications::TYPE_NOTICE;
        }
        
        $this->_view::SendResponse($result, $message, $message_type);
    }
        
    // Give response
    private function _doGiveResponse(array $data_array) {
        
        $result = false;
        $message = Notifications::DATABASE_ERROR;
        $message_type = Notifications::TYPE_ERROR;
        
        $new_response = $this->_model->createResponseObject($data_array['response']);   // create response object
        
        // the question is not closed and a response is not previously given
        if (!$this->_model->isQuestionClosed($new_response->getQuestionId()) && !$this->_model->isResponseGiven($new_response->getQuestionId(), $new_response->getAdviceId())) {
            
            $response_is_added = $this->_model->addResponse($new_response);

            // response is added
            if ($response_is_added) {

                $advising_company_id = $this->_model->getCompanyIdByUserId($data_array['advising_user_id']);
                $this->_model->updateCompanyScore($advising_company_id);   // update company rating score

                $result = true;
                $message = Notifications::RESPONSE_ADDED_SUCCESSFULY;
                $message_type = Notifications::TYPE_NOTICE;

                $this->_notifyAdvisingUser($new_response);  // notify the advising user

                // is best advice
                if ($new_response->isBestAdvice()) {
                    $this->_model->closeQuestion($new_response->getQuestionId());
                }
            } 
        } else {
            $message = Notifications::QUESTION_CLOSED_OR_RESPONSE_GIVEN;
            $message_type = Notifications::TYPE_WARNING;
        }
        
        $this->_view::SendResponse($result, $message, $message_type);
    }
    
    // Block company
    private function _doBlockCompany(array $data_array) {
        
        $company_id = $data_array['company_id'];
        
        $company_is_blocked = $this->_model->blockCompany($company_id);
        
        $message = $company_is_blocked ? Notifications::COMPANY_ADDED_TO_BLOCKED_LIST : Notifications::DATABASE_ERROR;
        $message_type = $company_is_blocked ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        $this->_view::SendResponse($company_is_blocked, $message, $message_type);
    }
    
    // Unblock company
    private function _doUnblockCompany(array $data_array) {
        
        $company_id = $data_array['company_id'];
        
        $company_is_unblocked = $this->_model->unblockCompany($company_id);
        
        $message = $company_is_unblocked ? Notifications::COMPANY_REMOVED_FROM_BLOCKED_LIST : Notifications::DATABASE_ERROR;
        $message_type = $company_is_unblocked ? Notifications::TYPE_NOTICE : Notifications::TYPE_ERROR;
        
        $this->_view::SendResponse($company_is_unblocked, $message, $message_type);
    }
    
    // Update advice status as readed
    private function _doUpdateAdviceStatus(array $data_array) {
        
        $advice_id = $data_array['advice_id'];
        $question_id = $data_array['question_id'];
        
        $result = $this->_model->setQuestionAdviceStatus($advice_id, $question_id);
        
        $this->_view::SendResponse($result);
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
    
    // Add suggested advice
    private function _doAddSuggestedAdvice(array $data_array) {
        
        $advice_id = $data_array['advice_id'];
        $question_id = $data_array['question_id'];
        
        $result = $this->_model->addSuggestedAdvice($advice_id, $question_id);
        
        $this->_view::SendResponse($result);
    }
    
    // </editor-fold>
    
    // Notify advising user about a new response
    private function _notifyAdvisingUser(Response $response) {
        
        $sender_name = $this->_model->getCurrentUser()->getFullName();
        
        $asking_user = $this->_model->findAdvisingUserByAdviceId($response->getAdviceId());
        
        MessagesHandler::SendAdvisingUserResponseNotification($sender_name, $asking_user, $response);
    }
    
    // Post the question to the suitable companies
    private function _linkQuestionToCompanies(array $companies_ids, int $question_id) {

        // link the question to the companies
        foreach ($companies_ids as $company_id) {
            $this->_model->linkQuestion($question_id, (int) $company_id);
        }
    }

    // </editor-fold>
}
