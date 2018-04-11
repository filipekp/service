<?php
  namespace prosys\web\view;
  
  use prosys\core\common\Settings;

  /**
   * Represents front-end view.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class View
  {
    /**
     * Load defined page from directory view/html
     * 
     * @param string $page File name of page
     */
    public function loadPage($page, $contentOnly = FALSE) {
      // jestlize je vyzadovan testovaci skript
      if (strpos($page, Settings::CLIENT_TEMPLATES_PREFIX) === 0) {
        include __DIR__ . '/templates/clients/' . $page;
      } else {
        $exploded = explode('/', $page);
        if (count($exploded) > 1) {
          list($page, $_IDENTIFICATION) = $exploded;
        } else {
          $_IDENTIFICATION = '';
        }

        include __DIR__ . '/templates/' . $page . '.php';
      }
    }
  }
