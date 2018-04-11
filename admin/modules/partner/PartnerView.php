<?php
  namespace prosys\admin\view;
  use prosys\model\Entity;

  /**
   * Reprezentuje view objekt entity partnera.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class PartnerView extends View
  {
    /**
     * Inicializuje popisky všech vlastností entity.
     */
    public function __construct()
    {
      $labels = array(
        'hashCode'    => 'ID pro odběr služby',
        'name'        => 'Jméno',
        'styleplusId' => 'StyloveKoupelny.cz ID',
        'web'         => 'Web',
        'active'      => 'Aktivní',
        'note'        => 'Poznámka',
        'constraints' => 'Omezení',
        'useRegularOkPrices' => 'Nepřepočítávat ceny',
        'pricesFromOrders'   => 'Ceny z orders.styleplus.cz',
        'showSellout' => 'Zobrazit výprodej',
        'noWatermark' => 'Obrázky bez vodoznaku',
        'user'        => UserView::getLabels(),
        'producers'   => 'Výrobci (marže)',
        'section'     => array(
          'info'        => 'Obecné informace',
          'producers'   => 'Seznam výrobců',
          'log'         => 'Log'
        ),
        'profit' => 'Profit',
        'log'         => array(
          'last_access' => 'Poslední stažení'
        )
      );

      parent::__construct(NULL, $labels);
    }
    
    /**
     * Zobrazí formulář pro správu profilu.
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @param array $optional associative array with optional data
     * 
     * @global \prosys\model\UserEntity $_LOGGED_USER
     */
    public function manageProfile(Entity $partner, $optional = array()) {
      /* @var $partner \prosys\model\PartnerEntity */
      $assign = $optional + array('partner' => $partner);
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('ManageProfile', 'Nastavení profitu pro jednotlivé výrobce', $assign, $templateOnly);
    }
    
    /**
     * Zobrazí formulář pro správu partnera.
     * 
     * @param \prosys\model\PartnerEntity $partner
     * @param array $optional associative array with optional data
     * 
     * @global \prosys\model\UserEntity $_LOGGED_USER
     */
    public function manage(Entity $partner, $optional = array()) {
      global $_LOGGED_USER;
      
      /* @var $partner \prosys\model\PartnerEntity */
      $assign = $optional + array('partner' => $partner);
      
      if ($partner->isNew()) {
        $heading = 'Nový partner';
        $assign['delete'] = '';
      } else {
        $heading = 'Úprava partnera &bdquo;' . $partner->name . '&ldquo;';
        if ($_LOGGED_USER->hasRight('partner', 'delete')) {
          $assign['delete'] = <<<DELETE
            <button type="submit" class="btn red delete" title="Smazat"><i class="icon-trash icon-white"></i> Smazat</button>
DELETE;
        } else {
          $assign['delete'] = '';
        }
      }
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Manage', $heading, $assign, $templateOnly);
    }
    
    /**
     * Zobrazí seznam partnerů.
     * 
     * @param \prosys\model\PartnerEntity[] $data
     * @param array $optional
     */
    public function table($data = array(), $optional = array()) {
      $assign = $optional + array(
        'data' => $data,
        'totalcount' => $optional['count'],
        'pagination' => View::generatePaging($optional['count'], $optional['get'], $optional['items_on_page'])
      );
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Table', 'Seznam partnerů', $assign, $templateOnly);
    }
  }
