<?php
  namespace prosys\model;

use prosys\admin\controller\General_GeneratorController,
    prosys\core\common\Agents,
    prosys\core\common\types\JSON,
    prosys\core\mapper\SqlFilter,
    prosys\core\common\Functions;

  /**
   * Reprezentuje entitu partnera.
   * 
   * @property string $hashCode element=hash_code&primary
   * @property UserEntity $user element=user_id
   * @property string $name
   * @property string $web
   * @property SKCZ_PartnerEntity $styleplusPartner element=styleplus_id
   * @property JSON $constraints
   * @property bool $useRegularOkPrices element=use_regular_ok_prices
   * @property bool $noWatermark element=no_watermark
   * @property string $note
   * @property string $type
   * @property bool $active
   * @property bool $pricesFromOrders element=prices_from_orders
   * @property bool $showSellout element=show_sellout
   * @property int $ordersPartnerId element=orders_partner_id
   * 
   * @property PartnerProducerEntity[] $producers binding=1n:user_id>:>
   * @property PartnerAccessEntity[] $log binding=1n:user_id>:access_at>DESC
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerEntity extends Entity
  {
    const GROUP_ID = 7;
    
    const TYPE_OLD = 'old';
    const TYPE_REGULAR = 'regular';
    
    /**
     * Podle toho, zda je partner CZ nebo SK vrati hodnotu spravne property produktu.
     * 
     * @param SKCZ_ProductEntity $product
     * @param string $slot
     * 
     * @return mixed
     */
    public function getProductInformationOld(SKCZ_ProductEntity $product, $slot) {
      return $product->{$slot . $this->styleplusPartner->engine};
    }
    
    /**
     * Podle toho, zda je partner CZ nebo SK vrati hodnotu spravne property produktu.
     * 
     * @param ProductEntity $product
     * @param string $slot
     * 
     * @return mixed
     */
    public function getProductInformation(ProductEntity $product, $slot) {
      return $product->{$slot . $this->styleplusPartner->engine};
    }

    /**
     * Getter.
     * @return string
     */
    public function getConstraintsDefault() {
      // jestlize nema omezeni, nastavi vychozi omezeni pro partnery "noveho typu"
      if (!$this->constraints) {
        $columns = array(22, 1, 2, 15, 3, 4, 16, 6, 7, 8, 13, 10, 11, 20, 12, 21, 14, 17, 23);
        $constraints = array(
          'modified_from' => ((($modifiedFrom = filter_input(INPUT_POST, 'modified_from'))) ? $modifiedFrom : date('Y-m-d 00:00:00', strtotime('-1 day'))),
          'show_removed' => TRUE, 'stock_count' => FALSE, 'size' => 'sp', 'excel' => FALSE,
          'category_path' => filter_input(INPUT_POST, 'category_type', FILTER_SANITIZE_STRING, ['default' => 'category']) === 'path',
          'columns' => array_combine($columns, array_fill(0, count($columns), NULL))
        );

        // vychozi filtr - jedna-li se o "noveho" partnera, ktery nema zadany vychozi filtr v databazi, stahne informace o nastavenych marzich vyrobcu
        if (!array_key_exists('default_filter', $constraints) && $this->type != PartnerEntity::TYPE_OLD) {
          $producers = [];
          if ($this->producers) {
            $requiredProducers = filter_input(INPUT_POST, 'producers', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);

            foreach ($this->producers as /* @var $partnerProducer PartnerProducerEntity */$partnerProducer) {
              if (!$requiredProducers || in_array($partnerProducer->producer->id, $requiredProducers)) {
                $producers[] = $partnerProducer->producer->id;
              }
            }
          }

          $constraints['default_filter'] = array('producer_id' => (($producers) ? $producers : [0]));
        }
        
        
        $this->constraints = new JSON($constraints);
      }
      
      return $this->constraints;
    }
    
    /**
     * Vrátí požadované omezení / nastavení.
     * 
     * @param string|array $constraint
     * @return mixed
     */
    public function getConstraint($constraint) {
      return Functions::item($this->constraints, $constraint);
    }
    
    /**
     * Načte poslední partnerův přístup.
     * 
     * @return PartnerAccessEntity
     */
    public function getLastAccess() {
      /* @var $partnerAccessDao PartnerAccessDao */
      $partnerAccessDao = Agents::getAgent('PartnerAccessDao', Agents::TYPE_MODEL);
      
      return $partnerAccessDao->loadLastAccess($this->user);
    }
    
    /**
     * Vrati ceny vyrobku z nakupniho systemu STYLE PLUS (orders.styleplus.cz) pro vsechny vyrobky vsech vyrobcu partnera.
     * @return array JSON: [[producer, katalog, cena], ...]
     */
    public function getProductPricesFromOrders() {
      // pripravi DAO
      /* @var $productDao ProductDao */
      $productDao = Agents::getAgent('ProductDao', Agents::TYPE_MODEL);
      
      /* @var $partnerDao PartnerDao */
      $partnerDao = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);
      
      // stahne vyrobky partnera
      $products = array_map(
        function($producer) {
          return [$producer->producer->id, $producer->producer->getCatalogIds()];
        },
        $partnerDao->loadPartnerProducers($this)
      );
        
      // stahne ceny
      return $productDao->getPricesFromOrders(
        $this,
        array_combine(
          array_map(function($item) { return $item[0]; }, $products),
          array_map(function($item) { return $item[1]; }, $products)
        )
      );
    }
			
		// stahne netto ceny
		public function getNettoPrices()
		{
			$nettoPricesDao = Agents::getAgent('PartnerNettoPriceDao', Agents::TYPE_MODEL);			
			$prices = $nettoPricesDao->loadRecords(SqlFilter::create()->comparise('partner_id', '=', $this->user->id));
			return array_combine(
								array_map(function($item){ return $item->product_id;}, $prices),
								array_map(function($item){ return $item->price;}, $prices)
							);
		}
		
		// stahne netto ceny
		public function getNettoProducts($modifiedFrom = '')
		{
			$nettoPricesDao = Agents::getAgent('PartnerNettoPriceDao', Agents::TYPE_MODEL);			
			$prices = $nettoPricesDao->loadRecords(SqlFilter::create()
																							->comparise('partner_id', '=', $this->user->id)
																							->andL()
																							->comparise('modified_at', '>=', $modifiedFrom));
			return array_map(function($item){ return $item->product_id;}, $prices);			
		}
  }
