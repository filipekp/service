<?php
  /* INCLUDES PHP CONSOLE MASTER */
  require_once 'admin/resources/libs/PhpConsole/__autoload.php';
  
  $phpConsoleConnector = PhpConsole\Helper::register(); // register global PC class, use: \PC::debug($whateverTheSameAsIn_var_dump);
  $phpConsoleInstance = PhpConsole\Connector::getInstance();
  $phpConsoleHandler = PC::getHandler();
  $phpConsoleHandler->start();
  
  /* AUTOLOAD */
  require_once 'web/__load__.php';
  
  $instance = \prosys\admin\controller\General_GeneratorController::createGenerator();
  $instance->generate();
  