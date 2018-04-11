<?php
  use prosys\core\common\Settings,
      prosys\core\common\Functions,
      prosys\model\PartnerEntity;
  
  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  $isLoggedProclient = (strpos($_LOGGED_USER->email, '@proclient.cz') !== FALSE);

  echo Functions::handleMessagesAdmin();
?>
  <div class="modal fade" id="partner_link_dialog" data-backdrop="static">
    <div class="modal-dialog modal-small">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Link pro partnera <span class="partner_name"></span></h4>
        </div>
        <div class="modal-body">
          <p><input type="text" class="m-wrap" style="width: 515px;" value="" data-all-address="<?php echo Settings::ROOT_URL; ?>get-products.php?identification="/></p>
        </div>
        <div class="modal-footer">
          <button id="idle-timeout-dialog-keepalive" type="button" class="btn btn-primary" data-dismiss="modal">Zavřít</button>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12" style="position: relative;">
        <h3 class="page-title">
          <?php echo $title; ?> <small>(celkem záznamů: <?php echo $count; ?>)</small>
        </h3>
        <?php
          prosys\admin\view\View::addBreadcumb($title, '', '?module=partner&activity=manage');
          echo prosys\admin\view\View::getBreadcumbs();
        
          if ($isLoggedProclient) {
        ?>
        <div style="position: absolute; bottom: 33px; right: 10px;">
          pro stažení pouze některých výrobců partnerova feedu, přidat do URL atribut `producers`: <i>producers[]=X&producers[]=Y&...</i>
        </div>
        <?php
          }
        ?>
      </div>
    </div>

    <div class="row-fluid">
      <div class="span12">
        <div class="portlet box grey">
          <div class="portlet-title">
            <div class="caption">
              <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=partner&activity=manage"><i class="icon-plus"></i>Přidat partnera</a>
            </div>
            
            <div class="actions">
              <div class="btn-group">
                <a class="btn mini blue" href="#" data-toggle="dropdown">
                  <i class="icon-download-alt" style="margin-right: 1px;"></i> Stáří stahovaného feedu
                  <i class="icon-angle-down"></i>
                </a>
                <ul class="dropdown-menu pull-right">
                  <li><a class="age_days" href="#">dní zpět...</a></li>
                  <li><a class="age_date" href="#">od data...</a></li>
                  <li><a class="age_infinity" href="#">kompletní feed</a></li>
                </ul>
              </div>

              <span class="input"><input class="mini number" type="number" value="<?php echo Settings::TEST_XML_FEED_AGE; ?>" min="1" /></span>
            </div>
          </div>
          <div class="portlet-body flip-scroll">
            <form class="filter" method="get">
              <input type="hidden" name="module" value="partner" />
              <table class="table-bordered table-striped table-condensed cf manage-table">
                <thead class="cf">
                  <tr>
                    <th class="icon"></th>
                    <th class="column_1"><?php echo $this->_labels['name']; ?></th>
                    <th class="column_1b"></th>
                    <th class="column_2 nowrap"><?php echo $this->_labels['producers']; ?></th>
                    <th class="column_3 nowrap"><?php echo $this->_labels['hashCode']; ?></th>
                    <th class="column_4 nowrap"><?php echo $this->_labels['styleplusId']; ?></th>
                    <th class="column_5 nowrap"><?php echo $this->_labels['user']['login']; ?></th>
                    <th class="column_6"><?php echo $this->_labels['user']['contact']; ?></th>
                    <th class="column_7"><?php echo $this->_labels['log']['last_access']; ?></th>
                    <th class="icon"></th>
                    <th class="icon"></th>
                    <th class="icon"></th>
                  </tr>
                  <tr>
                    <td class="icon"></td>
                    <td class="column_1">
                      <input type="text" class="text filter_input" name="filter_name" value="<?php echo $filter['filter_name']; ?>"/>
                    </td>
                    <td class="column_1b"></td>
                    <td class="column_2"></td>
                    <td class="column_3"></td>
                    <td class="column_4"></td>
                    <td class="column_5">
                      <input type="text" class="text filter_input" name="filter_login" value="<?php echo $filter['filter_login']; ?>"/>
                    </td>
                    <td class="column_6"></td>
                    <td class="column_7"></td>
                    <td class="icon"></td>
                    <td class="icon"></td>
                    <td class="icon">
                      <input type="submit" class="halflings-icon filter filter-icon" value="" title="Aplikovat filtr" />
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($data) {
                    /* @var $partner prosys\model\PartnerEntity */
                    foreach ($data as $partner) {
                      $constraints = (($partner->constraints) ? $partner->constraints->jsonSerialize() : []);
                  ?>
                  <tr id="object_id_<?php echo $partner->user->login; ?>" 
                      class="<?php echo (($partner->active) ? 'active' : 'unactive'); ?> <?php echo (($partner->type == PartnerEntity::TYPE_OLD) ? 'old' : 'regular'); ?>"
                      data-partner-identification="<?php echo $partner->hashCode; ?>"
                      data-partner-name="<?php echo $partner->name; ?>">
                    <td class="icon visibility"><i class="icon-eye-<?php echo (($partner->active) ? 'open' : 'close'); ?>"></i></td>
                    <td class="column_1" data-title="<?php echo $this->_labels['name']; ?>">
                      <?php
                        echo $partner->name .
                             (($partner->web) ? 
                                '<br /><a class="web" href="http://' . $partner->web . '" target="_blank">' . $partner->web . '</a>' : '');
                      ?>
                    </td>
                    <td class="column_1b">
                      <img class="flag" src="<?php echo Settings::ADMIN_IMAGES; ?>flags/<?php echo strtolower($partner->styleplusPartner->engine); ?>.png" alt="<?php echo $partner->hashCode . ' ' . $partner->styleplusPartner->engine; ?>" />
                      <?php
                        echo (($partner->useRegularOkPrices) ?
                            '<i class="icon-pushpin tooltips" data-placement="top" data-original-title="Při generování feedu ignoruje tabulku s fixními cenami na OKCZ"></i>' :
                            '<img src="' . Settings::ADMIN_ICONS . 'empty.png" />'
                        );
                      
                        echo ((Functions::item($constraints, 'excel') === FALSE || $partner->type == PartnerEntity::TYPE_REGULAR) ?
                            '<img src="' . Settings::ADMIN_ICONS . 'xml.png" />' :
                            '<img class="excel" src="' . Settings::ADMIN_ICONS . 'excel.png" />'
                        );
                        
                        echo (($partner->note) ?
                            '<i class="icon-comment tooltips" data-placement="top" data-original-title="' . $partner->note . '"></i>' :
                            '<img src="' . Settings::ADMIN_ICONS . 'empty.png" />'
                        );
                      ?>
                    </td>
                    <td class="column_2" data-title="<?php echo $this->_labels['producers']; ?>">
                      <?php
                        if ($partner->type == PartnerEntity::TYPE_REGULAR) {
                          echo implode(', ', array_map(function($partnerProducer) {
                            return $partnerProducer->producer->name . '&nbsp;(' . $partnerProducer->profit . '%)';
                          }, $partner->producers->getLoadedArrayCopy()));
                        } else {
                          $defaultFilter = (array)Functions::item($constraints, 'default_filter');
                          $producerIds = (array)Functions::item($defaultFilter, 'producer_id');                          
                          $producers = \prosys\core\common\Agents::getAgent('ProducerDao', \prosys\core\common\Agents::TYPE_MODEL)->loadRecords(
                            prosys\core\mapper\SqlFilter::create()->inArray('id',
                              (($producerIds) ? $producerIds : array(-1))
                            )
                          );
                         
                          if ($producers) {
                            echo implode(', ', array_map(function($producer) {
                              return $producer->name;
                            }, $producers));
                          } else if (!$defaultFilter) {
                            echo 'VŠICHNI VÝROBCI';
                          }
                        }
                      ?>
                    </td>
                    <td class="column_3" data-title="<?php echo $this->_labels['hashCode']; ?>"><?php echo $partner->hashCode; ?></td>
                    <td class="column_4" data-title="<?php echo $this->_labels['styleplusId']; ?>"><?php echo $partner->styleplusPartner->identification(FALSE, FALSE); ?></td>
                    <td class="column_5" data-title="<?php echo $this->_labels['user']['login']; ?>"><?php echo $partner->user->login; ?></td>
                    <td class="column_6" data-title="<?php echo $this->_labels['user']['contact']; ?>"><?php echo $partner->user->getContact('<br />'); ?></td>
                    <td class="column_7" data-title="<?php echo $this->_labels['log']['last_access']; ?>">
                      <?php
                        /* @var $lastAccess \prosys\model\PartnerAccessEntity */
                        $lastAccess = $partner->getLastAccess();
                        
                        if ($lastAccess) {
                          echo '<span class="label label-' . (($lastAccess->getStatus('message') == 'OK') ? 'success' : 'important') . '">' .
                                  $lastAccess->accessAt->format('d.m.Y H:i:s') .
                               '</span>';
                        } else {
                          echo '-';
                        }
                      ?>
                    </td>
                    
                    <?php if ($_LOGGED_USER->hasRight('partner', 'manage')) { ?>
                    <td class="icon" data-title="Editovat">
                      <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=partner&activity=manage&id=<?php echo $partner->hashCode; ?>" class="icon_link default_action">
                        <i class="icon-pencil"></i>
                      </a>
                    </td>
                    <?php } else { ?>
                      <td class="icon">&nbsp;</td>
                    <?php } ?>
                      
                    <?php if ($partner->user->login != $_LOGGED_USER->login && $_LOGGED_USER->hasRight('partner', 'delete')) { ?>
                    <td class="icon" data-title="Smazat">
                      <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?controller=partner&action=delete&id=<?php echo $partner->hashCode; ?>" class="icon_link" onclick="return confirm('Opravdu chcete partnera smazat?');">
                        <i class="icon-trash"></i>
                      </a>
                    </td>
                    <?php } else { ?>
                    <td class="icon">&nbsp;</td>
                    <?php } ?>
                      
                    <?php if ($partner->user->login != $_LOGGED_USER->login && $_LOGGED_USER->hasRight('partner', 'manage')) { ?>
                    <td class="icon" data-title="Smazat">
                      <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=partner&activity=send_information_mail&id=<?php echo $partner->hashCode; ?>" class="fancybox-iframe icon_link" title="Odeslat email s informacemi.">
                        <i class="icon-envelope"></i>
                      </a>
                    </td>
                    <?php } else { ?>
                    <td class="icon">&nbsp;</td>
                    <?php } ?>
                    
                    <?php if ($_LOGGED_USER->hasRight('partner', 'downloadXmlFeed')) { ?>
                    <td class="icon">
                      <a class="downloadXmlFeed" href="<?php echo Settings::ROOT_ADMIN_URL; ?>?controller=partner&action=downloadXmlFeed&id=<?php echo $partner->hashCode; ?>&xml_feed_age_days=<?php echo Settings::TEST_XML_FEED_AGE; ?>" class="icon_link" title="Stáhnout XML feed (výrobky změněné za posledních <?php echo Settings::TEST_XML_FEED_AGE; ?> dní)" target="_blank">
                        <i class="icon-download-alt"></i>
                      </a>
                    </td>
                    <?php } else { ?>
                      <td class="icon">&nbsp;</td>
                    <?php } ?>
                  </tr>
                  <?php
                    }
                  } else {
                  ?>  
                  <tr class="no_object">                  
                    <td colspan="8"><?php echo (($filter) ? 'Nenalezen' : 'Není'); ?> žádný partner</td>
                  </tr>
                  <?php
                  }
                  ?>
                </tbody>
              </table>
            </form>
            <br />
            <?php echo (($count > Settings::ITEMS_PER_PAGE) ? $pagination : ''); ?>
          </div>
        </div>
      </div>
    </div>
  </div>