<?php
  namespace prosys\admin\view;
  use prosys\model\Entity;

  /**
   * Represents the admin module view.
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class ModuleActionView extends View
  {
    /**
     * Initializes the label of every group property.
     */
    public function __construct(\prosys\model\GroupDao $groupDao)
    {
      $labels = array(
        'id'            => 'ID',
        'module'        => 'Modul',
        'action_name'   => 'Název akce',
        'action_type'   => 'Typ akce',
        'action_title'  => 'Popis akce'
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

                <tr>
                  <th>&nbsp;</th>
                  <td><input id="delete" class="submit" type="submit" name="delete" value="Smazat" /></td>
                </tr>
DELETE;
      }
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Manage', $heading, $assign, $templateOnly);
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
        'pagination' => View::generatePaging($optional['count'], filter_input_array(INPUT_GET), $optional['items_on_page'])
      );
      
      $templateOnly = ((array_key_exists('template_only', $optional)) ? $optional['template_only'] : FALSE);

      $this->printActivity('Table', 'Seznam skupin', $assign, $templateOnly);
    }
  }
