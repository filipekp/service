<?php
  namespace prosys\admin\view;
  
  use prosys\model\Entity,
      prosys\model\DataAccessObject,
      prosys\core\common\Settings,
      prosys\core\common\AppException,
      prosys\core\common\Agents,
      prosys\core\common\Functions;

  /**
   * Abstract class which should be the parent of every "viewable" classes.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  abstract class View implements \prosys\core\interfaces\IViewable
  {
    /** @var \prosys\model\DataAccessObject */
    protected $_dao;
    protected $_labels;
    public static $_breadcumbs;
    
    public static function getLabels() {
      $calledClassArr = explode('\\', get_called_class());
      $calledClass = array_pop($calledClassArr);
      $newView = Agents::getAgent($calledClass, Agents::TYPE_VIEW_ADMIN);
      
      return $newView->_labels;
    }

    /**
     * Initializes view with prop labels
     * 
     * @param array $labels
     * @param \prosys\model\DataAccessObject $dao
     */
    public function __construct(DataAccessObject $dao = NULL, $labels = array()) {
      global $_MODULE, $activity;
      /* @var $moduleActionDao \prosys\model\ModuleActionDao */
      $moduleActionDao = Agents::getAgent('ModuleActionDao', Agents::TYPE_MODEL);
      
      $this->_dao = $dao;
      $this->_labels = $labels;
      
      /* @var $menuDao \prosys\model\MenuDao */
      $menuDao = Agents::getAgent('MenuDao', Agents::TYPE_MODEL);
      /* @var $menuCurrent prosys\model\MenuEntity */
      $menuCurrent = $menuDao->loadByModule($_MODULE, $activity);
      $menuCurrent = ((!is_null($menuCurrent)) ? $menuCurrent->parent : $menuCurrent);
      
      self::$_breadcumbs = array();
      while ($menuCurrent && !$menuCurrent->isNew()) {
        /* @var $menuCurrent \prosys\model\MenuEntity */
        switch ($menuCurrent->type) {
          case 'module_action':
            /* @var $moduleAction \prosys\model\ModuleActionEntity */
            $moduleAction = $moduleActionDao->load($menuCurrent->typeValue);
            $link = '?module=' . $moduleAction->module->module . (($moduleAction->name == 'initial') ? '' : '&activity=' . $moduleAction->name);
          break;
          case 'link':
            $data = (array)json_decode($menuCurrent->typeValue, TRUE);
            $link = $data['href'];
            unset($data['href']);
          break;
          case 'section':
            $link = 'none';
          break;
        }
        
        $icon = json_decode($menuCurrent->icons);
        self::addBreadcumb($menuCurrent->name, $icon, $link);
        
        $menuCurrent = $menuCurrent->parent;
      }
      
      self::$_breadcumbs = array_reverse(self::$_breadcumbs);
    }
    
    /**
     * Generuje drobečkovou navigaci.
     * 
     * @return string
     */
    public static function getBreadcumbs() {
      $breadcumbs = self::$_breadcumbs;
      
      $items = array();
      $countItems = count($breadcumbs);
      for ($i = 0; $i < $countItems; $i++) {
        $icons = '';
        if (array_filter($breadcumbs[$i]['icon'])) {
          if (count($breadcumbs[$i]['icon']) > 1) {
            $icons = '<span class="icons">';
              foreach ($breadcumbs[$i]['icon'] as $icon) {
                $icons .= '<i class="icon-' . $icon . '"></i>';
              }
            $icons .= '</span>';
          } else {
            $icons = '<i class="icon-' . Functions::first($breadcumbs[$i]['icon']) . '"></i>';
          }
        }
        
        $items[] = $icons .
                   ((array_key_exists($i + 1, $breadcumbs) && $breadcumbs[$i]['href'] != 'none') ? '<a href="' . $breadcumbs[$i]['href'] . '">' . $breadcumbs[$i]['name'] . '</a>' : $breadcumbs[$i]['name']);
      }
      
      if ($items) {
        ob_start();
        ?><ul class="breadcrumb"><?php
        echo '<li>' . implode('<i class="icon-angle-right"></i></li><li>', $items) . '</li>';
        ?></ul><?php      
        return ob_get_clean();
      } else {
        return '';
      }
    }
    
    /**
     * Přidá položku do drobečkové navigace.
     * 
     * @param string $name
     * @param string $icon default ''
     * @param string $href default '#'
     */
    public static function addBreadcumb($name, $icon = '', $href = '#') {
      self::$_breadcumbs[] = array(
        'name' => $name,
        'icon' => (array)$icon,
        'href' => $href
      );
    }
    
    /**
     * Prints out the activity.
     * 
     * @param string $template
     * @param string $title
     * @param array $assigned
     */
    protected function printActivity($template, $title = Settings::WEB_NAME, $assigned = array(), $templateOnly = FALSE) {
      global $_MODULE, $_LOGGED_USER;
      
      // assign data into the template
      $assigned = $assigned + array('contentOnly' => FALSE);
      extract($assigned);

      $dir = 'modules/' . lcfirst(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1, -4));
      if ($templateOnly) {
        include "{$dir}/templates/{$template}.php";
      } else {
        include 'modules/admin/templates/include/header.php';
          include "{$dir}/templates/{$template}.php";
        include 'modules/admin/templates/include/footer.php';
      }
    }
    
    /**
     * Default behavior of following methods is throwing an exception.
     * @inherit
     */
    public function initial($arg = NULL)                        { throw new AppException('No initial activity is implemented yet.'); }
    public function detail(Entity $entity, $optional = array()) { throw new AppException('No detail activity is implemented yet.');  }
    public function manage(Entity $entity, $optional = array()) { throw new AppException('No manage activity is implemented yet.');  }
    public function table($data, $optional = array()) { throw new AppException('No table activity is implemented yet.');   }
    
    /**
     * Autoload of module resources.
     * 
     * @param string $includeResourceCallback
     * @param string $resourceRoot
     * @param string $titlePlural
     */
    private function includeModuleResource($includeResourceCallback, $resourceRoot, $titlePlural) {
      $reflection = new \ReflectionClass(get_called_class());
      $path = str_replace('\\', '/', dirname($reflection->getFileName()));
      
      $modulePath = array('', '', '');
      preg_match('|.*(modules/)(.*)|', $path, $modulePath);
      
      $resourcePath = $path . '/' . $resourceRoot . '/';
      $resourceUrl = Settings::ROOT_ADMIN_URL . $modulePath[1] . $modulePath[2] . '/' . $resourceRoot . '/';
      if (is_dir($resourcePath)) {
        echo PHP_EOL . '    <!-- BEGIN: ' . $titlePlural . ' of module: ' . $modulePath[2] . ' -->' . PHP_EOL;
        foreach (scandir($resourcePath) as $file) {
          if ($file != '.' && $file != '..') {
            echo View::$includeResourceCallback($file, $resourceUrl);
          }
        }
        echo '    <!-- END: ' . $titlePlural . ' of module: ' . $modulePath[2] . ' -->' . PHP_EOL;
      }
    }
    
    /**
     * Autoload of module javascripts.
     */
    public function includeModuleJS() {
      $this->includeModuleResource('htmlIncludeJS', 'js', 'javascripts');
    }
    
    /**
     * Autoload of module cascading style sheets.
     */
    public function includeModuleCSS() {
      $this->includeModuleResource('htmlIncludeCSS', 'css', 'cascading style sheets');
    }
    
    /**
     * Generate html include for java script file.
     * 
     * @param string $filename
     * @param string $dir
     * @return string
     */
    public static function htmlIncludeJS($filename, $dir = Settings::ADMIN_JS) {
      return '    <script src="' . $dir . $filename . '"></script>' . PHP_EOL;
    }
    
    /**
     * Generate html include for cascading style sheets file.
     * 
     * @param string $filename
     * @param string $dir
     * @return string
     */
    public static function htmlIncludeCSS($filename, $dir = Settings::ADMIN_CSS) {
      return '    <link type="text/css" rel="stylesheet" href="' . $dir . $filename . '" />' . PHP_EOL;
    }


    /**
     * Generates HTML code of the objects table.
     * 
     * @param Entity[] $data array of entities
     * @param array $definitions definitions of every column; structure:<br /><pre>
     *  array(
     *  &nbsp;&nbsp;array(
     *  &nbsp;&nbsp;&nbsp;&nbsp;'heading' => string,
     *  &nbsp;&nbsp;&nbsp;&nbsp;'value' => string|callback($dataitem),
     *  &nbsp;&nbsp;&nbsp;&nbsp;'class' => string|callback($dataitem)
     *  &nbsp;&nbsp;),
     *    ...
     *  )</pre>
     * @param array $itemDefinitions definitions of every row; structure:<br /><pre>
     *  array(
     *  &nbsp;&nbsp;'id' => callback($dataitem),
     *  &nbsp;&nbsp;'class' => callback($dataitem),
     *  &nbsp;&nbsp;'data' => array('name' => callback($dataitem), ...)
     *  )</pre>
     * @param array $actions definition of all items actions; structure (+ marks required items):<br /><pre>
     * array(
     *  &nbsp;&nbsp;+'query' => string,
     *  &nbsp;&nbsp;+'id_name' => string,
     *  &nbsp;&nbsp;+'id' => callback($dataitem),
     *  &nbsp;&nbsp;+'icon' => string,
     *  &nbsp;&nbsp;+'title' => string|callback($dataitem),
     *  &nbsp;&nbsp;'class' => string|callback($dataitem),
     *  &nbsp;&nbsp;'additionalHtml' => string|callback($dataitem),
     *  &nbsp;&nbsp;'hide' => callback($dataitem)
     * )</pre>
     * @param array $filters
     */
    public static function makeTable(array $data, array $definitions, array $itemDefinitions = array(),
                                     array $actions = array(), array $filters = array()) {
      // table heading
      $headingArr = $definitions;
      array_walk($headingArr, function(&$definition, $idx) {
        $definition = '<th class="column_' . ($idx + 1) . '">' . $definition['heading'] . '</th>';
      });
      $heading = implode('', $headingArr);
      
      // filters
      if ($filters) {
        
      }

      // table data - go through all items
      $rowsArr = array();
      foreach ($data as $item) {
        $cellsArr = array();
        foreach ($definitions as $idx => $definition) {     // go through cells
          $cellsArr[] = '<td class="column_' . ($idx + 1) . ((array_key_exists('class', $definition)) ? ' ' . ((is_callable($definition['class'])) ? $definition['class']($item) : $definition['class']) : '') . '">' .
                         ((is_callable($definition['value'])) ? $definition['value']($item) : $item->$definition['value']) .
                        '</td>';
        }
        
        // actions
        $rootUrl = Settings::ROOT_ADMIN_URL;
        $iconsUrl = Settings::ADMIN_ICONS;

        foreach ($actions as $action) {
          if (array_key_exists('hide', $actions) && $actions['hide']) {
            $cellsArr[] = <<<ACTION
              <td class="icon">&nbsp;</td>
ACTION;
          } else {
            $title = ((is_callable($action['title'])) ? $action['title']($item) : $action['title']);
            $class = ((array_key_exists('class', $action)) ?
                        ' ' . ((is_callable($action['class'])) ? $action['class']($item) : $action['class']) :
                        '');
            $additionalHtml = ((array_key_exists('additionalHtml', $action)) ?
                        ' ' . ((is_callable($action['additionalHtml'])) ? $action['additionalHtml']($item) : $action['additionalHtml']) :
                        '');
            
            $cellsArr[] = <<<ACTION
              <td class="icon{$class}">
                <a href="{$rootUrl}?{$action['query']}&{$action['id_name']}={$actions['id']($item)}" class="iconLink"{$additionalHtml}>
                  <img src="{$rootUrl}{$iconsUrl}{$action['icon']}" alt="{$title}" title="{$title}" />
                </a>
              </td>
ACTION;
          }
        }
        
        $id = ((array_key_exists('id', $itemDefinitions)) ? ' id="' . $itemDefinitions['id']($item) . '"' : '');
        $class = ((array_key_exists('class', $itemDefinitions)) ? ' class="' . $itemDefinitions['class']($item) . '"' : '');
        
        $htmlDataArr = array();
        if (array_key_exists('data', $itemDefinitions)) {
          foreach ($itemDefinitions['data'] as $name => $callback) {
            $htmlDataArr[] = ' data-' . $name . '="' . $callback($item) . '"';
          }
        }
        $htmlData = implode('', $htmlDataArr);

        $cells = implode('', $cellsArr);
        $rowsArr[] = <<<ROW
          <tr{$id}{$class}{$htmlData}>
            {$cells}
          </tr>
ROW;
      }
      
      $rows = implode(PHP_EOL, $rowsArr);
      return <<<TABLE
      <table>
        <thead>
          <tr>
            {$heading}
          </tr>
        </thead>
        <tbody>
          {$rows}
        </tbody>
      </table>
TABLE;
    }

    /**
     * Generates HTML code of selectbox created from given associative array.
     * 
     * @param array $data
     * @param string $selectedValue
     * @param string $name
     * @param string $id
     * @param bool $none
     * @param int $size
     * @param bool $multiple
     * @param bool $all
     * @param bool $disabled
     * @param string $additionalAttributes
     * 
     * @return string
     */
    public static function makeArraySelect(array $data, $selectedValue, $name, $id = '', $none = FALSE, $size = 1,
                                           $multiple = FALSE, $all = FALSE, $disabled = FALSE, $additionalAttributes = '') {
      // selectbox properties
      $id = (($id) ? ' id="' . $id . '"' : '');
      $multiple = (($multiple) ? ' multiple' : '');
      $disabled = (($disabled) ? ' disabled="disabled"': '');
      
      // special options
      $none = (($none) ? '<option value="">---</option>' : '');
      $all = (($all) ? '<option value="all"' . (($selectedValue == "all") ? ' selected' : '') . '>--- vše ---</option>' : '');
      
      // options
      array_walk($data, function(&$option, $value, $selected) {
        $option = '<option value="' . $value . '"' . (($value === $selected) ? ' selected' : '') . '>' . $option . '</option>';
      }, $selectedValue);

      $data = implode(PHP_EOL, $data);
      return <<<SELECT
        <select{$id} name="{$name}" size="{$size}"{$additionalAttributes}{$multiple}{$disabled}>
          {$none}
          {$data}
          {$all}
        </select>
SELECT;
    }
    
    /**
     * Generates HTML code of list created from given associative object.
     * 
     * @param array $data associative array value => option; option = show value or associative array: array('option' => 'show value', 'data' => array('name' => 'value', ...))
     * @param callback|string $valueGetter if callback, it has to get one parameter and return string
     * @param callback|string $showGetter if callback, it has to get one parameter and return string
     * @param string|array $selectedValues
     * @param string $name
     * @param string $defaultText [optional = '--- vyberte ---']
     * @param string $id [optional = '']
     * @param string $class [optional = '']
     * @param boolean $disabled [optional = FALSE]
     * @param string $htmlProperties [optional = '']
     * 
     * @return string
     */
    public static function makeListSelect(array $data, $valueGetter, $showGetter, $selectedValues, $name, $defaultText = '--- vyberte ---',
                                          $id = '', $class = '', $disabled = FALSE, $htmlProperties = '') {
      
      $values = $data;
      array_walk($values, function(&$item, $key, $getter) {
        $option = ((is_array($item)) ? $item['option'] : $item);
        $item = ((is_callable($getter)) ? $getter($option) : $option->$getter);
      }, $valueGetter);
      
      $options = $data;
      array_walk($options, function(&$item, $key, $getter) {
        if (is_array($item)) {
          $item['option'] = ((is_callable($getter)) ? $getter($item['option']) : $item['option']->$getter);
        } else {
          $item = ((is_callable($getter)) ? $getter($item) : $item->$getter);
        }
      }, $showGetter);
      
      return View::makeArrayListSelect(array_combine($values, $options), $selectedValues, $name, $defaultText, $id, $class, $disabled, $htmlProperties);      
    }
    
    /**
     * Generates HTML code of list created from given associative array.
     * 
     * @param array $data associative array value => option; option = show value or associative array: array('option' => 'show value', 'data' => array('name' => 'value', ...))
     * @param string|array $selectedValues
     * @param string $name
     * @param string $defaultText [optional = '--- vyberte ---']
     * @param string $id [optional = '']
     * @param string $class [optional = '']
     * @param boolean $disabled [optional = FALSE]
     * @param string $htmlProperties [optional = '']
     * 
     * @return string
     */
    public static function makeArrayListSelect(array $data, $selectedValues, $name, $defaultText = '--- vyberte ---',
                                               $id = '', $class = '', $disabled = FALSE, $htmlProperties = '') {
      // selectbox properties
      $id = (($id) ? ' id="' . $id . '"' : '');
      $class = (($class) ? ' class="' . $class . '"' : '');
      $disabled = (($disabled) ? ' data-disabled="disabled"': ''); 
      
      // options
      array_walk($data, function(&$option, $value, $selected) {
        $show = $option;
        
        $data = array('data-value="' . $value . '"', 'data-title="' . $show . '"');
        if (is_array($option)) {
          if (array_key_exists('data', $option)) {
            foreach ($option['data'] as $name => $dataval) {
              $data[] = 'data-' . $name . '="' . $dataval . '"';
            }
          }
          
          $show = $option['option'];
        }
        
        $option = '<li ' . implode(' ', $data) . ((((is_array($selected)) ? in_array($value, $selected): $value === $selected)) ? ' class="selected"' : '') . '>' . $show . '</li>';
      }, $selectedValues);

      $data = implode(PHP_EOL, $data);
      return <<<SELECT
        <ul{$id}{$class} data-name="{$name}" data-default-text="{$defaultText}" {$htmlProperties}{$disabled}>
          {$data}
        </ul>
SELECT;
    }
    
    /**
     * Generates HTML code of list created from given associative object.
     * 
     * @param array $data associative array value => option; option = show value or associative array: array('option' => 'show value', 'data' => array('name' => 'value', ...))
     * @param callback|string $valueGetter if callback, it has to get one parameter and return string
     * @param callback|string $showGetter if callback, it has to get one parameter and return string
     * @param string|array $selectedValues
     * @param string $name
     * @param string $defaultText [optional = '--- vyberte ---']
     * @param string $id [optional = '']
     * @param string $class [optional = '']
     * @param boolean $disabled [optional = FALSE]
     * @param string $htmlProperties [optional = '']
     * 
     * @return string
     */
    public static function makeSelect(array $data, $valueGetter, $showGetter, $selectedValues, $name, $defaultText = '--- vyberte ---',
                                          $id = '', $class = '', $disabled = FALSE, $htmlProperties = '') {
      
      $values = $data;
      array_walk($values, function(&$item, $key, $getter) {
        $option = ((is_array($item)) ? $item['option'] : $item);
        $item = ((is_callable($getter)) ? $getter($option) : $option->$getter);
      }, $valueGetter);
      
      $options = $data;
      array_walk($options, function(&$item, $key, $getter) {
        if (is_array($item)) {
          $item['option'] = ((is_callable($getter)) ? $getter($item['option']) : $item['option']->$getter);
        } else {
          $item = ((is_callable($getter)) ? $getter($item) : $item->$getter);
        }
      }, $showGetter);
      
      return View::makeArraySelectMulti(array_combine($values, $options), $selectedValues, $name, $defaultText, $id, $class, $disabled, $htmlProperties);      
    }
    
    /**
     * Generates HTML code of list created from given associative array.
     * 
     * @param array $data associative array value => option; option = show value or associative array: array('option' => 'show value', 'data' => array('name' => 'value', ...))
     * @param string|array $selectedValues
     * @param string $name
     * @param string $defaultText [optional = '--- vyberte ---']
     * @param string $id [optional = '']
     * @param string $class [optional = '']
     * @param boolean $disabled [optional = FALSE]
     * @param string $htmlProperties [optional = '']
     * 
     * @return string
     */
    public static function makeArraySelectMulti(array $data, $selectedValues, $name, $defaultText = '--- vyberte ---',
                                               $id = '', $class = '', $disabled = FALSE, $htmlProperties = '') {
      // selectbox properties
      $id = (($id) ? ' id="' . $id . '"' : '');
      $class = (($class) ? ' class="' . $class . '"' : '');
      $disabled = (($disabled) ? ' disabled="disabled"': ''); 
      
      // options
      
      array_walk($data, function(&$option, $value, $selected) {
        $show = $option;
        
        $data = array('value="' . $value . '"');
        if (is_array($option)) {
          if (array_key_exists('data', $option)) {
            foreach ($option['data'] as $name => $dataval) {
              $data[] = 'data-' . $name . '="' . $dataval . '"';
            }
          }
          
          $show = $option['option'];
        }
        
        $option = '<option ' . implode(' ', $data) . ((((is_array($selected)) ? in_array($value, $selected): $value === $selected)) ? ' selected="selected"' : '') . '>' . $show . '</option>';
      }, $selectedValues);
      
      $data = array_merge(array('<option></option>'), $data);
      $data = implode(PHP_EOL, $data);
      return <<<SELECT
        <select{$id}{$class} name="{$name}" placeholder="{$defaultText}" {$htmlProperties}{$disabled}>
          {$data}
        </select>
SELECT;
    }
    
    /**
     * Get las number of page
     * 
     * @param int $objCount
     * @param int $objectsPerPage
     * @return type
     */
    public static function getLastPageNumber($objCount, $objectsPerPage) {
      $division = intval($objCount / $objectsPerPage);

      return ((($objCount % $objectsPerPage) === 0) ? (($division) ? $division : 1) : $division + 1);
    }
    
    /**
     * Generating pagination 
     * 
     * @param int $objCount
     * @param array $getParams
     * @param int $objectsPerPage
     * @param int $pagesBeforeActualNumber
     * @param int $pagesAfterActualNumber
     * @param string $htmlClass
     * @param string $htmlId
     */
    public static function generatePaging($objCount, array $getParams, $objectsPerPage = 20, $pagesBeforeActualNumber = 3, $pagesAfterActualNumber = 3,
                                          $htmlClass = 'paging_bootstrap pagination', $htmlId = '', $showPageForm = TRUE) {
      
      $currentPage = ((array_key_exists('page', $getParams)) ? (int)$getParams['page'] : 1);
      $lastPage = self::getLastPageNumber($objCount, $objectsPerPage);

      $actualPage = min($currentPage, $lastPage);
      if (isset($getParams['page'])) { unset($getParams['page']); }

      $getParamsString = '';
      
      $getParamsString .= http_build_query($getParams);
      $getParamsString .= '&page=';
      
      $getParamsInputs = '';
      $InputsArr = array();

      function getNameRecursive($array, &$InputsArr, $keyOld = '') {
        foreach ($array as $key => $value) {
          if (is_array($value)) {
            $newKey = $keyOld . '[' . $key . ']';
            getNameRecursive($value, $InputsArr, $newKey);
          } else {
            $InputsArr[$keyOld . '[' . $key . ']'] = $value;
          }
        }
      }

      foreach ($getParams as $key => $value) {
        if (is_array($value)) {
          getNameRecursive($value, $InputsArr, $key);
        } else {
          $InputsArr[$key] = $value;
        }
      }

      foreach ($InputsArr as $key => $value) {
        $getParamsInputs .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
      }
      
      $html = '<div class="row-fluid pagination">';
      
      if ($showPageForm) {
        $html .= '<div class="span6">' .
                   '<div class="info_of_page">' .
                     '<form method="get">' .
                        $getParamsInputs .
                        'Strana <input class="span1 current_page m-wrap" type="number" name="page" value="' . $actualPage . '" min="1" max="' . $lastPage . '" /> z ' . $lastPage .
                     '</form>' .
                   '</div>' .
                 '</div>';
      } else {
        $html .= '<div class="span6"></div>';
      }
      
      $html .= '<div class="span6"><div' . (($htmlId) ? ' id="' . $htmlId . '"': '') . ' class="' . $htmlClass . '" style="float: right;">';
        $html .= '<ul>';
          // first page link
          $html .= "<li class=\"prev" . (($actualPage > 1) ? '' : ' disabled'). "\"><a " .(($actualPage > 1) ? "href=\"?{$getParamsString}1\"" : ''). ">";
            $html .= "<span class=\"hidden-480\">|<</span>";
          $html .= "</a></li>";

          // previous page link
          $previousPage = $actualPage - 1;
          $html .= "<li class=\"prev" . (($actualPage > 1) ? '' : ' disabled'). "\"><a " .(($actualPage > 1) ? "href=\"?{$getParamsString}{$previousPage}\"" : ''). ">";
            $html .= "<span class=\"hidden-480\"><</span>";
          $html .= "</a></li>";

          // dots before actual page
          $html .= (($actualPage > ($pagesBeforeActualNumber + 1)) ? '<li class="disabled"><span>...</span></li>' : '');

          // navigation before actual page
          $fromBeforeActualPage = ((($actualPage - $pagesBeforeActualNumber) <= 1) ? 1 : $actualPage - $pagesBeforeActualNumber);
          for ($i = $fromBeforeActualPage; $i < $actualPage; $i++) {
            $html .= "<li><a href=\"?{$getParamsString}{$i}\">$i</a></li>";
          }

          // actual page
          $html .= '<li class="active"><span>' . $actualPage . '</span></li>';

          // navigation after actual page
          $toAfterActualPage = ((($actualPage + $pagesBeforeActualNumber) >= $lastPage) ? $lastPage : $actualPage + $pagesBeforeActualNumber);
          for ($i = ($actualPage + 1); $i <= $toAfterActualPage; $i++) {
            $html .= "<li><a href=\"?{$getParamsString}{$i}\">$i</a></li>";
          }

          // dots after actual page
          $html .= (($actualPage < ($lastPage - $pagesBeforeActualNumber)) ? '<li class="disabled"><span>...</span></li>' : '');

          // next page link
          $nextPage = $actualPage + 1;
          $html .= "<li class=\"next" . (($actualPage < $lastPage) ? '' : ' disabled') . "\"><a " . (($actualPage < $lastPage) ? "href=\"?{$getParamsString}{$nextPage}\"" : '') . ">";
            $html .= "<span class=\"hidden-480\">></span>";
          $html .= "</a></li>";

          // last page link
          $html .= "<li class=\"next" . (($actualPage < $lastPage) ? '' : ' disabled') . "\"><a " . (($actualPage < $lastPage) ? "href=\"?{$getParamsString}{$lastPage}\"" : '') . ">";
            $html .= "<span class=\"hidden-480\">>|</span>";
          $html .= "</a></li>";

        $html .= '</div></div>';
      $html .= '</div>';

      return $html;
    }
  }
