<?php
  namespace prosys\admin\controller;
  
  use prosys\core\common\Agents;

  /**
   * Processes the menus requests.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @property \prosys\admin\view\MenuView $_view PROTECTED property, this annotation is only because of Netbeans Autocompletion
   * @property \prosys\model\MenuDao $_dao PROTECTED property, this annotation is only because of Netbeans Autocompletion
   */
  class MenuController extends Controller
  {
    /**
     * Initializes view and dao.
     */
    public function __construct() {
      parent::__construct();
      
      $this->_dao = Agents::getAgent('MenuDao', Agents::TYPE_MODEL);
      $this->_view = Agents::getAgent('MenuView', Agents::TYPE_VIEW_ADMIN, array($this->_dao));
    }
    
    /**
     * @inherit
     */
    public function response($activity) { }
  }

