<?php
  namespace prosys\model;

  /**
   * Represents the entity of menu.
   * 
   * @property int $id
   * @property string $name
   * @property string $title
   * @property string $type
   * @property string $typeValue element=type_value
   * @property string $icons
   * @property MenuEntity $parent element=parent
   * @property int $sequence
   * @property bool $displayAlways element=display_always
   * 
   * @property MenuEntity[] $children noelement
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class MenuEntity extends Entity
  {
    private $children = array();
    
    public function getChildren() {
      return $this->children;
    }

    public function setChildren($children) {
      $this->children = $children;
      return $this;
    }
  }
