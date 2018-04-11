<?php
  use prosys\core\common\Settings,
      prosys\core\common\Functions,
      prosys\admin\view\View;

  /* @var $_LOGGED_USER \prosys\model\UserEntity */
  
  /* @var $partner \prosys\model\PartnerEntity */
  /* @var $user \prosys\model\UserEntity */
  $user = $partner->user;
  
  /* @var $partnerProducers \prosys\model\PartnerProducerEntity[] */

  echo Functions::handleMessagesAdmin();
?>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <h3 class="page-title">
            <?php echo $title; ?>
          </h3>
          <?php
          View::addBreadcumb('Přehled partnerů', 'briefcase', '?module=partner');
          View::addBreadcumb($title, '', '?module=partner&activity=manage' . (($partner->isNew()) ? '' : '&id=' . $partner->hashCode));

          echo View::getBreadcumbs();
          ?>
        </div>
      </div>
      
      <div class="row-fluid">
        <div class="portlet box grey tabbable">
          <div class="portlet-title">
            <div class="caption">
              <a href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=partner"><i class="icon-briefcase"></i>Zpět na seznam partnerů</a>
            </div>
          </div>
          <div class="portlet-body form">
            <div class="tabbable portlet-tabs">
              <div style="float: right; height: 0px">
                <ul class="nav nav-tabs">            
                  <li data-tab="partner_info"><a href="#partner_info" data-toggle="tab"><?php echo $this->_labels['section']['info']; ?></a></li>
                  <li data-tab="partner_producers"><a href="#partner_producers" data-toggle="tab"><?php echo $this->_labels['section']['producers']; ?></a></li>
                  <li data-tab="partner_log"><a href="#partner_log" data-toggle="tab"><?php echo $this->_labels['section']['log']; ?></a></li>
                </ul>
              </div>
              <div class="tab-content">
                <form action="" class="form-horizontal filter" method="post">
                  <input type="hidden" name="controller" value="partner" />
                  <input type="hidden" name="action" value="save" />
                  <input type="hidden" name="back_to" value="<?php echo filter_input(INPUT_GET, 'activity'); ?>" />

                  <input type="hidden" name="deleted" value="0" />
                  <input type="hidden" name="partner[type]" value="<?php echo (($partner->isNew()) ? prosys\model\PartnerEntity::TYPE_REGULAR : $partner->type); ?>" />

                  <div id="partner_info" class="tab-pane">
                    <div class="row-fluid">
                      <div class="m-wrap span6">
                        <div class="control-group">
                          <label for="partner_name" class="control-label"><?php echo $this->_labels['name']; ?>: *</label>
                          <div class="controls">
                            <input id="partner_name" class="m-wrap medium" type="text" name="partner[name]" placeholder="<?php echo $this->_labels['name']; ?>" value="<?php echo $partner->name; ?>" autofocus />
                          </div>
                        </div>

                        <div class="control-group">
                          <label for="partner_hash_code" class="control-label"><?php echo $this->_labels['hashCode']; ?>: *</label>
                          <div class="controls">
                            <?php if ($partner->isNew()) { ?>
                            <input class="m-wrap medium" id="partner_hash_code" type="text" name="partner[hash_code]" maxlength="16" value="<?php echo $partner->hashCode; ?>" placeholder="<?php echo $this->_labels['hashCode']; ?>" />
                            <a href="#" class="btn black action-generate-hash-code nooutline" title="vygenerovat"><i class="icon-retweet"></i></a>
                            <?php } else { ?>
                              <span class="text"><b><?php echo $partner->hashCode; ?></b></span>
                              <input id="partner_hash_code" type="hidden" name="partner[hash_code]" value="<?php echo $partner->hashCode; ?>" />
                            <?php } ?>
                          </div>
                        </div>

                        <div class="control-group">
                          <label for="partner_styleplus_id" class="control-label"><?php echo $this->_labels['styleplusId']; ?>: *</label>
                          <div class="controls">
                            <input id="partner_styleplus_id" type="hidden" name="partner[styleplus_id]" value="<?php echo $partner->styleplusPartner->id; ?>" />
                            <input class="m-wrap medium" id="partner_styleplus" type="text" placeholder="<?php echo $this->_labels['styleplusId']; ?>" value="<?php echo $partner->styleplusPartner->identification(FALSE); ?>" />
                          </div>
                        </div>

                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['web']; ?>:</label>
                          <div class="controls">
                            <div class="input-prepend m-wrap span8">
                              <a href="<?php echo (($partner->web) ? 'http://' . $partner->web : '#'); ?>" class="add-on" target="_blank">http://</a><input class="m-wrap span12" type="text" name="partner[web]" value="<?php echo $partner->web; ?>" placeholder="www.domena.cz" />
                            </div>
                          </div>
                        </div>

                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['active']; ?>:</label>
                          <div class="controls">
                            <input type="hidden" name="partner[active]" value="0" />
                            <div class="basic-toggle-button">
                              <?php $checked = (($partner->active === FALSE) ? '' : ' checked="checked"'); ?>
                              <input type="checkbox" class="toggle" name="partner[active]" value="1"<?php echo $checked; ?> />
                            </div>
                          </div>
                        </div>

                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['useRegularOkPrices']; ?>:</label>
                          <div class="controls">
                            <input type="hidden" name="partner[use_regular_ok_prices]" value="0" />
                            <div class="basic-toggle-button">
                              <?php $checked = (($partner->useRegularOkPrices) ? ' checked="checked"' : ''); ?>
                              <input type="checkbox" class="toggle" name="partner[use_regular_ok_prices]" value="1"<?php echo $checked; ?> />
                            </div>
                            
                            <span class="help-inline by-toggle blue">při generování feedu ignoruje tabulku s cenami na OKCZ</span>
                          </div>
                        </div>

                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['pricesFromOrders']; ?>:</label>
                          <div class="controls">
                            <input type="hidden" name="partner[prices_from_orders]" value="0" />
                            <div class="basic-toggle-button">
                              <?php $checked = (($partner->pricesFromOrders) ? ' checked="checked"' : ''); ?>
                              <input type="checkbox" class="toggle" name="partner[prices_from_orders]" value="1"<?php echo $checked; ?> />
                            </div>
                            
                            <span class="help-inline by-toggle blue">při generování feedu stahuje ceny z aplikace orders.styleplus.cz</span>
                          </div>
                        </div>

                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['showSellout']; ?>:</label>
                          <div class="controls">
                            <input type="hidden" name="partner[show_sellout]" value="0" />
                            <div class="basic-toggle-button">
                              <?php $checked = (($partner->showSellout) ? ' checked="checked"' : ''); ?>
                              <input type="checkbox" class="toggle" name="partner[show_sellout]" value="1"<?php echo $checked; ?> />
                            </div>
                            
                            <span class="help-inline by-toggle blue">(ne)zobrazovat ve feedu výrovku ve výprodeji</span>
                          </div>
                        </div>

                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['noWatermark']; ?>:</label>
                          <div class="controls">
                            <input type="hidden" name="partner[no_watermark]" value="0" />
                            <div class="basic-toggle-button">
                              <?php $checked = (($partner->noWatermark === TRUE) ? ' checked="checked"' : ''); ?>
                              <input type="checkbox" class="toggle" name="partner[no_watermark]" value="1"<?php echo $checked; ?> />
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="m-wrap span6">
                        <div class="control-group">
                          <label class="control-label"><?php echo $this->_labels['note']; ?>:</label>
                          <div class="controls">
                            <textarea name="partner[note]" class="m-wrap span10" rows="5"><?php echo $partner->note; ?></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
										<?php if(Functions::item(json_decode($partner->constraints,TRUE), 'prices_from_orders')){ echo 'Ceny jsou získávány z orders.syleplus.cz';}?>										
                    <div class="row-fluid">
                      <h3 class="form-section">Informace o uživateli</h3>											
                      <?php if (!$partner->isNew()) { ?><input type="hidden" name="id" value="<?php echo $partner->user->id ?>" /><?php } ?>
                      
                      <div class="control-group">
                        <label for="user_login" class="control-label"><?php echo $this->_labels['user']['login']; ?>: *</label>
                        <div class="controls">
                          <?php if ($partner->isNew()) { ?>
                          <input class="m-wrap medium" id="login" type="text" name="login" maxlength="32" value="<?php echo $user->login; ?>" placeholder="<?php echo $this->_labels['user']['login']; ?>" autocomplete="off" />
                          <?php } else { ?>
                            <span class="text"><b><?php echo $user->login; ?></b></span>
                            <input id="login" type="hidden" name="login" value="<?php echo $user->login; ?>" />
                          <?php } ?>
                        </div>
                      </div>

                      <div class="control-group">
                        <label for="password" class="control-label"><?php echo $this->_labels['user']['password']; ?>: *</label>
                        <div class="controls">
                          <input class="m-wrap medium" id="password" type="password" name="password" placeholder="<?php echo $this->_labels['user']['password']; ?>" autocomplete="off" />
                        </div>
                      </div>

                      <div class="control-group">
                        <label for="repassword" class="control-label"><?php echo $this->_labels['user']['repassword']; ?>: *</label>
                        <div class="controls">
                          <input class="m-wrap medium" id="repassword" type="password" name="repassword" placeholder="<?php echo $this->_labels['user']['repassword']; ?>" autocomplete="off" />
                        </div>
                      </div>

                      <div class="control-group">
                        <label for="phone" class="control-label"><?php echo $this->_labels['user']['phone']; ?>:</label>
                        <div class="controls">
                          <input class="m-wrap medium" id="phone" type="text" name="phone" maxlength="64" value="<?php echo $user->phone; ?>" placeholder="<?php echo $this->_labels['user']['phone']; ?>" />
                        </div>
                      </div>

                      <div class="control-group">
                        <label for="email" class="control-label"><?php echo $this->_labels['user']['email']; ?>:</label>
                        <div class="controls">
                          <input class="m-wrap medium" id="email" type="text" name="email" maxlength="64" value="<?php echo $user->email; ?>" placeholder="<?php echo $this->_labels['user']['email']; ?>" />
                        </div>
                      </div>
                      
                      <input type="hidden" name="groups[]" value="<?php echo prosys\model\PartnerEntity::GROUP_ID; ?>" />
                    </div>
                  </div>

                  <div id="partner_producers" class="tab-pane">
                    <div class="row-fluid">
                      <div class="control-group">
                        <label for="partner_name" class="control-label"><?php echo $this->_labels['producers']; ?>: *</label>
                        <div class="controls">
                          <div class="row-fluid">
                            <?php
                              $columns = array_chunk($producers, ceil(count($producers) / 3));
                              foreach ($columns as $column) {
                            ?>
                            <div class="span3">
                            <?php
                                foreach ($column as /* @var $producer \prosys\model\ProducerEntity */$producer) {
                                  $id = 'producer_' . $producer->id;
                                  $manageProfit = $_LOGGED_USER->hasRight('partner', 'manage_profit');
                                  
                                  /* @var $partnerProducer \prosys\model\PartnerProducerEntity */
                                  $partnerProducer = Functions::item($partnerProducers, $producer->id);
                            ?>
                              <label class="checkbox line clearfix" for="<?php echo $id; ?>">
                                <span class="producer span5">
                                  <input id="<?php echo $id; ?>" type="checkbox" name="producers[]" value="<?php echo $producer->id ?>"<?php echo (($partnerProducer) ? ' checked="checked"' : ''); ?> />
                                  <?php echo $producer->name; ?>
                                </span>
                                
                                <span class="profit span7"<?php echo (($partnerProducer) ? '' : ' style="display: none;"'); ?>>
                                  <input type="<?php echo (($manageProfit) ? 'text' : 'hidden'); ?>" name="profits[<?php echo $producer->id; ?>]" value="<?php echo (($partnerProducer) ? $partnerProducer->profit : ''); ?>" />
                                  <?php echo (($manageProfit) ? '%' : (($partnerProducer) ? '<i>(' . $partnerProducer->profit . '%)</i>' : '')); ?>
                                </span>
                              </label>
                            <?php
                                }
                            ?>
                            </div>
                            <?php
                              }
                            ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  

                  <div id="partner_log" class="tab-pane">
                    <?php echo $logTemplate; ?>
                  </div>

                  <div class="mandatory">* Povinné položky</div>
                  <div class="clearfix"></div>
                  <div class="form-actions">
                    <?php if ($_LOGGED_USER->hasRight('partner', 'initial')) { ?>
                      <button type="submit" class="btn green" title="Uložit a vrátit se na přehled"><i class="icon-ok"></i> Uložit</button>
                    <?php } ?>
                      <button type="submit" class="btn blue" title="Aplikovat změny" name="apply" value="1"><i class="icon-upload"></i> Použít</button>
                      <?php echo $delete; ?>
                      <a class="btn yellow cancel" href="<?php echo Settings::ROOT_ADMIN_URL; ?>?module=partner"><i class="icon-ban-circle"></i> Zrušit</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>        
      </div>
    </div>
