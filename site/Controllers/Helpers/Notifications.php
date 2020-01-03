<?php

/*
 * Notifications
 * 
 * Stores system messages
 */
abstract class Notifications {

    /* Constants */
    public const ACCESS_DENIED = 'Access denied for this function!';
    public const ADVICES_LIMIT_REACHED = 'You have been reached your daily advice limit.';
    public const ADVICE_ADDED = 'Your advice is added successfuly.';
    public const ADVICE_ALREADY_GIVEN = 'You have already submited an advice to this question.';
    public const ADVISING_USER_NO_COMPANY = 'You cannot login to your account because it doesn\'t belong to any company. Please contact your company manager.';
    public const BLOCKED_ACCOUNT = 'This account is blocked. Please contact support.';
    public const BLOCKED_COMPANY = 'Your company seems to be blocked. Please contact support.';
    public const BLOCKED_FALSE_ATTEMPS = 'This option has been blocked after too many false attemps.';
    public const COMPANY_ADDED_TO_BLOCKED_LIST = 'The company is added to the blocked list. You will no longer receive advices from it.';
    public const COMPANY_APPROVED = 'The company is approved successfuly.';
    public const COMPANY_NOT_APPROVED = 'Your company is not approved yet.';
    public const COMPANY_REMOVED_FROM_BLOCKED_LIST = 'The company is removed from the blocked list. You will start to receive advices from it.';
    public const COMPANY_STATUS_CHANGED = 'The company status is changed successfuly.';
    public const COMPANY_ADDUSER_LINK_SENDED = 'Activation link is sent to the user. You will be notified if the user has been choosed to join your team.';
    public const COMPANY_ADDUSER_BELONGS_TO_ANOTHER_COMPANY = 'This user has been registered to another company.';
    public const COMPANY_ADDUSER_ERROR = 'This user either not of correct type, blocked, not approved or registered with different email.';
    public const CONTINUE_REGISTRATION_NOTICE = 'Please check your email to complete the registration proccess.';
    public const DATABASE_ERROR = 'Database error. Please try again later.';
    public const DAILY_QUESTIONS_LIMIT_REACHED = 'Sorry, you reached your daily questions limit. Please try again later...';
    public const EMAIL_TAKEN = 'That email is already registered. Please try another or login to your account.';
    public const GENERAL_ERROR = 'Sorry, error event has been occured. Please try again...';
    public const IMAGE_FILE_CORRUPTED = 'Image file seems to be corrupted.';
    public const IMAGE_FILE_TYPE_NOT_SUPPORTED = 'Image file type is not supported!';
    public const IMAGE_MAX_FILE_SIZE_EXCEEDED = 'Image file size is too big!';
    public const LOG_OUT = 'Logged out successfuly.';
    public const LOGIN_ERROR = 'User not found or wrong password.';
    public const MESSAGE_SENT = 'Message is sent.';
    public const MESSAGES_DELETED = 'Message\s is deleted.';
    public const NOT_ACTIVATED_ACCOUNT = 'This account is not activated yet, please check your email.';
    public const NUMBER_LOGIN_ATTEMPS = 'Login Attempts Exceeded. Please try again later...';
    public const PASSWORD_RECOVERY_NOT_ACTIVE_ACCOUNT = 'This account is blocked or not verified. Please contact support.';
    public const PASSWORD_RECOVERY_NOTICE = 'The new password has been sent to the provided email.';
    public const PASSWORD_RECOVERY_WRONG_USERNAME_EMAIL = 'Account not found or wrong email provided.';
    public const QUESTION_CLOSED_OR_RESPONSE_GIVEN = 'The question is closed or you have already submited a response to this advice.';
    public const QUESTION_CLOSED_SUCCESSFULY = 'The question has been marked as closed.';
    public const QUESTION_IS_CLOSED = 'This question seems to be closed.';
    public const QUESTION_STATUS_CHANGED = 'The question status is changed successfuly.';
    public const REQUEST_IS_SENT = 'Your request is sent.';
    public const RESPONSE_ADDED_SUCCESSFULY = 'Your response is added successfuly.';
    public const SETTINGS_SAVED_SUCCESSFULY = 'The settings is changed successfuly.';
    public const TRY_RELOAD_PAGE = 'Something went wrong. Please reload the page and try again later.';
    public const TYPE_ERROR = 'error';
    public const TYPE_NOTICE = 'info';
    public const TYPE_WARNING = 'warning';
    public const USER_STATUS_CHANGED = 'The user status is changed successfuly.';
    public const USERNAME_TAKEN = 'That username is taken. Please try another.';
}
