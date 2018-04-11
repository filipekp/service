<?php
  namespace prosys\core\common\types;
  
  /**
   * Trida reprezentujici iterator objektu ArrayObject, ktery je pouzit pro uchovavani entit vztahu M:N.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class EntityLazyLoadIterator extends \ArrayIterator {
    private $_itemType;
    
    /**
     * Provede "pretypovani" entity - je-li zadana hodnota primarniho klice entity, nacte entitu, atd. - vola funkci retypeToEntity ze tridy Functions.
     * 
     * @param mixed $index
     */
    private function lazyLoad($index) {
      $this[$index] = EntityArray::loadEntity($this, $index, $this->_itemType);
    }

    public function current() {
      $this->lazyLoad($this->key());
      return parent::current();
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
     * Nastavi datovy typ entit ulozenych v poli.
     * 
     * @param string $itemType
     * @return EntityLazyLoadIterator
     */
    public function setItemType($itemType) {
      $this->_itemType = $itemType;
      return $this;
    }
  }
