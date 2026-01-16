<?php

defined('BASEPATH') or exit('No direct script access allowed');
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', TRUE);
defined('FILE_READ_MODE') or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') or define('DIR_WRITE_MODE', 0755);
defined('FOPEN_READ') or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');
defined('EXIT_SUCCESS') or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/**** 

define("LMS_URL", getenv("base_url"));
define("WEBSITE_URL", getenv('web_site_url'));
define("COMPONENT_PATH", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "common_component" . DIRECTORY_SEPARATOR);
define("WEBSITE", getenv('BRAND_NAME'));
define("WEBSITE_UTM_SOURCE", WEBSITE_URL . "apply-now?utm_source=");




define("UPLOAD_PATH", getenv("base_path") . "upload/");
define("UPLOAD_LEGAL_PATH", getenv("base_path") . "upload/");
define("UPLOAD_SETTLEMENT_PATH", getenv("base_path") . "upload/");
define("UPLOAD_RECOVERY_PATH", getenv("base_path") . "upload/");
define("TEMP_UPLOAD_PATH", getenv("base_path") . "upload/");
define("UPLOAD_DISBURSAL_PATH", getenv("base_path") . "upload/");

define("LMS_DOC_S3_FLAG", true); //true=> Store in S3 bucket , false=> Physical store.


//define("LOANS_KYC_DOCS", "/kycdocs/loans/");

define("FEEDBACK_WEB_PATH", WEBSITE_URL . "/customer-feedback/");

// ********** API URL DEFINE *****

defined('SERVER_API_URL') or define('SERVER_API_URL', getenv('api_base_url')); //SERVER API URL

// ********** LMS DEFINED VARIABLE *****

define('PUBLIC_IMAGES', LMS_URL . DIRECTORY_SEPARATOR . "public/public_images/");

define("LMS_COMPANY_LOGO", PUBLIC_IMAGES . "company_logo.png");
define("LMS_COMPANY_WHITE_LOGO", PUBLIC_IMAGES . "company_logo_white.png");
define("LMS_BRAND_LOGO", LMS_COMPANY_LOGO);
define("EMAIL_BRAND_LOGO", LMS_COMPANY_LOGO);
define("LMS_COMPANY_MIS_LOGO", PUBLIC_IMAGES . "company_logo.jpg");
define("LMS_KASAR_LETTERHEAD", PUBLIC_IMAGES . "letter_head.png");
define("SANCTION_LETTER_HEADER", PUBLIC_IMAGES . "letter_head.png");
define("SANCTION_LETTER_FOOTER", PUBLIC_IMAGES . "letter_footer.png");




define("BANK_STATEMENT_UPLOAD", LMS_URL . "application/helpers/integration/");
define("COMPANY_NAME", getenv('COMPANY_NAME'));
define("RBI_LICENCE_NUMBER", getenv('RBI_LICENCE_NUMBER'));
define('CONTACT_PERSON', getenv('CONTACT_PERSON'));
define("REGISTED_ADDRESS", getenv('REGISTED_ADDRESS'));
define("REGISTED_MOBILE", getenv('REGISTED_MOBILE'));
define("BRAND_NAME", getenv('BRAND_NAME'));



//EMAIL Declarations
define('EMAIL_DOMAIN', '@gmail.com');
define("INFO_EMAIL", "omashatech" . EMAIL_DOMAIN);
define("TECH_EMAIL", "omashatech" . EMAIL_DOMAIN);
define("CTO_EMAIL", "omashataech" . EMAIL_DOMAIN);

define("CARE_EMAIL", "omashataech" . EMAIL_DOMAIN);
define("RECOVERY_EMAIL", "recovery" . EMAIL_DOMAIN);
define("COLLECTION_EMAIL", "collection" . EMAIL_DOMAIN);
define('CRON_EMAIL', 'support' . EMAIL_DOMAIN);
define("LEGAL_EMAIL", "legalteam" . EMAIL_DOMAIN);
define("ADVOCATE_EMAIL", "advocate" . EMAIL_DOMAIN);
define("POSTMASTER_EMAIL", "postmaster" . EMAIL_DOMAIN);
define("NOREPLY_EMAIL", "no-reply" . EMAIL_DOMAIN);

define('ALL_FROM_EMAIL', 'support' . EMAIL_DOMAIN);
define('BCC_SANCTION_EMAIL', 'support' . EMAIL_DOMAIN);
define('BCC_DISBURSAL_EMAIL', '');
define('BCC_NOC_EMAIL', 'support' . EMAIL_DOMAIN);
define('BCC_DISBURSAL_WAIVE_EMAIL', 'support' . EMAIL_DOMAIN);
define('CC_SANCTION_EMAIL', '');
define('CC_DISBURSAL_EMAIL', '');
define('CC_DISBURSAL_WAIVE_EMAIL', 'support' . EMAIL_DOMAIN);
define('TO_KYC_DOCS_ZIP_DOWNLOAD_EMAIL', 'support' . EMAIL_DOMAIN);


// ********** TEMPLETE DEFINED VARIABLE *****

define("DISBURSAL_LETTER_BANNER", LMS_URL . "public/emailimages/disbursal_banner.png");
define("GENERATE_SANCTION_LETTER_HEADER", LMS_URL . "public/emailimages/header.png");
define("GENERATE_SANCTION_LETTER_FOOTER", LMS_URL . "emailimages/sanction-letterfooter.png");

define("EKYC_BRAND_LOGO", LMS_URL . "public/emailimages/Digilocker_eKyc/images/ekyc_brand_logo.gif");
define("EKYC_HEADER_BACK", LMS_URL . "public/emailimages/Digilocker_eKyc/images/header_back.jpg");
define("EKYC_LINES", LMS_URL . "public/emailimages/Digilocker_eKyc/images/line.png");
define("EKYC_IMAGES_1", LMS_URL . "public/emailimages/Digilocker_eKyc/images/1st.jpg");
define("EKYC_IMAGES_1_SHOW", LMS_URL . "public/emailimages/Digilocker_eKyc/images/image1.png");
define("EKYC_IMAGES_2", LMS_URL . "public/emailimages/Digilocker_eKyc/images/2nd.jpg");
define("EKYC_IMAGES_2_SHOW", LMS_URL . "public/emailimages/Digilocker_eKyc/images/image2.png");
define("EKYC_IMAGES_3", LMS_URL . "public/emailimages/Digilocker_eKyc/images/image3.png");
define("EKYC_IMAGES_3_SHOW", LMS_URL . "public/emailimages/Digilocker_eKyc/images/3rd.jpg");
define("EKYC_IMAGES_4", LMS_URL . "public/emailimages/Digilocker_eKyc/images/4th.jpg");
define("EKYC_IMAGES_4_SHOW", LMS_URL . "public/Digilocker_eKyc/images/4th.jpg");

define("ADVOCATE_SIGN", LMS_URL . "public/images/sign-lg.jpg");
define("ADVOCATE_LOGO", LMS_URL . "public/images/advocate-lg.jpg");
define("New_HEADER_BG", LMS_URL . "public/images/New_HEADER_BG.jpg");
define("ADVOCATE_HEADER", LMS_URL . "public/images/LEGAL_HD.jpg");
define("ADVOCATE_MOBILE1", LMS_URL . "99101-52173");
define("ADVOCATE_MOBILE2", LMS_URL . "92898-77841");
define("ADVOCATE_MAIL", "FAUJDARAJAY99@GMAIL.COM");
define("ADVOCATE_COMPANY_MAIL", ADVOCATE_EMAIL);

define("LOAN_REPAY_LINK", WEBSITE_URL . "repay-loan");
define("REPAYMENT_REPAY_LINK", LMS_URL . "repay-loan-details");
define("AUTHORISED_SIGNATORY", LMS_URL . "public/front/images/Authorised-Signatory.jpeg");

define("PRE_APPROVED_LINES", LMS_URL . "public/emailimages/final-email-template/images/back-line.png");
define("PRE_APPROVED_BANNER", LMS_URL . "public/emailimages/final-email-template/images/email-salaryontime.gif");
define("PRE_APPROVED_LINE_COLOR", LMS_URL . "public/emailimages/final-email-template/images/line-color.png");
define("PRE_APPROVED_PHONE_ICON", LMS_URL . "public/emailimages/final-email-template/images/phone-icon.png");
define("PRE_APPROVED_WEB_ICON", LMS_URL . "public/emailimages/final-email-template/images/web-icon.png");
define("PRE_APPROVED_EMAIL_ICON", LMS_URL . "public/emailimages/final-email-template/images/emil-icon.png");
define("PRE_APPROVED_ARROW_LEFT", LMS_URL . "public/emailimages/final-email-template/images/arrow-left.png");
define("PRE_APPROVED_ARROW_RIGHT", LMS_URL . "public/emailimages/final-email-template/images/arrow-right.png");

define("FEEDBACK_HEADER", LMS_URL . "public/emailimages/feedback/images/header2.jpg");
define("FEEDBACK_LINE", LMS_URL . "public/emailimages/feedback/images/line.png");
define("FEEDBACK_PHONE_ICON", LMS_URL . "public/emailimages/feedback/images/phone-icon.png");
define("FEEDBACK_WEB_ICON", LMS_URL . "public/emailimages/feedback/images/web-icon.png");
define("FEEDBACK_EMAIL_ICON", LMS_URL . "public/emailimages/feedback/images/email-icon.png");

define("COLLECTION_BRAND_LOGO", LMS_URL . "public/emailimages/collection/image/lw-logo.png");
define("COLLECTION_EXE_BANNER", LMS_URL . "public/emailimages/collection/image/Collection-Executive-banner.jpg");
define("COLLECTION_LINE", LMS_URL . "public/emailimages/collection/image/line.png");
define("COLLECTION_INR_ICON", LMS_URL . "public/emailimages/collection/image/inr-icon.png");
define("COLLECTION_ROAD_BANNER", LMS_URL . "public/emailimages/collection/image/CRM.jpg");
define("COLLECTION_PHONE_ICON", LMS_URL . "public/emailimages/collection/image/phone-icon.png");
define("COLLECTION_EMAIL_ICON", LMS_URL . "public/emailimages/collection/image/emil-icon.png");
define("COLLECTION_WEB_ICON", LMS_URL . "public/emailimages/collection/image/web-icon.png");
define("LINKEDIN_LINK", getenv('LINKEDIN_LINK'));
define("INSTAGRAM_LINK", getenv('INSTAGRAM_LINK'));
define("FACEBOOK_LINK", getenv('FACEBOOK_LINK'));
define("YOUTUBE_LINK", getenv('YOUTUBE_LINK'));
define("TWITTER_LINK", getenv('TWITTER_LINK'));
define("FACEBOOK_ICON", PUBLIC_IMAGES . "facebook.png");
define("LINKEDIN_ICON", PUBLIC_IMAGES . "linked.png");
define("INSTAGRAM_ICON", PUBLIC_IMAGES . "instagram.png");
define("TWITTER_ICON", PUBLIC_IMAGES . "twitter.png");
define("YOUTUBE_ICON", PUBLIC_IMAGES . "youtube.png");
define("PHONE_LINK", "");
define("PHONE_ICON", PUBLIC_IMAGES . "phone.png");
define("URL_LINK", "");
define("URL_ICON", PUBLIC_IMAGES . "web.png");
define("WHATSAPP_LINK", "");
define("WHATSAPP_ICON", PUBLIC_IMAGES . "whatsapp.png");
define("EMAIL_LINK", "");
define("EMAIL_ICON", PUBLIC_IMAGES . "email.png");
define("WEB_ICON", PUBLIC_IMAGES . "web.png");
define("APPLE_STORE_ICON", LMS_URL . "public/new_images/images/appleLogo.jpeg");
define("ANDROID_STORE_ICON", LMS_URL . "public/new_images/images/playLogo.jpeg");
define("BIRTHDAY_LINE", LMS_URL . "public/emailimages/birthday/line.png");
define("BIRTHDAY_BIRTHDAY_PIC", LMS_URL . "public/emailimages/birthday/email-design.jpg");
define("FESTIVAL_BANNER", LMS_URL . "public/emailimages/festiv/image/offer.jpg");
define("FESTIVAL_CLOSE_BANNER", LMS_URL . "public/emailimages/new-cust/image/b.jpg");
define("FESTIVAL_OFFICIAL_NUMBER", LMS_URL . "public/emailimages/festiv/image/phone-icon.png");
define("FESTIVAL_LINE", LMS_URL . "public/emailimages/festiv/image/line.png");
define("BLOG", LMS_URL . "public/blog/");
define("WEBSITE_DOCUMENT_BASE_URL", getenv("S3_BASE_URL") . "/upload/");
define("COMP_PATH", $xco_path);
define("COLLEX_DOC_URL", LMS_URL . 'direct-document-file/');
define("COLLECTION_PHONE", '+919254984076');
define("COMPANY_LOGO_WHITE", PUBLIC_IMAGES . "company_logo_white.png");
define('WEB_DOMAIN', 'suryaloan.com');
define('PAYMENTS', 'https://payments.' . WEB_DOMAIN);
define('ENACH', 'https://enach.' . WEB_DOMAIN);
define('BRAND', 'SURYA LOAN');
define('BRAND_ACRONYM', 'SL');
define('LMS', $_SERVER['HTTP_HOST']);
define("SMS_ALTERNATE", FALSE);
define("SMS_PROVIDER", "VAPIO");
define("SMS_USERNAME", 'raghavi');
define("SMS_PASSWORD", 'raghavi@123');
define("SMS_HEADER", 'RFLINS');
define("SMS_PE_ID", '1701172907256286333');
define("SMS_DLR", 1);
*/