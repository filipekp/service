<?php
  namespace prosys\admin\controller;
  
  use prosys\core\common\Agents,
      prosys\core\common\AppException,
      prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions,
      prosys\core\common\Settings;

  /**
   * Zpracuje požadavky na modul partnerů.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @property \prosys\admin\view\PartnerView $_partnerView PROTECTED property, this annotation is only because of Netbeans Autocompletion
   * @property \prosys\model\PartnerDao $_partnerDao PROTECTED property, this annotation is only because of Netbeans Autocompletion
   */
  class PartnerController extends UserController
  { 
    /** @var \prosys\admin\view\PartnerView $_partnerView */
    protected $_partnerView;
    
    /** @var \prosys\model\PartnerDao $_partnerDao */
    protected $_partnerDao;
    
    /** @var \prosys\model\SKCZ_PartnerDao $_skczPartnerDao */
    protected $_skczPartnerDao;
    
    /** @var \prosys\admin\view\PartnerAccessView $_partnerAccessView */
    protected $_partnerAccessView;
    
    /**
     * Inicializuje view a dao.
     */
    public function __construct() {
      parent::__construct();
      
      $this->_partnerDao = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);
      $this->_partnerView = Agents::getAgent('PartnerView', Agents::TYPE_VIEW_ADMIN, array($this->_dao));
      
      $this->_skczPartnerDao = Agents::getAgent('SKCZ_PartnerDao', Agents::TYPE_MODEL);
      
      $this->_partnerAccessView = Agents::getAgent('PartnerAccessView', Agents::TYPE_VIEW_ADMIN,
        array(Agents::getAgent('PartnerAccessDao', Agents::TYPE_MODEL))
      );
    }
    
    /**
     * Vygeneruje nahodny textovy retezec jako hash kod pro pristup ke sluzbe poskytovani XML.
     */
    public function generateHashCode() {
      do {
        $hash = Functions::randomString();
      } while (!$this->_partnerDao->load($hash)->isNew());
      
      $output = array(
        'response'  => array('status'  => 200),
        'data'      => array('hashCode' => $hash)
      );
      
      header('Content-Type: application/json');
      echo json_encode($output);
      exit();
    }
    
    /**
     * Check required entries.
     * 
     * @param array $post
     * @param array $exceptions
     */
    private function checkMandatories(array &$post, array &$exceptions) {
      if (!$post['partner']['name']) { $exceptions['partner[name]'] = 'Musíte zadat jméno.'; }
      if (!$post['partner']['hash_code']) { $exceptions['partner[hash_code]'] = 'Musíte zadat ID pro odběr služby.'; }
      if (!$post['partner']['styleplus_id']) { $exceptions['partner[styleplus_id]'] = 'Musíte zadat StyloveKoupelny.cz ID.'; }
    }
    
    /**
     * Ulozi partnera do databaze.
     * 
     * @throws AppException
     */
    public function save($reload = TRUE, $verify = TRUE, array $post = array()) {
      $post = Functions::trimArray(filter_input_array(INPUT_POST));

      if (array_key_exists('delete', $post)) {
        header('Location: ' . Settings::ROOT_ADMIN_URL . '?controller=user&action=delete&id=' . $post['id']);
        exit();
      }
      
      // zkontroluje povinne polozky partnera
      $exceptions = array();
      $this->checkMandatories($post, $exceptions);
      
      // zkontroluje povinne polozky uzivatele
      $post['first_name'] = $post['partner']['name'];
      $post['last_name'] = '';

      parent::verify($post, $exceptions);

      Functions::unsetItem($exceptions, 'first_name');
      Functions::unsetItem($exceptions, 'last_name');

      // ulozi partnera
      if ($exceptions) {
        $_SESSION['post'] = $post;
        throw new AppException($exceptions);
      } else {
        /* @var $partner \prosys\model\PartnerEntity */
        $partner = $this->_partnerDao->load($post['partner']);

        // ulozi uzivatele
        try {
          $user = parent::save(FALSE, FALSE, $post);
        } catch (AppException $e) {
          throw new AppException("Partnera &bdquo;{$partner->name}&ldquo; se nepodařilo uložit.");
        }

        $partner->user = $user;
        
        if ($this->_partnerDao->store($partner)) {
          $_SESSION[Settings::MESSAGE_SUCCESS] = "Partner &bdquo;{$partner->name}&ldquo; byl úspěšně uložen.";
          
          $producers = (array)Functions::item($post, 'producers');
          $this->_partnerDao->setPartnerProducers($partner, $producers, $post['profits']);
          
          if ($reload) {
            if (array_key_exists('apply', $post)) {
              switch ($post['back_to']) {
                case 'manage_profile':
                  $this->reload($post['back_to']);
                break;
              
                default:
                  $this->reload($post['back_to'], 'id=' . $partner->hashCode);
                break;
              }
            } else {
              $this->reload();
            }
          }
        } else {
          $this->_dao->delete($user, TRUE);
          throw new AppException("Partnera &bdquo;{$partner->name}&ldquo; se nepodařilo uložit.");
        }
      }
    }
    
    /**
     * Odhlasi neaktivniho partnera.
     */
    public function accessDenied() {
      if (array_key_exists('logged_user_id', $_SESSION)) {
        unset($_SESSION['logged_user_id']);
        
        Functions::unsetItem($_SESSION, Settings::MESSAGE_INFO);
        
        $_SESSION[Settings::MESSAGE_ERROR] = 'Váš účet není aktivní, nemůžete se přihlásit.';
        Controller::redirect();
      }
    }
    
    /**
     * Ulozi partnera do databaze.
     * 
     * @throws AppException
     */
    public function saveProfile() {
      $post = Functions::trimArray(filter_input_array(INPUT_POST));
      try {
        $this->_partnerDao->changePartnerProducersProfits($post['partner_producer']);
      } catch (AppException $e) { }
     
      $_SESSION[Settings::MESSAGE_SUCCESS] = 'Nastavení bylo úspěšně uloženo.';
      $this->reload($post['back_to']);
    }
    
    /**
     * Smaze partnera z databaze.
     * 
     * @throws AppException
     */
    public function delete() {
      $id = (string)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
      $partner = $this->_partnerDao->load($id);
      
      if ($partner->isNew()) {
        throw new AppException("Partnera nebylo možné smazat. Partner s identifikačním kódem &bdquo;{$id}&ldquo; neexistuje.");
      } else {
        if ($this->_partnerDao->delete($partner)) {
          $this->_infoMessage = "Partner &bdquo;{$partner->name}&ldquo; byl úspěšně smazán.";
          $this->reload();
        } else {
          throw new AppException("Partnera &bdquo;{$partner->name}&ldquo; se nepodařilo smazat.");
        }
      }
    }
    
		public function test() {
			$modifiedFrom = (new \DateTime("2016-08-11 14:01:00"))->format('Y-m-d H:i:s');			
			$productDao = Agents::getAgent('ProductDao', Agents::TYPE_MODEL);
			$partner = $this->_partnerDao->loadRecords(SqlFilter::create()->comparise('user_id', '=' , 84));			
			\PC::debug($partner[0]->getNettoPrices());
			
		}
		
    /**
     * Stahne a zobrazi XML feed partnera.
     */
    public function downloadXmlFeed() {
      $hash = (string)filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
      $test = (string)filter_input(INPUT_GET, 'test', FILTER_SANITIZE_STRING);
      $scriptUrl = (($test) ? Settings::SERVICE_ACTION_SCRIPT_URL_TEST : Settings::SERVICE_ACTION_SCRIPT_URL);
      
      
      $params = [];
      if (($outputType = filter_input(INPUT_GET, 'output_type', FILTER_SANITIZE_STRING))) {
        $params['output_type'] = $outputType;
      }
      
      $xmlFeedAgeDays = (int)filter_input(INPUT_GET, 'xml_feed_age_days', FILTER_SANITIZE_NUMBER_INT);
      $xmlFeedAgeDate = (string)filter_input(INPUT_GET, 'xml_feed_age_date', FILTER_SANITIZE_STRING);
                
      if ($xmlFeedAgeDays) {
        $xmlFeedAge = date('Y-m-d H:i:s', strtotime('-' . $xmlFeedAgeDays . 'days'));
      } elseif ($xmlFeedAgeDate) {
        $xmlFeedAge = $xmlFeedAgeDate;
      } else {
        $xmlFeedAge = '2010-01-01 00:00:00';
      }
      
      $partner = $this->_partnerDao->load($hash);
      if (!$partner->constraints || ($partner->constraints && Functions::item($partner->constraints->jsonSerialize(), 'excel') === FALSE)) {
        // Get cURL resource
        $curl = curl_init();
          // set some options
          curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $scriptUrl . (($params) ? '?' . http_build_query($params) : ''),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query(
              [ 
                'identification'  => $hash,
                'modified_from'   => $xmlFeedAge,
                'admin'           => 1,
                'producers'       => filter_input(
                  INPUT_GET, 'producers', FILTER_VALIDATE_INT,
                  [
                    'flags' => FILTER_REQUIRE_ARRAY,
                    'options' => ['default' => []],
                  ]
                ),
                'force_generate'  => filter_input(
                  INPUT_GET, 'force_generate', FILTER_VALIDATE_INT,
                  [
                    'options' => ['default' => 0],
                  ]
                ),
              ]
            )
          ));
          // Send the request & save response to $resp
          $response = curl_exec($curl);					

        // Close request to clear up some resources
        curl_close($curl);

        // vypise XML na obrazovku
        header('Content-type: text/xml; charset=UTF-8');
        echo $response;
      } else {
        $params['identification'] = $hash;
        $params['admin'] = 1;
        
        if (($producers = filter_input(
          INPUT_GET, 'producers', FILTER_VALIDATE_INT,
          [
            'flags' => FILTER_REQUIRE_ARRAY,
            'options' => ['default' => []],
          ]
        ))) {
          $params['producers'] = (array)$producers;
        }
        
        if (($forceGenerate = filter_input(
          INPUT_GET, 'force_generate', FILTER_VALIDATE_INT,
          [
            'options' => ['default' => 0],
          ]
        ))) {
          $params['force_generate'] = $forceGenerate;
        }
        
        header('Location: ' . $scriptUrl . (($params) ? '?' . http_build_query($params) : ''));
      }
      
      exit();
    }
    
    /**
     * Nacte spolecnosti podle casti jejiho jmena - funkce pro autocomplete.
     */
    public function loadSkCzCompanyNames() {
      $term = filter_input(INPUT_GET, 'term');
      
      $company = ((is_numeric($term)) ? $this->_skczPartnerDao->load($term) : NULL);
      if (is_null($company) || $company->isNew() || $company->deleted) {
        $companies = ((mb_strlen($term) < 3) ? array() : $this->_skczPartnerDao->loadByTerm($term));
      } else {
        $companies = array($company);
      }
      
      if ($companies) {
        array_walk($companies, function(&$company) {
          $company = array(
            'label'   => $company->name,
            'id'      => $company->id,
            'country' => $company->engine
          );
        });
      }
      
      header('Content-Type: application/json');
      echo json_encode($companies);
      exit();
    }

    /**
     * @inherit
     */
    public function response($activity) {
      global $_LOGGED_USER;
      
      $get = filter_input_array(INPUT_GET);
      $templateOnly = ((array_key_exists('template_only', $get)) ? TRUE : FALSE);
      
      switch ($activity) {
        case 'send_information_mail':
          /* @var $adminView \prosys\admin\view\AdminView */
          $adminView = Agents::getAgent('AdminView', Agents::TYPE_VIEW_ADMIN);
          $partner = $this->_partnerDao->load(filter_input(INPUT_GET, 'id'));
          $labels = $this->_partnerView->getLabels();
          /* @var $partner \prosys\model\PartnerEntity */
          
          $partnerProducersLI = array_map(function($partnerProducer) {
            /* @var $partnerProducer \prosys\model\PartnerProducerEntity */
            return '<li>' . $partnerProducer->producer->name . ': ' . $partnerProducer->profit . '%</li>';
          }, $partner->producers->getLoadedArrayCopy());
          
          $mailBody = '
            <p>Dobrý den,</p>
            <p>
              zasílám Vám informace k implementaci ostrého XML feedu s databází výrobků společnosti STYLE PLUS s.r.o.
              Vaše identifikační údaje pro odebírání služby jsou následující:
              <ul>
                <li>%s: <b>%s</b></li>
                <li>Přihlašovací údaje do administrace
                  <ul>
                    <li>adresa administrace: <a href="%s">%s</a></li>
                    <li>%s: %s</li>
                    <li>%s: <i>heslo prosím doplňte ručně</i></li>
                  </ul>
                </li>
              </ul>
            </p>
            <p>
              <b>Vaši výrobci a jejich marže</b>
              <ul>
                %s
              </ul>
            </p>

            <p>Děkuji a přeji hezký den.</p>
            <p>%s<br />%s</p>';
          $mailBodyVariables = [
            Functions::item($labels, 'hashCode'), $partner->hashCode,
            Settings::ROOT_ADMIN_URL, Settings::ROOT_ADMIN_URL,
            Functions::item($labels, ['user', 'login']), $partner->user->login,
            Functions::item($labels, ['user', 'password']),
            implode(PHP_EOL, $partnerProducersLI),
            $_LOGGED_USER->getFullName(), $_LOGGED_USER->getContact()
          ];
          
          $adminView->sendManual([
            'recepientMail'  => $partner->user->email,
            'defaultSubject' => 'XML feed pro stahování výrobků společnosti STYLE PLUS s.r.o.',
            'defaultBody'    => vsprintf($mailBody, $mailBodyVariables)
          ]);
        break;
          
        case 'manage_profile':
          global $_LOGGED_USER;
          $partner = $this->_partnerDao->loadByUser($_LOGGED_USER);

          $optional = array(
            'template_only' => $templateOnly,
            'producers'     => $this->_partnerDao->loadPartnerProducers($partner)
          );

          $this->_partnerView->manageProfile($partner, $optional);
        break;
        
        case 'manage':
          $get = filter_input_array(INPUT_GET);
          $id = ((array_key_exists('id', $get)) ? $get['id'] : NULL);

          if (array_key_exists('post', $_SESSION)) {
            $partner = $this->_partnerDao->load($_SESSION['post']['partner']);
            $partner->user = $this->_dao->load($_SESSION['post']);
          } else {
            $partner = $this->_partnerDao->load($id);
          }

          /* @var $producerDao \prosys\model\ProducerDao */
          $producerDao = Agents::getAgent('ProducerDao', Agents::TYPE_MODEL);
          $producers = $producerDao->loadRecords(NULL, array(array('column' => 'sort_order', 'direction' => 'ASC')));
          
          ob_start();
            $this->_partnerAccessView->table($partner->log,
              ['template_only' => TRUE, 'count' => count($partner->log), 'items_on_page' => $this->_itemsOnPage]
            );
          $logTemplate = ob_get_clean();

          $optional = array(
            'template_only'     => $templateOnly,
            'producers'         => $producers,
            'partnerProducers'  => $this->_partnerDao->loadPartnerProducers($partner),
            'logTemplate'       => $logTemplate
          );

          Functions::unsetItem($_SESSION, 'post');
          $this->_partnerView->manage($partner, $optional);
        break;
      
        case 'initial':
        case 'table':
        default:
          $get = filter_input_array(INPUT_GET);
          $filterName = ((array_key_exists('filter_name', $get)) ? $get['filter_name'] : '');
          $filterLogin = ((array_key_exists('filter_login', $get)) ? $get['filter_login'] : '');
          
          $formFilter = array(
            'filter_name'    => $filterName,
            'filter_login'   => $filterLogin
          );
          
          // filter
          $filter = SqlFilter::create()->identity();
          
          if ($filterName) {
            $filter->andL(
              SqlFilter::create()->contains('name', $filterName)
            );
          }
          
          if ($filterLogin) {
            $filter->andL(
              SqlFilter::create()->inFilter('user_id',
                SqlFilter::create()->filter('id', 'users',
                  SqlFilter::create()->contains('login', (($filterLogin) ? $filterLogin : ''))
                )
              )
            );
          }
          
          // pagination
          $count = $this->_partnerDao->count($filter);
          
          // correction of current page
          $this->currentPageCorrection($count);
          
          $limitFrom = ($this->_currentPage - 1) * $this->_itemsOnPage;
          $partners = $this->_partnerDao->loadRecords(
            $filter,
            array(
              array('column' => 'type', 'direction' => 'asc'),
              array('column' => 'name', 'direction' => 'asc')
            ),
            array($limitFrom, $this->_itemsOnPage)
          );

          $optional = array(
            'count'             => $count,
            'items_on_page'     => $this->_itemsOnPage,
            'filter'            => $formFilter,
            'template_only'     => $templateOnly,
            'get'               => $this->_get
          );
          
          $this->_partnerView->table($partners, $optional);
        break;
      }
    }
  }

