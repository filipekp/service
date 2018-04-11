<?php
  namespace prosys\admin\controller;
  
  use prosys\core\common\Settings,
      prosys\admin\view\View,
      prosys\core\common\Functions;

  /**
   * Abstract class from which every controller should inherit.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class Controller implements \prosys\core\interfaces\IController
  { 
    /** @var int $_itemsOnPage */
    public $_itemsOnPage = Settings::ITEMS_PER_PAGE;
    
    /** @var int $_currentPage */
    protected $_currentPage;
    
    /** @var array $_currentFilter */
    protected $_currentFilter;
    
    /** @var string $_className */
    protected $_className;
    
    /** @var string $_activity */
    protected $_activity;
    
    /** @var \prosys\admin\view\View $_view */
    protected $_view;
    
    /** @var \prosys\model\DataAccessObject $_dao */
    protected $_dao;
    
    /** @var string $_infoMessage */
    protected $_infoMessage = '';
    
    /** @var array $_get */
    protected $_get;
    
    /** @var array $_post */
    protected $_post;
    
    /**
     * Initializes module pagination.
     */
    public function __construct() {
      global $_LOGGED_USER;
      
      // ulozi GET a POST
      $this->_get = (array)filter_input_array(INPUT_GET);
      $this->_post = (array)filter_input_array(INPUT_POST);
      
      // find the class name
      $reflection = new \ReflectionClass($this);
      $this->_className = join('', array_slice(explode('\\', $reflection->name), -1));
      $this->_activity = filter_input(INPUT_GET, 'activity', FILTER_SANITIZE_STRING, array('options' => array('default' => 'initial')));
      
      $module = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_STRING);
      
      // nastaveni stranky se provede pouze existuje-li modul
      if ($module) {
        // set pagination to session if not exists
        if (!array_key_exists('pagination', $_SESSION)) { $_SESSION['pagination'] = array(); }
        if (!array_key_exists($this->_className, $_SESSION['pagination'])) { $_SESSION['pagination'][$this->_className] = array(); }
        if (!array_key_exists($this->_activity, $_SESSION['pagination'][$this->_className])) { $_SESSION['pagination'][$this->_className][$this->_activity] = 1; }

        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
        if ($page) {
          // when page on url then set page to session
          $_SESSION['pagination'][$this->_className][$this->_activity] = (int)$page;
        }

        // stor page in property $_currentPage
        $this->_currentPage = $_SESSION['pagination'][$this->_className][$this->_activity];

        if ($_LOGGED_USER && $_LOGGED_USER->countItemPerPage) {
          $this->_itemsOnPage = $_LOGGED_USER->countItemPerPage;
        }
        
        
        // nastavi filtr do session kdyz neexistuje
        if (!array_key_exists('filter', $_SESSION)) { $_SESSION['filter'] = array(); }
        if (!array_key_exists($this->_className, $_SESSION['filter'])) { $_SESSION['filter'][$this->_className] = array(); }
        if (!array_key_exists($this->_activity, $_SESSION['filter'][$this->_className])) { $_SESSION['filter'][$this->_className][$this->_activity] = array(); }

        $filter = (array)Functions::item($this->_get, 'filter');
        if ($filter) {
          // kdyz je filtr v url tak ho ulozi do session
          $_SESSION['filter'][$this->_className][$this->_activity] = $filter;
        }

        // ulozi filtr do privatni promenne $_currentFilter
        $this->_currentFilter = $_SESSION['filter'][$this->_className][$this->_activity];
      }
      
      $this->_get['page'] = $this->_currentPage;
      $this->_get['filter'] = $this->_currentFilter;
    }
    
    /**
     * Vymaže aktuální filtr.
     */
    public function clearFilter() {
      $module = filter_input(INPUT_GET, 'controller', FILTER_SANITIZE_STRING);
      $activity = filter_input(INPUT_GET, 'activity', FILTER_SANITIZE_STRING, array('options' => array('default' => 'initial')));
      
      // vymaze filtr pro aktivitu ze session
      Functions::unsetItem($_SESSION['filter'][$this->_className], $activity);
      // vymaze strankovani pro aktivitu
      Functions::unsetItem($_SESSION['pagination'][$this->_className], $activity);
      
      $this->redirect($module, $activity);
    }
    
    /**
     * Redirects admin page.
     * 
     * @param string $module
     * @param string $activity
     * @param string $query
     * @param string $message
     */
    public static function redirect($module = '', $activity = '', $query = '', $message = '', $rootUrl = Settings::ROOT_ADMIN_URL) {
      if ($message) {
        $_SESSION[Settings::MESSAGE_INFO] = $message;
      }
      
      $module = (($module) ? '?module=' . $module : '');
      $activity = (($activity) ? '&activity=' . $activity : '');
      $query = (($query) ? '&' . $query : '');
      
      header('Location: ' . $rootUrl . $module . $activity . $query);
      exit();
    }

    /**
     * Redirects admin page in this module.
     * 
     * @param string $activity
     * @param string $query
     */
    protected function reload($activity = '', $query = '') {
      $module = lcfirst(preg_replace('/.*\\\\(.+)Controller/', '$1', get_class($this)));
      self::redirect($module, $activity, $query, $this->_infoMessage);
    }
    
    /**
     * Limits requested page between 1 and the last page.
     * 
     * @param int $count
     */
    public function currentPageCorrection($count) {
      $this->_currentPage = min($this->_currentPage, View::getLastPageNumber($count, $this->_itemsOnPage));
      $this->_get['page'] = $this->_currentPage;
      $_SESSION['pagination'][$this->_className][$this->_activity] = $this->_currentPage;
    }
  }
