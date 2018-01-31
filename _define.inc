<?php

defined('PS') ? NULL : define('PS', '/');
defined('DS') ? NULL : define('DS', DIRECTORY_SEPARATOR);
defined('EXT') ? NULL : define('EXT', '.php');
defined('REQ_PATH') ? NULL : define('REQ_PATH', $_SERVER['REQUEST_URI']);
defined('WEB_PATH') ? NULL : define('WEB_PATH', 'http://'.$_SERVER['HTTP_HOST'].PS.'www.rehand.com'.PS);
defined('DOC_ROOT') ? NULL : define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'].DS.'www.rehand.com'.DS);
defined('LIBRARY_PATH') ? NULL : define('LIBRARY_PATH', DOC_ROOT.'library'.DS.'');
defined('CONTROLLER_PATH') ? NULL : define('CONTROLLER_PATH', DOC_ROOT.DS.'application'.DS.'classes'.DS.'controllers'.DS);
defined('MODEL_PATH') ? NULL : define('MODEL_PATH', DOC_ROOT.DS.'application'.DS.'classes'.DS.'models'.DS);
defined('VIEW_PATH') ? NULL : define('VIEW_PATH', DOC_ROOT.DS.'application'.DS.'views'.DS);
defined('PUBLIC_PATH') ? NULL : define('PUBLIC_PATH', DOC_ROOT.DS.'public'.DS);
defined('LAYOUT_PATH') ? NULL : define('LAYOUT_PATH', PUBLIC_PATH.'layouts'.DS);
defined('JS_PATH') ? NULL : define('JS_PATH', WEB_PATH.'public'.PS.'js'.PS);
defined('CSS_PATH') ? NULL : define('CSS_PATH', WEB_PATH.'public'.PS.'css'.PS);
defined('SITE_IMAGES_PATH') ? NULL : define('SITE_IMAGES_PATH', WEB_PATH.PS.'public'.PS.'images'.PS);
defined('UPLOADED_LARGE_IMAGES_PATH') ? NULL : define('UPLOADED_LARGE_IMAGES_PATH', DS.'uploaded'.DS.'taggings'.DS);
defined('UPLOADED_LARGE_IMAGES_URL_PATH') ? NULL : define('UPLOADED_LARGE_IMAGES_URL_PATH', WEB_PATH.'uploaded'.PS.'taggings'.PS);
defined('UPLOADED_THUMB_IMAGES_PATH') ? NULL : define('UPLOADED_THUMB_IMAGES_PATH', DS.'uploaded'.DS.'taggings'.DS.'thumbnails'.DS);
defined('UPLOADED_THUMB_IMAGES_URL_PATH') ? NULL : define('UPLOADED_THUMB_IMAGES_URL_PATH', WEB_PATH.'uploaded'.PS.'taggings'.PS.'thumbnails'.PS);
defined('NO_OF_RECORDS_PER_PAGE') ? NULL : define('NO_OF_RECORDS_PER_PAGE', 10);

// This is fo the profile image display
defined('UPLOADED_PROFILE_IMAGES_PATH') ? NULL : define('UPLOADED_PROFILE_IMAGES_PATH', DOC_ROOT.'uploaded'.DS.'profiles'.DS);
defined('UPLOADED_PROFILE_IMAGES_URL_PATH') ? NULL : define('UPLOADED_PROFILE_IMAGES_URL_PATH', WEB_PATH.'uploaded'.PS.'profiles'.PS);

defined('SERVER') ? NULL : define('SERVER', 'localhost');
defined('USER') ? NULL : define('USER', 'root');
defined('PWORD') ? NULL : define('PWORD', '');
defined('DBASE') ? NULL: define('DBASE', 'rehand');
defined('RECORDS_PER_PAGE') ? NULL: define('RECORDS_PER_PAGE', 8);
defined('FACEBOOK_APP_ID_DEV') ? NULL: define('FACEBOOK_APP_ID_DEV', '355121614512570');
defined('FACEBOOK_SECRET_DEV') ? NULL: define('FACEBOOK_SECRET_DEV', 'fe115f6f76af19a97675acf410f94c49');
defined('HASHKEY') ? NULL : define('HASHKEY', 'mysecretkey');

class _define {
    
    public $db_param = array(
                    'localhost' => SERVER,
                    'user' => USER,
                    'password' => PWORD,
                    'database' => DBASE
                );
}