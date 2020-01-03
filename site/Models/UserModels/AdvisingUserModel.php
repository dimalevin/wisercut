<?php

/*
 * Advising user model
 * 
 * Provides advising user data operations and db connection
 */
class AdvisingUserModel extends UserModel {
    
    /* Constants */
    protected const _ARRAY_MENU_ITEMS = ['Companies Index', 'Questions', 'Messages', 'Settings', 'LOGOUT'];
    protected const _ARRAY_FILE_NAMES = ['companies_index.html', 'questions_advising.html', 'messages_in.html', 'settings.html'];
    protected const _SUB_DIR = 'advising';
    
    // CONSTRUCTOR
    public function __construct(User $user) {
        parent::__construct($user);
    }

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    
    // Create advice object
    public function createAdviceObject(array $advice_details) {
        
        $advice = Advice::CreateAdvice($advice_details, $this->getCurrentUser()->getId());
        
        return $advice;
    }
    
    // Load current user last advice
    public function currentUserLastAdvice() {
        
        $sql = 'SELECT * FROM advices AS a WHERE a.user_id=:self_id ORDER BY a.date_created DESC LIMIT 1';
        $data = ['self_id' => $this->_user->getId()];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $advice = $query_st->fetchObject('Advice');
        
        return $advice;
    }
    
    // Checks if advice is has been given
    public function isAdviceGiven(int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM advice_question AS aq'
            . ' INNER JOIN advices AS adv ON aq.adv_id=adv.adv_id'
            . ' WHERE aq.que_id=:question_id AND adv.user_id=:self_id';
        $data = [
            'question_id' => $question_id,
            'self_id' => $this->_user->getId()
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Add an advice to the db
    public function addAdvice(Advice $advice): bool {
        
        $sql = 'INSERT INTO advices (user_id, title, description, tech_data)'
        .' VALUES (:user_id, :title, :description, :tech_data)';

        $data = [
            'user_id' => $this->_user->getId(),
            'title' => $advice->getTitle(),
            'description' => $advice->getDescription(),
            'tech_data' => serialize($advice->getTechData()),
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Link advice to a question
    public function linkAdviceToQuestion(int $advice_id, int $question_id, bool $is_auto_suggested = false): bool {
        
        $sql = 'INSERT INTO advice_question (que_id, adv_id, is_new, is_auto_suggested)'
        .' VALUES (:que_id, :adv_id, :is_new, :is_auto_suggested)';

        $data = [
            'que_id' => $question_id,
            'adv_id' => $advice_id,
            'is_new' => 1,
            'is_auto_suggested' => $is_auto_suggested == true ? 1 : 0
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Remove advice
    public function removeAdvice(int $advice_id): bool {
        
        $sql = 'DELETE FROM advices WHERE adv_id=:advice_id';

        $data = ['advice_id' => $advice_id];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
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
                $data = $this->_getTabQuestions();
                break;
            case 2:
                $data = $this->_getTabMessages();
                break;
            case 3:
                $data = $this->_getTabSettings();
                break;

            default:
                $data = null;
        }
        
        return $data;
    }
    
    // Get tab: 'Questions'
    private function _getTabQuestions(): array {
        
        $result = null;
        
        $company_id = $this->getCompanyIdByUserId($this->_user->getId());  // company id of the current user
        
        $company_questions = $this->_getCompanyQuestions($company_id);  // company questions (visible only)
        
        /* @var $question Question */
        foreach ($company_questions as $question) {
            
            // the company is not blocked by the asking user
            if (!$this->_companyIsBlockedByUser($question->getUserId(), $company_id)) {
            
                $asking_user = $this->findUserByUserId($question->getUserId());
                
                $question_details_row = Question::ToArray($question);

                $question_details_row['asked_by'] = User::ToArray($asking_user);

                $question_details_row['has_response'] = $this->_questionHasResponse($question->getId());

                $question_details_row['advice_given'] = $this->_isUserGaveAdviceToQuestion($question->getId());

                $result[] = $question_details_row;  // add to the result
            }
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
    
    // Checks if a question has responses -to current user
    private function _questionHasResponse(int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM responses AS res'
            . ' INNER JOIN advices AS adv ON res.adv_id=adv.adv_id'
            . ' WHERE res.que_id=:question_id AND adv.user_id=:self_id';
        $data = [
            'question_id' => $question_id,
            'self_id' => $this->_user->getId()
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Checks if current user gave advice to a question
    private function _isUserGaveAdviceToQuestion(int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0 AS result FROM advice_question AS aq'
            . ' INNER JOIN advices AS adv ON aq.adv_id=adv.adv_id'
            . ' WHERE aq.que_id=:question_id AND adv.user_id=:self_id';
        $data = [
            'question_id' => $question_id,
            'self_id' => $this->_user->getId()
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Get advices of a question as formated array -with additional fields regarding to the module
    protected function _getQuestionAdvicesFormatedArray(int $question_id): array {
        
        $advice = $this->_getQuestionSelfAdvice($question_id);    //self advice
        $advice_details_row = null;
        
        if ($advice) {
            $advice_details_row = Advice::ToArray($advice);
            $advice_details_row['has_response'] = $this->_checkIfAdviceHasResponse($advice->getId(), $question_id);
        }
        
        return ['advices' => $advice_details_row];
    }
    
    // Get advice of a question -self advice
    private function _getQuestionSelfAdvice(int $question_id) {
        
        $sql = 'SELECT * FROM advices AS adv'
            . ' INNER JOIN advice_question AS aq ON adv.adv_id=aq.adv_id'
            . ' WHERE aq.que_id=:question_id AND adv.user_id=:self_id';
        $data = [
            'question_id' => $question_id,
            'self_id' => $this->_user->getId()
        ];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $advice = $query_st->fetchObject('Advice');
        
        return $advice;
    }
    
    // </editor-fold>
}
