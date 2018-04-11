<?php
  namespace prosys\model;
  
  use prosys\core\common\AppException,
      prosys\core\common\Functions,
      prosys\core\common\Agents,
      prosys\core\mapper\SqlFilter,
      prosys\core\common\types\EntityArray;

  /**
   * Abstract class which should be the parent of every "database row" type class.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class Entity implements \prosys\core\interfaces\IEntity
  {
    const BINDING_MN = 'mn';
    const BINDING_1N = '1n';
    
    private static $PRIMARY_KEY = array();
    private static $DELETE_FLAG = array();

    /**
     * Get entity class name including the namespace.
     * 
     * @param bool $namespace
     * @return string
     */
    public static function classname($namespace = TRUE) {
      $class = get_called_class();
      if ($namespace) {
        return '\\' . get_called_class();
      } else {
        $classpath = explode('\\', $class);
        return array_pop($classpath);
      }
    }

    /* properties */
    protected $_properties;

    private $wasChanged;
    private $isNew;
    private $inVerification;

    /**
     * Get the name of property (element) storing primary key.
     * 
     * @param boolean $element if TRUE, than the element is returned
     * @return string
     */
    public static function PRIMARY_KEY($element = FALSE) {
      if (!array_key_exists(self::classname(), self::$PRIMARY_KEY)) {
        self::loadMarkedProperty(
          array(
            'regexes' => array(
              '/@property +([^ ]+) +\$(id)(.*)(.*)(.*)/',
              '/@property +([^ ]+) +\$([^ ]+) +([^ ]*)(primary)(.*)/'
            )
          ),
          'PRIMARY_KEY',
          self::classname() . ': The entity has to have defined primary key property. It should be done through the annotation or there should be defined property "id".'
        );
      }

      return self::$PRIMARY_KEY[self::classname()][(($element) ? 'element' : 'property')];
    }
    
    /**
     * Get the name of property (element) storing the predicate if entity is deletable by flag.
     * 
     * @param boolean $element if TRUE, than the element is returned
     * @return string
     */
    public static function DELETE_FLAG($element = FALSE) {
      if (!array_key_exists(self::classname(), self::$DELETE_FLAG)) {
        self::loadMarkedProperty(
          array(
            'regexes' => array('/@property +([^ ]+) +\$([^ ]+) +([^ ]*)(delete_flag)(.*)/'),
            'types'   => array('bool', 'boolean')
          ),
          'DELETE_FLAG'
        );
      }

      return self::$DELETE_FLAG[self::classname()][(($element) ? 'element' : 'property')];
    }
    
    /**
     * Load property which should be saved in some public static array as predicate.
     * 
     * @return bool
     * @throws AppException if there is no predicate or something
     */
    private static function loadMarkedProperty(array $specification, $store, $notFoundException = FALSE) {
      // try to load key
      try {
        $reflection = new \ReflectionClass(static::classname());
        $updated = self::$$store;

        // @property datatype $propertyName query
        // e.g. query = element=birthday_number&primary
        $matches = array(NULL, NULL, NULL, NULL, NULL);

        $current = $reflection;
        $found = FALSE;

        do {
          foreach ($specification['regexes'] as $regex) {
            if (preg_match($regex, $current->getDocComment(), $matches)) {
              $found = TRUE;

              $property = trim($matches[2]);
              $propertyElementParseStr = trim($matches[3] . $matches[4] . $matches[5]);
              
              // check allowed types
              if (array_key_exists('types', $specification) && !in_array($matches[1], $specification['types'])) {
                throw new AppException(self::classname() . ': Data type of property ' . $property . ' (' . $matches[4] . ') is not allowed.');
              }
              
              break;
            }
          }

          if ($found) {
            // property
            $updated[self::classname()]['property'] = $property;

            // element
            $params = array();
            parse_str($propertyElementParseStr, $params);

            $element = ((array_key_exists('element', $params)) ? $params['element'] : $updated[self::classname()]['property']);
            $updated[self::classname()]['element'] = $element;
            
            // store
            self::$$store = $updated;

            return TRUE;
          }
        } while ($current = $current->getParentClass());
      } catch (\ReflectionException $e) { printf('%s', $e->getMessage()); }

      if ($notFoundException) {
        throw new AppException($notFoundException);
      } else {
        $updated[self::classname()] = array('property' => FALSE, 'element' => FALSE);

        // store
        self::$$store = $updated;
        
        return FALSE;
      }
    }
    
    /**
     * Vrati jmeno vazebni kolekce (napr. tabulky) dvou entit -> mne (tedy "self") a pozadovaneho typu.
     * 
     * @param string $bindedClass
     * @return string
     */
    private function parseBindingCollectionName($bindedClass) {
      return strtolower(str_replace('Entity', '', self::classname(FALSE)) . '_' . str_replace('Entity', '', $bindedClass));
    }
    
    /**
     * Vrati nazev elementu vazebni tabulky odkazujici se na entitu, ktera tvori vazbu -> na "self".
     * 
     * @return string
     */
    private function parseBinderElement() {
      return strtolower(str_replace('Entity', '', self::classname(FALSE))) . '_id';
    }
    
    /**
     * Vrati nazev elementu vazebni tabulky odkazujici se na entitu, ktera je vazbou spojovana.
     * 
     * @param string $bindedClass
     * @return string
     */
    private function parseBindedElement($bindedClass) {
      return strtolower(str_replace('Entity', '', $bindedClass)) . '_id';
    }
    
    /**
     * Rozparsuje vazbu na jinou tabulku vc. parametru.<br /><br />
     *  Vsechny dvojtecky a ostatni oddelovace jsou vzdy povinne, ale parametry nejsou nutne, je-li dodrzovana konvence.<br />
     *  Razeni je ve vychozim stavu vzestupne podle primarniho klice<br />
     *  <b>Vazba M:N</b><br />
     *    <i>tvar</i>
     *      <pre>  binding=mn:vazebni_tabulka:muj_sloupec>muj_sloupec_ve_VT:sloupec_vazby_v_odkazovane_tabulce>pozadovany_sloupec_ve_VT:element_vychoziho_razeni_v_odkazovane_tabulce>smer</pre>
     *    <i>priklad</i>
     *      <pre>  binding=mn:test_topics:id>test_id:id>topic_id:sort_order>desc</pre><br />
     *  <b>Vazba 1:N</b><br />
     *    <i>tvar</i>
     *      <pre>  binding=1n:muj_sloupec>muj_sloupec_v_cizi_tabulce:element_vychoziho_razeni_v_odkazovane_tabulce>smer</pre>
     *    <i>priklad</i>
     *      <pre>  binding=1n:id>student:sort_order>asc</pre><br />
     * 
     * @param string $binding
     * @param string $propertyType
     * 
     * @return type
     */
    private function parseBinding($binding, $propertyType) {
      $binding = explode(':', $binding);
      $type = array_shift($binding);

      $propertyClass = Agents::getNamespace(Agents::TYPE_MODEL) . $propertyType;
      $primaryKeyElement = $propertyClass::PRIMARY_KEY(TRUE);

      $binderElement = $this->parseBinderElement();
      $bindedElement = $this->parseBindedElement($propertyType);

      $parsed = array();
      switch ($type) {
        case self::BINDING_MN:        // Vazba M:N
          $parsed += array_combine(
            array('bindings', 'binder', 'binded', 'order'),
            array_map(function($item) {
              return ((strpos($item, '>') === FALSE) ? $item : explode('>', $item));
            }, $binding)
          );
            
          if (!$parsed['bindings'])  { $parsed['bindings'] = $this->parseBindingCollectionName($propertyType); }
          if (!$parsed['binded'][0]) { $parsed['binded'][0] = $primaryKeyElement; }
          if (!$parsed['binded'][1]) { $parsed['binded'][1] = $bindedElement; }
        break;
          
        case self::BINDING_1N:
          $parsed += array_combine(
            array('binder', 'order'),
            array_map(function($item) {
              return ((strpos($item, '>') === FALSE) ? $item : explode('>', $item));
            }, $binding)
          );
        break;
      }
      
      if ($parsed) {
        $parsed['type'] = $type;

        // set common default values
        if (!$parsed['binder'][0]) { $parsed['binder'][0] = self::PRIMARY_KEY(TRUE); }
        if (!$parsed['binder'][1]) { $parsed['binder'][1] = $binderElement; }
        if (!$parsed['order'][0]) { $parsed['order'][0] = $primaryKeyElement; }
        if (!$parsed['order'][1]) { $parsed['order'][1] = 'ASC'; }
      }
      
      return $parsed;
    }
    
    /**
     * Initializes annotation properties.
     */
    public function __construct() {
      // initializes the properties
      $this->_properties = new \ArrayObject(array());
      
      $this->wasChanged = FALSE;
      $this->inVerification = FALSE;

      try {
        $reflection = new \ReflectionClass($this);
        
        // @property datatype $propertyName query
        // e.g. query = element=birthday_number&primary
        $propsRegex = '/@property +(.+?) +.(.+)/';
        
        $current = $reflection;
        $props = array(NULL, NULL, NULL);
        do {
          preg_match_all($propsRegex, $current->getDocComment(), $props);

          foreach ($props[2] as $key => $row) {
            if (strpos($row, 'noelement') === FALSE) {
              $rowAnnotation = explode(' ', preg_replace('/ +/', ' ', $row));
              if (count($rowAnnotation) == 1) {
                $prop = trim($rowAnnotation[0]);
                $query = '';
              } else {
                $prop = trim($rowAnnotation[0]);
                $query = trim($rowAnnotation[1]);
              }

              $params = array('element' => $prop);
              if (!is_null($query)) {
                parse_str($query, $params);
              }

              $this->_properties[$prop] = array(
                'type' => $props[1][$key],
                'element' => ((array_key_exists('element', $params) && $params['element']) ? $params['element'] : $prop),
                'value' => NULL,
                'data' => array()
              );
              
              if (array_key_exists('binding', $params)) {
                $this->_properties[$prop]['data']['binding'] = $this->parseBinding($params['binding'], str_replace('[]', '', $this->_properties[$prop]['type']));
              }
            }
          }
        } while ($current = $current->getParentClass());
      } catch (\ReflectionException $e) { printf('%s', $e->getMessage()); }
    }
    
    /**
     * Vsechny property nastavi na NULL.
     */
    public function __destruct() {
      foreach ($this->_properties as $property => $details) {
        if ($this->isEntityArray($this->_properties[$property]['type']) && $this->_properties[$property]['value']) {
          foreach ($this->_properties[$property]['value'] as $entity) {
            $entity = NULL;
          }
        }
        
        $this->_properties[$property] = NULL;
      }
    }
    
    /**
     * Gets all of the entity properties.
     * 
     * @return array
     */
    public function getProperties() {
      return array_keys($this->_properties->getArrayCopy());
    }
    
    /**
     * Gets all of the property elements of the entity.<br />
     * e.g.: database column names, xml node names, and so on
     * 
     * @return array
     */
    public function getElements() {
      $elements = $this->_properties->getArrayCopy();
      array_walk($elements, function(&$item) {
        $item = $item['element'];
      });
      
      return array_values($elements);
    }
    
    /**
     * Gets element name of wanted property.
     * 
     * @param string $prop
     * @return string
     * @throws AppException if given property was not found
     */
    public function getElement($prop) {
      if (array_key_exists($prop, $this->_properties)) {
        return $this->_properties[$prop]['element'];
      }
      
      throw new AppException(get_class($this) . ": Property '{$prop}' was not found. Element couldn't be returned.");
    }
    
    /**
     * Check if property exists.
     * 
     * @param string $property
     * @return boolean
     */
    public function propertyExists($property) {
      return property_exists($this, $property) || array_key_exists($property, $this->_properties);
    }
    
    /**
     * Gets the name of property given by its element name.
     * 
     * @param string $element
     * @return string
     * @throws AppException if the property was not found
     */
    public function getProperty($element) {
      foreach ($this->_properties as $property => $details) {
        if ($details['element'] == $element) {
          return $property;
        }
      }
      
      throw new AppException(get_class($this) . ": Property of element '{$element}' was not found.");
    }
    
    /**
     * Zjisti, zda je property vazbou na jinou tabulku.
     * 
     * @param string $prop
     * @return bool
     */
    public function isPropertyBindingType($prop) {
      return array_key_exists('binding', $this->_properties[$prop]['data']);
    }
    
    /**
     * Checks if given type is type of Entity.
     * 
     * @param string $type
     * @return bool
     */
    protected function isEntityType($type) {
      return strpos($type, 'Entity') !== FALSE;
    }
    
    /**
     * Checks if given type is type of array of Entities.
     * 
     * @param string $type
     * @return bool
     */
    protected function isEntityArray($type) {
      return $this->isEntityType($type) && strpos($type, '[]') !== FALSE;
    }
    
    /**
     * Checks if given type is any type of primitive data type.
     * 
     * @param string $type
     * @return bool
     */
    protected function isPrimitiveType($type) {
      return in_array($type, array('string', 'int', 'integer', 'float', 'double', 'bool', 'boolean'));
    }
    
    /**
     * Vrati hodnotu primarniho klice, jedna-li se o Entitu a primo predany objekt v opacnem pripade.
     * 
     * @param mixed $object
     * @return mixed
     */
    private function primaryValue($object) {
      if (is_object($object)) {
        $class = get_class($object);
        if ($this->isEntityType($class)) {
          $object = $object->{$class::PRIMARY_KEY()};
        }
      }
      
      return $object;
    }
    
    /**
     * Retypes object to the requested type.
     * 
     * @param object $object
     * @param string $type
     * 
     * @return mixed
     * @throws AppException if the type is not supported
     */
    protected function retype($object, $type, $prop) {
      // create the array of entities -> array of ids respectively
      if ($this->isEntityArray($type)) {
        // if there is no need to retype
        if (Functions::isType($object, 'EntityArray')) {
          return $object;
        }
        
        $type = str_replace('[]', '', $type);
        
        if (is_null($this->_properties[$prop]['value'])) {
          $data = $this->_properties[$prop]['data']['binding'];

          /* @var $dao \prosys\model\DataAccessObject */
          $dao = Agents::getAgent(str_replace('Entity', 'Dao', $type), Agents::TYPE_MODEL);

          switch ($data['type']) {
            case self::BINDING_MN:
              $filter = SqlFilter::create()->inFilter($data['binded'][0],
                SqlFilter::create()->filter($data['binded'][1], $data['bindings'],
                  SqlFilter::create()->comparise($data['binder'][1], '=', $this->primaryValue($this->{$this->getProperty($data['binder'][0])}))
                )
              );
            break;

            case self::BINDING_1N:
              $filter = SqlFilter::create()->comparise($data['binder'][1], '=', $this->primaryValue($this->{$this->getProperty($data['binder'][0])}));
            break;
          }

          // z databaze musi byt vzdy stazen primarni klic, protoze ten je uzivan pro nacitani entity
          $propertyClass = Agents::getNamespace(Agents::TYPE_MODEL) . $type;
          $primaryKeyElement = $propertyClass::PRIMARY_KEY(TRUE);

          $bindedEntities = array_map(function($item) use ($primaryKeyElement) {
            return $item->$primaryKeyElement;
          }, $dao->getMapper()->findRecordsProjection(
            array($primaryKeyElement),
            $filter,
            array(
              array('column' => $data['order'][0], 'direction' => $data['order'][1])
            )
          ));
        } else {
          $bindedEntities = $this->_properties[$prop]['value'];
        }

        return new EntityArray(
          $type,
          array_combine(
            array_map(function($item) {
              return ((is_a($item, Agents::getNamespace(Agents::TYPE_MODEL) . 'Entity') && ($class = get_class($item))) ? $item->{$class::PRIMARY_KEY()} : $item);
            }, $bindedEntities),
            $bindedEntities
          )
        );
      
      // try to create entity from the primary key value
      } elseif ($this->isEntityType($type)) {
        try {
          return Functions::retypeToEntity($object, $type);
        } catch (\Exception $e) {
          throw new AppException(get_class($this) . ": {$e->getMessage()}");
        }
        
      // retype by recasting
      } else {
        // if there is no need to retype
        if (is_null($object) || Functions::isType($object, $type)) {
          return $object;
        }
        
        switch (strtolower($type)) {
          case 'string':  return Functions::toString($object, $this);
          case 'int':
          case 'integer': return ((is_numeric($object) || is_bool($object)) ? (int)$object : 0);
          case 'float':
          case 'double':
          case 'real':    return ((is_numeric($object) || is_bool($object)) ? (float)$object : 0);
          case 'bool':
          case 'boolean': return (bool)((int)$object);

          case 'date':
          case 'datetime':
          case '\datetime':
            if (is_string($object)) {
              return new \DateTime(date('Y-m-d H:i:s', strtotime($object)));
            } else {
              $type = 'DateTime';
            }

          default:
            if (class_exists($type)) {
              return new $type($object);
            } else if (class_exists(Agents::getNamespace(Agents::TYPE_TYPES) . $type)) {
              $type = Agents::getNamespace(Agents::TYPE_TYPES) . $type;
              return new $type($object);
            } else {
              throw new AppException(get_class($this) . ": The type '{$type}' is not supported and cannot be retyped.");
            }
        }
      }
    }
    
    /**
     * Tries to retype into the requested data type.
     * 
     * @param string $prop
     * @param mixed $value
     * @param string $type
     * 
     * @throws AppException if the value is wrong type
     */
    private function tryToRetype($prop, $value, $type) {
      try {
        return $this->retype($value, $type, $prop);
      } catch (AppException $exception) {
        throw new AppException(
          array(
            get_class($this) . ": Wrong type: Requested property '{$prop}' should be type of {$type}, given " . gettype($value) . " (" . var_export($value, TRUE) . ").",
            $exception->getMessage()
          )
        );
      }
    }

    /**
     * "Magic" getter.
     * 
     * @param string $prop
     * 
     * @return mixed
     * @throws AppException if the property does not exists or it's not documented in the annotation
     */
    public function __get($prop) {
      $method = 'get' . ucfirst($prop);
      if (method_exists($this, $method)) {    // if there is correspond method, call it
        return $this->$method();
      } else {
        if (array_key_exists($prop, $this->_properties)) {   // if the property is storage (e.g. MySQL column) element, it was loaded in the constructor
          if (!$this->inVerification && !$this->isPrimitiveType($this->_properties[$prop]['type']) && !is_object($this->_properties[$prop]['value'])) {
            $this->_properties[$prop]['value'] = $this->tryToRetype($prop, $this->_properties[$prop]['value'], $this->_properties[$prop]['type']);
          }

          return $this->_properties[$prop]['value'];
        } else if (property_exists($this, $prop)) {      // else check if the property exists and it's documented in the annotation
          try {
            $reflection = new \ReflectionClass($this);
            if (preg_match("/@property +.+? +.{$prop} +noelement/", $reflection->getDocComment()) ||
                preg_match("/@property-read +.+? +.{$prop} +noelement/", $reflection->getDocComment())) {
              return $this->$prop;
            }
          } catch (\ReflectionException $e) { printf('%s', $e->getMessage()); }
        }
      }

      throw new AppException(get_class($this) . ': Requested property "' . $prop . '" does not exist.');
    }
    
    /**
     * "Magic" setter.
     * 
     * @param string $prop
     * @param mixed $value
     * 
     * @throws AppException if the value is wrong type of or wanted property does not exist
     */
    public function __set($prop, $value) {
      $this->inVerification = TRUE;
        $original = ((is_object($this->$prop)) ? clone $this->$prop : $this->$prop);
      $this->inVerification = FALSE;

      $method = 'set' . ucfirst($prop);
      if (method_exists($this, $method)) {    // if there is correspond method, call it
        $this->$method($value);
      } else {
        if (array_key_exists($prop, $this->_properties)) {   // if the property is storage element (e.g. MySQL column), try to retype and set
          $this->_properties[$prop]['value'] = (($this->isPrimitiveType($this->_properties[$prop]['type'])) ?
            $this->tryToRetype($prop, $value, $this->_properties[$prop]['type']) :
            $value);
        } else if (property_exists($this, $prop)) {      // else check if the property exists and it's documented in the annotation
          try {
            $reflection = new \ReflectionClass($this);
            $match1 = array(NULL, NULL);
            $match2 = array(NULL, NULL);

            if (preg_match("/@property +(.+?) +.{$prop} +noelement/", $reflection->getDocComment(), $match1) ||
                preg_match("/@property-write +(.+?) +.{$prop} +noelement/", $reflection->getDocComment(), $match2)) {
              if ($match1[1] || $match2[1]) {
                $this->$prop = $this->tryToRetype($prop, $value, (($match1[1]) ? $match1[1] : $match2[1]));
              } else {
                throw new AppException(get_class($this) . ": Requested property '{$prop}' has not defined data type.");
              }
            } else {
              throw new AppException(get_class($this) . ": Requested property '{$prop}' does not exist.");
            }
          } catch (\ReflectionException $e) { printf('%s', $e->getMessage()); }
        } else {
          throw new AppException(get_class($this) . ": Requested property '{$prop}' does not exist.");
        }
        
      }
      
      // if the property was changed change state of the object
      $this->inVerification = TRUE;
        if ($this->$prop != $original) {
          $this->wasChanged = TRUE;
        }
      $this->inVerification = FALSE;
    }
    
    /**
     * Alias on getter of wasChanged slot.
     * @return bool
     */
    public function wasChanged() {
      return $this->getWasChanged();
    }
    
    /**
     * Getter.
     * @return bool
     */
    public function getWasChanged() {
      return $this->wasChanged;
    }
    
    /**
     * Setter.
     * @return Entity
     */
    public function setWasChanged($wasChanged) {
      $this->wasChanged = $wasChanged;
      return $this;
    }
    
    /**
     * Getter.
     * @return bool
     */
    public function isNew() {
      return $this->isNew;
    }
    
    /**
     * Setter.
     * @param bool $isNew
     * @return Entity
     */
    public function setIsNew($isNew) {
      $this->isNew = $isNew;
      return $this;
    }
    
    /**
     * Prints entity out.
     */
    public function __toString() {
      $toPrint = array();
      foreach ($this->getProperties() as $property) {
        $toPrint[] = "{$property}: " . (($this->isEntityArray($this->_properties[$property]['type'])) ?
                                          implode(', ', $this->$property->getArrayCopy()) :
                                          Functions::toString($this->$property, $this));
      }
      
      return implode('<br />', $toPrint);
    }
    
    /**
     * Function generate path for this module
     * 
     * @return string
     */
    public function getPath() {
      throw new AppException('Function getPath() of ' . static::classname() . ' not implemented yet.');
    }
  }
