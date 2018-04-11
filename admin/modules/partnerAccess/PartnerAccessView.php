<?php
  namespace prosys\admin\view;

  /**
   * Reprezentuje view objekt entity partnera.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerAccessView extends View
  {
    /**
     * Inicializuje popisky všech vlastností entity.
     */
    public function __construct()
    {
      $labels = array(
        'id'        => 'ID',
        'partner'   => 'Partner',
        'accessAt'  => 'Přístup',
        'ip'        => 'IP (proxy IP)',
        'method'    => 'Metoda',
        'params'    => array(
          'modified_from' => 'Filtr &bdquo;změněno od&ldquo;',
          'category_type' => 'Typ výpisu kategorie'
        ),
        'status'    => 'Status',
        'count'     => 'Počet výrobků'
      );

      parent::__construct(NULL, $labels);
    }
    
    /**
     * Zobrazí log přístupů.
     * 
     * @param \prosys\model\PartnerEntity[] $data
     * @param array $optional
     */
    public function table($data = array(), $optional = array()) {
      $assign = $optional + array(
        'data' => $data,
        'totalcount' => $optional['count'],
//        'pagination' => View::generatePaging(
//          $optional['count'], filter_input_array(INPUT_GET), $optional['items_on_page'], 3, 3, 'paging_bootstrap pagination', '', FALSE
//        )
      );
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Table', 'Log přístupů', $assign, $templateOnly);
    }
  }
