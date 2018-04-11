<?php
  namespace prosys\model;

  /**
   * Reprezentuje entitu partnera ze serveru StyloveKoupelny.cz.
   * 
   * @property int $id
   * @property string $name element=nazev
   * @property bool $deleted element=odstranen&delete_flag
   * @property string $engine
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_PartnerEntity extends Entity
  {
    /**
     * Vypise identifikaci partnera.
     * 
     * @param bool $html
     * @return string
     */
    public function identification($html = TRUE, $showCountry = TRUE) {
      if ($this->isNew()) {
        return '';
      }

      $id = (($html) ? '<i>(' . $this->id . ')</i>' : '(' . $this->id . ')');
      return $this->name . ' ' . $id . (($showCountry) ? ' [' . $this->engine . ']' : '');
    }
  }
