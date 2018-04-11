<?php
  namespace prosys\admin\view;
  use prosys\core\common\Settings;

  /**
   * Represents the admin home page view.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class AdminView extends View
  {
    /**
     * Inicializuje popisky modulu admin.
     */
    public function __construct()
    {
      $labels = [
        'email' => [
          'address' => 'E-mailová adresa',
          'email'   => 'adresa@domena.cz',
          'subject' => 'Předmět',
          'body'    => 'Zpráva'
        ]
      ];

      parent::__construct(NULL, $labels);
    }
    
    /**
     * Show dashboard.
     */
    public function initial($arg = NULL) {
      $this->printActivity('Home');
    }
    
    /**
     * Show login form.
     */
    public function login() {
      include 'templates/Login.php';
    }
    
    /**
     * Show error page.
     */
    public function errorPage($code) {
      switch ($code) {
        case 400:
          $title = 'Pozor vyjímka';
        break;
        case 401:
          $title = 'Přístup odmítnut';
        break;
        case 405:
          $title = 'Akce není povolena';
        break;
        default:
          $title = $code . ' - Chybová stránka';
        break;
      }
      
      $text = ((array_key_exists(Settings::MESSAGE_EXCEPTION, $_SESSION)) ? $_SESSION[Settings::MESSAGE_EXCEPTION] : 'Vyskatla se blíže nespecifikovaná chyba s kódem: ' . filter_var($code, FILTER_SANITIZE_NUMBER_INT));
      unset($_SESSION[Settings::MESSAGE_EXCEPTION]);
      
      $this->printActivity('Error', $title, array('text' => $text));
    }
    
    /**
     * Show phpinfo.
     */
    public function phpInfo() {
      $this->printActivity('PhpInfo', 'Informace o serveru', array());
    }
    
    /**
     * Zobrazí formulář pro odeslání manuálu klientovi.
     */
    public function sendManual($args = []) {
      $this->printActivity('SendManual', 'Poslat manuál', $args + ['contentOnly' => TRUE]);
    }
  }
