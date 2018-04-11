<?php
  /* STARTS SESSION */
  session_start();

  /* SET MB_ENCODING  */
  mb_internal_encoding('UTF-8');

  /* INCLUDES PHP CONSOLE MASTER */
//  require_once 'resources/libs/PhpConsole/__autoload.php';
//
//  $phpConsoleConnector = PhpConsole\Helper::register(); // register global PC class, use: \PC::debug($whateverTheSameAsIn_var_dump);
//  $phpConsoleInstance = PhpConsole\Connector::getInstance();
//  $phpConsoleHandler = PC::getHandler();
//  $phpConsoleHandler->start();
  
  /* AUTOLOAD */
  require_once '__load__.php';

  /* USING NAMESPACE */
  use prosys\core\common\Agents,
      prosys\core\common\AppException,
      prosys\core\common\Settings,
      prosys\admin\controller\Controller,
      prosys\core\common\Functions;
  
  // action login should be the first
  if ((string)filter_input(INPUT_POST, 'controller', FILTER_SANITIZE_STRING) == 'user' &&
      (string)filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) == 'login') {
    $controller = Agents::getAgent('UserController', Agents::TYPE_CONTROLLER_ADMIN);
    
    try {
      $controller->login();
    } catch (AppException $exception) {
      $_SESSION[Settings::MESSAGE_EXCEPTION] = $exception->getMessage();
    }
  }
  
  // check if the user is logged in
  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  $_LOGGED_USER = NULL;
  if (array_key_exists('logged_user_id', $_SESSION)) {
    $userDao = Agents::getAgent('UserDao', Agents::TYPE_MODEL);
    $_LOGGED_USER = $userDao->load($_SESSION['logged_user_id']);
    
    if ($_LOGGED_USER->isNew()) {
      $_LOGGED_USER = NULL;
    }
  }
  
  if (is_null($_LOGGED_USER)) {
    $controller = Agents::getAgent('AdminController', Agents::TYPE_CONTROLLER_ADMIN);
    $controller->response('login');

    $_SESSION[Settings::LAST_URI] = filter_input(INPUT_SERVER, 'QUERY_STRING');
  } else {
    /* @var $securityEngine \prosys\admin\security\SecurityEngine */
    $securityEngine = Agents::getAgent('SecurityEngine', Agents::TYPE_SECURITY);
      
    /* CONTROLS "ACTIVE" REQUESTS (FORMS, ...) */
    $controllerPost = filter_input(INPUT_POST, 'controller');
    $controllerGet = filter_input(INPUT_GET, 'controller');

    if ($controllerPost || $controllerGet) {
      $repository = (($controllerPost) ? INPUT_POST : INPUT_GET);
      $controller = (($controllerPost) ? $controllerPost : $controllerGet);
      
      $action = filter_input($repository, 'action');
      
      if ($action) {
        $actionName = '';
        if ($securityEngine->authorizeAction($controller, $action, filter_input_array($repository), $actionName)) {
          $controllerName = $controller;
          $controller = Agents::getAgent(ucfirst($controller . 'Controller'), Agents::TYPE_CONTROLLER_ADMIN);

          try {
            $controller->$action();
          } catch (AppException $exception) {
            $_SESSION[Settings::MESSAGE_EXCEPTION] = $exception->getMessage();
//            \PC::debug($exception, 'AppException');
    
            if (!filter_input(INPUT_GET, 'module', FILTER_SANITIZE_STRING)) {
              $query = filter_input_array($repository);
              Functions::unsetItem($query, 'action');
              Functions::unsetItem($query, 'controller');
              
              Controller::redirect(
                $controllerName,
                filter_input(INPUT_GET, 'activity', FILTER_SANITIZE_STRING, array('options' => array('default' => 'initial'))),
                http_build_query($query)
              );
            }
          }
        } else {
          $_SESSION[Settings::MESSAGE_EXCEPTION] = 'Nemáte práva k provedení akce "' . $actionName . '"';
          
          // reload to page 405 - access denied
          Controller::redirect('admin', 'e405', '&requested[module]=' . $controller . '&requested[action]=' . $action);
        }
      }
    }

    /* SHOWS ADMIN PAGE */
    $_MODULE = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_STRING, array('options' => array('default' => 'admin')));
    $activity = filter_input(INPUT_GET, 'activity', FILTER_SANITIZE_STRING, array('options' => array('default' => 'initial')));
    
    $pageName = '';
    if ($securityEngine->authorizeActivity($_MODULE, $activity, $_GET, $pageName)) {
      try {
        $controller = Agents::getAgent(ucfirst($_MODULE) . 'Controller', Agents::TYPE_CONTROLLER_ADMIN);
        
        $controller->response($activity);
      } catch (AppException $exception) {
        \PC::debug($exception, 'AppException');
      }
    } else {
      $_SESSION[Settings::MESSAGE_EXCEPTION] = 'Nemáte práva k zobrazení stránky: ' . $pageName;
      // reload to page 401 - access denied
      Controller::redirect('admin', 'e401',  '&requested[module]=' . $_MODULE . '&requested[action]=' . $activity);
    }
  }
