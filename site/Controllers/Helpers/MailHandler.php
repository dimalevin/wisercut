<?php

/* Objects */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/*
 * MailHandler
 */
class MailHandler {

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    
    // Send new password to the user
    public static function SendNewPassword(User $user, string $password): bool {

        $name = $user->getFullName();

        $message = MessagesTemplates::PasswordRecovery($name, $password);

        $result = self::_SendEmail($user->getEmail(), $name, 'Password Recovery', $message);

        return $result;
    }

    // Send account activation link to the user
    public static function SendNewUserActivationLink(User $user, string $link): bool {
        
        $name = $user->getFullName();
        $message = MessagesTemplates::AccountActivation($user->getFullName(), $link);
        $result = self::_SendEmail($user->getEmail(), $name, 'Account Verification', $message);

        return $result;
    }
    
    // Send account activation link to the user
    public static function SendNewCompanyUserActivationLink(User $user, User $manager, string $company_name, string $link): bool {
        
        $name = $user->getFullName();
        $message = MessagesTemplates::NewCompanyUserAdd($name, $manager, $company_name, $link);
        $result = self::_SendEmail($user->getEmail(), $name, 'Join Team', $message);

        return $result;
    }

    // Send notice to the company about registration process status
    public static function SendCompanyRegistrationStatus(string $company_name, User $manager, bool $status): bool {

        $manager_name = $manager->getFullName();
        
        $message = MessagesTemplates::CompanyApprovedStatus($manager_name, $company_name, $status);
        
        $result = self::_SendEmail($manager->getEmail(), $manager_name, 'Company Registration Status', $message);

        return $result;
    }
    
    // Send notice to the company about status change
    public static function SendCompanyStatus(string $company_name, User $manager, bool $is_blocked): bool {

        $manager_name = $manager->getFullName();
        
        $message = MessagesTemplates::CompanyStatusChange($manager_name, $company_name, $manager->getUsername(), $is_blocked);
        
        $result = self::_SendEmail($manager->getEmail(), $manager_name, 'Company Account Status Change', $message);

        return $result;
    }
    
    // Send notice to the user about status change
    public static function SendUserStatus(User $user, bool $is_blocked): bool {

        $name = $user->getFullName();
        
        $message = MessagesTemplates::UserStatusChange($name, $user->getUsername(), $is_blocked);
        
        $result = self::_SendEmail($user->getEmail(), $name, 'Account Status Change', $message);

        return $result;
    }
    
    // Notify user about adding him to company
    public static function NotifyAddedCompanyUser(User $user, User $manager, string $company_name): bool {

        $name = $user->getFullName();

        $message = MessagesTemplates::NewAddedCompanyUser($name, $manager, $company_name);
        
        $result = self::_SendEmail($user->getEmail(), $name, 'You Have Been Joined: '.$company_name, $message);

        return $result;
    }
    
    // Send message by email
    public static function SendMessageByEmail(string $name, string $email, Message $message): bool {

        $result = self::_SendEmail($email, $name, $message->getTitle(), $message->getBody());

        return $result;
    }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PRIVATE METHODS">
    
    // Send email
    private static function _SendEmail($email, $name, $subject, $message, $altmess = ''): bool {

        $mail = new PHPMailer(true);
        $result = false;
        
        try {
            //Server settings
            $mail->SMTPDebug = 0;
            //$mail->Debugoutput = function($str, $level) {SystemServices::AddToLog('PHPMailer Debug:', 'str: '.$str.'\nlevel:'.$level);};
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->SMTPAuth = false;
            //$mail->Username = SystemConstants::SENDER_MAIL;
            //$mail->Password = SystemConstants::PASSWORD;
            //$mail->SMTPSecure = 'tls';
            //$mail->Port = 587;
            /*TEST*/
			$mail->SMTPSecure = 'none';
            $mail->Port = 25;

            $mail->setFrom(SystemConstants::SENDER_MAIL, SystemConstants::SENDER_NAME);
            $mail->addAddress($email, $name);
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = $altmess;

            $result = $mail->send();
            
        } catch (Exception $e) {
            $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            SystemServices::AddToLog($e->getTraceAsString(), $error_message);
        }

        return $result;
    }
    // </editor-fold>
}
