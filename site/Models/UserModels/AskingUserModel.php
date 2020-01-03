<?php

/*
 * Asking user model
 * 
 * Provides asking user data operations and db connection
 */
class AskingUserModel extends UserModel {
    
    /* Constants */
    private const _QUERY_LIMIT = 50;
    private const _SUGGESTED_ADVICES_LIMIT = 3;
    protected const _ARRAY_MENU_ITEMS = ['Companies Index', 'Companies', 'Questions', 'Messages', 'Settings', 'LOGOUT'];
    protected const _ARRAY_FILE_NAMES = ['companies_index.html', 'companies_asking.html', 'questions_asking.html', 'messages_in.html', 'settings.html'];
    protected const _SUB_DIR = 'asking';
    
    // CONSTRUCTOR
    public function __construct(User $user) {
        parent::__construct($user);
    }
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
       
    // Checks if a question is closed
    public function isQuestionClosed(int $question_id): bool {
        
        $sql = 'SELECT is_closed AS result FROM questions WHERE que_id=:question_id';
        $data = ['question_id' => $question_id];
        
        $que_st = $this->_db->singleQueryRetStatement($sql, $data);
        $data_array = $que_st->fetch();
        
        $result = (bool) $data_array['result'];
        
        return $result;
    }
    
    // Create question object
    public function createQuestionObject(array $question_details) {
        
        $question = Question::CreateQuestion($question_details, $this->getCurrentUser()->getId());
        
        return $question;
    }
    
    // Create response object
    public function createResponseObject(array $response_details) {
        
        $response = Response::CreateResponse($response_details);
        
        return $response;
    }
    
    // Count user daily questions
    public function countUserDailyQuestions(): int {
        
        $sql = 'SELECT COUNT(*) AS result FROM questions WHERE user_id=:self_id AND date_opened>CURDATE()-1';
        $data = ['self_id' => $this->_user->getId()];

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        return (int)$data_array['result'];
    }
    
