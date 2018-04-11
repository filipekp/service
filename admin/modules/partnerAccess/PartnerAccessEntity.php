<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu pristupu partnera ke sluzbe.
   * 
   * @property int $id
   * @property UserEntity $partner element=partner_id
   * @property DateTime $accessAt element=access_at
   * @property string $ip
   * @property string $proxyIp element=proxy_ip
   * @property string $method
   * @property JSON $params
   * @property JSON $response
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerAccessEntity extends Entity
  {
    // IP adresy "osvobozene" od logovani
    public static $IP_WHITELIST = [
      '46.227.169.148',       // proclient
//      '85.93.177.223',        // server Prohosting
      '195.39.9.40',          // styleplus
      '217.170.96.60'
    ];
    
    /**
     * Vrati zpravu stavu odpovedi.
     * 
     * @param string $type code/message
     * @return string
     */
    public function getStatus($type = '') {
      $response = $this->response->jsonSerialize();
      
      if ($type) {
        return $response['status'][$type];
      } else {
        return $response['status']['code'] . ' ' . $response['status']['message'];
      }
    }
    
    /**
     * Vrati hodnotu promenne z pole response dle predane cesty.
     * 
     * @param array $path
     * @return string
     */
    public function getResponseValue(array $path) {
      $response = $this->response->jsonSerialize();
      
      return \prosys\core\common\Functions::item($response, $path);
    }
    
    /**
     * Vrati pocet vyrobku stazeneho feedu.
     * @return int
     */
    public function getResponseCount() {
      $response = $this->response->jsonSerialize();
      return (int)$response['count'];
    }
    
    /**
     * Vrati parametr predany generatoru feedu.
     * 
     * @param string $param identification/modified_from/category_type
     * @return string|NULL
     */
    public function getParam($param) {
      $params = (($this->params) ? $this->params->jsonSerialize() : []);
      return \prosys\core\common\Functions::item($params, $param);
    }
  }
