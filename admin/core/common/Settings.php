<?php
  namespace prosys\core\common;

  /**
   * Global settings of the application.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class Settings
  {
    // database
    const DB_SERVER = '';
    const DB_USER = '';
    const DB_PASSWORD = '';
    const DB_DATABASE = '';
    const DB_PREFIX = '';
    
    const SKCZ_DB_SERVER = '';
    const SKCZ_DB_USER = '';
    const SKCZ_DB_PASSWORD = '';
    const SKCZ_DB_DATABASE = '';
    const SKCZ_DB_PREFIX = '';
    
    const OKCZ_DB_SERVER = '';
    const OKCZ_DB_USER = '';
    const OKCZ_DB_PASSWORD = '';
    const OKCZ_DB_DATABASE = '';
    const OKCZ_DB_PREFIX = '';
    
    const OKSK_DB_SERVER = '';
    const OKSK_DB_USER = '';
    const OKSK_DB_PASSWORD = '';
    const OKSK_DB_DATABASE = '';
    const OKSK_DB_PREFIX = '';
    
    // general
    const WEB_NAME = 'Service StylePlus.cz';
    const WEB_NAME_HTML = 'Service <span style="text-transform: none;">StylePlus.cz</span>';
    const WEB_TITLE_HOME = 'service.styleplus.cz - Generátor seznamu výrobků StylePlus.cz';
    const WEB_TITLE = 'service.styleplus.cz';
    const ITEMS_PER_PAGE = 20;
    const DATE_FORMAT = 'd.m.Y';
    const TIME_FORMAT = 'H:i:s';
    const DATETIME_FORMAT = 'd.m.Y H:i:s';
    const PRICE_DECIMALS = 0;
    const SHOW_DELETED_PREDICATE = '$FORCE_SHOWING_DELETED$';
    const BINDING_TYPE_PROPERTY_LABEL = '$BINDING_TYPE_PROPERTY$';
    const SERVICE_ACTION_SCRIPT_URL = SETTINGS_SERVICE_ACTION_SCRIPT_URL;
    const SERVICE_ACTION_SCRIPT_URL_TEST = SETTINGS_SERVICE_ACTION_SCRIPT_URL_TEST;
    const TEST_XML_FEED_AGE = 7;    // "stari" vyrobku v XML feedu ve dnech => NOW() - TEST_XML_FEED_AGE days
    const LAST_URI = 'logged_out_from';
    
    const AUTOLOAD_DEBUG = FALSE;
        
    // front-end
    const ROOT_URL = 'http://service.styleplus.cz/';                // '/'
    const ROOT_FE_URL = SETTINGS_ROOT_FE_URL;
    const FE_RESOURCES = SETTINGS_FE_RESOURCES;
    const FE_VIEW = SETTINGS_FE_VIEW;
    const FE_VIEW_TEMPLATES = SETTINGS_FE_VIEW_TEMPLATES;
    
    const CLIENT_TEMPLATES_PREFIX = 'client';               // timto prefixem zacinaji vsechny testovaci skripty klientu
    
    // admin
    const ROOT_ADMIN_URL = 'http://service.styleplus.cz/admin/';    // '/admin/'
    const ADMIN_MODULES = SETTINGS_ADMIN_MODULES;
    const ADMIN_RESOURCES = SETTINGS_ADMIN_RESOURCES;
    const ADMIN_CSS = SETTINGS_ADMIN_CSS;
    const ADMIN_JS = SETTINGS_ADMIN_JS;
    const ADMIN_JS_PLUGINS = SETTINGS_ADMIN_JS_PLUGINS;
    const ADMIN_IMAGES = SETTINGS_ADMIN_IMAGES;
    const ADMIN_ICONS = SETTINGS_ADMIN_ICONS;
    
    // password length range
    const MIN_PASSWORD_LENGTH = 6;
    const MAX_PASSWORD_LENGTH = 32;
    
    // system messages
    const MESSAGE_PREFIX = 'msg_';
    const MESSAGE_INFO = 'msg_201';
    const MESSAGE_WARNING = 'msg_417';
    const MESSAGE_ERROR = 'msg_500';
    const MESSAGE_EXCEPTION = 'msg_400';
    const MESSAGE_SUCCESS = 'msg_200';
    
    // proclient data
    const PROCLIENT_AUTHOR = 'Proclient s.r.o., Aksamitova 1071/1, 779 00 Olomouc, http://www.proclient.cz, e-mail: info@proclient.cz';
  }
  
  define('SETTINGS_ROOT_FE_URL', Settings::ROOT_URL . 'web/');
  define('SETTINGS_FE_RESOURCES', SETTINGS_ROOT_FE_URL . 'resources/');
  define('SETTINGS_FE_VIEW', SETTINGS_ROOT_FE_URL . 'view/');
  define('SETTINGS_FE_VIEW_TEMPLATES', SETTINGS_FE_VIEW . 'templates/');
  
  define('SETTINGS_ADMIN_MODULES', Settings::ROOT_ADMIN_URL . 'modules/');
  define('SETTINGS_ADMIN_RESOURCES', Settings::ROOT_ADMIN_URL . 'resources/');
  define('SETTINGS_ADMIN_CSS', SETTINGS_ADMIN_RESOURCES . 'css/');
  define('SETTINGS_ADMIN_JS', SETTINGS_ADMIN_RESOURCES . 'js/');
  define('SETTINGS_ADMIN_JS_PLUGINS', SETTINGS_ADMIN_JS . 'plugins/');
  define('SETTINGS_ADMIN_IMAGES', SETTINGS_ADMIN_RESOURCES . 'images/');
  define('SETTINGS_ADMIN_ICONS', SETTINGS_ADMIN_IMAGES . 'icons/');

  define('SETTINGS_SERVICE_ACTION_SCRIPT_URL', Settings::ROOT_URL . 'get-products.php');
  define('SETTINGS_SERVICE_ACTION_SCRIPT_URL_TEST', Settings::ROOT_URL . 'get-products-new.php');