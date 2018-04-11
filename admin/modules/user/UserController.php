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
   * @property \prosys\admin\view\UserView $_view PROTECTED property, this annotation is only because of Netbeans Autocompletion
   * @property \prosys\model\UserDao $_dao PROTECTED property, this annotation is only because of Netbeans Autocompletion
   */
  class UserController extends Controller
  {
    /**
     * Initializes view and dao.
     */
    public function __construct() {
      parent::__construct();
      
      $this->_dao = Agents::getAgent('UserDao', Agents::TYPE_MODEL);
      $this->_view = Agents::getAgent('UserView', Agents::TYPE_VIEW_ADMIN, array($this->_dao));
    }
    
    /**
     * Check login uniqueness.
     * 
     * @param array $post
     * @param array $exceptions
     */
    private function checkLoginUniqueness(array $post, array &$exceptions) {
      if ($this->_dao->loadByLogin($post['login'])) {
        $exceptions['login'][] = 'Přihlašovací jméno již existuje, zvolte prosím jiné.';
      }
    }
    
    /**
     * Check if password and repassword are equal.
     * 
     * @param array $post
     * @param array $exceptions
     */
    private function checkPasswordsEquality(array $post, array &$exceptions) {
      if (array_key_exists('password', $post)) {
        if ($post['password'] !== $post['repassword']) {
          $exceptions['repassword'][] = 'Heslo a ověření hesla musí být stejné.';
        }
      }
    }
    
    /**
     * Check password length.
     * 
     * @param array $post
     * @param array $exceptions
     */
    private function checkPasswordLength(array $post, array &$exceptions) {
      if (array_key_exists('password', $post)) {
        $length = mb_strlen($post['password'], 'UTF-8');

        if ($length < Settings::MIN_PASSWORD_LENGTH || $length > Settings::MAX_PASSWORD_LENGTH) {
          $exceptions['password'][] = 'Heslo musí být dlouhé ' . Settings::MIN_PASSWORD_LENGTH . ' - ' . Settings::MAX_PASSWORD_LENGTH . ' znaků.';
        }
      }
    }
    
    /**
     * Check required entries.
     * 
     * @param array $post
     * @param array $exceptions
     */
    public function verify(array &$post, array &$exceptions) {
      $user = $this->_dao->load($post);
      if ($user->isNew()) {
        if (!$post['login']) { $exceptions['login'][] = 'Musíte zadat přihlašovací jméno.'; }
        $this->checkLoginUniqueness($post, $exceptions);
        
        if (!$post['password']) { $exceptions['password'][] = 'Musíte zadat heslo.'; }
        $this->checkPasswordsEquality($post, $exceptions);
        $this->checkPasswordLength($post, $exceptions);
      } else {
        if (array_key_exists('password', $post)) {
          if ($post['password']) {
            $this->checkPasswordsEquality($post, $exceptions);
            $this->checkPasswordLength($post, $exceptions);
          } else {
            unset($post['password']);
            unset($post['repassword']);
          }
        }
      }
      
      if (!$post['first_name']) { $exceptions['first_name'][] = 'Musíte zadat jméno.'; }
      if (!$post['last_name']) { $exceptions['last_name'][] = 'Musíte zadat příjmení.'; }

      array_walk($exceptions, function(&$item) {
        $item = ((is_array($item)) ? implode('<br />', $item) : $item);
      });
    }
    
    /**
     * Save user into the database.
     * 
     * @throws AppException
     */
    public function save($reload = TRUE, $verify = TRUE, array $post = array()) {
      $post = (($post) ? $post : Functions::trimArray(filter_input_array(INPUT_POST)));
      
      if (array_key_exists('delete', $post)) {
        header('Location: ' . Settings::ROOT_ADMIN_URL . '?controller=user&action=delete&id=' . $post['id']);
        exit();
      }
      
      // check form validity
      $exceptions = array();
      if ($verify) {
        $this->verify($post, $exceptions);
      }
      
      if ($exceptions) {
        throw new AppException($exceptions);
      } else {
        // cypher password
        if (array_key_exists('password', $post)) {
          $post['password'] = $this->_dao->cypher($post['password']);
        }

        $user = $this->_dao->load($post);
        $user->avatar = 'default';
        $user->theme = 'default';

        if ($this->_dao->store($user)) {
          $_SESSION[Settings::MESSAGE_SUCCESS] = "Uživatel &bdquo;{$user->getFullName()}&ldquo; byl úspěšně uložen.";
          
          if (array_key_exists('groups', $post) && $post['groups']) {
            /* @var $groupDao \prosys\model\GroupDao */
            $groupDao = Agents::getAgent('GroupDao', Agents::TYPE_MODEL);
            foreach ($post['groups'] as $group) {
              /* @var $group \prosys\model\GroupEntity */
              $group = $groupDao->load($group);
              $userGroup = $this->_dao->getUserGroupItemByUserAndGroup($user, $group);
              if (!$userGroup) {
                $userGroup = (object)array(
                  'group_id'  => $group->id,
                  'user_id'   => $user->id
                );
                $this->_dao->saveUserGroup($userGroup);
              }
            }

            $this->_dao->deleteOtherUserGroups($user, $post['groups']);
          } else {
            $this->_dao->deleteUserGroups($user);
          }
          
          if ($reload) {
            if (array_key_exists('apply', $post)) {
              switch ($post['back_to']) {
                case 'changeProfile':
                  $this->reload($post['back_to']);
                break;
                default:
                  $this->reload($post['back_to'], 'id=' . $user->id);
                break;
              }
            } else {
              $this->reload();
            }
          } else {
            return $user;
          }
        } else {
          throw new AppException("Uživatele &bdquo;{$user->getFullName()}&ldquo; se nepodařilo uložit.");
        }
      }
    }
    
    /**
     * Delete user from the database.
     * 
     * @throws AppException
     */
    public function delete() {
      $id = (string)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
      $user = $this->_dao->load($id);
      
      if ($user->isNew()) {
        throw new AppException("Uživatele nebylo možné smazat. Uživatel s přihlašovacím jménem &bdquo;{$id}&ldquo; neexistuje.");
      } else {
        if ($this->_dao->delete($user)) {
          $this->_infoMessage = "Uživatel &bdquo;{$user->getFullName()}&ldquo; byl úspěšně smazán.";
          $this->reload();
        } else {
          throw new AppException("Uživatele &bdquo;{$user->getFullName()}&ldquo; se nepodařilo smazat.");
        }
      }
    }
    
    public function saveProfile() {
      $post = Functions::trimArray(filter_input_array(INPUT_POST));
      $change = TRUE;
      
      // check form validity
      $exceptions = array();
      
      $userOld = $this->_dao->load($post['id']);
      
      if (array_key_exists('password', $post) || array_key_exists('repassword', $post) || array_key_exists('old_password', $post)) {
        if (!$post['password'] && !$post['repassword'] && !$post['old_password']) {
          $change = FALSE;
        }
        
        if ($post['password'] == $post['repassword']) {
          if (array_key_exists('old_password', $post) && $this->_dao->cypher($post['old_password']) != $userOld->password) {
            $exceptions[] = 'Původní heslo nebylo správně zadáno.';
          }
        } else {
          $exceptions[] = 'Nové heslo a kontrola nového hesla se musí shodovat.';
        }
      }
      
      if ($change) {
        if ($exceptions) {
          $_SESSION[Settings::MESSAGE_EXCEPTION] = $exceptions;
          $this->redirect('user', 'changeProfile', $post['back_to']);
        } else {
          // cypher password
          if (array_key_exists('password', $post)) {          
            $post['password'] = $this->_dao->cypher($post['password']);
          }

          $user = $this->_dao->load($post);
          if ($this->_dao->store($user)) {
            $_SESSION[Settings::MESSAGE_SUCCESS] = "Váš profil byl uložen.";

            $this->redirect('user', 'changeProfile', $post['back_to']);
          } else {
            throw new AppException("Váš profil se nepodařilo uložit.");
          }
        }
      } else {
        $this->redirect('user', 'changeProfile', $post['back_to']);
      }
    }
    
    /**
     * Log user in.
     */
    public function login() {
      // ulozeni posledni url
      $lastUri = $_SESSION[Settings::LAST_URI];
      $lastUriArr = [];

      parse_str($lastUri, $lastUriArr);
      
      /* @var $user \prosys\model\UserEntity */
      $user = $this->_dao->doLogin(filter_input(INPUT_POST, 'login'),
                                   $this->_dao->cypher(filter_input(INPUT_POST, 'password')));
      
      if ($user) {
        $_SESSION['logged_user_id'] = $user->id;
        $user->lastLogin = new \DateTime();
        $this->_dao->store($user);
        
        $module = Functions::unsetItem($lastUriArr, 'module');
        $activity = Functions::unsetItem($lastUriArr, 'activity');

        Controller::redirect($module, $activity, http_build_query($lastUriArr), 'Uživatel byl úspěšně přihlášen', Settings::ROOT_ADMIN_URL);
      } else {
        throw new AppException('Přihlášení se nepodařilo.<br />Pravděpodobně špatně zadané přihlašovací údaje.');
      }
    }
    
    /**
     * Log user out.
     */
    public function logout() {
      if (array_key_exists('logged_user_id', $_SESSION)) {
        unset($_SESSION['logged_user_id']);
        
        Controller::redirect('', '', '', 'Uživatel byl úspěšně odhlášen');
      }
    }

    /**
     * @inherit
     */
    public function response($activity) {
      $get = filter_input_array(INPUT_GET);
      $templateOnly = ((array_key_exists('template_only', $get)) ? TRUE : FALSE);
      
      /* @var $groupDao \prosys\model\GroupDao */
      $groupDao = Agents::getAgent('GroupDao', Agents::TYPE_MODEL);
      $groups = $groupDao->loadRecords(NULL, array(array('column' => 'is_admin', 'direction' => 'DESC'), array('column' => 'name')));
      
      switch ($activity) {
        case 'changeProfile':
          $get = filter_input_array(INPUT_GET);           
          global $_LOGGED_USER;
          $user = $_LOGGED_USER;
          
          $userGroups = $this->_dao->getUserGroupByUser($user);
          array_walk($userGroups, function(&$item) use ($groupDao) {
            $item = $groupDao->load($item->group_id)->name;
          });

          $optional = array(
            'userGroups'  => $userGroups,
            'template_only'     => $templateOnly
          );
          
          $this->_view->manageProfile($user, $optional);
        break;
        
        case 'manage':
          $get = filter_input_array(INPUT_GET);
          $id = ((array_key_exists('id', $get)) ? $get['id'] : NULL);
          $user = $this->_dao->load($id);
          
          $userGroups = $this->_dao->getUserGroupByUser($user);
          array_walk($userGroups, function(&$item) {
            $item = $item->group_id;
          });

          $optional = array(
            'groups'      => $groups,
            'userGroups'  => $userGroups,
            'template_only'     => $templateOnly
          );
          $this->_view->manage($user, $optional);
        break;
      
        case 'initial':
        case 'table':
        default:
          $get = filter_input_array(INPUT_GET);
          $filterLogin = ((array_key_exists('filter_login', $get)) ? $get['filter_login'] : '');
          $filterName = ((array_key_exists('filter_name', $get)) ? $get['filter_name'] : '');
          
          $formFilter = array(
            'filter_login'   => $filterLogin,
            'filter_name'    => $filterName
          );
          
          // filter
          $filter = SqlFilter::create()->contains('login', '');
          
          if ($filterLogin) {
            $filter->andL()->contains('login', $filterLogin);
          }
          
          if ($filterName) {
            $filter->andL(
            SqlFilter::create()
              ->contains('CONCAT(last_name, " ", first_name)', $filterName)
            );
          }
          
          
          // pagination
          $count = $this->_dao->count($filter);
          
          // correction of current page
          $this->currentPageCorrection($count);
          
          $limitFrom = ($this->_currentPage - 1) * $this->_itemsOnPage;

          $users = $this->_dao->loadRecords(
            $filter,
            array(
              array('column' => 'CONCAT(last_name, " ", first_name)', 'direction' => 'asc')
            ),
            array($limitFrom, $this->_itemsOnPage)
          );

          $optional = array(
            'count'             => $count,
            'items_on_page'     => $this->_itemsOnPage,
            'filter'            => $formFilter,
            'template_only'     => $templateOnly
          );
          
          $this->_view->table($users, $optional);
        break;
      }
    }
  }

