<?php
  namespace prosys\model;
  
  use prosys\core\common\Agents,
      prosys\core\mapper\SqlFilter;

  /**
   * Reprezentuje entitu vyrobce.
   * 
   * @property int $id
   * @property string $code
   * @property string $name
   * @property int $sortOrder element=sort_order
   * @property boolean $deleted delete_flag
   *
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ProducerEntity extends Entity
  {
    /**
     * Vrati katalogova cisla vsech vyrobku vyrobce.
     * @return array
     */
    public function getCatalogIds() {
      /* @var $productDao \prosys\model\SKCZ_ProductDao */
      $productDao = Agents::getAgent('SKCZ_ProductDao', Agents::TYPE_MODEL);
      
      return array_map(
        function($row) {
          return $row->katalogove_cislo;
        },
        $productDao->findRecordsProjection(
          ['katalogove_cislo'],
          SqlFilter::create()->comparise('id_vyrobce', '=', $this->id)
                             ->andL()->comparise('katalogove_cislo', '!=', '')
                             ->andL()->comparise('odstranen', '=', '0')
                             ->andL()->comparise('aktivni', '=', '1')
        )
      );
    }
  }
