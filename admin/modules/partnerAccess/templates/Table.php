
<div class="row-fluid">
  <div class="control-group">
    <div class="row-fluid">
      <div class="span12">
        <div class="portlet box blue">
          <div class="portlet-title">
            <div class="caption">
              <?php echo $title; ?>
            </div>
          </div>
          <div class="portlet-body no-more-tables">
            <table class="table-bordered table-striped table-condensed cf manage-table">
              <thead class="cf">
                <tr>
                  <th class="column_1"><?php echo $this->_labels['accessAt']; ?></th>
                  <th class="column_2"><?php echo $this->_labels['ip']; ?></th>
                  <th class="column_3"><?php echo $this->_labels['method']; ?></th>
                  <th class="column_4"><?php echo $this->_labels['params']['modified_from']; ?></th>
                  <th class="column_5"><?php echo $this->_labels['status']; ?></th>
                  <th class="column_6"><?php echo $this->_labels['count']; ?></th>
                  <th class="icon"></th>
                </tr>
                <tr>
                  <td class="column_1">-</td>
                  <td class="column_2">-</td>
                  <td class="column_3"></td>
                  <td class="column_4"></td>
                  <td class="column_5"></td>
                  <td class="column_6"></td>
                  <td class="icon">
                    <input type="submit" class="halflings-icon filter filter-icon" value="" title="Aplikovat filtr" />
                  </td>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($data) {
                  $countItems = 0;
                  /* @var $access prosys\model\PartnerAccessEntity */
                  foreach ($data as $access) {
                    if ($countItems >= 20) { break; }
                ?>
                <tr id="object_id_<?php echo $access->id; ?>">
                  <td class="column_1" data-title="<?php echo $this->_labels['accessAt']; ?>"><?php echo $access->accessAt->format('d.m.Y H:i:s'); ?></td>
                  <td class="column_2" data-title="<?php echo $this->_labels['ip']; ?>"><?php echo $access->ip . (($access->proxyIp) ? ' (' . $access->proxyIp . ')' : ''); ?></td>
                  <td class="column_3" data-title="<?php echo $this->_labels['status']; ?>"><?php echo $access->method; ?></td>
                  <td class="column_4" data-title="<?php echo $this->_labels['params']['modified_from']; ?>"><?php echo $access->getParam('modified_from'); ?></td>
                  <td class="column_5" data-title="<?php echo $this->_labels['status']; ?>"><?php echo $access->getStatus(); ?><?php echo ((!is_null(($lfc = $access->getResponseValue(['load_from_cache']))) && $lfc) ? ' <i class="icon-refresh" title="načteno z cache"></i>' : ''); ?></td>
                  <td class="column_6" data-title="<?php echo $this->_labels['count']; ?>"><?php echo $access->getResponseCount(); ?></td>
                  <td class="icon">&nbsp;</td>
                </tr>
                <?php
                    $countItems++;
                  }
                } else {
                ?>  
                <tr class="no_object">                  
                  <td colspan="8">Partner zatím nestáhl žádný feed.</td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
            <br />
              
            <?php //echo (($count > $items_on_page) ? $pagination : ''); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>