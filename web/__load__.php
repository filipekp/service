<?php
  /* INCLUDES FILE STRUCTURE */
  spl_autoload_register(function ($class) {
    $namespace = explode('\\', $class);
    $classname = array_pop($namespace);
    
    // don't interfere with other autoloaders
    if (count($namespace) === 0 || !in_array($namespace[0], array('prosys'))) {
      return;
    }
    
    // remove the 'prosys' part of the namespace
    if ($namespace && $namespace[0] == 'prosys') { array_shift($namespace); }
    
    $realNamespace = implode('/', $namespace);

    $dir = '';
    $moduleDir = ((strpos($classname, 'DataAccessObject') !== FALSE) ? '' : 
                    lcfirst(str_replace(array('Dao', 'Entity', 'Controller', 'View'), '', $classname)));
    
    $moduleDirPrefixed = explode('_', $moduleDir);
    if (count($moduleDirPrefixed) == 2) {
      $moduleDir = lcfirst($moduleDirPrefixed[1]);
    }
    
    if ($namespace) {
      switch ($namespace[0]) {
        case 'admin':
        case 'model':
          $dir = 'admin/' . (($moduleDir) ? 'modules/' . $moduleDir : 'core');
        break;
      
        default:
          $dir = (($namespace[0] == 'core') ? 'admin/' : '') . $realNamespace;
        break;
      }
    }

    $path = $dir . '/' . $classname . '.php';

    if (file_exists($path)) {
      require_once $path;
    } else {
      PC::debug($class, 'class');
      PC::debug($realNamespace, 'real_namespace');
      PC::debug($classname, 'classname');
      PC::debug($moduleDir, 'module_dir');
      PC::debug($path, 'path');
      PC::debug('------------------------------------------------');
    }
  });
