<?php
  namespace prosys\model;
  
  use prosys\core\mapper\MySqlMapper,
      prosys\core\mapper\SqlFilter,
      prosys\core\common\Agents,
      prosys\core\common\Settings;

  /**
   * Reprezentuje entitu vyrobku ze serveru OnlineKupelne.sk.
   * 
   * @property int $id
   * @property float $commonPrice element=common_price
   * @property float $price
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class OKSK_ProductEntity extends Entity
  {
    /** @var MySqlMapper */
    private $_priceServiceMapper;
    
    /**
     * Getter.
     * @return float
     */
    public function getRealPrice() {
      $servicePrice = $this->getPriceServiceMapper()->max(
        'float', 'price', SqlFilter::create()->comparise('item_id', '=', $this->id)
      );

      return (($servicePrice) ? $servicePrice : $this->price);
    }
    
    /**
     * Getter.
     * @return MySqlMapper
     */
    private function getPriceServiceMapper() {
      if ($this->_priceServiceMapper) {
        return $this->_priceServiceMapper;
      } else {
        $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::OKSK_DB_SERVER,
                                                                                       Settings::OKSK_DB_USER,
                                                                                       Settings::OKSK_DB_PASSWORD,
                                                                                       Settings::OKSK_DB_DATABASE,
                                                                                       Settings::OKSK_DB_PREFIX),
                                         'OKSK_MySqlConnection');
      
        return new MySqlMapper($mySqlHandler, 'shop_items_price_service', 'item_id');
      }
    }
  }
