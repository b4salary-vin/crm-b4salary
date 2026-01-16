<?php
date_default_timezone_set('Asia/Kolkata');
foreach($_SERVER as $key => $value)
{
    define($key,$value);
}

defined('ADMIN_COOKIE')   		OR define('ADMIN_COOKIE', base64_encode('admin_logged_in'));
defined('CUSTOMER_COOKIE')      OR define('CUSTOMER_COOKIE', base64_encode('customer_logged_in'));
defined('CUSTOMER')             OR define('CUSTOMER', base64_encode('customer_logged_in'));

define('company_id',1);
define('product_id',1);
define('DB_SERVER','localhost');
//crm.b4salary.co.in
if($_SERVER['SERVER_ADDR']=== '172.31.41.52'):
    define("DB_NAME", 'vin_prod_b4salary');
    define("DB_USER", 'vin_b4salary');
    define('DB_PASSWORD','!@#QET_13#13vin%12');
    define('SIGNZY_TOKEN','4Se5pG4dzHqfjKzutq9iyCzdXcWQwSGp');
//uat.b4salary.co.in
elseif($_SERVER['SERVER_ADDR']=== '82.25.125.207'):
    define("DB_NAME", 'u635357561_b4salary');
    define("DB_USER", 'u635357561_b4salary');
    define('DB_PASSWORD','Omash@#247');
    define('SIGNZY_TOKEN','4Se5pG4dzHqfjKzutq9iyCzdXcWQwSGp');
    //define('SIGNZY_TOKEN','F6YPRp4dwdLv4LfKViNHztTktck4uRRy');
else:
    define("DB_NAME", 'lms_b4salary');
    define("DB_USER", 'root');
    define('DB_PASSWORD','');
    define('SIGNZY_TOKEN','4Se5pG4dzHqfjKzutq9iyCzdXcWQwSGp');
    //define('SIGNZY_TOKEN','F6YPRp4dwdLv4LfKViNHztTktck4uRRy');
endif;

define('ENVIRONMENT', ($_SERVER['SERVER_ADDR']!== '::1')?'production':'production');
define('BASE_URL',($_SERVER['SERVER_ADDR']!== '::1')?'https://'.SERVER_NAME:'http://'.SERVER_NAME);
define('CURLVAL',($_SERVER['SERVER_ADDR']!== '::1')?true:false);
define('COMP_ENVIRONMENT',ENVIRONMENT); 
define("COMPANY_NAME", "Kisga Leasing and Finance Pvt Ltd.");
define("COMPANY_ADDRESS", "B-7, New Multan Nagar, Paschim Vihar, New Delhi — 110056");
define("REGISTED_ADDRESS", "C-31, DSIDC, ROHTAK ROAD INDUSTRIAL COMPLEX, NANGLOI, DELHI, Delhi, India, 110041"); 
define("RBI_LICENCE_NUMBER", "B-14.01626");
define("CIN","U74899DL1993PTC053939");
define('CC_PHONE', '+91-7733866663');
define("WHATSAPP_PHONE", "+91-9289877932");
define('CONTACT_PERSON', 'Support Team');
define('PHONE1', '+91-7733866663');
define('TITLE', 'B4SALARY : ');
define("BRAND", "B4SALARY");
define("BRAND_NAME", "B4SALARY");
define("BRAND_ACRONYM", "B4S");
define("DOMAIN","b4salary.com");
define("CC_EMAIL", "care@".DOMAIN);
define("SUPPORT_EMAIL", "support@".DOMAIN);
define("INFO_EMAIL", "info@".DOMAIN);
define("CTO_EMAIL", "cto@".DOMAIN);
define("TECH_EMAIL", "tech@".DOMAIN);
define("RECOVERY_EMAIL", "recovery@".DOMAIN);
define("COLLECTION_EMAIL", "collection@".DOMAIN);
define("ACCOUNTS_EMAIL", "accounts@".DOMAIN);
define("NOREPLY_EMAIL", "no-reply@".DOMAIN);
define('ALL_FROM_EMAIL', 'info@'.DOMAIN);
define('BCC_SANCTION_EMAIL', 'info@'.DOMAIN);
define('BCC_DISBURSAL_EMAIL', '');
define('BCC_NOC_EMAIL', 'info@'.DOMAIN);
define('BCC_DISBURSAL_WAIVE_EMAIL', 'info@'.DOMAIN);
define('CC_SANCTION_EMAIL', '');
define('CC_DISBURSAL_EMAIL', '');
define('CC_DISBURSAL_WAIVE_EMAIL', 'info@'.DOMAIN);
define('TO_KYC_DOCS_ZIP_DOWNLOAD_EMAIL', 'info@'.DOMAIN);



