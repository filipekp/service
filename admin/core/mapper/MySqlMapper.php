<?php
  namespace prosys\core\mapper;
  
  class MySqlMapper extends SqlMapper
  { 
    /**
     * Zavola ulozenou proceduru SQL, ktera vola SELECT na konci definice.
     * 
     * @param string $function
     * @param array $params
     * 
     * @return mixed procedura muze na konci zavolat SELECT, takze je mozne, ze vraci result set
     */
    public function callProcedureSelect($procedure, array $params) {
      $this->_dataHandler->callProcedureSelect($procedure, $params);
      return $this->_dataHandler->fetchObjects();
    }
  }
