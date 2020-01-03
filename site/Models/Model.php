<?php

/*
 * Model
 * 
 * -see MVC design pattern
 */
abstract class Model {

    /* Constants */
    private const _SALT = '+$M7*#B<&)';
    private const _PASSWORD_LEN = 8;
    private const _PAGES_DIR = __DIR__.'/../Views/Partials/';
    protected const _ARRAY_MENU_ITEMS = '';
    protected const _ARRAY_FILE_NAMES = '';
    protected const _SUB_DIR = '';

    // PROTECTED PROPERTIES
    protected $_user, $_db;

    // CONSTRUCTOR
    function __construct() {
        $this->_db = DbClass::getInstance();
    }
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Get session id
    public function getSessionId(): string { return session_id(); }
    
    // Get user menu items
    public function getUserMenuItems(): array { return $this::_ARRAY_MENU_ITEMS; }
    
    // Get user content file names
    public function getUserContentFileNames(): array { return $this::_ARRAY_FILE_NAMES; }
    
    // Get file name by index
    public function getFilePathByIndex($index) {
        
        $files_array = $this->getUserContentFileNames();
        $filename = '';
        
        // index is in range
        if ($index >= 0 && $index < count($files_array)) {
            $filename = $files_array[$index];              // get filename
        }
        
        return $this::_SUB_DIR.'/'.$filename;
    }
    
    // Find user -by username
    public function findUserByUsername(string $username) {
        
        $t_user = null;
            
        // username not empty
        if ($username) {
        
            $sql = 'SELECT * FROM users WHERE username=:username';
            $data = [':username' => $username];

            $query_st = $this->_db->singleQueryRetStatement($sql, $data);

            $t_user = $query_st->fetchObject('User');
        }
        
        return $t_user;
    }
    
    // Find user -by email
    public function findUserByEmail(string $email) {
        
        $t_user = null;
            
        // email not empty
        if ($email) {
        
            $sql = 'SELECT * FROM users WHERE u_email=:email';
            $data = [':email' => $email];

            $query_st = $this->_db->singleQueryRetStatement($sql, $data);

            $t_user = $query_st->fetchObject('User');
        }
        
        return $t_user;
    }
    
    // Get page
    public function getPage(int $index): array {
        
        // html
        $file_path = $this->getFilePathByIndex($index);     // get filename
           
        $full_path = self::_PAGES_DIR.$file_path;
        
        $html = self::_PageHtml($full_path);
        
        // content
        $content = $this->_getPageContent($index);
        
        $result = [
            'html' => $html,
            'http_code' => $html ? 200 : 404,
            'content' => $content
        ]; 
        
        return $result;
    }
    
    // Get company id -by user id
    public function getCompanyIdByUserId(int $user_id): int {
        
        $sql = 'SELECT cmp.company_id FROM companies AS cmp'
            . ' INNER JOIN company_users AS cmpu ON cmp.company_id=cmpu.company_id'
            . ' WHERE cmpu.user_id=:user_id';
        $data = [ 'user_id' => $user_id ];
        
        $que_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $data_array = $que_st->fetch();
        
        $result = $data_array['company_id'] ?? 0;
        
        return (int) $result;
    }
    
    
    /* Static */
    
    // Create new user activation link
    public static function UserActivationLink(string $username): string {

        $passphrase = self::_CreateActivationPassphrase($username);
        $page_url = SystemConstants::ACTIVATION_LINK_PATHS[SystemConstants::NEW_USER_ACTIVATION];
        $base_url = Helper::GetBaseUrl();
        
        $link = $base_url.$page_url.'?username='.$username.'&passphrase='.$passphrase;
        
        return $link;
    }
    
    // Create activation passhphrase
    protected static function _CreateActivationPassphrase(string $username): string {

        return md5($username.self::_SALT);
    }
    
    // Validate activation passhphrase
    public static function ValidateActivationPassphrase(string $username, string $passphrase): bool {

        return $passphrase === md5($username.self::_SALT);
    }
    
    // Password hash
    public static function PasswordHash(string $psw_normal): string { return password_hash($psw_normal, PASSWORD_DEFAULT); }
    
    // Generate random password
    public static function GeneratePassword(): string {
        
        $characters = '@_-?+=0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_max = strlen($characters) - 1;
        $new_password = '';
        
        // generate password
        for ($index = 0; $index < self::_PASSWORD_LEN; $index++) {
            $new_password .= $characters[random_int(0, $random_max)];
        }
        
        return $new_password;
    }
    
    
    /* Abstract */
    
    public abstract function getData(array $data_array);
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE\PROTECTED METHODS">

    // Find company -by manager id
    protected function _findCompanyByManagerId(int $manager_id) {
        
        $sql = 'SELECT * FROM companies WHERE manager_id=:manager_id';
        $data = ['manager_id' => $manager_id];
        
        $query_st = $this->_db->singleQueryRetStatement($sql, $data);
        
        $company = $query_st->fetchObject('Company');
        
        return $company;
    }
    
    /* Static */
    
    // Get page html by page index
    protected static function _PageHtml(string $file_path) {
        
        $file_content = null;
        
        // file exists
        if ($file_path && file_exists($file_path)) {

            $file_content = file_get_contents($file_path);
        }
        
        return $file_content;
    }
    
    /* Abstract */
    protected abstract function _getPageContent(int $index);   // get page data of specific page by it's index
    // </editor-fold>

}
