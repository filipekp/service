<?php
  /* INCLUDES FILE STRUCTURE */
  spl_autoload_register(function ($class) {
    $namespace = explode('\\', $class);
    $classname = array_pop($namespace);
    
    // remove the 'prosys' and the 'admin' part of the namespace
    if ($namespace && $namespace[0] == 'prosys') { array_shift($namespace); }
    if ($namespace && $namespace[0] == 'admin')  { array_shift($namespace); }
    
    $realNamespace = implode('/', $namespace);

    $dir = '';
    $moduleDir = ((strpos($classname, 'DataAccessObject') !== FALSE || strpos($classname, 'Entity') === 0) ? '' : 
                    lcfirst(str_replace(array('Dao', 'Entity', 'Controller', 'View'), '', $classname)));
    
    $moduleDirPrefixed = explode('_', $moduleDir);
    if (count($moduleDirPrefixed) == 2) {
      $moduleDir = lcfirst($moduleDirPrefixed[1]);
    }
    
    if ($namespace) {
      switch ($namespace[0]) {
        case 'model':
        case 'view':
        case 'controller':
          $dir = (($moduleDir) ? 'modules/' . $moduleDir : 'core');
        break;
        
        case 'security':
          $dir = 'core';
        break;
      
        default:
          $dir = $realNamespace;
        break;
      }
    }

    $path = $dir . '/' . $classname . '.php';
    if (file_exists($path)) {
      require_once $path;
    } else {
      if (\prosys\core\common\Settings::AUTOLOAD_DEBUG) {
        PC::debug($class, 'class');
        PC::debug($realNamespace, 'real_namespace');
        PC::debug($classname, 'classname');
        PC::debug($moduleDir, 'module_dir');
        PC::debug($path, 'path');
        PC::debug(debug_backtrace(), 'backtrace');
        PC::debug('------------------------------------------------');
      }
    }
  });
