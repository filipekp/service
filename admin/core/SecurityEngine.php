<?php
namespace prosys\admin\security;

use prosys\core\common\Agents,
    prosys\core\mapper\SqlFilter,
    prosys\model\ModuleActionEntity,
    prosys\core\common\Settings;

/**
 * Description of SecurityEngine
 *
 * @author Pavel Filípek
 */
class SecurityEngine {
  /** @var \prosys\model\GroupRightDao */
  private $_rightDao;
  /** @var \prosys\model\UserEntity*/
  private $_user;
  /** @var prosys\model\GroupRightEntity[]*/
  private $_rightsAction;
  /** @var prosys\model\GroupRightEntity[]*/
  private $_rightsActivity;


  public function __construct() {
    global $_LOGGED_USER;
    $this->_user = $_LOGGED_USER;
    
    // initialize GroupRightDao
    $this->_rightDao = Agents::getAgent('GroupRightDao', Agents::TYPE_MODEL);
    
    // load all rights for user
    $rights = $this->_rightDao->loadRecords(
      SqlFilter::create()
        ->inFilter('group_id', 
          SqlFilter::create()
            ->filter('group_id', 'user_group',
              SqlFilter::create()
                ->comparise('user_id', '=', $this->_user->id)))
    );
    
    // load action rights
    $this->_rightsAction = array_filter($rights, function($right) {
      return $right->action->type === ModuleActionEntity::TYPE_ACTION;
    });
    
    // load activity rights
    $this->_rightsActivity = array_filter($rights, function($right) {
      return $right->action->type === ModuleActionEntity::TYPE_ACTIVITY;
    });
  }
  
  /**
   * Return TRUE when user has rights.
   * 
   * @param \prosys\model\GroupRightEntity[] $rights
   * @param string $module
   * @param string $action
   * @param array $query
   * @return boolean
   */
  private function hasAuthorization($rights, $module, $action, $query, &$pageName) {
    /* @var $moduleActionDao \prosys\model\ModuleActionDao */
    $moduleActionDao = Agents::getAgent('ModuleActionDao', Agents::TYPE_MODEL);
    $moduleAction = $moduleActionDao->loadByModuleAndAction($module, $action);
    if (!$moduleAction) { return TRUE; }
    $pageName = $moduleAction->title;
    
    return (bool)array_filter($rights, function($right) use ($module, $action, &$query) {
      $allowedQueries = json_decode($right->allowedQueries, true);
      
      $allowed = TRUE;
      if ($allowedQueries) {
        foreach ($allowedQueries as $param => $regexArr) {
          if (array_key_exists($param, $query)) {
            if (is_array($query[$param])) {
              foreach ($query[$param] as $idx => $value) {
                $allowedCurrent = (bool)array_filter($regexArr, function($regex) use ($value) {
                  return preg_match(Settings::REGEX_START_END_CHAR . $regex . Settings::REGEX_START_END_CHAR, $value);
                });
                
                if (!$allowedCurrent) {
                  unset($query[$param][$idx]);
                }
              }
              
              $allowed = $allowed && (bool)$query[$param];
            } else {
              $allowed = $allowed && (bool)array_filter($regexArr, function($regex) use ($query, $param) {
                return preg_match('@' . $regex . '@', $query[$param]);
              });
            }
          } else {
            $allowed = FALSE;
            break;
          }
        }
      }
      
      return $right->action->module->module === $module && $right->action->name === $action && $allowed;
    });
  }
  
  /**
   * Authorize controller and action.
   * @param string $controller
   * @param string $action
   * @return boolean
   */
  public function authorizeAction($controller, $action, $query, &$pageName) {
    // exception for error
    if (preg_match('|e\d+|', $action)) { return TRUE; }
    
    return $this->hasAuthorization($this->_rightsAction, $controller, $action, $query, $pageName);
  }
  
  /**
   * Authorize module and activity.
   * @param string $module
   * @param string $activity
   * @return boolean
   */
  public function authorizeActivity($module, $activity, $query, &$pageName) {
    // exception for error
    if (preg_match('|e\d+|', $activity)) { return TRUE; }
    
    return $this->hasAuthorization($this->_rightsActivity, $module, $activity, $query, $pageName);
  }
}
