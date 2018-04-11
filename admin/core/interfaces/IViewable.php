<?php
  namespace prosys\core\interfaces;
  
  use prosys\model\Entity;

  /**
   * Interface of viewable object.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  interface IViewable
  {
    /**
     * Shows default action.<br />
     * Default action of all views is most commonly showing table.
     * 
     * @param mixed $arg
     */
    public function initial($arg = NULL);
    
    /**
     * Prints the table of records out.
     * 
     * @param Entity[] $data
     * @param array $optional associative array with optional data
     */
    public function table($data, $optional = array());
    
    /**
     * Prints the detail of the record out.
     * 
     * @param Entity $entity
     * @param array $optional associative array with optional data
     */
    public function detail(Entity $entity, $optional = array());
    
    /**
     * Prints manage form of the record out.
     * 
     * @param Entity $entity
     * @param array $optional associative array with optional data
     */
    public function manage(Entity $entity, $optional = array());
  }
