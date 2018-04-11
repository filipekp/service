<?php
  namespace prosys\admin\controller;
  
  /**
   * Processes the users requests.
   * 
   * @author Pavel Filípek <www.filipek-czech.cz>
   * @copyright (c) 2017, Proclient s.r.o.
   */
  class GeneratorController extends General_GeneratorController
  {
    public function __construct($fileName = NULL) {
      parent::__construct($fileName);
    }

    public function generate() {
      parent::generate();
      
      header('Content-type: text/xml; charset=UTF-8');
      echo $this->_XML->saveXML();
      exit();
    }
  }

