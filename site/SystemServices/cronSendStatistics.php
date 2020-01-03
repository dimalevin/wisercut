<?php

/* 
 * -cron script-
 * 
 * Send statistics to the companies
 * Interval in days:
 * 1- daily
 * 3- bi-weekly
 * 7- weekly
 * 30- monthly
 */

require_once __DIR__.'/ClassLoader.php'; 

$interval = SystemSettings::GetStatisticsInterval();
$last_time_statistics_sent = $argv[1];

if ($interval > 0) {
    
    $days_passed = round((time() - $last_time_statistics_sent) / (86400)); //86400 = 60*60*24 seconds in 24h
    
    // statistics should be sent
    if ($days_passed >= $interval) {
        
        $general_statistics = generalStatistics($interval);

        $companies = getCompanies();    // get all valid companies

        /* @var $company Company */
        foreach ($companies as $company) {

            $company_statistics = companyStatistics($company->getId(), $interval);     // gather statistic for the company

            // result array
            $result['interval'] = $interval;
            $result['general_statistics'] = $general_statistics;
            $result['company_statistics'] = $company_statistics;
            
            $manager = findUserByUserId($company->getManagerId());  // find company manager

            MessagesHandler::SendCompanyStatistics($manager, $company->getName(), $result); // send statistics to the manager
        }
    }
}

// <editor-fold defaultstate="collapsed" desc="Functions">

// General statistics
function generalStatistics(int $interval): array {
    
    $statistics['opened_questions'] = totalQuestionsOpenedBefore($interval);

    
    return $statistics;
}

// Company statistics
function companyStatistics(int $company_id, int $interval): array {
    
    $received_questions_ids = questionsIdsReceivedByCompanyBefore($company_id, $interval);
    
    // questions is found
    if ($received_questions_ids) {
        
        $statistics['received_questions'] = count($received_questions_ids);
        $given_advices_ids = advicesIdsGivendByCompanyToQuestions($received_questions_ids, $company_id);
        
        if ($given_advices_ids) {
            $statistics['answered_questions'] = totalQuestionsWithGivenAdvices($received_questions_ids, $given_advices_ids);
            $statistics['not_answered_questions'] = $statistics['received_questions'] - $statistics['answered_questions'];
            $statistics['total_advices_given'] = count($given_advices_ids);
            $statistics['advices_responded'] = adviceForCompanyByQuestionsResult($given_advices_ids, $received_questions_ids, 'cnt');
            $statistics['min_advice_score'] = adviceForCompanyByQuestionsResult($given_advices_ids, $received_questions_ids, 'min');
            $statistics['max_advice_score'] = adviceForCompanyByQuestionsResult($given_advices_ids, $received_questions_ids, 'max');
            $statistics['avg_advice_score'] = adviceForCompanyByQuestionsResult($given_advices_ids, $received_questions_ids, 'avg');
            $statistics['best_advices'] = bestAdvicesCountForQuestionsAdvices($given_advices_ids, $received_questions_ids);
        } else {
            $statistics['answered_questions'] = 0;
            $statistics['not_answered_questions'] = $statistics['received_questions'];
            $statistics['total_advices_given'] = 0;
            $statistics['advices_responded'] = 0;
            $statistics['min_advice_score'] = 0;
            $statistics['max_advice_score'] = 0;
            $statistics['avg_advice_score'] = 0;
            $statistics['best_advices'] = 0;
        }
        
    } else {
        $statistics['received_questions'] = 0;
        $statistics['answered_questions'] = 0;
        $statistics['not_answered_questions'] = 0;
        $statistics['total_advices_given'] = 0;
        $statistics['advices_responded'] = 0;
        $statistics['min_advice_score'] = 0;
        $statistics['max_advice_score'] = 0;
        $statistics['avg_advice_score'] = 0;
        $statistics['best_advices'] = 0;
    }
    
    return $statistics;
}

// Get companies (not blocked, approved and with non blocked manager, who allowed newsletters)
function getCompanies() {

    $db = DbClass::getInstance();
    
    $sql = 'SELECT * FROM companies AS c'
        . ' INNER JOIN users AS u ON c.manager_id=u.user_id'
        . ' WHERE c.is_approved=1 AND u.is_blocked=0 AND u.allow_newsletters=1 AND c.is_blocked=0';

    $query_st = $db->singleQueryRetStatement($sql);

    if ($query_st) {
        $companies = $query_st->fetchAll(PDO::FETCH_CLASS, 'Company');
    }

    return $companies ?? null;
}

// Find user -by user id
function findUserByUserId(int $user_id) {

    $db = DbClass::getInstance();

    $sql = 'SELECT * FROM users WHERE user_id=:user_id';
    $data = [':user_id' => $user_id];

    $query_st = $db->singleQueryRetStatement($sql, $data);

    $user = $query_st->fetchObject('User');

    return $user;
}

// Return total number of questions opened -certain days before today
function totalQuestionsOpenedBefore(int $days): int {
        
    $db = DbClass::getInstance();
    
    $sql = 'SELECT COUNT(*) AS result FROM questions WHERE date_opened>=CURDATE()-:days';
    $data = ['days' => $days];

    $que_st = $db->singleQueryRetStatement($sql, $data);

    $data_array = $que_st->fetch();

    return $data_array['result'];
}

