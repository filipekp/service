<?php
  namespace prosys\model;
  use prosys\core\common\Agents,
      prosys\core\common\Functions;

  /**
   * Represents the entity of backend user.
   * 
   * @property int $id
   * @property string $login
   * @property string $password element=user_password
   * @property string $firstName element=first_name
   * @property string $lastName element=last_name
   * @property string $phone
   * @property string $email
   * @property string $theme
   * @property string $avatar
   * @property int $countItemPerPage element=count_item_per_page   
   * @property \DateTime $lastLogin element=last_login
   * @property boolean $deleted delete_flag
   * 
   * @property GroupEntity[] $groups binding=mn:user_group:>:>:name>
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class UserEntity extends Entity
  {
    private $_RIGHTS = array();
    
    public static $_THEMES = array(
      'blue'    => 'Modrá',
      'brown'   => 'Hnědá',
      'default' => 'Výchozí',
      'grey'    => 'Šedá',
      'light'   => 'Bílá',
      'Purple'  => 'Fialová'
    );
    /**
     * Returns full name of the user.
     * @return string
     */
    public function getFullName() {
      return $this->lastName . ' ' . $this->firstName;
    }
    
    /**
     * Returns identification of the user (name and e-mail).
     * @return string
     */
    public function getIdentification() {
      return $this->getFullName() . (($this->email) ? ' (' . $this->email . ')' : '');
    }
    
    /**
     * Returns contact of the user (e-mail and phone).
     * @return type
     */
    public function getContact($separator = ', ') {
      $contact = array();
      if ($this->email) { $contact[] = $this->email; }
      if ($this->phone) { $contact[] = '<span class="nowrap">' . $this->phone . '</span>'; }
      
      return implode($separator, $contact);
    }
    
    public function hasRight($module, $action) {
      /* @var $userDao UserDao */
      $userDao = Agents::getAgent('UserDao',  Agents::TYPE_MODEL);
      $userGroups = $userDao->getUserGroupByUser($this);
      if (!$userGroups) {return FALSE;}
      array_walk($userGroups, function(&$item) {
        $item = $item->group_id;
      });
      
      /* @var $groupRightDao GroupRightDao */
      $groupRightDao = Agents::getAgent('GroupRightDao', Agents::TYPE_MODEL);
      $right = $groupRightDao->loadGroupRightByGroupsModuleAndAction($userGroups, $module, $action);
      return (($right) ? TRUE : FALSE);
    }
    
    /**
     * Zjistí jestli k danému uživateli je přiřazený autor.
     * 
     * @return boolean
     */
    public function hasAuthor() {
      /* @var $userDao UserDao */
      $userDao = Agents::getAgent('UserDao',  Agents::TYPE_MODEL);
      return ((Functions::first($userDao->getUserAuthorsByUser($this))) ? TRUE : FALSE);
    }
  }
