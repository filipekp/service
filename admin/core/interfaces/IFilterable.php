<?php
  namespace prosys\core\interfaces;

  /**
   * Represents the filter's interface for query languages.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  interface IFilterable
  {
    /**
     * Create filter.
     */
    public static function create();
    
    /**
     * Compare elemnt and value/element with specific operator (&lt;, &gt;, =).
     */
    public function comparise($element, $cmp, $value);
    
    /**
     * Check elemnt is empty.
     */
    public function isEmpty($element);
    
    /**
     * Check elemnt is not empty.
     */
    public function isNotEmpty($element);
    
    /**
     * Find string contain string from param.
     */
    public function contains($element, $string);
    
    /**
     * Find string begins string from param.
     */
    public function startWith($element, $string);
    
    /**
     * Check if element is in array.
     */
    public function inArray($element, array $array);
    
    /**
     * Check if element is not in array.
     */
    public function notInArray($element, array $array);
    
    /**
     * Check if element is in filter.
     */
    public function inFilter($element, IFilterable $filter);
    
    /**
     * Check if element is not in filter.
     */
    public function notInFilter($element, IFilterable $filter);
    
    /**
     * Check if filter exists.
     */
    public function exists(IFilterable $filter);
    
    /**
     * Check if filter not exists.
     */
    public function notExists(IFilterable $filter);
    
    /**
     * Insert logical multiplicator.
     */
    public function andL(IFilterable $filter);
    
    /**
     * Insert logical sum.
     */
    public function orL(IFilterable $filter);
    
    /**
     * Create subquery filter.
     */
    public function filter($element, $collectionName, IFilterable $filter);
    
    /**
     * Return result of filter.
     */
    public function resultFilter();
  }
