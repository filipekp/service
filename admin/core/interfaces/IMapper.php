<?php
  namespace prosys\core\interfaces;

  /**
   * Represents the data mapper interface.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  interface IMapper
  {

    public function find($arg);

    public function insert(array $data);

    public function update(array $data);

    public function delete($primaryKeyValue);

    public function findRecords(IFilterable $filter, array $order, array $limit);

    public function deleteRecords(IFilterable $filter);

    public function max($type, $element, IFilterable $filter);

    public function avg($type, $element, IFilterable $filter);
    
    public function sum($type, $element, IFilterable $filter);
    
    public function count($element, IFilterable $filter);
    
    public function groupCount($groupColumn, $countColumn, IFilterable $filter = NULL);
    
    public function callFunction($function, array $params);
    
    public function clearResult();

  }
