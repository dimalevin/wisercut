<?php

/*
 * Allowing to add an user to a company by checking the details provided thru the supplied link
 */

require_once __DIR__.'/ClassLoader.php';

// parameter is exists and are not empty
if (!empty($_GET['username']) && !empty($_GET['passphrase']) && !empty($_GET['id'])) {
    
    $username = $_GET['username'];
    $passphrase = $_GET['passphrase'];
    $company_id = $_GET['id'];
    
    $is_added = SystemServices::AddNewCompanyUser($username, $passphrase, $company_id);
    
    // the user has been added
    if ($is_added) {
        
        $msg = 'You are added to the company team. You can now login to your account.';
            
    } else { $msg = 'The url is either invalid or you already have activated your account.'; }
    
    
} else { $msg = 'Invalid approach, please use the link that has been send to your email.'; }

echo '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Account Verification</title>
                        </head>
                        <body>
                            <div style="position: relative;display: block;top: 28vh;margin: 0 auto;text-transform: uppercase;color: #0e0e0e;font-size: 22px; text-align: center;width: 450px;height: 100px;background: #d8d8d8;border: 3px solid #838383;padding-top: 3%;">
                                <div class="statusmsg">' . $msg . '</div>
                            </div>
                        </body>
                    </html>';
