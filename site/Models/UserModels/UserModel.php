<?php

/*
 * User model
 * 
 * Provides data operations and db connection for logged in users
 */
abstract class UserModel extends Model {
    
    public function __construct(User $user) {
        parent::__construct();
        $this->_user = $user;
    }
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    
    // Get data
    public function getData(array $data_array) {
        
        switch ($data_array['data_name']) {
            
            case 'questionDetails':
                $data = $this->_getDataQuestionDetails($data_array);
                break;
            
            case 'adviceDetails':
                $data = $this->_getDataAdviceDetails($data_array);
                break;
            
            case 'questionAdvices':
                $data = $this->_getDataAdvices($data_array);
                break;
            
            case 'questionAdviceComments':
                $data = $this->_getDataQuestionAdviceComments($data_array);
                break;
            
            case 'responseDetails':
                $data = $this->_getDataResponseDetails($data_array);
                break;
        
            default:
                $data = $this->_getSpecificUserData($data_array);
        }
        
        return $data;
    }
    
    // Check if a question is closed
    public function isQuestionClosed(int $question_id): bool {
        
        $sql = 'SELECT is_closed AS result FROM questions WHERE que_id=:question_id';
        $data = [':question_id' => $question_id];

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        $result = $data_array['result'];
        
        return $result;
    }
    
    // Create message object
    public function createMessageObject(array $message_details) {
        
        $message = Message::CreateMessage($message_details);
        
        return $message;
    }
    
