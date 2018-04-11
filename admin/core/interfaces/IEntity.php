<?php
  namespace prosys\core\interfaces;

  /**
   * Represents the data handleable interface.<br />
   * Notice: designed for MySQL Connection, but it should be fully functional for any type of the data handling.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  interface IEntity
  {
    public static function classname();
  }