define('GRIEVANCE_PERSON', 'ROHIT AGGARWAL');
define('GRIEVANCE_PHONE', '9810016791');
define('GRIEVANCE_EMAIL', 'grievance@'.DOMAIN);

define('DS','/');
define("LMS_URL", BASE_URL);
define('API_URL',BASE_URL.DS.'api'.DS);
define("WEBSITE", DOMAIN);
define('WEBSITE_URL','https://'.DOMAIN.DS);

define('PUBLIC_PATH', DOCUMENT_ROOT.DS.'public'.DS);
define('PUBLIC_URL', WEBSITE_URL.'public'.DS);
define('UPLOAD_PATH', DOCUMENT_ROOT.DS.'upload'.DS);
define('UPLOAD_URL', LMS_URL.'upload'.DS);
define("TEMP_DOC_PATH", DOCUMENT_ROOT.DS.'temp_upload'.DS);
define("TEMP_DOC_URL", LMS_URL.'temp_upload'.DS);
define('IMAGES',    PUBLIC_URL.'images'.DS);
define("COMP_PATH", DOCUMENT_ROOT.DS.'components'.DS);
define("COMPONENT_PATH",  COMP_PATH);

define("UPLOAD_LEGAL_PATH", UPLOAD_PATH);
define("UPLOAD_SETTLEMENT_PATH", UPLOAD_PATH);
define("UPLOAD_RECOVERY_PATH", UPLOAD_PATH);
define("TEMP_UPLOAD_PATH", UPLOAD_PATH);
define("UPLOAD_DISBURSAL_PATH", UPLOAD_PATH);

define('FAVICON_16',LMS_URL.'favicon.ico');
define('FAVICON_32',FAVICON_16);
define('LOGO',PUBLIC_URL.'brand/logo.png');
define("LMS_COMPANY_WHITE_LOGO",LOGO);
define("LMS_COMPANY_LOGO", LOGO);
define("LMS_BRAND_LOGO", LOGO);
define("EMAIL_BRAND_LOGO", LOGO);
define('WEB_BANNER_1',PUBLIC_URL.'banners/banner_1.jpg');
define('WEB_BANNER_2',PUBLIC_URL.'banners/banner_2.jpg');
define('WEB_BANNER_3',PUBLIC_URL.'banners/banner_3.jpg');


define('CSS_VERSION', 1.1); // highest automatically-assigned error code
//define("LOANS_KYC_DOCS", "/kycdocs/loans/");
define("FEEDBACK_WEB_PATH", LMS_URL . "customer-feedback/");
// ********** API URL DEFINE *****


// ********** LMS DEFINED VARIABLE *****

define("WEBSITE_UTM_SOURCE", WEBSITE_URL . "apply-now?utm_source=");
define("New_HEADER_BG", IMAGES."New_HEADER_BG.jpg");
define("LMS_KASAR_LETTERHEAD", IMAGES."kasar_letter_head_bg.jpg");
define("SANCTION_LETTER_HEADER", LMS_URL . "public/emailimages/header.png");
define("SANCTION_LETTER_FOOTER", IMAGES."kasar_letter_footer.jpg");
define("DISBURSAL_LETTER_BANNER", LMS_URL . "public/emailimages/disbursal_banner.png");
define("AUTHORISED_SIGNATORY", LMS_URL . "public/images/Authorised-Signatory.jpeg");
define("BANK_STATEMENT_UPLOAD", LMS_URL . "application/helpers/integration/");



// ********** TEMPLETE DEFINED VARIABLE *****

define("GENERATE_SANCTION_LETTER_HEADER", LMS_URL . "public/emailimages/header.png");
define("GENERATE_SANCTION_LETTER_FOOTER", LMS_URL . "public/emailimages/sanction-letterfooter.png");
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

//******** Start Advocate Mail Constant *******************//
define("ADVOCATE_HEADER", IMAGES."LEGAL_HD.jpg");
define("ADVOCATE_SIGN", IMAGES."sign-lg.jpg");
define("ADVOCATE_LOGO", IMAGES."advocate-lg.jpg");
define("ADVOCATE_MOBILE1", LMS_URL . "99101-52173");
define("ADVOCATE_MOBILE2", LMS_URL . "92898-77841");
define("ADVOCATE_MAIL", LMS_URL . "FAUJDARAJAY99@GMAIL.COM");
define("ADVOCATE_COMPANY_MAIL", LMS_URL . "advocateharmod@rupeemitra.com");
define("LOAN_REPAY_LINK", WEBSITE_URL . "pay-now");
define("REPAYMENT_REPAY_LINK", WEBSITE_URL . "pay-now");

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
// *********SOCIAL MEDIA LINK ********



