<?php
  namespace prosys\model;
  
  use prosys\core\common\Agents,
      prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions,
      prosys\core\mapper\MySqlMapper,
      prosys\core\common\Settings;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity partnera (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerDao extends MyDataAccessObject
  {
    /** @var PartnerProducerDao */
    private $_partnerProducerDao;
    
    /** @var ProductDao */
    private $_productDao;
    
    public function __construct() {
      // zavola konstruktor rodice
      parent::__construct('partners', PartnerEntity::classname());
      
      // vytvori objekt pristupu k datum partnerovych vyrobcu
      $this->_partnerProducerDao = Agents::getAgent('PartnerProducerDao', Agents::TYPE_MODEL);
      
      // vytvori objekt pristupu k datum partnerovych vyrobku
      $this->_productDao = Agents::getAgent('ProductDao', Agents::TYPE_MODEL);
    }
    
    /**
     * Nacte partnera podle id uzivatele.
     * 
     * @param \prosys\model\UserEntity $user
     * @return PartnerEntity
     */
    public function loadByUser(UserEntity $user) {
      $partner = Functions::first(
        $this->loadRecords(
          SqlFilter::create()->comparise('user_id', '=', $user->id)
        )
      );
      
      return (($partner) ? $partner : $this->load());
    }
    
    /**
     * Nacte seznam aktivnich partneru.
     * 
     * @return PartnerEntity[]
     */
    public function loadActivePartners() {
      return $this->loadRecords(
        SqlFilter::create()->comparise('active', '=', 1)
      );
    }
    
    /**
     * Stahne seznam hlavnich vyrobku (nikoliv variant) partnera ze serveru StyloveKoupelny.cz.
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @param \prosys\core\mapper\SqlFilter $condition
     * @param string $modifiedFrom
     * @param bool $showDeleted
     * 
     * @return ProductEntity[]
     */
    public function loadPartnerProducts(PartnerEntity $partner, SqlFilter $condition = NULL, $modifiedFrom = '', $showDeleted = FALSE) {
      // odfiltruje vsechny hlavni vyrobky, nebo vyrobky, ktere nemaji varianty
      $filter = SqlFilter::create()->comparise('sales_channel', '=', ProductEntity::SALE_CHANNEL_ALL)
                                   ->orL()
                                   ->comparise('sales_channel', '=',
                                     constant(Agents::getNamespace(Agents::TYPE_MODEL) . "ProductEntity::SALE_CHANNEL_{$partner->styleplusPartner->engine}")
                                   );
      
      // je-li dano datum zmeny, aplikuje jej do filtru
      if ($modifiedFrom) {
        $modifiedFrom = (new \DateTime($modifiedFrom))->format('Y-m-d H:i:s');
        $filter->andL(SqlFilter::create()->comparise('modified_at', '>=', $modifiedFrom));
      }
      
      // aplikace vychoziho filtru
      if (is_null($condition) && ($defaultFilterDefinition = Functions::item($partner->constraints->jsonSerialize(), 'default_filter'))) {
        $defaultFilter = SqlFilter::create()->contradiction();
        foreach ($defaultFilterDefinition as $column => $values) {
          $defaultFilter->orL()->inArray($column, $values);
        }
        
        $filter->andL($defaultFilter);
      }

      // stahne vsechny vyrobky vc. podvyrobku
      $allRecords = $this->_productDao->loadRecords($filter, array(
        ['column' => 'producer', 'direction' => 'asc'],
        ['column' => 'parent', 'direction' => 'asc'],
        ['column' => 'id', 'direction' => 'asc']
      ), [], $showDeleted, FALSE, TRUE, TRUE);

      // stahne rodice
      $parents = array_filter($allRecords, function($item) {
        return $item['id'] == $item['parent'];
      });
      
      // nachysta podvyrobky
      $children = array_combine(
        array_map(function($parent) { 
          return $parent['id'];
        }, $parents), 
        array_fill(0, count($parents), [])
      );
      
      // stahne podvyrobky
      foreach ($allRecords as $item) {
        if ($item['id'] != $item['parent']) {
          $children[$item['parent']][] = $item;
        }
      }
      
      // ulozi podvyrobky
      foreach ($parents as &$parent) {
        $parent['variants'] = $children[$parent['id']];
      }

      // vrati hlavni vyrobky
      return $parents;
    }
    
    /**
     * Stahne seznam hlavnich vyrobku (nikoliv variant) partnera ze serveru StyloveKoupelny.cz.
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @param \prosys\core\mapper\SqlFilter $condition
     * @param string $modifiedFrom
     * @param bool $showDeleted
     * @param array $allProducers  id vyrobcu, ktere chceme stahnout bez ohledu na modified_at
     * 
     * @return ProductEntity[]
     */
    public function loadPartnerProductsNew(PartnerEntity $partner, SqlFilter $condition = NULL, $modifiedFrom = '', $showDeleted = FALSE, array $allProducers = []) {
      
      // odfiltruje vsechny hlavni vyrobky, nebo vyrobky, ktere nemaji varianty
      $filter = SqlFilter::create()->comparise('sales_channel', '=', ProductEntity::SALE_CHANNEL_ALL)
                                   ->orL()
                                   ->comparise('sales_channel', '=',
                                     constant(Agents::getNamespace(Agents::TYPE_MODEL) . "ProductEntity::SALE_CHANNEL_{$partner->styleplusPartner->engine}")
                                   );
      
      // je-li dano datum zmeny, aplikuje jej do filtru
      if ($modifiedFrom) {
        $modifiedFrom = (new \DateTime($modifiedFrom))->format('Y-m-d H:i:s');
				
				// vsechny vyrobky z tabulky stylovych koupelen, ktere byly zmeneny po $modifiedFrom
        $localFilter = SqlFilter::create()->comparise('modified_at', '>=', $modifiedFrom);
				
				
				// NEBO vsechny vyrobky takovych vyrobcu, kterym byla zmenena marze na service.styleplus.cz (predanych v $allProducers)
        if ($allProducers) {
          // výrobce, série, katalogové číslo
          $localFilter->orL(
            SqlFilter::create()->inArray('producer_id', array_map(function($producer) {
                return $producer['id'];
              }, $allProducers))
          );
        }
				
				// NEBO vsechny vyrobky, kterym byla v service.styleplus.cz zmenena "cena partnera" (z tabulky partner_prices)
				$localFilter->orL()->inArray('id', $partner->getNettoProducts($modifiedFrom));
				
				// prida filtr k vychozimu
        $filter->andL($localFilter);
      }
      
      // aplikace vychoziho filtru
      if (($defaultFilterDefinition = Functions::item($partner->constraints->jsonSerialize(), 'default_filter'))) {
        $defaultFilter = SqlFilter::create()->contradiction();
        foreach ($defaultFilterDefinition as $column => $values) {
          $defaultFilter->orL()->inArray($column, $values);
        }
        
        $filter->andL($defaultFilter);
      }	
      
      if (!is_null($condition)) {
        $filter->andL($condition);
      }
      
      $timer = microtime(TRUE);
      // stahne vsechny vyrobky vc. podvyrobku
      $allRecords = $this->_productDao->loadRecords($filter, array(
        ['column' => 'producer', 'direction' => 'asc'],
        ['column' => 'parent', 'direction' => 'asc'],
        ['column' => 'id', 'direction' => 'asc']
      ), [], TRUE, FALSE, TRUE, TRUE);
      
      if (count($allRecords) == 0) { return []; }
      
      // odstrani duplicitni vyrobky ktere maji stejne katalogove cislo, vyrobce a serii a ponecha to s nejvyssim datem modified_at
      $tmp = [];
      $toFilter = [];
      foreach ($allRecords as $idx => $item) {
        if (trim($item['catalog_id']) == '') {
          continue;
        }
        
        $identification = md5((($item['catalog_id']) ? $item['catalog_id'] : $item['name_cz']) . $item['producer_id'] . $item['serie_id']);
        if (array_key_exists($identification, $tmp)) {
          if ($tmp[$identification]['modifiedAt'] < ($currentModifiedAt = new \DateTime($item['modified_at']))) {
            $toFilter[$tmp[$identification]['id']] = $tmp[$identification]['id'];
            
            $tmp[$identification]['id'] = $item['id'];
            $tmp[$identification]['modifiedAt'] = $currentModifiedAt;
          } else {
            $toFilter[$item['id']] = $item['id'];
          }
        } else {
          $tmp[$identification] = [
            'id' => $item['id'],
            'modifiedAt' => new \DateTime($item['modified_at']),
          ];
        }
      }
      
      $allRecords = array_diff_key($allRecords, $toFilter);

      // stahne rodice
      $parents = array_filter($allRecords, function($item) {
        return $item['id'] == $item['parent'];
      });
      
      // nachysta podvyrobky
      $children = array_combine(
        array_map(function($parent) { 
          return $parent['id'];
        }, $parents), 
        array_fill(0, count($parents), [])
      );
      
      // stahne podvyrobky
      foreach ($allRecords as $item) {
        if ($item['id'] != $item['parent']) {
          $children[$item['parent']][] = $item;
        }
      }
      
      // ulozi podvyrobky
      foreach ($parents as &$parent) {
        $parent['variants'] = $children[$parent['id']];
      }

      // vrati hlavni vyrobky
      return $parents;
    }
    
    /**
     * Vrati asociativni pole [id produktu => cena].
     * 
     * @param array $productIds
     * @return array
     */
    public function loadPartnerPrices(PartnerEntity $partner, array $productIds) {
      if ($productIds) {
        $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::SKCZ_DB_SERVER,
                                                                                       Settings::SKCZ_DB_USER,
                                                                                       Settings::SKCZ_DB_PASSWORD,
                                                                                       Settings::SKCZ_DB_DATABASE,
                                                                                       Settings::SKCZ_DB_PREFIX),
                                         'SKCZ_MySqlConnection');

        $mySqlMapper = new MySqlMapper($mySqlHandler, 'vyrobky', NULL);
        
//        $result = array();
//        foreach (array_chunk($productIds, 5000) as $chunk) {
//          $data = $mySqlMapper->callProcedureSelect('getCompanyPrices', array($partner->styleplusPartner->id, implode(', ', $chunk)));
//
//          $result += array_combine(
//            array_map(function($item) { return $item->id; }, $data),
//            array_map(function($item) { return (float)$item->price; }, $data)
//          );
//        }
        
        $mysqli = new \mysqli(Settings::SKCZ_DB_SERVER, Settings::SKCZ_DB_USER, Settings::SKCZ_DB_PASSWORD, Settings::SKCZ_DB_DATABASE);
        $imploded = implode(',', $productIds);
        $resultMysqli = $mysqli->query("SELECT
  id,
  bestPrice({$partner->styleplusPartner->id}, -1, id, 1) AS price
FROM vyrobky
WHERE id IN ({$imploded})");
        $dataM = [];
        while ($row = $resultMysqli->fetch_assoc()) {
          $dataM[$row['id']] = round((float)$row['price'], 2);
        }
        
//        $data = $mySqlMapper->findRecordsProjection(['id', 'bestPrice(' . $partner->styleplusPartner->id . ', -1, id, 1) AS price'], SqlFilter::create()->inArray('id', $productIds));
//        
//        $result = array_combine(
//          array_map(function($item) { return $item->id; }, $data),
//          array_map(function($item) { return (float)$item->price; }, $data)
//        );
        
        return $dataM;
      } else {
        return array();
      }
    }
    
    /**
     * Nacte vyrobce partnera.
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @return PartnerProducerEntity[] vrati pole vyrobcu, kde klice jsou jejich id
     */
    public function loadPartnerProducers(PartnerEntity $partner) {
      $partnerProducers = $this->_partnerProducerDao->loadByPartner($partner->user);

      return array_combine(array_map(
          function($partnerProducer) {
            return $partnerProducer->producer->id;
          }, $partnerProducers
        ),
        array_values($partnerProducers)
      );
    }
    
    /**
     * Ulozi vyrobce partnera.
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @param array $producers id vyrobcu
     * @param array $profits pole nastavenych profitu
     */
    public function setPartnerProducers(PartnerEntity $partner, array $producers, array $profits = []) {
      $current = array_keys($this->loadPartnerProducers($partner));
      $intersect = array_intersect($current, $producers);     // spolecne prvky stavajicich a novych vyrobcu
      
      // smazu
      if (($toDelete = array_diff($current, $intersect))) {
        $this->_partnerProducerDao->deleteRecords(
          SqlFilter::create()
            ->comparise('partner_id', '=', $partner->user->id)
            ->andL()
            ->inArray('producer_id', $toDelete)
        );
      }
      
      // pridam
      array_map(function($producer) use ($partner, $profits) {
        $this->_partnerProducerDao->store(
          $this->_partnerProducerDao->load(
            array(
              'partner' => $partner->user,
              'producer_id' => $producer,
              'profit' => (float)Functions::item($profits, $producer)
            )
          )
        );
      }, array_diff($producers, $intersect));
      
      // upravim profity
      array_map(function($producer) use ($partner, $profits) {
        $partnerProducer = $this->_partnerProducerDao->loadByPartnerAndProducer($partner->user, $producer);
        $partnerProducer->profit = (float)Functions::item($profits, $producer);
        
        $this->_partnerProducerDao->store($partnerProducer);  
      }, $intersect);
    }
    
    /**
     * Ulozi nastaveni profitu pro kazdeho vyrobce.
     * 
     * @param array $producers
     */
    public function changePartnerProducersProfits(array $producers) {
      foreach ($producers as $id => $profit) {
        $this->_partnerProducerDao->store(
          $this->_partnerProducerDao->load(array('id' => $id, 'profit' => (float)$profit))
        );
      }
    }
    
    /**
     * Smaze partnera z databaze.
     * 
     * @param PartnerEntity $arg
     * @param bool $force
     */
    public function delete($arg, $force = FALSE) {
      /* @var $userDao \prosys\model\UserDao */
      $userDao = Agents::getAgent('UserDao', Agents::TYPE_MODEL);
      
      return $this->_partnerProducerDao->deleteRecords(     // smaze zaznamy o pridelenych vyrobcich
        SqlFilter::create()->comparise('partner_id', '=', $arg->user->id)
      ) &&
      parent::delete($arg, TRUE) &&                         // smaze partnera
      $userDao->delete($arg->user, TRUE);                   // smaze uzivatele
    }
  }
