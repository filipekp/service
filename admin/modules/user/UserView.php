<?php
  namespace prosys\admin\view;
  use prosys\model\Entity;

  /**
   * Represents the admin user module view.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class UserView extends View
  {
    /**
     * Initializes the label of every user property.
     */
    public function __construct(\prosys\model\UserDao $userDao)
    {
      $labels = array(
        'login'       => 'Přihlašovací jméno',
        'old_password'=> 'Staré heslo',
        'password'    => 'Heslo',
        'new_password'=> 'Nové heslo',
        'repassword'  => 'Ověření hesla',
        'first_name'  => 'Jméno',
        'last_name'   => 'Příjmení',
        'phone'       => 'Telefon',
        'email'       => 'E-mail',
        'contact'     => 'Kontakt',
        'userGroup'   => 'Uživatelské skupiny',
        'theme'       => 'Barevné schéma',
        'countItemPerPage'  => 'Počet položek na stránku'
      );

      parent::__construct($userDao, $labels);
    }
    
    /**
     * Prints out manage form to managing the user profile.
     * 
     * @param \prosys\model\UserEntity $user
     * @param array $optional associative array with optional data
     */
    public function manageProfile(Entity $user, $optional = array()) {
      /* @var $user \prosys\model\UserEntity */
      $assign = $optional + array('user' => $user);
      
      $heading = 'Úprava profilu &bdquo;' . $user->getIdentification() . '&ldquo;';      
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('ManageProfile', $heading, $assign, $templateOnly);
    }
    
    /**
     * Prints out manage form to managing the user.
     * 
     * @param \prosys\model\UserEntity $user
     * @param array $optional associative array with optional data
     */
    public function manage(Entity $user, $optional = array()) {
      /* @var $user \prosys\model\UserEntity */
      $assign = $optional + array('user' => $user);
      
      if ($user->isNew()) {
        $heading = 'Nový uživatel';
        $assign['delete'] = '';
        
      } else {
        global $_LOGGED_USER;
        $heading = 'Úprava uživatele &bdquo;' . $user->getIdentification() . '&ldquo;';
        if ($_LOGGED_USER->hasRight('user', 'delete')) {
          $assign['delete'] = <<<DELETE
            <button type="submit" class="btn red delete" title="Smazat"><i class="icon-trash icon-white"></i> Smazat</button>
DELETE;
        } else {
          $assign['delete'] = '';
        }
      }
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Manage', $heading, $assign, $templateOnly);
    }
    
    /**
     * Prints out the table of users.
     * 
     * @param \prosys\model\UserEntity[] $data
     * @param array $optional
     */
    public function table($data = array(), $optional = array()) {
      $assign = $optional + array(
        'data' => $data,
        'totalcount' => $optional['count'],
        'pagination' => View::generatePaging($optional['count'], filter_input_array(INPUT_GET), $optional['items_on_page'])
      );
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Table', 'Přehled uživatelů', $assign, $templateOnly);
    }
  }
