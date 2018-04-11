<?php
  namespace prosys\admin\controller;
  
  use prosys\core\common\Agents,
      prosys\core\common\AppException,
      prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions,
      prosys\core\common\Settings;

  /**
   * Processes the users requests.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @property \prosys\admin\view\GroupRightView $_view PROTECTED property, this annotation is only because of Netbeans Autocompletion
   * @property \prosys\model\GroupRightDao $_dao PROTECTED property, this annotation is only because of Netbeans Autocompletion
   */
  class GroupRightController extends Controller
  {
    /**
     * Initializes view and dao.
     */
    public function __construct() {
      parent::__construct();
      
      $this->_dao = Agents::getAgent('GroupRightDao', Agents::TYPE_MODEL);
      $this->_view = Agents::getAgent('GroupRightView', Agents::TYPE_VIEW_ADMIN, array($this->_dao));
    }
    
    /**
     * Save user into the database.
     * 
     * @throws AppException
     */
    public function save($data) {
      $post = Functions::trimArray(filter_input_array(INPUT_POST));
      
      if (array_key_exists('delete', $post)) {
        header('Location: ' . Settings::ROOT_ADMIN_URL . '?controller=group&action=delete&id=' . $post['id']);
        exit();
      }
      
      // check form validity
      $exceptions = array();
      $this->checkMandatories($post, $exceptions);
      
      if ($exceptions) {
        throw new AppException($exceptions);
      } else {        
        $group = $this->_dao->load($post);
        if ($this->_dao->store($group)) {
          $this->_infoMessage = "Skupina &bdquo;{$group->name}&ldquo; byla úspěšně uložena.";
          
          if ($reload) {
            if (array_key_exists('apply', $post)) {
              $this->reload('manage', 'id=' . $group->id);
            } else {
              $this->reload();
            }
          }
        } else {
          throw new AppException("Skupinu &bdquo;{$group->name}&ldquo; se nepodařilo uložit.");
        }
      }
    }
    
    /**
     * Delete user from the database.
     * 
     * @throws AppException
     */
    public function delete() {
      $login = (string)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
      $group = $this->_dao->load($login);
      
      if ($group->isNew()) {
        throw new AppException("Skupinu nebylo možné smazat, protože neexistuje id &bdquo;#{$group->id}&ldquo;.");
      } else {
        if ($this->_dao->delete($group)) {
          $this->_infoMessage = "Skupina &bdquo;{$group->name}&ldquo; byla úspěšně smazána.";
          $this->reload();
        } else {
          throw new AppException("Skupinu &bdquo;{$group->name}&ldquo; se nepodařilo smazat.");
        }
      }
    }

    /**
     * @inherit
     */
    public function response($activity) {
      switch ($activity) {
        case 'manage':
        case 'initial':
        case 'table':
        default:
          throw new AppException('Response isn\'t implemented yet.');
      }
    }
  }

