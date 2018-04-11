<?php
  namespace prosys\model;
  
  use prosys\core\mapper\SqlFilter,
      prosys\core\mapper\MySqlMapper,
      prosys\core\common\Agents,
      prosys\core\common\Settings,
      prosys\core\common\Functions;

  /**
   * Represents the user data access object.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class UserDao extends MyDataAccessObject
  {
    /** @var MySqlMapper */
    private $_userGroupMapper;
    
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::DB_SERVER,
                                                                                     Settings::DB_USER,
                                                                                     Settings::DB_PASSWORD,
                                                                                     Settings::DB_DATABASE,
                                                                                     Settings::DB_PREFIX));
      // set user_group mapper
      $this->_userGroupMapper = new MySqlMapper($mySqlHandler, 'user_group', '');
      
      parent::__construct('users', UserEntity::classname());
    }
    
    /**
     * Stahne nesmazaneho uzivatele podle zadaneho prihlasovaciho jmena.
     * 
     * @param string $login
     * @return UserEntity
     */
    public function loadByLogin($login) {
      $user = Functions::first(
        $this->loadRecords(
          SqlFilter::create()->comparise('login', '=', $login)
        )
      );

      return $user;
    }

    /**
     * Cyphers the password (generally the string).
     * 
     * @param string $string
     * @return string
     */
    public function cypher($string) {
      return sha1($string);
    }

    /**
     * Tries to log into the system. In the case of successfull login returns logged player.
     * 
     * @param string $login
     * @param string $password
     * 
     * @return \prosys\model\UserEntity
     */
    public function doLogin($login, $password) {
      /* @var $logged \prosys\model\UserEntity */
      $logged = Functions::first(
        $this->loadRecords(
          SqlFilter::create()->comparise('login', '=', $login)
                             ->andL()
                             ->comparise('user_password', '=', $password)
        )
      );
      
      return $logged;
    }
    
    /**
     * Get user group by user.
     * 
     * @param UserEntity $user
     * @return
     */
    public function getUserGroupByUser($user) {
      return $this->_userGroupMapper->findRecords(
        SqlFilter::create()
          ->comparise('user_id', '=', $user->id));
    }
    
    /**
     * Get user group by user.
     * 
     * @param UserEntity $user
     * @param GroupEntity $group
     * 
     * @return object
     */
    public function getUserGroupItemByUserAndGroup($user, $group) {
      return Functions::first($this->_userGroupMapper->findRecords(
        SqlFilter::create()
          ->comparise('user_id', '=', $user->id)
          ->andL()->comparise('group_id', '=', $group->id)));
    }
    
    /**
     * Uloží do tabulky user_group skupinu pro uživatele.
     * 
     * @param object $userGroup
     * @return bool
     */
    public function saveUserGroup($userGroup) {
      return $this->_userGroupMapper->insert(array('user_id' => $userGroup->user_id, 'group_id' => $userGroup->group_id));      
    }
    
    /**
     * Vymaze vsechna prirazeni skupin k uzivateli.
     * 
     * @param UserEntity $user
     * @return \PDOStatement
     */
    public function deleteUserGroups(UserEntity $user) {
      return $this->_userGroupMapper->deleteRecords(SqlFilter::create()
        ->comparise('user_id', '=', $user->id));      
    }
    
    /**
     * Vymaže všechny ostatní skupiny z tabulky user_group pro daného uživatele.
     * 
     * @param UserEntity $user
     * @param array $groups
     * 
     * @return bool
     */
    public function deleteOtherUserGroups(UserEntity $user, $groups) {
      return $this->_userGroupMapper->deleteRecords(SqlFilter::create()
        ->comparise('user_id', '=', $user->id)
        ->andL()->notInArray('group_id', $groups));      
    }
    
    /**
     * Vrátí všechny záznamy s předaným uživatelem.
     * 
     * @param UserEntity $user
     * @return type
     */
    public function getUserAuthorsByUser(UserEntity $user) {
      return $this->_userAuthorMapper->findRecords(
        SqlFilter::create()
          ->comparise('user_id', '=', $user->id)
      );
    }
    
    /**
     * Smaze uzivatele z db.
     * 
     * @param mixed $arg
     * @param bool $force
     */
    public function delete($arg, $force = FALSE) {
      // jestlize je vynucene smazani "natvrdo", smaze skupiny uzivatele
      if ($force) {
        $this->_userGroupMapper->deleteRecords(
          SqlFilter::create()->comparise('user_id', '=', ((is_object($arg)) ? $arg->{$this->getPrimaryKey()} : $arg))
        );
      }
      
      // smaze uzivatele
      return parent::delete($arg, $force);
    }
  }
