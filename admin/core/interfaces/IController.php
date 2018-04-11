<?php
  namespace prosys\core\interfaces;

  /**
   * Represents the controller's interface.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  interface IController
  {
    /**
     * Controls module requests.
     * 
     * @param string $activity
     */
    public function response($activity);
  }
