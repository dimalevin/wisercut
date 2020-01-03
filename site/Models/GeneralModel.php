<?php

/*
 * General model
 * 
 * Used when the system loads till user login.
 * Provides appropriate data operations and db connection.
 */
class GeneralModel extends Model {
    
    /* Constants */
    protected const _ARRAY_MENU_ITEMS = ['Home', 'About', 'Contact us', 'Registration', 'Login'];
    protected const _ARRAY_FILE_NAMES = ['home.html', 'about.html', 'contact.html', 'register.html', 'login.html'];
    protected const _SUB_DIR = 'default';
    
    // CONSTRUCTOR
    public function __construct() {
        parent::__construct();
    }
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Get specific data
    public function getData(array $data_array) {
        
        switch ($data_array['data_name']) {

            default:
                $data = null;
        }
        
        return $data;
    }
    
    // Create company object
    public function createCompanyObject(array $company_details, int $manager_id) {
        
        $company = Company::CreateCompany($company_details, $manager_id);
        
        return $company;
    }
    
    // Create user object
    public function createUserObject(array $user_details) {
        
        $user = User::CreateUser($user_details);
        
        return $user;
    }
    
    // Add new user
    public function addNewUser(User $user_to_add): bool {
        
        $user_to_add->setPassword(Model::PasswordHash($user_to_add->getPassword()));    // hash password
        
        $result = $this->_addUser($user_to_add);    // add user to the db
        
        return $result;     
    }
    
    // Remove an user from the db
    public function removeUserByUsername(string $username): bool {
    
        $sql = 'DELETE FROM users WHERE username=:username';
        $data = ['username' => $username];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }

    // Add new company
    public function addNewCompany(Company $company_to_add): bool {
        
        $result = false;
        
        // manager id is valid
        if ($company_to_add->getManagerId() > 0) {
            
            $result = $this->_addCompany($company_to_add);
        }
            
        return $result;     
    }
    
    // Remove company from the database
    public function removeCompanyByManagerId(int $manager_id): bool {
            
        $sql = 'DELETE FROM companies WHERE manager_id=:manager_id';
        $data = ['manager_id' => $manager_id];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Add manager to the company users
    public function addManagerToCompanyUsers(int $manager_id): bool {
        
        $company = $this->_findCompanyByManagerId($manager_id);     // find company

        $result = $this->_addUserToCompanyUsers($manager_id, $company->getId());

        return $result;
    }
    
    // Remove user from the company users
    public function removeUserFromCompanyUsers(int $user_id): bool {
        
        $sql = 'DELETE FROM company_users WHERE user_id=:user_id';
        $data = ['user_id' => $user_id];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Update user password
    public function updateUserPassword(int $user_id, string $new_password): bool {
        
        $sql = 'UPDATE users SET user_psw=:new_password WHERE user_id=:user_id';
        $data = [
            'new_password' => $new_password,
            'user_id' => $user_id
        ];
        
        $result = $this->_db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Get company blocked status -by user id
    public function getCompanyBlockedStatusByUserId(int $user_id): bool {
            
        $sql = 'SELECT cmp.is_blocked AS result FROM companies AS cmp'
            . ' INNER JOIN company_users AS cmpu ON cmp.company_id=cmpu.company_id'
            . ' WHERE cmpu.user_id=:user_id';
        $data = ['user_id' => $user_id];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return $data_array['result'];
    }
    
    // Get company approve status -by user id
    public function getCompanyApproveStatusByUserId(int $user_id): bool {
            
        $sql = 'SELECT cmp.is_approved AS result FROM companies AS cmp'
            . ' INNER JOIN company_users AS cmpu ON cmp.company_id=cmpu.company_id'
            . ' WHERE cmpu.user_id=:user_id';
        $data = ['user_id' => $user_id];

        $que_st = $this->_db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return $data_array['result'];
    }
    
    /* STATIC */
    
    // Password verify
    public static function PasswordValidate(string $psw_normal, string $psw_hash): bool { return password_verify($psw_normal, $psw_hash); }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">

    // Get content of a specific page
    protected function _getPageContent(int $index) {
        
        switch ($index) {
            
            case 3:
                $data = $this->_getTabRegistration();
                break;
                
            default:
                $data = null;
        }
        
        return $data;
    }
    
    // Get tab: 'Registration'
    private function _getTabRegistration(): array {
        
        $result = Company::COMPANY_SPECIALTIES;
        
        return ['specialties' => $result];
    }
    
    // Add an user to the db
    private function _addUser(User $user): bool {
        
        $sql = 'INSERT INTO users (username, user_psw, user_type, user_fname, user_lname, u_email, picture, is_blocked, is_approved, duplicate_to_mail, allow_newsletters)'
        .' VALUES (:username, :user_psw, :user_type, :user_fname, :user_lname, :u_email, :picture, :is_blocked, :is_approved, :duplicate_to_mail, :allow_newsletters)';

        $data = [
            'username' => $user->getUsername(),
            'user_psw' => $user->getPassword(),
            'user_type' => $user->getType(),
            'user_fname' => $user->getFName(),
            'user_lname' => $user->getLName(),
            'u_email' => $user->getEmail(),
            'picture' => $user->getPicture(),
            'is_blocked' => 0,
            'is_approved' => 0,
            'duplicate_to_mail' => $user->isDuplicateToMail() == true ? 1 : 0,
            'allow_newsletters' => $user->isAllowNewsletters() == true ? 1 : 0
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
    
    // Add an company to the db
    private function _addCompany(Company $company): bool {
        
        $sql = 'INSERT INTO companies (manager_id, company_name, company_description, company_specialties, score, logo, is_blocked, is_approved, date_approved)'
        .' VALUES (:manager_id, :company_name, :company_description, :company_specialties, :score, :logo, :is_blocked, :is_approved, :date_approved)';

        $data = [
            'manager_id' => $company->getManagerId(),
            'company_name' => $company->getName(),
            'company_description' => $company->getDescription(),
            'company_specialties' => $company->getSpecialties(),
            'score' => $company->getScore(),
            'logo' => $company->getLogo(),
            'is_blocked' => 0,
            'is_approved' => 0,
            'date_approved' => $company->getDateApproved()
        ];

        $query_result = $this->_db->singleQueryRetResult($sql, $data);

        return $query_result;
    }
        
    // Add an user to the company
    private function _addUserToCompanyUsers(int $user_id, int $company_id): bool {
        
        $sql = 'INSERT INTO company_users (company_id, user_id)'
        .' VALUES (:company_id, :user_id)';

        $data = [
            'company_id' => $company_id,
            'user_id' => $user_id
        ];

        $result = $this->_db->singleQueryRetResult($sql, $data);

        return $result;
    }
    // </editor-fold>
}
