<?php

/*
 * Admin user model
 * 
 * Provides admin user data operations and db connection
 */
class AdminUserModel extends UserModel {

    /* Constants */
    protected const _ARRAY_MENU_ITEMS = ['Companies Index', 'Users', 'Companies', 'Questions', 'Messages', 'Settings', 'LOGOUT'];
    protected const _ARRAY_FILE_NAMES = ['companies_index.html', 'users_admin.html', 'companies_admin.html', 'questions_admin.html', 'messages.html', 'settings_admin.html'];
    protected const _SUB_DIR = 'admin';
    
    // CONSTRUCTOR
    public function __construct(User $user) {
        parent::__construct($user);
    }
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    
    // Approve company
    public function approveCompany(int $company_id): bool {
        
        $sql = 'UPDATE companies SET is_approved=1, date_approved=NOW() WHERE company_id=:company_id AND is_blocked=0';
        $data = ['company_id' => $company_id];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Set company status
    public function setCompanyStatus(int $company_id, bool $is_blocked): bool {
        
        $sql = 'UPDATE companies SET is_blocked=:is_blocked WHERE company_id=:company_id AND is_approved=1';
        $data = [
            'company_id' => $company_id,
            'is_blocked' => $is_blocked == true ? 1 : 0
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Set user status
    public function setUserStatus(int $user_id, bool $is_blocked): bool {
        
        $sql = 'UPDATE users SET is_blocked=:is_blocked WHERE user_id=:user_id AND is_approved=1';
        $data = [
            'user_id' => $user_id,
            'is_blocked' => $is_blocked == true ? 1 : 0
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Set user status
    public function setQuestionStatus(int $question_id, bool $status): bool {
        
        $sql = 'UPDATE questions SET is_visible=:status WHERE que_id=:question_id';
        $data = [
            'question_id' => $question_id,
            'status' => $status == true ? 1 : 0
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
            
        return $result;
    }
    
    // Find company -by company id
    public function findCompanyById(int $company_id) {
        
        $sql = 'SELECT * FROM companies WHERE company_id=:company_id';
        $data = ['company_id' => $company_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $company = $query_st->fetchObject('Company');
        
        return $company;
    }
    
    // Find question -by id
    public function findQuestionById(int $question_id) {
        
        $sql = 'SELECT * FROM questions WHERE que_id=:question_id';
        $data = ['question_id' => $question_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $question = $query_st->fetchObject('Question');
        
        return $question;
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
                $data = $this->_getTabUsers();
                break;
            
            case 2:
                $data = $this->_getTabCompanies();
                break;
            
            case 3:
                $data = $this->_getTabQuestions();
                break;
            
            case 4:
                $data = $this->_getTabMessages();
                break;
            
            case 5:
                $data = $this->_getTabSettings();
                break;

            default:
                $data = null;
        }
        
        return $data;
    }
    
    // Get tab: 'Users'
    private function _getTabUsers(): array {
        
        $result = null;
        
        $all_users = $this->_getAllUsers();
        
        // users found
        if (!empty($all_users)) {
            
            /* @var $user User */
            foreach ($all_users as $user) {
                
                $company_name = $this->_getCompanyNameByUserId($user->getId());
                
                $user_details_row = User::ToArray($user);
                $user_details_row['company'] = $company_name;
                
                $result[] = $user_details_row;  // add to result
            }
        }
    
        return ['users' => $result];
    }
    
    // Get tab: 'Companies'
    private function _getTabCompanies(): array {
        
        $result = null;
        
        $companies = $this->_getCompanies(true);    // get all companies
        
        /* @var $company Company */
        foreach ($companies as $company) {
            
            // Manager details
            
            /* @var $manager User */
            $manager = $this->findUserByUserId($company->getManagerId());
            $manager_details = User::ToArray($manager);
            
            // Company details
            $company_details_row = Company::ToArray($company);
            $company_details_row['manager_details'] = $manager_details;
            
            $result[] = $company_details_row;   // add to result
        }
        
        return ['companies' => $result];
    }
    
    // Get tab: 'Questions'
    private function _getTabQuestions(): array {
        
        $result = null;
        
        $questions = $this->_getAllQuestions();    // all questions
        
        /* @var $question Question */
        foreach ($questions as $question) {
            
            $asking_user = $this->findUserByUserId($question->getUserId());
            
            $question_details_row = Question::ToArray($question);
            $question_details_row['asked_by'] = User::ToArray($asking_user);
            $question_details_row['has_advices'] = $this->_questionHasAdvices($question->getId());
            
            $result[] = $question_details_row;  // add to the result
        }
        
        return ['questions' => $result];
    }
    
    // Get tab: 'Messages'
    private function _getTabMessages(): array {
        
        $result[] = $this->_getCurrentUserInMessages();     // in messages
        $result[] = $this->_getCurrentUserOutMessages();    // out messages
        
        return ['messages' => $result];
    }
    
    // Get tab: 'Settings'
    private function _getTabSettings(): array {
        
        $result['user_settings'] = $this->_user->getSettings();
        $result['system_settings'] = SystemSettings::GetSettings();
        
        return ['settings' => $result];
    }
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="GET DATA">
    
    // Get specific user data
    protected function _getSpecificUserData(array $data_array) {
        
        switch ($data_array['data_name']) {

            case 'recipients':
                $data = $this->_getDataRecipients();
                break;
        
            default:
                $data = null;
        }
        
        return $data;
    }
    
    // Get data -recipients
    private function _getDataRecipients() {
        
        $result = null;
        
        $sql = 'SELECT * FROM users WHERE is_blocked=0 AND is_approved=1 AND user_id!=:self_id';
        $data = ['self_id' => $this->_user->getId()];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $users = $query_st->fetchAll(PDO::FETCH_CLASS, 'User');
        } else {
            $users = null;
        }
        
        // convert to array
        foreach ($users as $user) {
            
            $result[] = User::ToArray($user);
        }
        
        return $result;
    }
    
    // </editor-fold>
    
    // Get advices of a question as formated array -with additional fields regarding to the module
    protected function _getQuestionAdvicesFormatedArray(int $question_id): array {
        
        $result = null;
        
        $advices = $this->_getQuestionAdvices($question_id);    // all advices (from all users)

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
        
        
        return ['advices' => $result];
    }
    
    // Get advices of a question -all advices (from all users)
    private function _getQuestionAdvices(int $question_id) {
        
        $sql = 'SELECT * FROM advices AS adv'
            . ' INNER JOIN advice_question AS aq ON adv.adv_id=aq.adv_id'
            . ' WHERE aq.que_id=:question_id';
        $data = ['question_id' => $question_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $advices = $query_st->fetchAll(PDO::FETCH_CLASS, 'Advice');
        }
        
        return $advices ?? null;
    }
    
    // Get users
    private function _getAllUsers() {
        
        $sql = 'SELECT * FROM users WHERE user_id!=:self_id';
        $data = ['self_id' => $this->_user->getId()];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $users = $query_st->fetchAll(PDO::FETCH_CLASS, 'User');
        }
        
        return $users ?? null;
    }

    // Get questions
    private function _getAllQuestions() {
        
        $sql = 'SELECT * FROM questions';
        
        $query_st = $this->_db->singleQueryRetStatement($sql);
        
        if ($query_st) {
            $questions = $query_st->fetchAll(PDO::FETCH_CLASS, 'Question');
        }
        
        return $questions ?? null;
    }
    
    // Checks if question has advices
    private function _questionHasAdvices(int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM advice_question WHERE que_id=:question_id';
        $data = ['question_id' => $question_id];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // </editor-fold>

}
