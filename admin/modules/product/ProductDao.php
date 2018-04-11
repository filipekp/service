<?php
  namespace prosys\model;
  
  use prosys\core\common\Agents,
      prosys\core\common\Settings,
      prosys\core\common\AppException;

  /**
   * Reprezentuje data access object vyrobku.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ProductDao extends SQLDataAccessObject
  {
    const ORDERS_PRICES_URL = 'http://orders.styleplus.cz/executable/exec-json-pricelist-export-prices.php';
    
    public function __construct() {
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::SKCZ_DB_SERVER,
                                                                                     Settings::SKCZ_DB_USER,
                                                                                     Settings::SKCZ_DB_PASSWORD,
                                                                                     Settings::SKCZ_DB_DATABASE,
                                                                                     Settings::SKCZ_DB_PREFIX),
                                       'SKCZ_MySqlConnection');
      parent::__construct(ProductEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'xml_products');
    }
    
    /**
     * Neni mozne smazat vyrobek z pohledu databaze.
     * 
     * @param mixed $arg
     * @throws AppException
     */
    public function delete($arg) {
      throw new AppException('Neni mozne smazat vyrobek z pohledu databaze.');
    }
    
    /**
     * Neni mozne ulozit vyrobek do pohledu databaze.
     * 
     * @param \prosys\model\Entity $entity
     * @throws AppException
     */
    public function store(Entity $entity) {
      throw new AppException('Neni mozne ulozit vyrobek do pohledu databaze.');
    }
    
    /**
     * Vrati ceny vyrobku z nakupniho systemu STYLE PLUS (orders.styleplus.cz).
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @param array $products [producer1 => [katalog1, katalog2, ...], producer2 => [katalog1, katalog2, ...], ...]
     * 
     * @return array JSON: [[producer, katalog, cena], ...]
     */
    public function getPricesFromOrders(PartnerEntity $partner, array $products) {
      // Get cURL resource
      $curl = curl_init();

        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => self::ORDERS_PRICES_URL,
          CURLOPT_POST => 1,
          CURLOPT_POSTFIELDS => http_build_query([
            'partner'  => $partner->ordersPartnerId,
            'products' => $products
          ])
        ));

        // Send the request & save response to $resp
        $response = curl_exec($curl);

      // Close request to clear up some resources
      curl_close($curl);

      // print XML out
      return json_decode($response, TRUE);
    }
  }
