<?php

/*
 * Messages
 * 
 * Handles inner system messaging system:
 * sendind & receiving messages & etc.
 * in addition can forward message to email handling class depending on user settings
 */
abstract class MessagesHandler {
    
    // Send contact us form to the admin
    public static function SendContactUsForm(string $name, string $email, string $subject, string $content): bool {
        
        $message = new Message();
        $message->setTitle('Contact us form -'.$subject);
        $message->setBody(MessagesTemplates::ContactUsForm($name, $email, $subject, $content));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId(SystemConstants::ADMIN_ID);
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail(SystemConstants::ADMIN_ID)) {
            MailHandler::SendMessageByEmail(SystemConstants::SENDER_NAME, SystemConstants::ADMIN_MAIL, $message);
        }
        
        return $result;
    }
    
    // Send notice to the admin about a new registered company
    public static function SendCompanyRegistrationAdminNotice(Company $company, User $manager): bool {
        
        $message = new Message();
        $message->setTitle('New Company Registration');
        $message->setBody(MessagesTemplates::NewCompanyAdminNotice($company, $manager));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId(SystemConstants::ADMIN_ID);
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail(SystemConstants::ADMIN_ID)) {
            MailHandler::SendMessageByEmail(SystemConstants::SENDER_NAME, SystemConstants::ADMIN_MAIL, $message);
        }
        
        return $result;
    }
    
    // Company user unblock request
    public static function SendCompanyUserUnblockRequest(int $manager_id, string $company_name, string $username, string $info): bool {
        
        $message = new Message();
        $message->setTitle('Company User Unblock Request');
        $message->setBody(MessagesTemplates::CompanyUserUnblockRequest($company_name ,$username, $info));
        $message->setSenderId($manager_id);
        $message->setReceiverId(SystemConstants::ADMIN_ID);
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail(SystemConstants::ADMIN_ID)) {
            MailHandler::SendMessageByEmail(SystemConstants::SENDER_NAME, SystemConstants::ADMIN_MAIL, $message);
        }
        
        return $result;
    }
    
    // Send user question status
    public static function SendUserQuestionStatus(User $user, Question $question, bool $is_visible): bool {
        
        $name = $user->getFullName();
        
        $message = new Message();
        $message->setTitle('Question Status Changed -question id:'.$question->getId());
        $message->setBody(MessagesTemplates::QuestionStatusChanged($name, $question, $is_visible));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId($user->getId());
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail($user->getId())) {
            
            MailHandler::SendMessageByEmail($name, $user->getEmail(), $message); // notify the user by email
        }
        
        return $result;
    }
    
    // Send to advising user notification about a new response
    public static function SendAdvisingUserResponseNotification(string $sender_name, User $receiver, Response $response): bool {
        
        $message = new Message();
        $message->setTitle('New Response Received For: -advice id:'.$response->getAdviceId());
        $message->setBody(MessagesTemplates::AdvisingUserResponseNotification($receiver->getFullName(), $sender_name, $response));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId($receiver->getId());
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail($receiver->getId())) {
            MailHandler::SendMessageByEmail($receiver->getFullName(), $receiver->getEmail(), $message); // notify the user by email
        }
        
        return $result;
    }
        
    // Notify company manager about newly joined user
    public static function NotifyCompanyManagerOnNewUserJoin(User $manager, string $company_name, User $joined_user): bool {
        
        $message = new Message();
        $message->setTitle($joined_user->getFullName().' is joined your team!');
        $message->setBody(MessagesTemplates::NewJoinedCompanyUserManagerNotice($manager->getFullName(), $company_name, $joined_user));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId($manager->getId());
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail($manager->getId())) {
            MailHandler::SendMessageByEmail($manager->getFullName(), $manager->getEmail(), $message); // notify the user by email
        }
        
        return $result;
    }
    
    // Send company statistics
    public static function SendCompanyStatistics(User $manager, string $company_name, array $statistics): bool {

        $interval = $statistics['interval'];
        //$general_statistics = $statistics['general_statistics'];
        //$company_statistics = $statistics['company_statistics'];
        
        switch ($interval) {
            case 1:
                $statistics_interval = 'daily ';
                break;
            case 3:
                $statistics_interval = 'bi-weekly ';
                break;
            case 7:
                $statistics_interval = 'weekly ';
                break;
            case 30:
                $statistics_interval = 'monthly ';
                break;
            default:
                $statistics_interval = '';
                break;
        }
        
        $message = new Message();
        $message->setTitle($company_name.', this is your '.$statistics_interval.'statistics');
        $message->setBody(MessagesTemplates::StatisticsMessage($manager->getFullName(), $statistics));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId($manager->getId());
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail($manager->getId())) {
            MailHandler::SendMessageByEmail($manager->getFullName(), $manager->getEmail(), $message); // notify the user by email
        }
        
        return $result;
    }
        
    // Notify companies about a new question
    public static function NotifyCompaniesOnNewQuestion(array $companies_ids, Question $question) {
        
        foreach ($companies_ids as $company_id) {
            
            /* @var $company Company */
            $company = self::_FindCompanyById($company_id); // find company
            
            /* @var $manager User */
            $manager = self::_FindUserById($company->getManagerId());   // find manager
            
            self::_CompanyNewQuestionNotification($company, $manager, $question);    // send message
        }
    }
    
    // Notify asking user about new advice
    public static function NotifyAskingUserAboutNewAdvice(User $asking_user, int $question_id): bool {
        
        $message = new Message();
        $message->setTitle('New Advice Received For: -question id:'.$question_id);
        $message->setBody(MessagesTemplates::AskingUserNewAdviceNotification($asking_user->getFullName(), $question_id));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId($asking_user->getId());
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail($asking_user->getId())) {
            MailHandler::SendMessageByEmail($asking_user->getFullName(), $asking_user->getEmail(), $message); // notify the user by email
        }
        
        return $result;
    }
    
    // Get 'IN' messages for specific user -by user id
    public static function GetUserInMessages(int $user_id, bool $all = true, bool $is_new = false) {
        
        $db = DbClass::getInstance();
        
        // all mesages
        if ($all) {
            
            $sql = 'SELECT * FROM messages_in WHERE receiver_id=:user_id';
            $data = ['user_id' => $user_id];
            
        } else {
         
            $sql = 'SELECT * FROM messages_in WHERE receiver_id=:user_id AND is_new=:is_new';
            $data = [
                'user_id' => $user_id,
                'is_new' => $is_new == true ? 1 : 0
            ];
        }
        
        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $messages = $query_st->fetchAll(PDO::FETCH_CLASS, 'Message');
        
        return $messages;
    }
    
    // Get 'OUT' messages for specific user -by user id
    public static function GetUserOutMessages(int $user_id, bool $all = true, bool $is_sent = false) {
        
        $db = DbClass::getInstance();
        
        // all mesages
        if ($all) {
            
            $sql = 'SELECT * FROM messages_out WHERE sender_id=:user_id';
            $data = ['user_id' => $user_id];
            
        } else {
         
            $sql = 'SELECT * FROM messages_out WHERE sender_id=:user_id AND is_sent=:is_sent';
            $data = [
                'user_id' => $user_id,
                'is_sent' => $is_sent == true ? 1 : 0
            ];
        }
        
        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $messages = $query_st->fetchAll(PDO::FETCH_CLASS, 'Message');
        
        return $messages;
    }
    
    // Delete OUT message -by message id
    public static function DeleteOutMessage(int $message_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'DELETE FROM messages_out WHERE msg_id=:msg_id';
        
        $data = ['msg_id' => $message_id ];
        
        $result = $db->singleQueryRetResult($sql, $data);

        return $result;
    }
    
    // Delete IN message -by message id
    public static function DeleteInMessage(int $message_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'DELETE FROM messages_in WHERE msg_id=:msg_id';
        $data = ['msg_id' => $message_id ];
        
        $result = $db->singleQueryRetResult($sql, $data);

        return $result;
    }
    
    // Send message
    public static function Send(array $messages): bool {
        
        $result = !empty($messages);
        
        // add messages to outbox
        foreach ($messages as $message) {
            
            $message_added = self::_AddMessageToOutbox($message);
            
            $result *= $message_added;
            
            // error occured
            if (!$result) {
                SystemServices::AddToLog(__FILE__, 'error sending message');
                break;
            }
        }

        // message\s is added
        if ($result) {
            
            self::_SendUserMessages($message->getSenderId());  // send all messages for this user
            
        } else {
            
            // remove messages from the outbox
            foreach ($messages as $message) {

                self::DeleteOutMessage($message->getId());
            }
        }
        
        return $result;
    }
    
    // Set message as readed
    public static function SetInMessageAsReaded(int $message_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'UPDATE messages_in SET is_new=:is_new WHERE msg_id=:message_id';
        $data = [
            'message_id' => $message_id,
            'is_new' => 0
        ];
        
        $result = $db->singleQueryRetResult($sql, $data);

        return $result;
    }
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE METHODS">
        
    // Send notification to a company about a new question
    private static function _CompanyNewQuestionNotification(Company $company, User $manager, Question $question): bool {
        
        $message = new Message();
        $message->setTitle('New Question Received -question id:'.$question->getId());
        $message->setBody(MessagesTemplates::CompanyNewQuestionNotification($manager->getFullName(), $company->getName(), $question));
        $message->setSenderId(SystemConstants::ADMIN_ID);
        $message->setReceiverId($manager->getId());
        
        $result = self::_AddMessageToInbox($message);
        
        // duplicate to email
        if ($result && self::_IsUserSetDuplicateToEmail($manager->getId())) {
            
            MailHandler::SendMessageByEmail($manager->getFullName(), $manager->getEmail(), $message); // notify the user by email
        }
        
        return $result;
    }
    
    // Send unsent user messages
    private static function _SendUserMessages(int $user_id) {
        
        $unsent_messages = self::GetUserOutMessages($user_id, false, false);
        
        /* @var $message Message */
        foreach ($unsent_messages as $message) {
            
            $sended = self::_AddMessageToInbox($message);
            
            // message is sent
            if ($sended) {
                self::_SetMessageAsSent($message->getId());     // change out message status
                
                // duplicate to email
                if (self::_IsUserSetDuplicateToEmail($message->getReceiverId())) {

                    $user_details = self::_GetUserNameAndEmail($message->getReceiverId());
                    MailHandler::SendMessageByEmail($user_details['name'], $user_details['email'], $message);
                }
            }
        }
    }
    
    // Set message as sent
    private static function _SetMessageAsSent(int $msg_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'UPDATE messages_out SET is_sent=:is_sent WHERE msg_id=:msg_id';
        $data = [
            'msg_id' => $msg_id,
            'is_sent' => 1
        ];
        
        $result = $db->singleQueryRetResult($sql, $data);
        
        return $result;
    }
    
    // Add message to inbox
    private static function _AddMessageToInbox(Message $message): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'INSERT INTO messages_in (sender_id, receiver_id, title, body, is_new)'
        .' VALUES (:sender_id, :receiver_id, :title, :body, :is_new)';

        $data = [
            'sender_id' => $message->getSenderId(),
            'receiver_id' => $message->getReceiverId(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'is_new' => 1
        ];

        $query_result = $db->singleQueryRetResult($sql, $data);

        return $query_result;
    }

    // Add message to outbox
    private static function _AddMessageToOutbox(Message $message): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'INSERT INTO messages_out (sender_id, receiver_id, title, body, is_sent)'
        .' VALUES (:sender_id, :receiver_id, :title, :body, :is_sent)';

        $data = [
            'sender_id' => $message->getSenderId(),
            'receiver_id' => $message->getReceiverId(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'is_sent' => 0
        ];

        $query_result = $db->singleQueryRetResult($sql, $data);

        return $query_result;
    }

    // User choosed duplicate to email
    private static function _IsUserSetDuplicateToEmail(int $user_id): bool {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT duplicate_to_mail AS result FROM users WHERE user_id=:user_id';
        $data = [':user_id' => $user_id];

        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        return $data_array['result'];
    }
    
    // Get user name and email -by user id
    private static function _GetUserNameAndEmail(int $user_id): array {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT CONCAT(user_fname, " ", user_lname) AS name, u_email AS email FROM users WHERE user_id=:user_id';
        $data = [':user_id' => $user_id];

        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $data_array = $query_st->fetch();
        
        $result = [
            'name' => $data_array['name'],
            'email' => $data_array['email']
        ]; 
        
        return $result;
    }
    
    // Find user -by user id
    private static function _FindUserById(int $user_id) {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT * FROM users WHERE user_id=:user_id';
        $data = [':user_id' => $user_id];

        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $user = $query_st->fetchObject('User');
        
        return $user;
    }
    
    // Find company -by company id
    private static function _FindCompanyById(int $company_id) {
        
        $db = DbClass::getInstance();
        
        $sql = 'SELECT * FROM companies WHERE company_id=:company_id';
        $data = [':company_id' => $company_id];

        $query_st = $db->singleQueryRetStatement($sql, $data);
        
        $company = $query_st->fetchObject('Company');
        
        return $company;
    }
    // </editor-fold>
}
