<?php

/* 
 * System services
 * 
 * Provides system services and system operations
 */
abstract class SystemServices {
    
    /* @var $db DbClass */
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    
    // Add a message to the log
    public static function AddToLog(string $trace, string $message) {
        
        $log_msg = date('[D, d-M-Y H:i:s.ms]').PHP_EOL.$trace.PHP_EOL.'Error: '.$message;
        
        $log_fl = fopen(SystemConstants::LOG_FILE_PATH, 'a');
        fwrite($log_fl, $log_msg);  
        fclose($log_fl);  
    }
    
    // Approve new unverified user
    public static function ApproveNewUser(string $username, string $passphrase): bool {
        
        $result = false;
            
        // passphrase is match
        if (Model::ValidateActivationPassphrase($username, $passphrase)) {
            $result = self::_SetUserAsApproved($username);
        }
        
        return $result;
    }
    
    // Add new company user
    public static function AddNewCompanyUser(string $username, string $passphrase, int $company_id): bool {
        
        $result = false;
            
        // passphrase is match
        if (Model::ValidateActivationPassphrase($username, $passphrase)) {

             /* @var $user_to_add User */
             $user_to_add = self::_FindUserByUsername($username);

            // request record exists
            if (self::_AddUserToCompanyRequestExists($company_id, $user_to_add->getId())) {
                $result = self::_AddUserToCompany($user_to_add->getId(), $company_id);
            }
        }

        // user is added
        if ($result) {

            /* @var $company Company */
            $company = self::_FindCompanyById($company_id);
            $manager = self::_FindUserByUserId($company->getManagerId());

            MailHandler::NotifyAddedCompanyUser($user_to_add, $manager, $company->getName());                   // notify the added user by email
            MessagesHandler::NotifyCompanyManagerOnNewUserJoin($manager, $company->getName(), $user_to_add);    // notification to the company manager
            
            self::_AddUserToCompanyDeleteRequests($company_id, $user_to_add->getId());  // remove all requests
        } 

        return $result;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PRIVATE METHODS">
    
    // Check if add new user to a company request is exists
    private static function _AddUserToCompanyRequestExists(int $company_id, int $user_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT COUNT(*) > 0  AS result FROM add_to_company_requests'
            . ' WHERE company_id=:company_id AND user_id=:user_id';
        $data = [
            'company_id' => $company_id,
            'user_id' => $user_id
        ];

        $que_st = $db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        return (bool) $data_array['result'];
    }
    
    // Delete all user requests
    private static function _AddUserToCompanyDeleteRequests(int $company_id, int $user_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'DELETE FROM add_to_company_requests'
            . ' WHERE AND user_id=:user_id';
        $data = [
            'company_id' => $company_id,
            'user_id' => $user_id
        ];

        $result = $db->singleQueryRetResult($sql, $data);

        return $result;
    }
    
    // Find user -by user id
    private static function _FindUserByUserId(int $user_id) {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT * FROM users WHERE user_id=:user_id';
        $data = [':user_id' => $user_id];

        $query_st = $db->singleQueryRetStatement($sql, $data);

        $user = $query_st->fetchObject('User');
        
        return $user;
    }
        
    // Find user -by username
    private static function _FindUserByUsername(string $username) {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT * FROM users WHERE username=:username';
        $data = [':username' => $username];

        $query_st = $db->singleQueryRetStatement($sql, $data);

        $user = $query_st->fetchObject('User');
        
        return $user;
    }
    
    // Find company -by company id
    private static function _FindCompanyById(int $company_id) {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT * FROM companies WHERE company_id=:company_id';
        $data = ['company_id' => $company_id];
        
        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $company = $query_st->fetchObject('Company');
        
        return $company;
    }
    
    // Set user as approved -non blocked
    private static function _SetUserAsApproved(string $username): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'UPDATE users SET is_approved=1 WHERE username=:username AND is_blocked=0';
        $data = ['username' => $username];
        
        $result = $db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
        
    // Add an user to the company
    private static function _AddUserToCompany(int $user_id, int $company_id): bool {
        
        $db = DbClass::getInstance();

        $sql = 'INSERT INTO company_users (company_id, user_id)'
        .' VALUES (:company_id, :user_id)';

        $data = [
            'company_id' => $company_id,
            'user_id' => $user_id
        ];

        $result = $db->singleQueryRetResult($sql, $data);

        return $result;
    }

    // </editor-fold>
    
}
