<?php
  namespace prosys\admin\view;
  use prosys\model\Entity,
      prosys\model\ModuleActionEntity;

  /**
   * Represents the admin group module view.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class GroupView extends View
  {
    /**
     * Initializes the label of every group property.
     */
    public function __construct(\prosys\model\GroupDao $groupDao)
    {
      $labels = array(
        'id'      => 'ID',
        'name'    => 'Jméno skupiny',
        'rights'  => 'Práva skupiny'
      );

      parent::__construct($groupDao, $labels);
    }
    
    /**
     * Prints out manage form to managing the group.
     * 
     * @param \prosys\model\GroupEntity $group
     * @param array $optional associative array with optional data
     */
    public function manage(Entity $group, $optional = array()) {
      /* @var $group \prosys\model\GroupEntity */
      $assign = $optional + array('group' => $group);
      
      if ($group->isNew()) {
        $heading = 'Nová skupina';
        $assign['delete'] = '';
        
      } else {
        $heading = 'Úprava skupiny &bdquo;' . $group->name . '&ldquo;';
        $assign['delete'] = <<<DELETE
          <button type="submit" class="btn red delete" title="Smazat"><i class="icon-trash icon-white"></i> Smazat</button>
DELETE;
      }
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Manage', $heading, $assign, $templateOnly);
    }
    
    /**
     * 
     * @param array $data
     */
    private static function generateGroupRights($data, $groupRights) {
      $html = '';
      foreach ($data as $moduleActionData) {
        /* @var $moduleAction ModuleActionEntity */
        $moduleAction = $moduleActionData['item'];

        $disabled = array_key_exists($moduleAction->id, $groupRights) && !$groupRights[$moduleAction->id];
        ob_start(); 
        ?>
        <li>
          <?php echo (($disabled) ? '<input type="hidden" name="group_rights[]" value="' . $moduleAction->id . '" />' : ''); ?>
          <label for="group_right_<?php echo $moduleAction->id; ?>">
            <input type="checkbox"
                 name="group_rights[]" 
                 id="group_right_<?php echo $moduleAction->id; ?>"
                 data-id="<?php echo $moduleAction->id; ?>"
                 data-parent="<?php echo (($moduleAction->parent) ? $moduleAction->parent->id : '0'); ?>"
                 value="<?php echo $moduleAction->id; ?>"
                 <?php echo (($disabled) ? '' : ' data-group="' . $moduleAction->module->module . '"'); ?>
                 <?php echo ((array_key_exists($moduleAction->id, $groupRights)) ? ' checked="checked"' : ''); ?>
                 <?php echo (($disabled) ? ' disabled="disabled"' : ''); ?>/>
            <?php echo $moduleAction->title; ?> 
            <span class="activity_tag" style="background-color: <?php echo ModuleActionEntity::$TYPES[$moduleAction->type]['bgColor']; ?>; color: <?php echo ModuleActionEntity::$TYPES[$moduleAction->type]['color']; ?>;">
              <?php echo ModuleActionEntity::$TYPES[$moduleAction->type]['name']; ?>
            </span>
          </label>
        <?php
        if (array_key_exists('children', $moduleActionData)) {
          echo '<ul>';
          echo self::generateGroupRights($moduleActionData['children'], $groupRights);
          echo '</ul>';
        }
        ?>
        </li>
        <?php
        $html .= ob_get_clean();
      }
      
      return $html;
    }
    
    /**
     * 
     * @param \prosys\model\ModuleActionEntity[] $data
     */
    public static function getHtmlGroupRights($data) {
      $html = '';
      foreach ($data['modulesWithAction'] as $moduleId => $module) {
        $html .= '<div class="module_name check_all" data-group-id="' . $moduleId . '">' . $module['name'] . '</div>';
        $html .= '<ul>';
        $html .= self::generateGroupRights($module['items'], $data['groupRights']);
        $html .= '</ul>';
      }
      
      return $html;
    }
    
    /**
     * Prints out the table of groups.
     * 
     * @param \prosys\model\GroupEntity[] $data
     * @param array $optional
     */
    public function table($data = array(), $optional = array()) {
      $assign = $optional + array(
        'data' => $data,
        'totalcount' => $optional['count'],
        'pagination' => View::generatePaging($optional['count'], $optional['get'], $optional['items_on_page'])
      );
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Table', 'Seznam skupin', $assign, $templateOnly);
    }
  }
