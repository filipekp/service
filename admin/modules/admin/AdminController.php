<?php
  namespace prosys\admin\controller;
  
  use prosys\core\common\Agents,
      prosys\core\common\Settings,
      prosys\core\common\Functions,
      prosys\core\common\Mailer,
      prosys\core\common\MailerAttachment,
      prosys\core\common\AppException;

  /**
   * Processes main requests.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @property \prosys\view\admin\AdminView $_view PROTECTED property, this annotation is only because of Netbeans Autocompletion
   */
  class AdminController extends Controller
  {
    const DEFAULT_SENDER = 'podpora@proclient.cz';
    const DEFAULT_SENDER_NAME = 'Helpdesk PRO CLIENT s.r.o.';

    private $manualPath;
    
    /**
     * Initializes view.
     */
    public function __construct() {
      parent::__construct();
      
      $this->_view = Agents::getAgent('AdminView', Agents::TYPE_VIEW_ADMIN);
      $this->manualPath = __DIR__ . '/../../../doc/Popis sluzby.pdf';
    }
    
    /**
     * Odesle manual na danou e-mailovou adresu.
     */
    public function sendManual() {
      /* @var $_LOGGED_USER \prosys\model\UserEntity */
      global $_LOGGED_USER;
      
//      $sender     = (($_LOGGED_USER->email) ? $_LOGGED_USER->email : self::DEFAULT_SENDER);
//      $senderName = (($_LOGGED_USER->getFullName()) ? $_LOGGED_USER->getFullName() : self::DEFAULT_SENDER);
      $sender     = self::DEFAULT_SENDER;
      $senderName = self::DEFAULT_SENDER_NAME;
      
      if (($recipientString = (string)Functions::item($this->_post, 'email'))) {
        try {
          $recipients = (array)array_map(function($recipient) {
            return trim($recipient);
          }, (array)explode(';', $recipientString));
          
          $attachments = [];
          if (Functions::item($this->_post, 'attached_manual')) {
            $attachments[] = new MailerAttachment(basename($this->manualPath), 'application/pdf; charset=UTF-8', $this->manualPath);
          }
          
          if (Mailer::sendMail(
                (string)Functions::item($this->_post, 'subject'),
                (string)Functions::item($this->_post, 'body'),
                [$sender => $senderName], $recipients, [], [$sender => $senderName], Mailer::TYPE_HTML,
                $attachments
              )) {
            $_SESSION[Settings::MESSAGE_SUCCESS] = "Manuál byl úspěšně odeslán na adres" . ((count($recipients) > 1) ? 'y' : 'u') . "`" . implode('`, `', $recipients) . "` a do skryté kopie na `{$sender}`.";
            $this->reload('send_manual');
          } else {
            throw new \Exception('...');
          }
        } catch (\Exception $e) {
          throw new AppException("Manuál se nepodařilo odeslat na adresu `{$recipient}`.");
        }
      } else {
        throw new AppException('Musíte zadat e-mailovou adresu, na kterou chcete manuál zaslat.');
      }
    }
    
    /**
     * @inherit
     */
    public function response($activity) {
      /* @var $_LOGGED_USER \prosys\model\UserEntity */
      global $_LOGGED_USER;
      
      switch ($activity) {
        case 'login':
          $this->_view->login();
        break;
        
        case 'phpinfo':
          $this->_view->phpInfo();
        break;
      
        case 'e401':
        case 'e405':
          $get = filter_input_array(INPUT_GET);
          $requestet = ((array_key_exists('requestet', $get)) ? $get['requestet'] : array('module' => '', 'action' => ''));
          
          /* @var $moduleActionDao \prosys\model\ModuleActionDao */
          $moduleActionDao = Agents::getAgent('ModuleActionDao', Agents::TYPE_MODEL);

          /* @var $moduleAction \prosys\model\ModuleActionEntity */
          $moduleAction = $moduleActionDao->loadByModuleAndAction($requestet['module'], $requestet['action']);
          
          if ($moduleAction && !$moduleAction->isNew()) {
            $message = 'Nemáte práva k ';
            $message.= (($moduleAction->type == 1) ? 'zobrazení stránky: ' : 'provedení akce: ') . '<b>&bdquo;' . $moduleAction->title . '&ldquo;</b>';
            $message.= ' modulu: <b>&bdquo;' . $moduleAction->module->name . '&ldquo;</b>.';
            
            $_SESSION[Settings::MESSAGE_EXCEPTION] = $message;
          }
          
          $this->_view->errorPage($activity);
        break;
      
        case 'send_manual':
          $mailto = self::DEFAULT_SENDER;
          $this->_view->sendManual([
            'recepientMail'  => '',
            'attachedManual' => TRUE,
            'defaultSubject' => 'XML feed pro stahování výrobků společnosti STYLE PLUS s.r.o.',
            'defaultBody'    => <<<BODY
<p>Dobrý den,</p>
<p>
  na Vaši žádost Vám zasíláme manuál ke službě pro poskytování XML feedu s databází výrobků společnosti STYLE PLUS s.r.o.
  Veškeré informace, vč. odkazu na testovací balíček a postupu pro poskytnutí plnohodnotného feedu, naleznete v manuálu.
</p>
<p>
  Prosím Vás, abyste <b>řešení na veškeré problémy nejprve hledali v manuálu</b>,
  a teprve v případě neúspěchu kontaktovali naši technickou podporu <a href="mailto:{$mailto}">{$mailto}</a>.
</p>

<p>Děkujeme za pochopení.</p>
BODY
          ]);
        break;

        default:
          $this->_view->initial();
        break;
      }
    }
  }
