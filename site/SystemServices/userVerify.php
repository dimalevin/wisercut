<?php

/*
 * Allowing to verify user by checking the details provided thru the supplied link
 */

require_once __DIR__.'/ClassLoader.php';

// parameter is exists and are not empty
if (!empty($_GET['username']) && !empty($_GET['passphrase'])) {
    
    $username = $_GET['username'];
    
    $passphrase = $_GET['passphrase'];
    
    $is_verified = SystemServices::ApproveNewUser($username, $passphrase);
    
    // the user has been verified
    if ($is_verified) {
        
        
        $main_msg = 'Your account has been activated, you can now login.';
        
        $notice = '<div style="text-transform: uppercase;color: #0e0e0e;font-size: 15px; text-align: center;">
                               <br>(in case of advising user, your company manager should add you to their team) 
                            </div>';
        $msg = $main_msg.$notice;
        
    } else { $msg = 'The url is either invalid or you already have activated your account.'; }
    
    
} else { $msg = 'Invalid approach, please use the link that has been send to your email.'; }

echo '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Account Verification</title>
                        </head>
                        <body>
                            <div style="position: relative;display: block;top: 28vh;margin: 0 auto;text-transform: uppercase;color: #0e0e0e;font-size: 22px; text-align: center;width: 450px;height: 120px;background: #d8d8d8;border: 3px solid #838383;padding-top: 3%;">
                                <div class="statusmsg">'.$msg.'</div>
                            </div>
                        </body>
                    </html>';