    // Link a question to the company
    public function linkQuestion(int $question_id, int $company_id): bool {
        
        $sql = 'INSERT INTO company_questions (company_id, que_id)'
        .' VALUES (:company_id, :que_id)';

        $data = [
            'company_id' => $company_id,
            'que_id' => $question_id
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Update company score
    public function updateCompanyScore(int $company_id): bool {
        
        $new_score = $this->_calculateCompanyScore($company_id);

        $sql = 'UPDATE companies SET score=:new_score WHERE company_id=:company_id';
        $data = ['company_id' => $company_id,
            'new_score' => $new_score
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
        
    // Add comment to the db
    public function addComment(int $advice_id, int $question_id, string $new_comment): array {

        /* @var $comments QuestionAdviceComments */
        $comments_obj = $this->_getQuestionAdviceComments($advice_id, $question_id);
        
        $comments_obj->addComment($new_comment, $this->_user->getType());
        
        $status = $this->_saveQuestionAdviceComments($comments_obj);
        
        $result = [
            'status' => $status,
            'updated_comments' => $comments_obj->getComments()
        ];

        return $result;
    }
    
    // Get current user
    public function getCurrentUser(): User { return $this->_user; }
    
    // Find user -by user id
    public function findUserByUserId(int $user_id) {

        $sql = 'SELECT * FROM users WHERE user_id=:user_id';
        $data = [':user_id' => $user_id];

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);

        $t_user = $query_st->fetchObject('User');
        
        return $t_user;
    }
    
    // Update user settings
    public function updateUserSettings(array $user_settings): bool {
        
        $f_name = $user_settings['f_name'];
        $l_name = $user_settings['l_name'];
        $picture = $user_settings['picture_filename'];
        $duplicate_to_mail = $user_settings['duplicate_to_mail'];
        $allow_newsletters = $user_settings['allow_newsletters'];
        
        $sql = 'UPDATE users SET user_fname=:f_name, user_lname=:l_name, picture=:picture, duplicate_to_mail=:duplicate_to_mail, allow_newsletters=:allow_newsletters'
            . ' WHERE user_id=:self_id';
        $data = [
            'self_id' => $this->_user->getId(),
            'f_name' => $f_name,
            'l_name' => $l_name,
            'picture' => $picture,
            'duplicate_to_mail' => $duplicate_to_mail == true ? 1 : 0,
            'allow_newsletters' => $allow_newsletters == true ? 1 : 0
        ];
        
        $query_res = $this->_db->singleQueryRetResult($sql, $data);
        
        // query succeeded
        if ($query_res) {
            $this->_user->setSettings($user_settings);
        }
        
        return $query_res;
    }
    
    // Save image file
    public function saveImage(string $image_coded_str, string $file_ext, string $type): string {
        
        // filename
        if ($type === SystemConstants::IMAGE_USER_PIC) {
            $filename = 'pict_'.$this->_user->getId();
        } else {
            $filename = 'pict_'.$this->_company->getId();
        }
        
        $filename .= '.'.$file_ext;
        
        $full_path = __DIR__.'/../../'.SystemConstants::IMAGES_PATHS[$type].$filename;
        
        // decode string
        $image_str = substr($image_coded_str, strpos($image_coded_str, ',') + 1);
        $image_str = str_replace(' ', '+', $image_str);
        $image_file = base64_decode($image_str);
        
        $status = file_put_contents($full_path, $image_file);
        
        return $status ? $filename : '';
    }
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">

    // <editor-fold defaultstate="collapsed" desc="GET DATA">
        
    // Get data -question details
    protected function _getDataQuestionDetails(array $data_array) {
        
        $question_id = $data_array['question_id'];
        
        $sql = 'SELECT * FROM questions WHERE que_id=:question_id';
        $data = ['question_id' => $question_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        /* @var $question Question */
        $question = $query_st->fetchObject('Question');
        
        $result = QuestionTechnicalDetails::ToArray($question->getTechData());
            
        return ['question_tech_data' => $result];
    }
    
    // Get data -advice details
    protected function _getDataAdviceDetails(array $data_array) {
        
        $advice_id = $data_array['advice_id'];
        
        $sql = 'SELECT * FROM advices WHERE adv_id=:advice_id';
        $data = ['advice_id' => $advice_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        /* @var $advice Advice */
        $advice = $query_st->fetchObject('Advice');
        
        $result = AdviceTechnicalDetails::ToArray($advice->getTechData());
            
        return $result;
    }
    
    // Get data -advices
    private function _getDataAdvices(array $data_array) {
        
        $question_id = $data_array['question_id'];
        
        $question_status = $this->isQuestionClosed($question_id);
        
        $advices_arr = $this->_getQuestionAdvicesFormatedArray($question_id);   // module specific
        
        // add question status
        $advices_arr_len = count($advices_arr);
        for ($i = 0; $i < $advices_arr_len; $i++) {
            $advices_arr[$i]['question_closed'] = $question_status;
        }
        
        return ['advices' => $advices_arr];
    }
    
    // Get data -question\advice comments
    protected function _getDataQuestionAdviceComments(array $data_array): array {
        
        $advice_id = $data_array['advice_id'];
        $question_id = $data_array['question_id'];
        
        /* @var $q_a_comments QuestionAdviceComments */
        $q_a_comments = $this->_getQuestionAdviceComments($advice_id, $question_id);
        
        return ['comments' => $q_a_comments->getComments()];
    }
    
    // Get data -response details
    protected function _getDataResponseDetails(array $data_array): array {
        
        $advice_id = $data_array['advice_id'];
        $question_id = $data_array['question_id'];
        
        $sql = 'SELECT * FROM responses WHERE adv_id=:advice_id AND que_id=:question_id';
        $data = [
            'advice_id' => $advice_id,
            'question_id' => $question_id
        ];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);

        $response = $query_st->fetchObject('Response');
        
        $result = Response::ToArray($response);
        
        return ['response' => $result];
    }
    
    // </editor-fold>
    
    // Get tab: 'Companies Index'
    protected function _getTabCompaniesIndex(): array {
        
        $result = null;
        
        $companies = $this->_getCompanies(false, false, true);     // get not blocked and approved companies
        
        if (!empty($companies)) {
            
            /* @var $company Company */
            foreach ($companies as $company) {
                
                $total_questions = $this->_getQuestionsCountPerCompany($company->getId());
                $total_advices_given = $this->_getAdvicesCountPerCompany($company->getId());
                $total_best_advices = $this->_getAdvicesCountPerCompany($company->getId(), false, true);
                $total_responses_received = $this->_getResponsesCountPerCompany($company->getId());
                
                $company_details_row = Company::ToArray($company);
                $company_details_row['total_questions'] = $total_questions;
                $company_details_row['total_advices_given'] = $total_advices_given;
                $company_details_row['total_responses_received'] = $total_responses_received;
                $company_details_row['total_best_advices'] = $total_best_advices;
                
                $result[] = $company_details_row;
            }
        }
        
        return ['companies' => $result];
    }
    
    // Checks if advice has response
    protected function _checkIfAdviceHasResponse(int $advice_id, int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM responses'
            . ' WHERE adv_id=:advice_id AND que_id=:question_id';
        $data = [
            'advice_id' => $advice_id,
            'question_id' => $question_id
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Get question-advice comments
    private function _getQuestionAdviceComments(int $advice_id, int $question_id): QuestionAdviceComments {
        
        $sql = 'SELECT * FROM question_advice_comments WHERE adv_id=:advice_id AND que_id=:question_id';
        $data = [
            'advice_id' => $advice_id,
            'question_id' => $question_id
        ];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);

        /* @var $qac_obj QuestionAdviceComments */
        $qac_obj = $query_st->fetchObject('QuestionAdviceComments');
        
        // if new
        if (!$qac_obj) {
            $qac_obj = new QuestionAdviceComments();
            $qac_obj->setAdviceId($advice_id);
            $qac_obj->setQuestionId($question_id);
            $qac_obj->setIsNew(true);
        }
            
        return $qac_obj;
    }

    // Save question-advice comments
    private function _saveQuestionAdviceComments(QuestionAdviceComments $qac_obj): bool {
        
        // if new
        if ($qac_obj->isNew()) {
            $sql = 'INSERT INTO question_advice_comments (adv_id, que_id, comments) VALUES (:advice_id, :question_id, :comments)';
            $data = [
                'advice_id' => $qac_obj->getAdviceId(),
                'question_id' => $qac_obj->getQuestionId(),
                'comments' => $qac_obj->getComments()
            ];

            $result = $this->_db->singleQueryRetResult($sql, $data);
        } else {
            $sql = 'UPDATE question_advice_comments SET comments=:updated_comments WHERE adv_id=:advice_id AND que_id=:question_id';
            $data = [
                'advice_id' => $qac_obj->getAdviceId(),
                'question_id' => $qac_obj->getQuestionId(),
                'updated_comments' => $qac_obj->getComments()
            ];

            $result = $this->_db->singleQueryRetResult($sql, $data);
        }
        
        return $result;
    }

    // Get total number of questions to company
    private function _getQuestionsCountPerCompany(int $company_id): int {
        
        $sql = 'SELECT COUNT(*) AS result FROM company_questions WHERE company_id=:company_id';
        $data = ['company_id' => $company_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        return (int) $data_array['result'];
    }
    
    // Get total number of advices given by a company
    private function _getAdvicesCountPerCompany(int $company_id, bool $all = true, bool $is_best_advice = true): int {
        
        // all advices
        if ($all) {
            
            $sql = 'SELECT COUNT(*) AS result FROM advice_question AS aq'
                . ' INNER JOIN advices AS adv ON aq.adv_id=adv.adv_id'
                . ' INNER JOIN company_users AS cu ON adv.user_id=cu.user_id'
                . ' WHERE cu.company_id=:company_id';
            $data = ['company_id' => $company_id ];
            
        } else {
            
            $sql = 'SELECT COUNT(*) AS result FROM responses AS res'
                . ' INNER JOIN advices AS adv ON res.adv_id=adv.adv_id'
                . ' INNER JOIN company_users AS cu ON adv.user_id=cu.user_id'
                . ' WHERE cu.company_id=:company_id AND res.is_best_advice=:is_best_advice';
            $data = [
                'company_id' => $company_id,
                'is_best_advice' => $is_best_advice == true ? 1 : 0
            ];
        }

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        return (int) $data_array['result'];
    }
    
    // Get responses count per company -all responses that users of that company received
    private function _getResponsesCountPerCompany(int $company_id): int {
        
        $sql = 'SELECT COUNT(*) AS result FROM responses AS res'
            . ' INNER JOIN advices AS adv ON res.adv_id=adv.adv_id'
            . ' INNER JOIN company_users AS cu ON adv.user_id=cu.user_id'
            . ' WHERE cu.company_id=:company_id';
        $data = ['company_id' => $company_id ];
            
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        return (int) $data_array['result'];
    }
    
    // Get company name by user id
    protected function _getCompanyNameByUserId(int $user_id): string {
        
        $sql = 'SELECT company_name FROM companies AS cmp'
            . ' INNER JOIN company_users AS cmpu ON cmp.company_id=cmpu.company_id'
            . ' WHERE cmpu.user_id=:user_id';
        $data = [ 'user_id' => $user_id ];
        
        $que_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $que_st->fetch();

        $result = $data_array['company_name'] ?? '';
        
        return $result;
    }
    
    // Get companies
    protected function _getCompanies(bool $all, bool $is_blocked = false, bool $is_approved = true) {
        
        // all companies
        if ($all) {
            $sql = 'SELECT * FROM companies';
            $data = null;
        } else {
            
            $sql = 'SELECT * FROM companies WHERE is_blocked=:is_blocked AND is_approved=:is_approved';
            $data = [
                'is_blocked' => $is_blocked == true ? 1 : 0,
                'is_approved' => $is_approved == true ? 1 : 0
            ];
        }
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $companies = $query_st->fetchAll(PDO::FETCH_CLASS, 'Company');
        }
        
        return $companies ?? null;
    }

    // Get current user IN messages as array
    protected function _getCurrentUserInMessages(): array {
        
        $result = null;
        
        $all_messages = MessagesHandler::GetUserInMessages($this->_user->getId());
        
        /* @var $message Message */
        foreach ($all_messages as $message) {
            
            /* @var $sender User */
            $sender = $this->findUserByUserId($message->getSenderId());
            
            $sender_details = User::ToArray($sender);
            
            $message_details_row = Message::ToArray($message);
            
            $message_details_row['sender'] = $sender_details;
                
            $result[] = $message_details_row;  // add to result
        }
        
        return ['messages_in' => $result];
    }
    
    // Get current user Out messages as array
    protected function _getCurrentUserOutMessages(): array {
        
        $result = null;
        
        $all_messages = MessagesHandler::GetUserOutMessages($this->_user->getId());
        
        /* @var $message Message */
        foreach ($all_messages as $message) {
            
            /* @var $receiver User */
            $receiver = $this->findUserByUserId($message->getReceiverId());
            
            $receiver_details = User::ToArray($receiver);
            
            $message_details_row = Message::ToArray($message);
            
            $message_details_row['receiver'] = $receiver_details;
                
            $result[] = $message_details_row;  // add to result
        }
        
        return ['messages_out' => $result];
    }
        
    // Get company questions (visible only)
    protected function _getCompanyQuestions(int $company_id) {
        
        $sql = 'SELECT * FROM questions AS q'
            . ' INNER JOIN company_questions AS cq ON q.que_id=cq.que_id'
            . ' WHERE cq.company_id=:company_id AND q.is_visible=1';
        $data = ['company_id' => $company_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $questions = $query_st->fetchAll(PDO::FETCH_CLASS, 'Question');
        }
        
        return $questions ?? null;
    }
    
    // Checks if company is blocked by user
    protected function _companyIsBlockedByUser(int $user_id, int $company_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM user_blocked_companies'
            . ' WHERE user_id=:user_id AND company_id=:company_id';
        $data = [
            'user_id' => $user_id,
            'company_id' => $company_id
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Calculate company score
    private function _calculateCompanyScore(int $company_id): int {
        
        $company_responses = $this->_getCompanyResponses($company_id);
        
        $score = 0;
        $scores_sum = 0;
        $total_advices = count($company_responses);
        
        if ($total_advices > 0) {
            
            /* @var $response Response */
            foreach ($company_responses as $response) {

                $scores_sum += $response->getScore();

                if ($response->isBestAdvice()) {
                    $scores_sum++;
                } else if ($response->getScore() < 5) {
                    $scores_sum--;
                }
            }

            $score = round($scores_sum / $total_advices);
            
            // check bounds
            if ($score > 10) {
                $score = 10;
            } else if ($score < 0) { 
                $score = 1;
            }
        }
        
        return $score;
    }
    
    // Get all responses to a company
    private function _getCompanyResponses(int $company_id) {
        
        $sql = 'SELECT * FROM responses AS res'
            . ' INNER JOIN questions as q ON res.que_id=q.que_id'
            . ' INNER JOIN advices AS adv ON res.adv_id=adv.adv_id'
            . ' INNER JOIN company_users AS cu ON adv.user_id=cu.user_id'
            . ' WHERE q.is_visible=1 AND cu.company_id=:company_id';
        $data = ['company_id' => $company_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $responses = $query_st->fetchAll(PDO::FETCH_CLASS, 'Response');
        }

        return $responses ?? null;
    }
    
    /* Abstract */
    protected abstract function _getSpecificUserData(array $data_array);
    protected abstract function _getQuestionAdvicesFormatedArray(int $question_id): array;
    // </editor-fold>
}
