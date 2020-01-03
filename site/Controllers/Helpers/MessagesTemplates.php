<?php

/*
 * Message
 * 
 * Creates messages from templates, to be sent inside the system and/or to email
 */
abstract class MessagesTemplates {

    // <editor-fold defaultstate="collapsed" desc="MESSSAGES TEMPLATES">
    
    // General body template
    public static function GeneralBodyTemplate(string $body) {
        
        $sentences = explode('.', $body);
        
        $new_body = '<img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">'
            . '<p style="color:#000;font-size:18px;">';
        
        foreach ($sentences as $sentence) {
            $new_body .= $sentence.'.<br>';
        }
        
        $new_body .= '</p>';
        
        return $new_body;
    }
    
    // Contact us form
    public static function ContactUsForm(string $name, string $email, string $subject, string $message): string {
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Contact Us Form</title>
                        </head>
                        <body style="direction:ltr;">
                            <h2 style="color:#1973B2; font-size:22px;">Subject: '.$subject.'<br></h2>
                            <h2 style="color:#1973B2; font-size:22px;">Message:<br></h2>  
                            <p style="color:#000;font-size:18px;">'.$message.'<br></p>
                            <p style="color:#000;font-size:18px;">Contact Info:<br>
                                Name: '.$name.'<br>Email: '.$email.'</p>
                            <p style="font-style:italic;color:#000;">
                                Notice: This message is sent thru contact us form from the main page of the site.
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Statistics message
    public static function StatisticsMessage(string $full_name, array $statistics): string {
        
        $interval = $statistics['interval'];
        $general_statistics = $statistics['general_statistics'];
        $company_statistics = $statistics['company_statistics'];
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Statistics for '.$interval.'days.</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$full_name.',</h2>
                            <p style="color:#000;font-size:18px;">Statistics:
                            </p>
                            <p style="color:#000;font-size:15px;"><br>Total questions opened: '.$general_statistics['opened_questions'].
                            '<br>Your company received \''.$company_statistics['received_questions'].'\', dye to your specialties and asking users preferences.
                            <br>Users in your team answered \''.$company_statistics['answered_questions'].'\' questions and \''.$company_statistics['total_advices_given'].'\' total advices given for all questions.
                            <br>\''.$company_statistics['advices_responded'].'\' of the advices got responded with the following scores:
                            <br>Min. score: '.$company_statistics['min_advice_score'].'.
                            <br>Max. score: '.$company_statistics['max_advice_score'].'.
                            <br>Avg. score: '.$company_statistics['avg_advice_score'].'.
                            <br>Total best advices: '.$company_statistics['best_advices'].'</p>
                            <p style="font-style:italic;color:#000;">
                                If you wish to stop receiving this mail, please ucheck \'Allow Newsletters\' option in your settings tab. 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Password recovery
    public static function PasswordRecovery(string $name, string $password): string {

        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Password Recovery</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">The new password has been sent upon your request.<br>Please login to your account with following details:
                                <span style="display:block;font-weight:bold; color:color:#1973B2"><br>PASSWORD: '.$password.'</span>
                            </p>
                            <p>
                            The password may be changed in your "Account Settings" at any time.
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }

    // Account activation
    public static function AccountActivation(string $name, string $link): string {

        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Account Verification</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">Thanks for signing up! Please verify you email by pressing the url below. <br>The activation link will expire after '.SystemConstants::UNVERIFIED_USER_TIME_LIMIT.' days.
                            </p>
                            <p style="color:#000;font-size:18px;">
                            <a href='.$link.' target="_blank" style="cursor:pointer;font-size:14px;">'.$link.'</a></p>
                            <p style="font-style:italic;color:#000;">
                                Important notice: In case of company registration, the provided information is subject of approvance by our team. 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }

    // Notification to a company about a new question
    public static function CompanyNewQuestionNotification(string $manager_name, string $company_name, Question $question): string {

        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>You have a new question.</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$manager_name.',</h2>
                            <p style="color:#000;font-size:18px;">Your company: <p style="font-style:bold; color:#1973B2;">'.$company_name.'</p>received a question suited to your specialty field.
                            </p>
                            <p style="color:#000;font-size:15px;"><br>Question details:<br>Title: '.$question->getTitle().'<br>Description:<br>'.$question->getDescription().'</p>
                            <p style="font-style:italic;color:#000;">
                                In order to get full details, please login to your account. 
                            </p>
                            <p style="font-style:italic;color:#000;">
                                Notice: You can edit your specialties in your account. 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }

    // Notification to the asking user about a new advice
    public static function AskingUserNewAdviceNotification(string $full_name, int $question_id): string {

        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>You have received a new advice.</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$full_name.',</h2>
                            <p style="color:#000;font-size:18px;">You have received a new advice for your question.
                            </p>
                            <p style="color:#000;font-size:15px;"><br>Question details:<br>Question id: '.$question_id.'</p>
                            <p style="font-style:italic;color:#000;">
                                In order to get full details, please login to your account. 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Company approved status
    public static function CompanyApprovedStatus(string $manager_name, string $company_name, bool $status): string {

        $status_message = $status ? 'Congratulations! Your apply is approved.' : 'Unfortunately your apply is declined.';
        $notice_message = $status ? 'You can now login to your account and start using the system.' : 'All registration details will be deleted from the server shortly.';
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Company Registration</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$manager_name.',</h2>
                            <p style="color:#000;font-size:18px;">You registered company: <p style="font-style:bold; color:#1973B2;">'.$company_name.'</p>On our system.
                                <br>'.$status_message.
                            '</p>
                            <p style="font-style:italic;color:#000;">
                                Important notice: '.$notice_message.' 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Notice to the admin about a unblock request for a company user
    public static function CompanyUserUnblockRequest(string $company_name, string $username, string $info): string {
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>New Company Registration</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello,</h2>
                            <p style="color:#000;font-size:18px;">The manager of the company: '.$company_name.', have been requested to unblock the following user:<br></p>
                            <p style="color:#000;font-size:18px;">Username: '.$username.'<br>Additional Info:<br>'.$info.'</p>
                            <p style="font-style:italic;color:#000;">
                                Notice: You can unblock user in your account, on \'Users\' tab.
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Notice to the admin about a new registered company
    public static function NewCompanyAdminNotice(Company $company, User $manager): string {

        $specialties_arr = explode(' ', $company->getSpecialties());
        
        // create specialties list
        $specialties_list = '<ul>';
        
        foreach ($specialties_arr as $specialty) {
            $specialties_list .= '<li>'.$specialty.'</li>';
        }
        
        $specialties_list .= '</ul>';

        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>New Company Registration</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello,</h2>
                            <p style="color:#000;font-size:18px;">Company information:<br>Name: '.$company->getName().'<br>Description: '.$company->getDescription().'<br>Specialties:'.$specialties_list.'</p>
                            <p style="color:#000;font-size:18px;">Manager information:<br>Name: '.$manager->getFullName().'<br>Username: '.$manager->getUsername().'<br>Email: '.$manager->getEmail().'</p>
                            <p style="font-style:italic;color:#000;">
                                Important notice: You can set the company as approved in your account on \'Companies\' tab. 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Notice to the company about status change
    public static function CompanyStatusChange(string $manager_name, string $company_name, string $username, bool $is_blocked): string {
        
        $status_message = !$is_blocked ? 'Your account is now unblocked.' : 'Your account is now blocked.';
        $notice_message = !$is_blocked ? 'You can now login to your account and start using the system.' : 'Using of the account is forbidden.';
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Company Account Status Change</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$manager_name.',</h2>
                            <p style="color:#000;font-size:18px;">'.$status_message.'<br>Account Details:<br>
                                <span style="display:block;font-weight:bold; color:color:#1973B2"><br>Company name: '.$company_name.'<br>Username: '.$username.'</span>
                            </p>
                            <p style="font-style:italic;color:#000;">
                                Important notice: '.$notice_message.'
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Message to a newly added user to a company
    public static function NewAddedCompanyUser(string $name, User $manager, string $company_name): string {
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>You Have Been Joined: '.$company_name.'</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">You have been added to the '.$company_name.'\'s team on our system.<br>
                                You can now login to your account and start using the system.
                                <br>Additional details:<br>
                                <span style="display:block;font-weight:bold; color:color:#1973B2"><br>Manager name: '.$manager->getFullName().'<br>email: '.$manager->getEmail().'</span>
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Message to a company manager about newly joined user
    public static function NewJoinedCompanyUserManagerNotice(string $name, string $company_name, User $added_user): string {
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>'.$added_user->getFullName().' is joined '.$company_name.'!</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">A new user is joined to your team on our system.<br>
                                <br>User details:<br>
                                <span style="display:block;font-weight:bold; color:color:#1973B2"><br>Name: '.$added_user->getFullName().
                                '<br>Username: '.$added_user->getUsername().'<br>email: '.$added_user->getEmail().'</span>
                            </p>
                            <p style="font-style:italic;color:#000;">
                                Notice: You can now see the user in the \'Users\' tab in your account.
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Message to user about joining a company
    public static function NewCompanyUserAdd(string $name, User $manager, string $company_name, string $link): string {
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Join To Company</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">The manager of company:\''.$company_name.'\' is want to add you to their team on our site.
                            If you wish to join that company, please press the url below. <br>The activation link will expire after '.SystemConstants::UNVERIFIED_USER_TIME_LIMIT.' days.
                            <br>Company manager details:<br>
                            </p>
                            <p>
                            <span style="display:block;font-weight:bold;font-size:18px;">Name: '.$manager->getFullName().'<br>
                                Email: '.$manager->getEmail().'</span>
                            </p>
                            <p style="color:#000;font-size:18px;">Please click this link to activate your account:<br>
                            <a href='.$link.' target="_blank" style="cursor:pointer;font-size:14px;">'.$link.'</a></p>
                            <p style="font-style:italic;color:#000;">
                                Notice: If you don\'t want to join that company, ignore this message.
                            </p>                            
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Notice to the company about status change
    public static function UserStatusChange(string $name, string $username, bool $is_blocked): string {
        
        $status_message = !$is_blocked ? 'Your account is now unblocked.' : 'Your account is now blocked.';
        $notice_message = !$is_blocked ? 'You can now login to your account and start using the system.' : 'Using of the account is forbidden.';
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>User Account Status Change</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">'.$status_message.'<br>Account Details:<br>
                                <span style="display:block;font-weight:bold; color:color:#1973B2"><br>Username: '.$username.'</span>
                            </p>
                            <p style="font-style:italic;color:#000;">
                                Important notice: '.$notice_message.'
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Notice to user about question status change
    public static function QuestionStatusChanged(string $name, Question $question, bool $is_visible): string {
        
        $status_message = $is_visible ? 'Your question is now visible.' : 'Your question is now unvisible.';
        $notice_message = $is_visible ? 'Your question can be recieved by companies and can be replied.' : 'Your question is invisible to companies and can\'t be replied.';
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>User Account Status Change</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$name.',</h2>
                            <p style="color:#000;font-size:18px;">'.$status_message.'<br>Question Details:<br>
                                <span style="display:block;font-weight:bold; color:color:#1973B2"><br>Question id: '.$question->getId().'
                                <br>Question Type: '.$question->getType().'
                                <br>Title: '.$question->getTitle().'
                                <br>Description: '.$question->getDescription().'
                                <br>Publish Date: '.$question->getDateOpened().'
                            </span>
                            </p>
                            <p style="font-style:italic;color:#000;">
                                Important notice: '.$notice_message.'
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    
    // Notification to an advising user about new response
    public static function AdvisingUserResponseNotification(string $receiver_name, string $sender_name, Response $response): string {
        
        $message = '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>New Response Received</title>
                        </head>
                        <body style="direction:ltr;">
                            <img src="'.SystemConstants::LOGO_PATH.'" alt="WiserCut - Support" style="width:156px; height:58px; margin:25px auto; left:0; right:0">
                            <h2 style="color:#1973B2; font-size:22px;">Hello '.$receiver_name.',</h2>
                            <p style="color:#000;font-size:18px;">You have received a new response from '.$sender_name.', having the following details:<br>Response Title: '.$response->getTitle().'<br>Description:<br></p>
                            <p style="color:#000;font-size:15px;">'.$response->getDescription().'</p>
                            <p style="font-style:italic;color:#000;">
                                In order to get full details, please login to your account. 
                            </p>
                            <p style="font-style:italic;color:#1973B2;">
                                Best Regards,<br> WiseCut -Support
                            </p>
                        </body>
                    </html>';

        return $message;
    }
    // </editor-fold>
}
