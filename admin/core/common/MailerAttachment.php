<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace prosys\core\common;

/**
 * Description of MailerAttachment
 *
 * @author FILIPEK
 */
class MailerAttachment {
  private $_PATH;
  private $_NAME;
  private $_DATA;
  private $_CONTENTTYPE;
  
  public function __construct($name, $contentType, $path = '', $data = NULL) {
    $this->_DATA = $data;
    $this->_PATH = $path;
    $this->_NAME = $name;
    $this->_CONTENTTYPE = $contentType;
  }
  
  public function getPath() {
    return $this->_PATH;
  }

  public function getName() {
    return $this->_NAME;
  }

  public function getData() {
    return $this->_DATA;
  }

  public function getContentType() {
    return $this->_CONTENTTYPE;
  }
}
