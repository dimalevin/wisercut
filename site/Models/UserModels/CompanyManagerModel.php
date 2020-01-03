<?php

/*
 * Company manager model
 * 
 * Provides company manager data operations and db connection
 */
class CompanyManagerModel extends UserModel {
    
    /* Constants */
    protected const _ARRAY_MENU_ITEMS = ['Companies Index', 'Users', 'Questions', 'Messages', 'Settings', 'LOGOUT'];
    protected const _ARRAY_FILE_NAMES = ['companies_index.html', 'users_company.html', 'questions_company.html', 'messages.html', 'settings_company.html'];
    protected const _SUB_DIR = 'company';
    
    // PROTECTED PROPERTIES
    protected $_company;

    // CONSTRUCTOR
    public function __construct(User $user) {
        parent::__construct($user);
        
        // set company
        $this->_company = $this->_findCompanyByManagerId($user->getId());
    }
    
    // GETTERS

    // Get current company
    public function getCurrentCompany() { return $this->_company; }
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    
    // Block user -non blocked and approved
    public function blockUser(int $user_id): bool {
        
        $sql = 'UPDATE users SET is_blocked=1 WHERE user_id=:user_id AND is_approved=1';
        $data = ['user_id' => $user_id];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Update company settings
    public function updateCompanySettings(array $data_array): bool {
        
        $company_description = $data_array['company_description'];
        $company_specialties = $data_array['company_specialties'];
        $logo = $data_array['logo_filename'];
        
        $sql = 'UPDATE companies SET company_description=:company_description, company_specialties=:company_specialties, logo=:logo'
            . ' WHERE company_id=:company_id';
        $data = [
            'company_id' => $this->_company->getId(),
            'company_description' => $company_description,
            'company_specialties' => $company_specialties,
            'logo' => $logo
        ];
        
        $query_res = $this->_db->singleQueryRetResult($sql, $data);
        
        // query succeeded
        if ($query_res) {
            $this->_company->setSettings($data_array);
        }
        
        return $query_res;
    }

    // Create new user activation link
    public function companyActivationLink(string $username): string {

        $passphrase = self::_CreateActivationPassphrase($username);
        $page_url = SystemConstants::ACTIVATION_LINK_PATHS[SystemConstants::NEW_COMPANY_USER_ACTIVATION];
        $base_url = Helper::GetBaseUrl();
        $company_id = $this->_company->getId();
        
        $link = $base_url.$page_url.'?username='.$username.'&passphrase='.$passphrase.'&id='.$company_id;
        
        return $link;
    }
    
    // Add to the db -request for adding user to a company
    public function addUserToCompanyRequest(int $company_id, int $user_id): bool {

        $sql = 'INSERT INTO add_to_company_requests (company_id, user_id)'
        .' VALUES (:company_id, :user_id)';

        $data = [
            'company_id' => $company_id,
            'user_id' => $user_id
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Remove from the db -request for adding user to a company
    public function removeUserToCompanyRequest(int $company_id, int $user_id): bool {

        $sql = 'DELETE FROM add_to_company_requests WHERE company_id=:company_id AND user_id=:user_id)';

        $data = [
            'company_id' => $company_id,
            'user_id' => $user_id
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
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
    
    // Get tab: 'Users'
    private function _getTabUsers(): array {
        
        $result = null;
        
        $company_users = $this->_getCompanyUsers($this->_company->getId());    // all company users
        
        // users found
        if (!empty($company_users)) {
            
            /* @var $user User */
            foreach ($company_users as $user) {

                $date_joined = $this->_getUserJoinDateToCompany($user->getId());
                
                // user details
                $user_details_row = User::ToArray($user);
                $user_details_row['date_joined'] = $date_joined;
                
                $result[] = $user_details_row;  // add to result
            }
        }
        
        return ['users' => $result];
    }
    
    // Get tab: 'Messages'
    private function _getTabMessages(): array {
        
        $result[] = $this->_getCurrentUserInMessages();     // in messages
        $result[] = $this->_getCurrentUserOutMessages();    // out messages
        
        return ['messages' => $result];
    }
    
    // Get tab: 'Questions'
    private function _getTabQuestions(): array {
        
        $result = null;
        
        $company_questions = $this->_getCompanyQuestions($this->_company->getId());    // not blocked questions
        
        /* @var $question Question */
        foreach ($company_questions as $question) {
            
            // the company is not blocked by the asking user
            if (!$this->_companyIsBlockedByUser($question->getUserId(), $this->_company->getId())) {
                $asking_user = $this->findUserByUserId($question->getUserId());

                $question_details_row = Question::ToArray($question);

                $question_details_row['asked_by'] = User::ToArray($asking_user);

                $question_details_row['has_advices'] = $this->_questionHasAdvices($question->getId());

                $result[] = $question_details_row;  // add to the result
            }
        }
        
        return ['questions' => $result];
    }
    
    // Get tab: 'Settings'
    private function _getTabSettings(): array {
        
        $result['user_settings'] = $this->_user->getSettings();
        $result['company_settings'] = $this->_company->getSettings();
        $result['allowed_specialties'] = Company::COMPANY_SPECIALTIES;
        
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
    
    // Get recipients -users of same company (not blocked and approved) and asking user (not blocked and approved that hasn't blocked this company)
    private function _getDataRecipients() {
        
        $result = null;
        $sql = 'SELECT * FROM users AS u WHERE u.user_id'
            . ' IN(SELECT u.user_id FROM users AS u INNER JOIN company_users AS cu ON u.user_id=cu.user_id'
            . ' WHERE cu.company_id=:company_id AND u.is_blocked=0 AND u.is_approved=1 AND u.user_id!=:self_id)'
            . ' OR user_id IN (SELECT user_id FROM users WHERE is_approved=1 AND is_blocked=0 AND user_type="asking"'
            . ' AND user_id NOT IN(SELECT user_id FROM user_blocked_companies WHERE company_id=:company_id))';
        $data = [
            'company_id' => $this->_company->getId(),
            'self_id' => $this->_user->getId()
        ];
        
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
    
    // Checks if a question has advices of current company users
    private function _questionHasAdvices(int $question_id): bool {
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM advice_question AS aq'
            . ' INNER JOIN advices AS adv ON aq.adv_id=adv.adv_id'
            . ' INNER JOIN company_users AS cu ON adv.user_id=cu.user_id'
            . ' WHERE aq.que_id=:question_id AND cu.company_id=:company_id';
        $data = [
            'question_id' => $question_id,
            'company_id' => $this->_company->getId()
        ];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Get advices of a question as formated array -with additional fields regarding to the module
    protected function _getQuestionAdvicesFormatedArray(int $question_id): array {
        
        $result = null;
        
        $advices = $this->_getQuestionAdvices($question_id);    //all advices given by users (non blocked) in that company
        
        /* @var $advice Advice */
        foreach ($advices as $advice) {
            
            $advising_user = $this->findUserByUserId($advice->getUserId());
            
            $advice_details_row = Advice::ToArray($advice);
            
            $advice_details_row['given_by'] = User::ToArray($advising_user);
            $advice_details_row['has_response'] = $this->_checkIfAdviceHasResponse($advice->getId(), $question_id);
            
            $result[] = $advice_details_row;
        }
        
        return ['advices' => $result];
    }
    
    // Get advices of a question -all advices given by users (non blocked) in that company
    private function _getQuestionAdvices(int $question_id) {

        //all advices given by users (non blocked) in that company
        
        $sql = 'SELECT * FROM advices AS adv'
            . ' INNER JOIN advice_question AS aq ON adv.adv_id=aq.adv_id'
            . ' INNER JOIN company_users AS cu ON adv.user_id=cu.user_id'
            . ' INNER JOIN users AS u ON cu.user_id=u.user_id'
            . ' WHERE aq.que_id=:question_id AND cu.company_id=:company_id AND u.is_blocked=0';
        $data = [
            'question_id' => $question_id,
            'company_id' => $this->_company->getId()
        ];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $advices = $query_st->fetchAll(PDO::FETCH_CLASS, 'Advice');
        }
        
        return $advices ?? null;
    }

    // Get users of specific company -by company id
    private function _getCompanyUsers(int $company_id) {
        
        $sql = 'SELECT * FROM users AS u INNER JOIN company_users AS cu ON u.user_id=cu.user_id'
            . ' WHERE cu.company_id=:company_id AND u.user_id!=:self_id';
        $data = [
            'company_id' => $company_id,
            'self_id' => $this->_user->getId()
        ];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        if ($query_st) {
            $users = $query_st->fetchAll(PDO::FETCH_CLASS, 'User');
        }
        
        return $users ?? null;
    }
    
    // Get user join date to company
    private function _getUserJoinDateToCompany(int $user_id) {
        
        $sql = 'SELECT date_joined AS result FROM company_users WHERE user_id=:user_id';
        $data = [':user_id' => $user_id];

        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        $result = $data_array['result'];
        
        return $result;
    }
    
    // </editor-fold>
}
