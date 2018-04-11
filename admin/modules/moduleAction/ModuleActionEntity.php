<?php
  namespace prosys\model;

  /**
   * Represents the entity of module.
   * 
   * @property int $id
   * @property ModuleEntity $module
   * @property int $type element=action_type
   * @property string $name element=action_name
   * @property string $title element=action_title
   * @property ModuleActionEntity $parent
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ModuleActionEntity extends Entity
  {
    const TYPE_ACTIVITY = 1;
    const TYPE_ACTION = 2;
    const TYPE_OTHER_PERMISSION = 3;
    
    public static $TYPES = array(
      self::TYPE_ACTIVITY => array('name' => 'stránka', 'color' => '#000000', 'bgColor' => '#E2DB00'),
      self::TYPE_ACTION => array('name' => 'akce', 'color' => '#FFFFFF', 'bgColor' => '#C80000'),
      self::TYPE_OTHER_PERMISSION => array('name' => 'další práva', 'color' => '#FFFFFF', 'bgColor' => '#0084E2')
    );
  }
