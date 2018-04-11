<?php
  namespace prosys\admin\controller;
  
  require_once __DIR__ . '/../../resources/libs/phpexcel/PHPExcel.php';

  use PHPExcel,
      PHPExcel_CachedObjectStorageFactory,
      PHPExcel_Settings,
      PHPExcel_Style_Alignment as PSA,
      prosys\core\common\Settings,
      prosys\core\common\Functions as Fn;

  /**
   * Processes the users requests.
   * 
   * @author Pavel Filípek <www.filipek-czech.cz>
   * @copyright (c) 2017, Proclient s.r.o.
   */
  class Excel_GeneratorController extends General_GeneratorController
  {
    private $lang = NULL;
    protected $generateExcelName = '';
    private $currentRow = 1;
    
    protected $_EXCEL = NULL;

    public function __construct($fileName = NULL) {
      parent::__construct($fileName);
      
      if ($this->partner->type != \prosys\model\PartnerEntity::TYPE_OLD) {
        $this->sendAccessDenied();
      }
      
      $this->lang = strtolower($this->partnerEngine);
      $this->generateExcelName = 'Product export for ' . Fn::seoTypeConversion($this->partner->name) . ' (' . $this->_NOW->format('Ymd Hi') . ')';
      
      $this->allColumnsDefinition[self::COLUMN_GUARANTEE]['alignment'] = [ 'horizontal' => PSA::HORIZONTAL_CENTER ];
      $this->allColumnsDefinition[self::COLUMN_STOCK]['alignment'] = [ 'horizontal' => PSA::HORIZONTAL_CENTER ];
      $this->allColumnsDefinition[self::COLUMN_MODIFIED_AT]['alignment'] = [ 'horizontal' => PSA::HORIZONTAL_CENTER ];
      $this->allColumnsDefinition[self::COLUMN_REMOVED]['alignment'] = [ 'horizontal' => PSA::HORIZONTAL_CENTER ];
      
      // Initialize cache of PHPExcel
      PHPExcel_Settings::setCacheStorageMethod(
        PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp,
        [ 'memoryCacheSize' => '2048MB' ]
      );
      $this->_EXCEL = new PHPExcel();
      $this->_EXCEL->getProperties()
        ->setCreator(Settings::WEB_NAME)
        ->setLastModifiedBy(Settings::WEB_NAME)
        ->setTitle($this->generateExcelName)
        ->setSubject($this->generateExcelName)
        ->setDescription('StylePlus s.r.o. products offer list export for partner ' . Fn::seoTypeConversion($this->partner->name))
        ->setKeywords('product export')
        ->setCategory('Product export');
      
      // global settings
      $this->_EXCEL->setActiveSheetIndex(0)
        ->setTitle('Export produktů')
        ->freezePane('A2')
        ->getStyle('1')->applyFromArray([
            'fill' => [
              'type' => \PHPExcel_Style_Fill::FILL_SOLID,
              'color' => [ 'rgb' => 'C6EFCE' ]
            ],
            'font' => [
              'size' => 13,
              'color' => [ 'rgb' => '006100' ],
              'bold' => TRUE
            ],
            'alignment' => [
              'horizontal' => PSA::HORIZONTAL_CENTER,
              'vertical' => PSA::VERTICAL_CENTER
            ]
        ]);
    }
    
    /**
     * Přidá řádek excelu.
     * 
     * @param array $rowData
     */
    private function addLine($rowData) {
      $columnLetter = 'A';
      foreach ($rowData as $cell) {
        $currentCell = $columnLetter . $this->currentRow;
        $cell = ((is_array($cell)) ? $cell : [ 'value' => $cell ]);
        
        $cellType = Fn::item($cell, ['type'], NULL);
        $activeSheet = $this->_EXCEL->getActiveSheet();
        if (is_null($cellType)) {
          $cellExcel = $activeSheet->setCellValue($currentCell, $cell['value'], TRUE);
        } else {
          $cellExcel = $activeSheet->setCellValueExplicit($currentCell, $cell['value'], Fn::item($cell, ['type'], \PHPExcel_Cell_DataType::TYPE_STRING), TRUE);
        }
        if (($cellAlignment = Fn::item($cell, ['alignment']))) {
          $cellExcel
            ->getStyle()->applyFromArray([ 'alignment' => $cellAlignment ]);
        }
        
        $columnLetter++;
      }
      
      $this->currentRow++;
    }
    
    /**
     * Přidá řádek produktu.
     * 
     * @param \DOMNode $product
     * @param array $parentData
     */
    private function addProductLine($product, $parentData = []) {
      $rowData = [];
      foreach ($this->constraints['columns'] as $column => $columnData) {
        $cell = [];
        
        // najde a zapise hodnotu sloupce
        $node = $this->_XPATH->query($columnData['column'], $product);
        $value = (($node->length) ? $node->item(0)->nodeValue : '');
        
        if (!$value) {
          $value = Fn::item($parentData, $columnData['column']);
        }
        
        $cell['value'] = $value;
        $cell['type'] = ((in_array($column, [self::COLUMN_CATALOG, self::COLUMN_EAN])) ? \PHPExcel_Cell_DataType::TYPE_STRING : \PHPExcel_Cell_DataType::TYPE_STRING);
        $cell['alignment'] = Fn::item($columnData, 'alignment', FALSE);
        
        $rowData[] = $cell;
      }
      
      $this->addLine($rowData);
    }
    
    /**
     * Uloží excel ke klientovi.
     */
    protected function save() {
      // Redirect output to a client’s web browser (Excel2007)
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . Fn::seoTypeConversion($this->generateExcelName) . '.xlsx"');
      header('Cache-Control: max-age=0');

      $writer = \PHPExcel_IOFactory::createWriter($this->_EXCEL, 'Excel2007');
      $writer->save('php://output');
    }

    public function generate() {
      parent::generate();
      
      // zapise hlavicku souboru
      $this->addLine(array_map(function($column) {
        return ((is_array($column)) ? Fn::item($column, ['heading', $this->lang]) : $column);
      }, $this->constraints['columns']));
     
      // zapis vyrobku
      foreach ($this->_XPATH->query('//ProductSet/Product') as $product) {
        // zjisti, zda se jedna o vyrobek bez variant
        $node = $this->_XPATH->query($this->allColumnsDefinition[self::COLUMN_CATALOG]['column'], $product);
        $catalog = (($node->length) ? $node->item(0)->nodeValue : NULL);

        if ($catalog) {
          $this->addProductLine($product);
        } else { // nebo s variantami
          $categoryNode = $this->_XPATH->query($this->allColumnsDefinition[self::COLUMN_CATEGORY]['column'], $product);
          $producerNode = $this->_XPATH->query($this->allColumnsDefinition[self::COLUMN_PRODUCER]['column'], $product);
          $serieNode = $this->_XPATH->query($this->allColumnsDefinition[self::COLUMN_SERIE]['column'], $product);
          $descriptionNode = $this->_XPATH->query($this->allColumnsDefinition[self::COLUMN_DESCRIPTION]['column'], $product);

          $parentData = [
            $this->allColumnsDefinition[self::COLUMN_CATEGORY]['column']    => (($categoryNode->length) ?    $categoryNode->item(0)->nodeValue    : ''),
            $this->allColumnsDefinition[self::COLUMN_PRODUCER]['column']    => (($producerNode->length) ?    $producerNode->item(0)->nodeValue    : ''),
            $this->allColumnsDefinition[self::COLUMN_SERIE]['column']       => (($serieNode->length) ?       $serieNode->item(0)->nodeValue       : ''),
            $this->allColumnsDefinition[self::COLUMN_DESCRIPTION]['column'] => (($descriptionNode->length) ? $descriptionNode->item(0)->nodeValue : ''),
          ];

          foreach ($this->_XPATH->query('Variants/Variant', $product) as $variant) {
            $this->addProductLine($variant, $parentData);
          }
        }
      }
      
      // automaticke šířky sloupců
      $columnLetter = 'A';
      for ($i = 1; $i <= count($this->constraints['columns']); $i++) {
        $this->_EXCEL->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(TRUE);
        $columnLetter++;
      }
      
      $this->save();
    }
  }