    // Checks if response is has been given
    public function isResponseGiven(int $question_id, int $advice_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM responses'
            . ' WHERE que_id=:question_id AND adv_id=:advice_id';
        $data = [
            'question_id' => $question_id,
            'advice_id' => $advice_id
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Add suggested advice
    public function addSuggestedAdvice(int $advice_id, int $question_id): bool {
        
        $sql = 'INSERT INTO advice_question (que_id, adv_id, is_new, is_auto_suggested)'
            . ' VALUES (:que_id, :adv_id, :is_new, :is_auto_suggested)';

        $data = [
            'que_id' => $question_id,
            'adv_id' => $advice_id,
            'is_new' => 1,
            'is_auto_suggested' => 1
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
        
    // Set question-advice status
    public function setQuestionAdviceStatus(int $advice_id, int $question_id, bool $is_new = false): bool {
        
        $sql = 'UPDATE advice_question SET is_new=:is_new WHERE adv_id=:advice_id AND que_id=:question_id';
        $data = [
            'advice_id' => $advice_id,
            'question_id' => $question_id,
            'is_new' => $is_new == true ? 1 : 0
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Find best advices of similiar questions
    public function suggestedAdvices(Question $question) {
        
        $result = null;
        
        $similiar_questions_ids = $this->_findSimiliarQuestionsIds($question->getType());  // find similliar questions ids that has best advices
        
        $similiar_questions = $this->_loadQuestionsByIds($similiar_questions_ids);  // load questions by id's
            
        // similliar questions found
        if ($similiar_questions) {
            
            // store similliar questions in array where key is identity percentage
            /* @var $similiar_question Question */
            foreach ($similiar_questions as $similiar_question) {

                $key = $question->getTechData()->compareTo($similiar_question->getTechData());

                $compared_questions[] = [$key => $similiar_question];
            }

            ksort($compared_questions, SORT_DESC);    // sort the array

            for ($i = 0; $i < count($compared_questions); $i++){

                // add best advices to result
                foreach ($compared_questions[$i] as $compared_question) {

                    if ($i < self::_SUGGESTED_ADVICES_LIMIT) {

                        /* @var $best_advice Advice */
                        $best_advice = $this->_findQuestionBestAdvice($compared_question->getId());

                        $advice_row['advice_details'] = Advice::ToArray($best_advice);
                        $advice_row['advice_details']['manufacturer'] = $this->_getCompanyNameByUserId($best_advice->getUserId());
                        $advice_row['advice_tech_data'] = AdviceTechnicalDetails::ToArray($best_advice->getTechData());
                        $advice_row['advice_details']['question_id'] = $question->getId();

                        $result[] = $advice_row;

                    } else { break; }
                }
            }
        }
        
        return $result;
    }
    
    // Add a question to the db
    public function addQuestion(Question $question): bool {
        
        $sql = 'INSERT INTO questions (user_id, type, title, description, tech_data, is_closed, is_visible)'
            . ' VALUES (:user_id, :type, :title, :description, :tech_data, :is_closed, :is_visible)';

        $data = [
            'user_id' => $this->_user->getId(),
            'type' => $question->getType(),
            'title' => $question->getTitle(),
            'description' => $question->getDescription(),
            'tech_data' => serialize($question->getTechData()),
            'is_closed' => 0,
            'is_visible' => 1
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Block company 
    public function blockCompany(int $company_id): bool {
        
        $sql = 'INSERT INTO user_blocked_companies (company_id, user_id)'
            .' VALUES (:company_id, :self_id)';
        $data = [
            'company_id' => $company_id,
            'self_id' => $this->_user->getId()
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Unblock company 
    public function unblockCompany(int $company_id): bool {
        
        $sql = 'DELETE FROM user_blocked_companies WHERE company_id=:company_id AND user_id=:self_id';
        $data = [
            'company_id' => $company_id,
            'self_id' => $this->_user->getId()
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
        
    // Close question
    public function closeQuestion(int $question_id): bool {
        
        $sql = 'UPDATE questions SET is_closed=1, date_closed=NOW() WHERE que_id=:question_id';
        $data = ['question_id' => $question_id];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Add a response to the db
    public function addResponse(Response $response): bool {
        
        $sql = 'INSERT INTO responses (adv_id, que_id, title, description, score, is_best_advice, is_new)'
        .' VALUES (:advice_id, :question_id, :title, :description, :score, :is_best_advice, :is_new)';

        $data = [
            'advice_id' => $response->getAdviceId(),
            'question_id' => $response->getQuestionId(),
            'title' => $response->getTitle(),
            'description' => $response->getDescription(),
            'score' => $response->getScore(),
            'is_best_advice' => $response->isBestAdvice() == true ? 1 : 0,
            'is_new' => 1
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Get current user last question
    public function getCurrentUserLastQuestion() {
        
        $sql = 'SELECT * FROM questions WHERE user_id=:user_id ORDER BY date_opened DESC LIMIT 1';
        $data = [':user_id' => $this->_user->getId()];

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $question = $query_st->fetchObject('Question');
        
        return $question;
    }
    
    // Find id's of question suited companies -non blocked and approved
    public function idsOfQuestionSuitedCompanies(Question $question): array {
        
        $result = [];
        
        $q_types = explode(' ', $question->getType(), 2);
        $main_specialty = $q_types[0];
        
        $sql = 'SELECT company_id AS result FROM companies WHERE is_blocked=0 AND is_approved=1'
            . ' AND INSTR(company_specialties, :specialty)'
            . ' AND company_id NOT IN (SELECT company_id FROM user_blocked_companies WHERE user_id=:user_id)';

        $data = [
            'specialty' => $main_specialty,
            'user_id' => $question->getUserId()
        ];
        
        $que_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_arr = $que_st->fetchAll();

        foreach ($data_arr as $arr) {
            $result[] = $arr['result'];
        }
        
        return $result;
    }
    
    // Find advising user -by advice id
    public function findAdvisingUserByAdviceId(int $advice_id) {
        
        $sql = 'SELECT * FROM users AS us INNER JOIN advices AS adv ON us.user_id=adv.user_id WHERE adv_id=:advice_id';
        $data = [':advice_id' => $advice_id];

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $user = $query_st->fetchObject('User');
        
        return $user;
    }
    
    // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">

    // <editor-fold defaultstate="collapsed" desc="PAGE DATA">

    // Get content of a specific page
    protected function _getPageContent(int $index) {
        
        switch ($index) {
            
            case 0:
                $data = $this->_getTabCompaniesIndex();
                break;
            
            case 1:
                $data = $this->_getTabCompanies();
                break;
            
            case 2:
                $data = $this->_getTabQuestions();
                break;
            
            case 3:
                $data = $this->_getTabMessages();
                break;
            
            case 4:
                $data = $this->_getTabSettings();
                break;

            default:
                $data = null;
        }
        
        return $data;
    }
    
    // Get tab: 'Companies'
    private function _getTabCompanies(): array {
        
        $result = null;
        
        $companies = $this->_getCompanies(false, false, true);    // get verified, non blocked companies
        
        /* @var $company Company */
        foreach ($companies as $company) {
            
            $is_blocked = $this->_isCompanyBlockedByCurrentUser($company->getId());     // if company blocked by current user
            
            $company_details_row = Company::ToArray($company);
            $company_details_row['is_blocked'] = $is_blocked;
            
            $result[] = $company_details_row;   // add to result
        }
        
        return ['companies' => $result];
    }
    
    // Get tab: 'Questions'
    private function _getTabQuestions(): array {
        
        $result = null;
        
        $questions = $this->_getAllUserQuestions();     // only visible questions
        
        /* @var $question Question */
        foreach ($questions as $question) {
            
            // add to the result
            $question_details_row = Question::ToArray($question);
            
            $question_details_row['has_advices'] = $this->_questionHasAdvices($question->getId());
            
            $result[] = $question_details_row;
        }
        
        return ['questions' => $result];
    }
    
    // Get tab: 'Messages'
    private function _getTabMessages(): array {
        
        $result = $this->_getCurrentUserInMessages();     // in messages
        
        return $result;
    }
    
    // Get tab: 'Settings'
    private function _getTabSettings(): array {
        
        $result['user_settings'] = $this->_user->getSettings();
        
        return ['settings' => $result];
    }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="GET DATA">

    // Get specific user data
    protected function _getSpecificUserData(array $data_array) {
        
        switch ($data_array['data_name']) {
            
            default:
                $data = null;
        }
        
        return $data;
    }
    
    // </editor-fold>
    
    // Checks if a question has advices
    private function _questionHasAdvices(int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0 AS result FROM advice_question WHERE que_id=:question_id';
        $data = ['question_id' => $question_id];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }

    // Get advices of a question as formated array -with additional fields regarding to the module
    protected function _getQuestionAdvicesFormatedArray(int $question_id): array {
        
        $result = null;
        
        $advices = $this->_getQuestionAdvices($question_id);  //all advices (from non blocked users)
        
        /* @var $advice Advice */
        foreach ($advices as $advice) {
           
            $advising_user = $this->findUserByUserId($advice->getUserId());
            $advising_company_name = $this->_getCompanyNameByUserId($advice->getUserId());
            
            $advice_details_row = Advice::ToArray($advice);
            
            $advice_details_row['company'] = $advising_company_name;
            $advice_details_row['given_by'] = User::ToArray($advising_user);
            $advice_details_row['has_response'] = $this->_checkIfAdviceHasResponse($advice->getId(), $question_id);
            
            $result[] = $advice_details_row;
        }
        
        return $result;
    }
    
    // Get advices of a question -all advices (from non blocked users)
    private function _getQuestionAdvices(int $question_id) {
        
        $sql = 'SELECT * FROM advices AS adv'
            . ' INNER JOIN advice_question AS aq ON adv.adv_id=aq.adv_id'
            . ' INNER JOIN users AS u ON adv.user_id=u.user_id'
            . ' WHERE aq.que_id=:question_id AND u.is_blocked=0';
        $data = ['question_id' => $question_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $advices = $query_st->fetchAll(PDO::FETCH_CLASS, 'Advice');
        }
        
        return $advices ?? null;
    }
    
    // Find questions by id's
    private function _loadQuestionsByIds(array $question_ids_array) {
        
        if (!empty($question_ids_array)) {
            
            $question_ids = implode(' ', $question_ids_array);

            $sql = 'SELECT * FROM questions WHERE INSTR(:question_ids, que_id) > 0';
            $data = [':question_ids' => $question_ids];

            $query_st = $this->_db->singleQueryRetStatement($sql, $data);

            $questions = $query_st->fetchAll(PDO::FETCH_CLASS, 'Question');
        }
        
        return $questions ?? null;
    }
    
    // Find best advice of a question
    private function _findQuestionBestAdvice(int $question_id) {
        $sql = 'SELECT *, a.description, a.title FROM advices AS a'
            . ' INNER JOIN responses AS r ON a.adv_id=r.adv_id'
            . ' WHERE r.que_id=:question_id AND r.is_best_advice=1';
        $data = ['question_id' => $question_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $advice = $query_st->fetchObject('Advice');
        
        return $advice;
    }

    // Get user questions -visible
    private function _getAllUserQuestions() {
        
        $sql = 'SELECT * FROM questions WHERE user_id=:user_id AND is_visible=1';
        $data = ['user_id' => $this->_user->getId()];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $questions = $query_st->fetchAll(PDO::FETCH_CLASS, 'Question');
        }
        
        return $questions ?? null;
    }
    
    // Find similiar questions id's that has best advices -for current user (from non blocked companies by the user)
    private function _findSimiliarQuestionsIds(string $question_type): array {
        
        $result = [];
        
        $sql = 'SELECT DISTINCT a.adv_id, q.que_id AS result FROM questions AS q'
            . ' INNER JOIN advice_question AS aq ON q.que_id=aq.que_id'
            . ' INNER JOIN responses AS r ON aq.adv_id=r.adv_id'
            . ' INNER JOIN advices AS a ON aq.adv_id=a.adv_id'
            . ' WHERE q.is_visible=1 AND q.is_closed=1 AND r.is_best_advice=1 AND q.type=:type AND a.user_id'
            . ' NOT IN (SELECT u.user_id FROM users AS u'
            . ' INNER JOIN company_users AS cu ON u.user_id=cu.user_id'
            . ' INNER JOIN companies AS c ON cu.company_id=c.company_id'
            . ' WHERE u.is_blocked=1 OR c.is_blocked=1 OR c.company_id'
            . ' IN (SELECT ubc.company_id FROM user_blocked_companies AS ubc WHERE ubc.user_id=:self_id))'
            . ' GROUP BY a.adv_id ORDER BY q.date_closed DESC'
            . ' LIMIT '.self::_QUERY_LIMIT;
        $data = [
            'type' => $question_type,
            'self_id' => $this->_user->getId()
        ];
        
        $que_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_arr = $que_st->fetchAll();

        foreach ($data_arr as $arr) {
            $result[] = $arr['result'];
        }
        
        return $result;
    }

    // Find similiar questions -for current user (from non blocked companies by the user)
    private function _isCompanyBlockedByCurrentUser(int $company_id): bool {
        
        $sql = 'SELECT company_id AS result FROM user_blocked_companies WHERE user_id=:self_id AND company_id=:company_id';
        $data = [
            'company_id' => $company_id,
            'self_id' => $this->_user->getId()
        ];
        
        $que_st = $this->_db->singleQueryRetStatement($sql, $data);
        $data_array = $que_st->fetch();
        
        $result = !empty($data_array['result']);
        
        return $result;
    }

    // </editor-fold>
    
}