// Questions ids received by company -certain days before today
function questionsIdsReceivedByCompanyBefore($company_id, $days) {
        
    $db = DbClass::getInstance();
    
    $sql = 'SELECT q.que_id AS result FROM questions AS q'
        . ' INNER JOIN company_questions AS cq ON q.que_id=cq.que_id'
        . ' WHERE cq.company_id=:company_id AND q.date_opened>=CURDATE()-:days';
    $data = [
        'days' => $days,
        'company_id' => $company_id
    ];

    $que_st = $db->singleQueryRetStatement($sql, $data);

    $data_arr = $que_st->fetchAll();

    foreach ($data_arr as $arr) {
        $result[] = $arr['result'];
    }

    return $result ?? null;
}

// Advices ids givend by company to questions
function advicesIdsGivendByCompanyToQuestions(array $question_ids_array, int $company_id) {
        
    $question_ids = implode(', ', $question_ids_array);
    
    $db = DbClass::getInstance();
    
    $sql = 'SELECT aq.adv_id AS result FROM advice_question AS aq'
        . ' INNER JOIN company_questions AS cq ON aq.que_id=cq.que_id'
        . ' WHERE INSTR(:question_ids, cq.que_id) > 0  AND cq.company_id=:company_id';
    $data = [
        'question_ids' => $question_ids,
        'company_id' => $company_id
    ];

    $que_st = $db->singleQueryRetStatement($sql, $data);

    $data_arr = $que_st->fetchAll();
    
    foreach ($data_arr as $arr) {
        $result[] = $arr['result'];
    }

    return $result ?? null;
}

// Total questions with given advices
function totalQuestionsWithGivenAdvices(array $question_ids_array, array $advices_ids_array): int {
        
    $question_ids = implode(' ', $question_ids_array);
    $advices_ids = implode(' ', $advices_ids_array);
    
    $db = DbClass::getInstance();
    
    $sql = 'SELECT COUNT(DISTINCT que_id) AS result FROM advice_question'
        . ' WHERE INSTR(:question_ids, que_id) > 0 AND INSTR(:advices_ids, adv_id) > 0';
    $data = [
        'question_ids' => $question_ids,
        'advices_ids' => $advices_ids
    ];

    $que_st = $db->singleQueryRetStatement($sql, $data);

    $data_array = $que_st->fetch();

    return $data_array['result'];
}

// Total questions with given advices
function totalAdvicesOfQuestionsGotResponded(array $question_ids_array, array $advices_ids_array): int {
        
    $question_ids = implode(' ', $question_ids_array);
    $advices_ids = implode(' ', $advices_ids_array);
    
    $db = DbClass::getInstance();
    
    $sql = 'SELECT COUNT(DISTINCT que_id) AS result FROM advice_question'
        . ' WHERE INSTR(:question_ids, que_id) > 0 AND INSTR(:advices_ids, adv_id) > 0';
    $data = [
        'question_ids' => $question_ids,
        'advices_ids' => $advices_ids
    ];

    $que_st = $db->singleQueryRetStatement($sql, $data);

    $data_array = $que_st->fetch();

    return $data_array['result'];
}
    
//adviceForCompanyByQuestionsResult
function adviceForCompanyByQuestionsResult(array $advices_ids_array, array $question_ids_array, string $type): float {
        
    $question_ids = implode(' ', $question_ids_array);
    $advices_ids = implode(' ', $advices_ids_array);
    
    // define type
    switch ($type) {
        
        case 'cnt':
            $query_type = 'COUNT(*)';
            break;

        case 'min':
            $query_type = 'MIN(score)';
            break;
        
        case 'max':
            $query_type = 'MAX(score)';
            break;
        
        case 'avg':
            $query_type = 'AVG(score)';
            break;
    }
    
    // if type is set
    if (isset($type)) {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT '.$query_type.' AS result FROM responses'
            . ' WHERE INSTR(:question_ids, que_id) > 0 AND INSTR(:advices_ids, adv_id) > 0';
        $data = [
            'question_ids' => $question_ids,
            'advices_ids' => $advices_ids
        ];

        $que_st = $db->singleQueryRetStatement($sql, $data);

        $data_array = $que_st->fetch();

        $result = $data_array['result'];
    }
    
    return $result ?? 0;
}

// Best advices count for questions advices
function bestAdvicesCountForQuestionsAdvices(array $advices_ids_array, array $question_ids_array): float {
        
    $question_ids = implode(' ', $question_ids_array);
    $advices_ids = implode(' ', $advices_ids_array);
    
    $db = DbClass::getInstance();
    
    $sql = 'SELECT COUNT(*) AS result FROM responses'
        . ' WHERE INSTR(:question_ids, que_id) > 0 AND INSTR(:advices_ids, adv_id) > 0 AND is_best_advice=1';
    $data = [
        'question_ids' => $question_ids,
        'advices_ids' => $advices_ids
    ];

    $que_st = $db->singleQueryRetStatement($sql, $data);

    $data_array = $que_st->fetch();

    return $data_array['result'];
}

// </editor-fold>


