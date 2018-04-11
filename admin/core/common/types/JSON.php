<?php
  namespace prosys\core\common\types;

  /**
   * Objekt reprezentujici datovy typ JSON.
   *
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @todo možná ho implementovat jako potomka ArrayObject a pretizit nektere metody, aby bylo mozne jednoduse (pres []) pridavat a upravovat data
   */
  class JSON implements \JsonSerializable {
    private $_json;
    
    public function __construct($json) {
      if (!is_array($json)) {
        $json = ((($decoded = @json_decode($json, TRUE))) ? $decoded : array());
      }

      $this->_json = (($json) ? $json : array());
    }
    
    public function jsonSerialize() {
      return $this->_json;
    }

    public function __toString() {
      return json_encode($this->_json, JSON_PRETTY_PRINT);
    }
}
