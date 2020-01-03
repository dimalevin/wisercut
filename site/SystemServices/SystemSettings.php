<?php

/*
 * System settings
 * 
 * Handles the system settings
 */
abstract class SystemSettings {
    
    /* @var $db DbClass */
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Get maximum num. of questions allowed per day
    public static function GetDailyQuestionsLimit(): int {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT daily_questions_limit AS result FROM system_settings';

        $query_st = $db->singleQueryRetStatement($sql);
        
        $data_array = $query_st->fetch();
        
        return $data_array['result'];
    }
    
    // Get statistics interval value
    public static function GetStatisticsInterval(): int {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT statistics_interval AS result FROM system_settings';

        $query_st = $db->singleQueryRetStatement($sql);
        
        $data_array = $query_st->fetch();
        
        return $data_array['result'];
    }

    // Set settings
    public static function SetSettings(array $data_array): bool {
        
        $daily_questions_limit = $data_array['daily_questions_limit'];
        $statistics_interval = $data_array['statistics_interval'];
        
        $db = DbClass::getInstance();
        
        $sql = 'UPDATE system_settings SET daily_questions_limit=:daily_questions_limit, statistics_interval=:statistics_interval';
        $data = [
            'daily_questions_limit' => $daily_questions_limit,
            'statistics_interval' => $statistics_interval
        ];
        
        $query_res = $db->singleQueryRetResult($sql, $data);
        
        return $query_res;
    }
    
    // Get settings
    public static function GetSettings(): array {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT * FROM system_settings';
        
        $query_st = $db->singleQueryRetStatement($sql);
        
        $data_array = $query_st->fetch();
        
        return $data_array;
    }
    // </editor-fold>
}
