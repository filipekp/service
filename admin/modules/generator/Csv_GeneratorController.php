<?php
  namespace prosys\admin\controller;

use PHPExcel_IOFactory,
  PHPExcel_Writer_CSV,
  prosys\core\common\Functions AS Fn;

  /**
   * Processes the users requests.
   * 
   * @author Pavel Filípek <www.filipek-czech.cz>
   * @copyright (c) 2017, Proclient s.r.o.
   */
  class Csv_GeneratorController extends Excel_GeneratorController
  {
    public function __construct($fileName = NULL) {
      parent::__construct($fileName);
    }

    public function generate() {
      parent::generate();
    }

    protected function save() {
      // Redirect output to a client’s web browser (Excel2007)
      header('Content-Type: application/csv');
      header('Content-Disposition: attachment; filename="' . Fn::seoTypeConversion($this->generateExcelName) . '.csv"');
      header('Cache-Control: max-age=0');

      /* @var $writer PHPExcel_Writer_CSV */
      $writer = PHPExcel_IOFactory::createWriter($this->_EXCEL, 'CSV');
      $writer->setSheetIndex(0);
      $writer->setDelimiter(';');
      $writer->save('php://output');
    }
  }

