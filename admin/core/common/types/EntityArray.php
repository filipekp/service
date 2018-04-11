<?php
  namespace prosys\core\common\types;
  
  use prosys\core\common\Agents,
      prosys\core\common\Functions;
  
  /**
   * Trida reprezentujici pole entit, ktere je pouzite pro uchovavani entit vztahu M:N.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class EntityArray extends \ArrayObject {
    private $_itemType;

    /**
     * Pretizi konstruktor tak, aby stacilo zadat typ entit v poli.
     * 
     * @param string $itemType
     * @param array $array
     * @param int $flags
     */
    public function __construct($itemType = '', $array = [], $flags = 0) {
      parent::__construct($array, $flags, Agents::getNamespace(Agents::TYPE_TYPES) . 'EntityLazyLoadIterator');

      // nastavi datovy typ polozek pole
      $this->_itemType = $itemType;
    }
    
    /**
     * Z parametru nacte entitu. Metoda je implementaci pro EntityArray a EntityLazyLoadIterator.
     * 
     * @param \ArrayAccess $access
     * @param mixed $index
     * @param string $itemType
     * 
     * @return mixed
     */
    public static function loadEntity(\ArrayAccess $access, $index, $itemType) {
      $array = $access->getArrayCopy();
      $value = $array[$index];
      
      return Functions::retypeToEntity($value, $itemType);
    }
    
    /**
     * Provede "pretypovani" entity - je-li zadana hodnota primarniho klice entity, nacte entitu, atd. - vola funkci retypeToEntity ze tridy Functions.
     * 
     * @param mixed $index
     */
    private function lazyLoad($index) {
      $this[$index] = self::loadEntity($this, $index, $this->_itemType);
    }

    public function offsetGet($index) {
      $this->lazyLoad($index);
      return parent::offsetGet($index);
    }

    public function offsetExists($index) {
      $this->lazyLoad($index);
      return parent::offsetExists($index);
    }

    /**
     * Pri kazdem zavolani teto metody se do aktualniho iteratoru vlozi typ ulozenych entit.
     * 
     * @return EntityLazyLoadIterator
     */
    public function getIterator() {
      return parent::getIterator()->setItemType($this->_itemType);
    }
    
    /**
     * Nacte vsechny entity a vrati je v "obycejnem" poli.
     * 
     * @return EntityArray
     */
    public function getLoadedArrayCopy() {
      $copy = array();
      foreach ($this as $index => $entity) {
        $copy[$index] = $entity;
      }
      
      return $copy;
    }


    /**
     * Vrati klice pole entit -> tedy seznam jejich id.
     * 
     * @return array
     */
    public function keys() {
      return array_keys((array)$this);
    }
    
    /**
     * Vrati prvni prvek pole.
     * 
     * @return Entity
     */
    public function first() {
      $keys = $this->keys();
      
      if ($keys) {
        return $this[reset($keys)];
      } else {
        return NULL;
      }
    }
  }
