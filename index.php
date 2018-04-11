<?php
  /* STARTS SESSION */
  session_start();
  
  /* SET MB_ENCODING  */
  mb_internal_encoding('UTF-8');
  
  /* INCLUDES PHP CONSOLE MASTER */
//  require_once 'admin/resources/libs/PhpConsole/__autoload.php';
//  
//  $phpConsoleConnector = PhpConsole\Helper::register(); // register global PC class, use: \PC::debug($whateverTheSameAsIn_var_dump);
//  $phpConsoleInstance = PhpConsole\Connector::getInstance();
//  $phpConsoleHandler = PC::getHandler();
//  $phpConsoleHandler->start();
  
  /* AUTOLOAD */
  require_once 'web/__load__.php';
  
  /* USING NAMESPACE */
  use prosys\core\common\Agents,
      prosys\core\common\AppException,
      prosys\core\common\Settings;

  /* CONTROLS "ACTIVE" REQUESTS (FORMS, ...) */
  $pController = (string)filter_input(INPUT_POST, 'controller', FILTER_SANITIZE_STRING);
  $gController = (string)filter_input(INPUT_GET, 'controller', FILTER_SANITIZE_STRING);

  if ($pController || $gController) {
    $controller = (($pController) ? $pController : $gController);
    $action = filter_input((($pController) ? INPUT_POST : INPUT_GET), 'action', FILTER_SANITIZE_STRING);

    if ($action) {
      $controller = Agents::getAgent(ucfirst($controller . 'Controller'), Agents::TYPE_CONTROLLER_FRONTEND);

      try {
        $controller->$action();
      } catch (AppException $exception) {
        $_SESSION[Settings::MESSAGE_EXCEPTION_FRONTEND] = $exception->getMessage();
      }
    }
  }

  /* SHOWS FRONTEND PAGE */
  $view = Agents::getAgent('View', Agents::TYPE_VIEW_FRONTEND);

  $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
  
  if (!$page) {
    // presmerovani na admin
    header('Location: ' . Settings::ROOT_ADMIN_URL);
    exit();
  }
  
  $view->loadPage($page);