// ******* SOCIAL MEDIA ICONS & LINKS***********
define("PHONE_ICON", PUBLIC_URL . "social/phone-icon.png");
define("EMAIL_ICON", PUBLIC_URL . "social/emil_icon.png");
define("LINKEDIN_ICON", PUBLIC_URL . "social/linkedin.png");
define("LINKEDIN_LINK", "https://www.linkedin.com/");
define("INSTAGRAM_ICON", PUBLIC_URL . "social/instagram.png");
define("INSTAGRAM_LINK", "https://www.instagram.com/");
define("FACEBOOK_ICON", PUBLIC_URL . "social/facebook.png");
define("FACEBOOK_LINK", "https://www.facebook.com/");
define("YOUTUBE_ICON", PUBLIC_URL . "social/youtube.png");
define("YOUTUBE_LINK", "https://www.youtube.com/");
define("TWITTER_ICON", PUBLIC_URL . "social/twitter.png");
define("TWITTER_LINK", "https://x.com/");
define("APPLE_STORE_ICON", PUBLIC_URL . "social/appleLogo.jpeg");
define("APPLE_STORE_LINK", "https://play.google.com/store/apps/details?id=");
define("ANDROID_STORE_ICON", PUBLIC_URL . "social/playLogo.jpeg");
define("ANDROID_STORE_LINK", "https://apps.apple.com/in/app/");
define("WEB_ICON", PUBLIC_URL . "social/web_icon.png");


// ******* CRON JOBS ********
define("BIRTHDAY_LINE", LMS_URL . "public/emailimages/birthday/line.png");
define("BIRTHDAY_BIRTHDAY_PIC", LMS_URL . "public/emailimages/birthday/email-design.jpg");
define("FESTIVAL_BANNER", LMS_URL . "public/emailimages/festiv/image/offer.jpg");
define("FESTIVAL_CLOSE_BANNER", LMS_URL . "public/emailimages/new-cust/image/b.jpg");
define("FESTIVAL_OFFICIAL_NUMBER", LMS_URL . "public/emailimages/festiv/image/phone-icon.png");
define("FESTIVAL_LINE", LMS_URL . "public/emailimages/festiv/image/line.png");
define("BLOG", LMS_URL . "public/blog/");
define("WEBSITE_DOCUMENT_BASE_URL",  "https://example-website.s3.ap-south-1.amazonaws.com/upload/");

define("COLLEX_DOC_URL", LMS_URL . 'direct-document-file/'); //production
define("AWS_S3_FLAG", true); //true=> Store in S3 bucket , false=> Physical store.    //5feb25
//define("WEBSITE_DOCUMENT_BASE_URL", getenv("S3_BASE_URL") . "/upload/");
define("LMS_DOC_S3_FLAG", true);

define("COMP_DOC_URL", LMS_URL . DS . 'direct-document-file/'); //production

// define("COMP_ANDROID_STORE_ID", getenv('COMP_ANDROID_STORE_ID'));
// define("WHATSAPP_LOAN_OFFER", getenv('WHATSAPP_LOAN_OFFER'));
// define("WHATSAPP_BASE_URL", getenv('WHATSAPP_BASE_URL'));
// define("COMP_BUREAU_PROVIDER", getenv('COMP_BUREAU_PROVIDER')); //GET_BUREAU_SCORE(CRIF) GET_CIBIL_SCORE(CIBIL)


//define('PAN_FETCH','https://api-preproduction.signzy.app/api/v3/panextensive');

define('VAPIO_USER','kisga');
define('VAPIO_SENDERID',strtoupper('kisga'));
define('VAPIO_URL','https://vapio.in/api.php');
define('VAPIO_PEID','1101557670000090661');
define('VAPIO_KEY','DJhgTi3ofqGz');

define("AA_PROVIDER", '');
define("PAYMENTS", '');

define("S3_BUCKET_ACCESS_KEY", 'AKIA3CAGC77CZCI5YFVC');
define("S3_BUCKET_SECRET_KEY", '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');
define("S3_BUCKET_NAME", 'b4salary');

//echo "<pre>"; print_r( get_defined_constants()); exit;
