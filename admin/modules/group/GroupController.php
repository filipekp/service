<?php
  namespace prosys\admin\controller;
  
  use prosys\core\common\Agents,
      prosys\core\common\AppException,
      prosys\core\common\Functions,
      prosys\core\common\Settings;

  /**
   * Processes the users requests.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @property \prosys\admin\view\GroupView $_view PROTECTED property, this annotation is only because of Netbeans Autocompletion
   * @property \prosys\model\GroupDao $_dao PROTECTED property, this annotation is only because of Netbeans Autocompletion
   */
  class GroupController extends Controller
  {
    /**
     * Initializes view and dao.
     */
    public function __construct() {
      parent::__construct();
      
      $this->_dao = Agents::getAgent('GroupDao', Agents::TYPE_MODEL);
      $this->_view = Agents::getAgent('GroupView', Agents::TYPE_VIEW_ADMIN, array($this->_dao));
    }
    
    /**
     * Check required entries.
     * 
     * @param array $post
     * @param array $exceptions
     */
    private function checkMandatories(array $post, array &$exceptions) {
      if (!$post['name']) { $exceptions[] = 'Musíte zadat jméno skupiny.'; }
    }
    
    /**
     * Save user into the database.
     * 
     * @throws AppException
     */
    public function save($reload = TRUE) {
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
          $this->_dao->assignGroupGroupRights($group, (array)Functions::item($post, 'group_rights'));

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
      $get = filter_input_array(INPUT_GET);
      $templateOnly = ((array_key_exists('template_only', $get)) ? TRUE : FALSE);
      
      switch ($activity) {
        case 'manage':          
          $id = (string)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
          $group = $this->_dao->load($id);

          $groupRights = array_combine(
            array_map(function($item) {
              /* @var $item \prosys\model\GroupRightEntity */
              return $item->action->id;
            }, $group->rights->getLoadedArrayCopy()),
            array_map(function($item) {
              /* @var $item \prosys\model\GroupRightEntity */
              return $item->isUncheckable;
            }, $group->rights->getLoadedArrayCopy())
          );
          
          /* @var $moduleActionDao \prosys\model\ModuleActionDao */
          $moduleActionDao = Agents::getAgent('ModuleActionDao', Agents::TYPE_MODEL);
          $modulesWithAction = $moduleActionDao->getModulesWithActions();
          
          $optional = array(
            'modulesWithAction' => $modulesWithAction,
            'groupRights'       => $groupRights,
            'template_only'     => $templateOnly
          );
          $this->_view->manage($group, $optional);
        break;

        case 'initial':
        case 'table':
        default:
          $formFilter = array(
            'name'   => ''
          );
          
          // filter
          $filter = NULL;
          
          // pagination
          $count = $this->_dao->count($filter);
          
          // correction of current page
          $this->currentPageCorrection($count);
          
          $limitFrom = ($this->_currentPage - 1) * $this->_itemsOnPage;
          $groups = $this->_dao->loadRecords(
            $filter,
            array(
              array('column' => 'name', 'direction' => 'asc')
            ),
            array($limitFrom, $this->_itemsOnPage)
          );

          $optional = array(
            'get'               => $this->_get,
            'count'             => $count,
            'items_on_page'     => $this->_itemsOnPage,
            'filter'            => $formFilter,
            'template_only'     => $templateOnly
          );
          
          $this->_view->table($groups, $optional);
        break;
      }
    }
  }

