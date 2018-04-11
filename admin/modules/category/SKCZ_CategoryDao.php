<?php
  namespace prosys\model;
  
  use prosys\core\common\Settings,
      prosys\core\common\Agents;
  
  /**
   * Reprezentuje objekt pro přístup k datům entity kategorie ze serveru StyloveKoupelny.cz (DAO).
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class SKCZ_CategoryDao extends SQLDataAccessObject
  {
    public function __construct() {
      // create data mapper
      $mySqlHandler = Agents::getAgent('MySqlConnection', Agents::TYPE_COMMON, array(Settings::SKCZ_DB_SERVER,
                                                                                     Settings::SKCZ_DB_USER,
                                                                                     Settings::SKCZ_DB_PASSWORD,
                                                                                     Settings::SKCZ_DB_DATABASE,
                                                                                     Settings::SKCZ_DB_PREFIX),
                                       'SKCZ_MySqlConnection');
      parent::__construct(SKCZ_CategoryEntity::classname(), 'prosys\core\mapper\MySqlMapper', $mySqlHandler, 'kategorie');
    }
    
    /**
     * Vrati cestu ke kategorii.
     * 
     * @param \prosys\model\SKCZ_CategoryEntity $category
     * @param \prosys\model\PartnerEntity $partner
     * 
     * @return array
     */
    public function getPath(SKCZ_CategoryEntity $category, PartnerEntity $partner) {
      $slot = 'name' . $partner->styleplusPartner->engine;
      $path = array($category->$slot);

      $current = $category;
      while (!$current->parent->isNew()) {
        $path[] = $current->parent->$slot;
        $current = $current->parent;
      }

      return implode(' | ', array_reverse($path));
    }
  }
