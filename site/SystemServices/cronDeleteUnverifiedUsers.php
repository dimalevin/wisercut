<?php

/* 
 * -cron script-
 * Deletes unverified users from the system after certain time they didn't verify they account
 */

require_once __DIR__.'/ClassLoader.php';    

$user_ids_to_remove = findUserIdsToRemove();

if (!empty($user_ids_to_remove)) {
    removeCompanyUsersRecords($user_ids_to_remove);    // remove company users records
    removeCompaniesByManagerIds($user_ids_to_remove);  // remove companies
    removeUsersByIds($user_ids_to_remove); // remove users
}

// <editor-fold defaultstate="collapsed" desc="Functions">

// Find unverified user id's meeting the remove policy
function findUserIdsToRemove(): array {

    $result = [];
    
    $db = DbClass::getInstance();

    $sql = 'SELECT user_id AS result FROM users'
        . ' WHERE is_approved=0 AND date_created < CURDATE()-'.SystemConstants::UNVERIFIED_USER_TIME_LIMIT;

    $que_st = $db->singleQueryRetStatement($sql);
        
    $data_arr = $que_st->fetchAll();

    foreach ($data_arr as $arr) {
        $result[] = $arr['result'];
    }

    return $result;
}

// Remove company users records -by user id's
function removeCompanyUsersRecords(array $user_ids_array) {

    $user_ids = implode(' ', $user_ids_array);
    
    $db = DbClass::getInstance();

    $sql = 'DELETE FROM company_users WHERE INSTR(:user_ids, user_id) > 0';
    $data = [':user_ids' => $user_ids];
    
    $db->singleQueryRetResult($sql, $data);
}

// Remove companies -by manager id's
function removeCompaniesByManagerIds(array $manager_ids_array) {

    $manager_ids = implode(' ', $manager_ids_array);
    
    $db = DbClass::getInstance();

    $sql = 'DELETE FROM companies WHERE INSTR(:manager_ids, manager_id) > 0';
    $data = [':manager_ids' => $manager_ids];
    
    $db->singleQueryRetResult($sql, $data);
}

// Remove users -by ids
function removeUsersByIds(array $user_ids_array) {

    $user_ids = implode(' ', $user_ids_array);
    
    $db = DbClass::getInstance();

    $sql = 'DELETE FROM users WHERE INSTR(:user_ids, user_id) > 0';
    $data = [':user_ids' => $user_ids];
    
    $db->singleQueryRetResult($sql, $data);
}
// </editor-fold>

