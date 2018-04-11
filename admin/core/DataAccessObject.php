<?php
  namespace prosys\model;
  
  use prosys\core\common\Agents,
      prosys\core\common\AppException,
      prosys\core\interfaces\IFilterable,
      prosys\core\common\Settings,
      prosys\core\mapper\SqlFilter,
      prosys\core\common\Functions;

  /**
   * Abstract class which should be the the object to access entity data.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class DataAccessObject
  {
    /** @var \prosys\core\interfaces\IMapper */
    protected $_mapper;
    protected $_entityClass;

    /**
     * Gets the name of the storage collection.
     * @return string
     */
    public function getCollectionName() {
      return $this->_mapper->getTable();
    }
    
    /**
     * Returns element name of entity identifier.
     * 
     * @param bool $element
     * @return string
     */
    public function getPrimaryKey($element = FALSE) {
      $pkMethod = new \ReflectionMethod($this->_entityClass, 'PRIMARY_KEY');
      return $pkMethod->invoke(NULL, $element);
    }
    
    /**
     * Return property (element) name of entity delete flag. If entity is deletable directly (without flag), return false.
     * 
     * @param type $element
     */
    public function getDeleteFlag($element = FALSE) {
      $pkMethod = new \ReflectionMethod($this->_entityClass, 'DELETE_FLAG');
      return $pkMethod->invoke(NULL, $element);
    }
    
    /**
     * Initializes DAO.
     * 
     * @param string $entityClass
     * @param string $mapperType
     * @param \prosys\core\interfaces\IDataHandler $dataHandler
     * @param string $storageName
     */
    public function __construct($entityClass, $mapperType, \prosys\core\interfaces\IDataHandler $dataHandler, $storageName) {
      $this->_entityClass = $entityClass;
      $this->_mapper = new $mapperType($dataHandler, $storageName, $this->getPrimaryKey(TRUE));
    }
    
    /**
     * Set all given properties of entity.
     * 
     * @param \prosys\model\Entity $entity
     * @param array $data
     */
    private function setProperties(Entity $entity, array $data) {
      $props = $entity->getProperties();
      foreach ($data as $prop => $value) {
        if (!in_array($prop, $props)) {
          try {
            $prop = $entity->getProperty($prop);
          } catch (AppException $ex) { $prop = ''; }
        }

        try {
          if ($prop && !$entity->isPropertyBindingType($prop)) {
            $entity->$prop = $value;
          }
        } catch (AppException $ex) { echo $ex; }
      }
    }

    /**
     * Loads entity - means download from the storage, create new, or create new and set given data.
     * 
     * @param mixed|array|NULL $arg mixed is primary key type (typically string, int)
     * @param bool $verifyData
     * 
     * @return Entity
     */
    public function load($arg = NULL, $verifyData = TRUE) {
      $entity = new $this->_entityClass();
      
      if ($verifyData) {
        $primaryKey = $this->getPrimaryKey();
        $primaryKeyElement = $this->getPrimaryKey(TRUE);

        $deleteFlag = $this->getDeleteFlag();
        $deleteFlagElement = $this->getDeleteFlag(TRUE);

        // load data
        $loadedData = array();
        if (is_array($arg)) {
          $primaryKeyInArray = ((array_key_exists($primaryKeyElement, $arg)) ? 
                                  $primaryKeyElement : 
                                  ((array_key_exists($primaryKey, $arg)) ? $primaryKey : NULL));

          if ($primaryKeyInArray) {
            $result = $this->_mapper->find($arg[$primaryKeyInArray]);
            $loadedData = (($result) ? (array)$result : array());

            // union with $arg priority
            $arg = array_merge($loadedData, $arg);
          }
        } else if ($arg) {
          $record = $this->_mapper->find($arg);
          $loadedData = $arg = (($record) ? (array)$record : array());
        }

        // set default delete flag - the default delete state of entity is FALSE (entity exists)
        if ($arg && $deleteFlag) {
          if (!array_key_exists($deleteFlagElement, $arg)) {
            $arg[$deleteFlag] = 0;
          }

          // if entity is deleted (by flag) and there isn't Settings::SHOW_DELETED_PREDICATE = TRUE inside of param, it shouldn't be loaded
          if ((int)Functions::item($arg, $deleteFlag) && !(bool)Functions::item($arg, Settings::SHOW_DELETED_PREDICATE)) {
            $loadedData = $arg = array();
          }
        }

        // create entity and set loaded data
        $entity->setIsNew((($loadedData) ? FALSE : TRUE));

        $this->setProperties($entity, $loadedData);
        $entity->setWasChanged(FALSE);

        // set given data
        if ($arg) {
          $this->setProperties($entity, $arg);
        }
      } else {
        $entity->setIsNew(FALSE);
        $this->setProperties($entity, $arg);
        $entity->setWasChanged(FALSE);
      }

      return $entity;
    }
    
    /**
     * Gets data of entity used by data handler.
     * 
     * @param Entity $entity
     * @return array
     */
    protected function getData(Entity $entity) {
      $data = array();
      foreach ($entity->getProperties() as $prop) {
        if (!$entity->isPropertyBindingType($prop)) {
          $data[$entity->getElement($prop)] = $entity->$prop;
        }
      }
      
      return $data;
    }
    
    /**
     * Converts object to the string.
     * 
     * @param mixed $object
     * 
     * @return string
     * @throws AppException if the object is not possible to be converted
     */
    protected function prepareToStore($object) {
      if (is_a($object, Agents::getNamespace(Agents::TYPE_MODEL) . 'Entity')) {
        // in the ProSYS Entity returns whatever is inside the primary key, whether it's some string or not
        $objectClass = get_class($object);
        return $object->{$objectClass::PRIMARY_KEY()};
      } else {
        return Functions::toString($object, $this);
      }
    }
    
    /**
     * Converts data to raw data.
     * 
     * @param \prosys\model\Entity $entity
     * @return array
     */
    protected function rawData(Entity $entity) {
      $data = $this->getData($entity);

      array_walk($data, function(&$item, $element) use ($entity) {
        if ($entity->isPropertyBindingType($entity->getProperty($element))) {
          $item = Settings::BINDING_TYPE_PROPERTY_LABEL;
        } else {
          $item = $this->prepareToStore($item);
        }
      });

      return array_filter($data, function($item) {
        return $item !== Settings::BINDING_TYPE_PROPERTY_LABEL;
      });
    }
    
    /**
     * Stores entity - means insert or update.
     * 
     * @param Entity $entity
     * @return bool|NULL
     */
    public function store(Entity $entity) {
      if ($entity->wasChanged()) {
        if ($entity->isNew()) {
          $inserted = $this->_mapper->insert($this->rawData($entity));

          if (!is_bool($inserted)) {
            $entity->{$this->getPrimaryKey()} = $inserted;
            $inserted = TRUE;
          }
          
          return $inserted;
        } else {
          return $this->_mapper->update($this->rawData($entity));
        }
      } else {
        return TRUE;
      }
    }

    /**
     * Deletes the Entity according to given primary key value or Entity itself.
     * 
     * @param mixed|Entity $arg
     * @return bool
     */
    public function delete($arg) {
      $deleteFlag = $this->getDeleteFlag();
      if ($deleteFlag) {
        $arg = ((is_object($arg)) ? $arg : $this->load($arg));

        if (!$arg->isNew()) {
          $arg->$deleteFlag = TRUE;
          return $this->store($arg);
        } else {
          throw new AppException('Entity, which should be deleted, is not exists in the collection.');
        }
      } else {
        $arg = ((is_object($arg)) ? $arg->{$this->getPrimaryKey()} : $arg);
        return $this->_mapper->delete($arg);
      }
    }
    
    /**
     * Deletes records according to given filter.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     * @return bool
     */
    public function deleteRecords(IFilterable $filter) {
      return $this->_mapper->deleteRecords($filter);
    }
    
    /**
     * If the entity is "delete by flag" type, load only records with delete flag set to FALSE.<br />
     * → should be default behaviour
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     */
    private function deletedByFlagFilterCorrection(IFilterable &$filter = NULL) {
      if (($deleteFlag = $this->getDeleteFlag(TRUE))) {
        $filterNotDeleted = SqlFilter::create()->comparise($deleteFlag, '=', '0');
        if (is_null($filter)) {
          $filter = $filterNotDeleted;
        } else {
          $filter->andL($filterNotDeleted);
        }
      }
    }
    
    /**
     * Loads records from the storage.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter the same rules as MySqlMapper::findRecords($filter, $order, $limit)
     * @param array $order the same rules as MySqlMapper::findRecords($filter, $order, $limit)
     * @param array $limit
     * @param bool $showDeleted
     * @param bool $identifiersOnly
     * @param bool $dataOnly
     * 
     * @return Entity[]
     */
    public function loadRecords(IFilterable $filter = NULL, array $order = array(), array $limit = array(), $showDeleted = FALSE, $identifiersOnly = FALSE, $dataOnly = FALSE, $identifiersAsKey = FALSE) {
      // correction of deleted entities (if the entity is "delete by flag" type)
      if (!$showDeleted) {
        $this->deletedByFlagFilterCorrection($filter);
      }

      // get records
      $records = array();
      foreach ($this->_mapper->findRecords($filter, $order, $limit) as $record) {
        $record = (array)$record;
        $record[Settings::SHOW_DELETED_PREDICATE] = $showDeleted;
        
        if ($identifiersAsKey) {
          $records[$record[$this->getPrimaryKey(TRUE)]] = (($identifiersOnly) ? $record[$this->getPrimaryKey(TRUE)] : (($dataOnly) ? $record : $this->load($record, FALSE)));
        } else {
          $records[] = (($identifiersOnly) ? $record[$this->getPrimaryKey(TRUE)] : (($dataOnly) ? $record : $this->load($record, FALSE)));
        }
      }
      
      // clear memory
      $this->_mapper->clearResult();
      
      return $records;
    }
    
    /**
     * Vrati pozadovane sloupce vyfiltrovanych radku.
     * 
     * @param array $elements
     * @param prosys\core\interfaces\IFilterable $filter
     * @param array $groupBy                @TODO: neni zapracovane
     * @param array $order order by
     * @param array $limit limit
     * 
     * @return array
     */
    public function findRecordsProjection(array $elements = array(), SqlFilter $filter = NULL, array $groupBy = [], array $order = [], array $limit = [], $showDeleted = FALSE) {
      // korekce pro zobrazeni smazanych entit priznakem
      if (!$showDeleted) {
        $this->deletedByFlagFilterCorrection($filter);
      }
      
      return $this->_mapper->findRecordsProjection($elements, $filter, $order, $limit);
    }
    
    /**
     * Function for count records by filter.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     * @return int
     */
    public function count(IFilterable $filter = NULL) {
      // correction of deleted entities (if the entity is "delete by flag" type)
      $this->deletedByFlagFilterCorrection($filter);

      return $this->_mapper->count('*', $filter);
    }
    
    /**
     * Function for sum column by filter.
     * 
     * @param $what condition (`price` * `quantity`)
     * @param \prosys\core\interfaces\IFilterable $filter
     * 
     * @return float
     */
    public function sum($what, IFilterable $filter = NULL) {
      // correction of deleted entities (if the entity is "delete by flag" type)
      $this->deletedByFlagFilterCorrection($filter);

      return $this->_mapper->sum('float', $what, $filter);
    }
    
    /*
     * Vrati jedinečné hodnoty pro sloupec a podle filtru.
     * 
     * @param string $what
     * @param \prosys\core\interfaces\IFilterable $filter
     * @param string $direction optional: ASC (default) | DESC
     * 
     * @return array
     */
    public function distinct($what, IFilterable $filter = NULL, $direction = 'ASC') {
      return $this->_mapper->distinct($what, $filter, $direction);
    }
    
    /**
     * Getter.
     * 
     * @return \prosys\core\interfaces\IMapper
     */
    public function getMapper() {
      return $this->_mapper;
    }
  }
