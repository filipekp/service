<?php
  namespace prosys\core\mapper;
  
  use prosys\core\interfaces\IFilterable;

  /**
   * Represents SQL filter - means WHERE condition.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SqlFilter implements IFilterable
  {
    private $_filter = array('where' => '', 'bindings' => array());

    /**
     * Creates new filter.
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public static function create() {
      return new SqlFilter();
    }
    
    /**
     * Quotes the database element with `
     * 
     * @param type $element
     * @return type
     */
    private function quote($element) {
      return ((strpos($element, '`') === FALSE) ? "{$element}" : $element);
    }
    
    /**
     * Creates tautology condition.
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function identity() {
      $this->_filter['where'] .= '1';

      return $this;
    }
    
    /**
     * Create contradiction condition.
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function contradiction() {
      $this->_filter['where'] .= '0 = 1';

      return $this;
    }

    /**
     * Creates comparison condition of WHERE.
     * 
     * @param string $element
     * @param string $cmp
     * @param string $value
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function comparise($element, $cmp, $value) {
      $this->_filter['where'] .= "{$this->quote($element)} {$cmp} ?";
      $this->_filter['bindings'][] = $value;
      
      return $this;
    }
    
    /**
     * Creates comparison columns condition of WHERE.
     * 
     * @param string $element
     * @param string $cmp
     * @param string $value
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function compariseColumns($element1, $cmp, $element2) {
      $this->_filter['where'] .= "{$this->quote($element1)} {$cmp} {$this->quote($element2)}";
      
      return $this;
    }

    /**
     * Checks if element IS NULL.
     * 
     * @param string $element
     * @return \prosys\core\mapper\SqlFilter
     */
    public function isEmpty($element) {
      $this->_filter['where'] .= "{$this->quote($element)} IS NULL";
      
      return $this;
    }

    /**
     * Checks if element IS NOT NULL.
     * 
     * @param string $element
     * @return \prosys\core\mapper\SqlFilter
     */
    public function isNotEmpty($element) {
      $this->_filter['where'] .= "{$this->quote($element)} IS NOT NULL";

      return $this;
    }

    /**
     * Checks if element value contains given string.
     * 
     * @param string $element
     * @param string $string
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function contains($element, $string) {
      $this->_filter['where'] .= "{$this->quote($element)} LIKE ?";
      $this->_filter['bindings'][] = "%{$string}%";
      
      return $this;
    }

    /**
     * Checks if element value starts with given string.
     * 
     * @param string $element
     * @param string $string
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function startWith($element, $string) {
      $this->_filter['where'] .= "{$this->quote($element)} LIKE ?";
      $this->_filter['bindings'][] = "{$string}%";

      return $this;
    }
    
    /**
     * Checks if element value starts with given string.
     * 
     * @param string $element
     * @param string $string
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function endWith($element, $string) {
      $this->_filter['where'] .= "{$this->quote($element)} LIKE ?";
      $this->_filter['bindings'][] = "%{$string}";

      return $this;
    }
    
    /**
     * Checks if element is between value range.
     * 
     * @param string $element
     * @param mixed $from
     * @param mixed $to
     */
    public function between($element, $from, $to) {
      $this->_filter['where'] .= "{$this->quote($element)} BETWEEN ? AND ?";
      $this->_filter['bindings'] = array_merge($this->_filter['bindings'], array($from, $to));
      
      return $this;
    }

    /**
     * Checks if element value is IN the array of values.
     * 
     * @param string $element
     * @param array $array
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function inArray($element, array $array) {
      if ($array) {
        $this->_filter['where'] .= "{$this->quote($element)} IN (" . implode(', ', array_fill(0, count($array), '?')) . ")";
        $this->_filter['bindings'] = array_merge($this->_filter['bindings'], array_values($array));
      } else {
        if (substr($this->_filter['where'], -3) === 'OR ') {
          $this->contradiction();
        } else {
          $this->identity();
        }
      }
      
      return $this;
    }

    /**
     * Checks if element value is NOT IN the array of values.
     * 
     * @param string $element
     * @param array $array
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function notInArray($element, array $array) {
      if ($array) {
        $this->_filter['where'] .= "{$this->quote($element)} NOT IN (" . implode(", ", array_fill(0, count($array), '?')) . ")";
        $this->_filter['bindings'] = array_merge($this->_filter['bindings'], array_values($array));
      } else {
        if (substr($this->_filter['where'], -3) === 'OR ') {
          $this->contradiction();
        } else {
          $this->identity();
        }
      }
      
      return $this;
    }

    /**
     * Checks if element value is IN filter subquery.
     * 
     * @param string $element
     * @param \prosys\core\interfaces\IFilterable $filter
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function inFilter($element, IFilterable $filter)
    {
      $filter = $filter->resultFilter();
      
      $this->_filter['where'] .= "{$this->quote($element)} IN ({$filter['where']})";
      $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $filter['bindings']);
      
      return $this;
    }

    /**
     * Checks if element value is NOT IN filter subquery.
     * 
     * @param string $element
     * @param \prosys\core\interfaces\IFilterable $filter
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function notInFilter($element, IFilterable $filter)
    {
      $filter = $filter->resultFilter();
      
      $this->_filter['where'] .= "{$this->quote($element)} NOT IN ({$filter['where']})";
      $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $filter['bindings']);
      
      return $this;
    }
    
    /**
     * Checks if filter returns any row.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     */
    public function exists(IFilterable $filter) {
      $filter = $filter->resultFilter();
      
      $this->_filter['where'] .= "EXISTS ({$filter['where']})";
      $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $filter['bindings']);
      
      return $this;
    }
    
    /**
     * Checks if filter returns no row.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     */
    public function notExists(IFilterable $filter) {
      $filter = $filter->resultFilter();
      
      $this->_filter['where'] .= "NOT EXISTS ({$filter['where']})";
      $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $filter['bindings']);
      
      return $this;
    }
    
    /**
     * Logical AND between subqueries.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     * @return \prosys\core\mapper\SqlFilter
     */
    public function andL(IFilterable $filter = NULL)
    {
      if (is_null($filter)) {
        $this->_filter['where'] .= ' AND ';
      } else {
        $filter = $filter->resultFilter();

        $this->_filter['where'] = "({$this->_filter['where']}) AND ({$filter['where']})";
        $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $filter['bindings']);
      }
      
      return $this;
    }

    /**
     * Logical OR between subqueries.
     * 
     * @param \prosys\core\interfaces\IFilterable $filter
     * @return \prosys\core\mapper\SqlFilter
     */
    public function orL(IFilterable $filter = NULL)
    {
      if (is_null($filter)) {
        $this->_filter['where'] .= ' OR ';
      } else {
        $filter = $filter->resultFilter();

        $this->_filter['where'] = "({$this->_filter['where']}) OR ({$filter['where']})";
        $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $filter['bindings']);
      }
      
      return $this;
    }

    /**
     * Creates filter by creating SELECT subquery.
     * 
     * @param string $element
     * @param string $collectionName
     * @param \prosys\core\interfaces\IFilterable $filter
     * 
     * @return \prosys\core\mapper\SqlFilter
     */
    public function filter($element, $collectionName, IFilterable $filter = NULL)
    {
      $element = (($element == '*') ? $element : $this->quote($element));
      
      $where = '';
      $whereBindings = array();
      if (!is_null($filter)) {
        $filter = $filter->resultFilter();

        $where = " WHERE {$filter['where']}";
        $whereBindings = $filter['bindings'];
      }

      $this->_filter['where'] = "SELECT {$element} FROM {$this->quote($collectionName)}{$where}";
      $this->_filter['bindings'] = array_merge($this->_filter['bindings'], $whereBindings);
      
      return $this;
    }

    /**
     * Returns result -> WHERE condition.
     * 
     * @return string
     */
    public function resultFilter()
    {
      return (($this->_filter['where']) ? $this->_filter : array());
    }
  }
