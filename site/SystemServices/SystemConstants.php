<?php

/*
 * System constants
 * 
 * Holds constants used in the system
 */
abstract class SystemConstants {
    
    /* Constants */
    
    // Mail related
    public const ADMIN_MAIL = 'admin@example.com';
    public const SENDER_NAME = 'WiserCut - Support';
    public const SENDER_MAIL = 'admin@example.com';
    public const PASSWORD = '1234567';
    
    // Images
    public const LOGO_PATH = 'http://www.mysite.com/logo.png';
    public const DEFAULT_IMAGE_NAME = 'default.png';                                            // default image name for user picture and company logo
    public const ALLOWABLE_IMG_FILE_TYPES = ['gif', 'jpeg', 'jpg', 'png'];
    public const IMAGE_USER_PIC = 'user_picture';
    public const IMAGE_COMPANY_LOGO = 'company_logo';
    public const IMAGES_PATHS = [
        self::IMAGE_USER_PIC => '/Content/images/user_pictures/',
        self::IMAGE_COMPANY_LOGO => '/Content/images/companies_logos/'
    ];
    public const MAX_IMG_FILE_SIZE = 300;   // in Kb
    
    // Other
    public const UNVERIFIED_USER_TIME_LIMIT = 10;       // days passed since user registration
    public const MAX_LOGIN_ATTEMPTS = 10;               // login attemps from same session
    public const ADMIN_ID = 31;
    public const LOG_FILE_PATH = __DIR__.'/../system_log';
    public const NEW_USER_ACTIVATION = 'new_user';
    public const NEW_COMPANY_USER_ACTIVATION = 'new_company_user';
    public const ACTIVATION_LINK_PATHS = [
        self::NEW_USER_ACTIVATION => 'SystemServices/userVerify.php',
        self::NEW_COMPANY_USER_ACTIVATION => 'SystemServices/addUserToCompany.php'
    ];
}
