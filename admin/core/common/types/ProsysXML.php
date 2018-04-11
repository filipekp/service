<?php
  namespace prosys\core\common\types;
  
  /**
   * Objekt reprezentujici datovy typ XML.<br />
   * Zahrnuje XMLWriter (rychlejsi, nez DOM s mensimi naroky na pamet), ktery ale umi "sve" XML, po uzavreni, prohledavat pomoci xpath.
   *
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   * 
   * @todo implementovat XPath
   */
  class ProsysXML extends \XMLWriter {
    const INDENT_STRING = '  ';
    const STD_OUTPUT = 'php://output';
    
    private $_closed = FALSE;
    private $_outputTouched = FALSE;
    
    private $_output;
    private $_data;
    
    /**
     * Nastavi vychozi hodnoty XMLWriteru.
     * 
     * @param string $output vychozi stream je do pameti, je-li vyzadovan vystup primo na obrazovku, pouzit 'php://output', v pripade, ze cesta k souboru neexistuje, bude vytvorena
     * @param string $root nazev korenoveho uzlu
     * @param string $version
     * @param string $charset
     */
    public function __construct($output = NULL, $root = 'ResponseData', $version = '1.0', $charset = 'utf-8', $prettyPrint = TRUE) {
      // nastavi vystup
      if (is_null($output)) {
        $this->openMemory();
      } else {
        // kdyz soubor neexistuje, vytvori jej
        if ($output != self::STD_OUTPUT && !file_exists($output)) {
          if (!file_exists(dirname($output))) {
            mkdir(dirname($output), 0777, TRUE);
          }
          
          touch($output);
          $output = realpath($output);
          
          $this->_outputTouched = TRUE;
        }

        $this->openURI($output);
      }
      
      $this->_output = $output;

      // nastavi pretty print
      $this->setIndent($prettyPrint);
      $this->setIndentString(self::INDENT_STRING);
      
      // zacatek dokumentu s korenovym uzlem
      $this->startDocument($version, $charset);
        $this->startElement($root);
    }
    
    /**
     * Vytvori "jednoduchy" uzel do streamu tohoto XMLWriteru.
     * 
     * @param string $node
     * @param string $content
     * @param array $attributes
     */
    public function createSimpleElement($node, $content, array $attributes = [], $cdata = FALSE) {
      $this->startElement($node);
        foreach ($attributes as $key => $value) {
          $this->writeAttribute($key, $value);
        }
      
        if ($cdata) {
          $this->writeCdata($content);
        } else {
          $this->text($content);
        }
      $this->endElement();
    }
    
    /**
     * Uzavre XML dokument.
     * 
     * @return mixed je-li vystup do pameti, vrati XML, v opacnem pripade pocet zapsanych bytu
     */
    public function close() {
      $this->_closed = TRUE;
      
        $this->endElement();
      $this->endDocument();

      $this->_data = $this->flush();
      return $this->_data;
    }
    
    /**
     * Je-li vystup nejaky soubor, je mozne jej z tohoto souboru nacist.
     * 
     * @return string validni XML
     */
    public function load() {
      if (!is_null($this->_output) && $this->_output != self::STD_OUTPUT) {
        $this->_data = file_get_contents($this->_output);
      }
      
      return $this->_data;
    }
    
    /**
     * Zjisti, zda vystup neexistoval -> je-li touched, pak neexistoval.
     * @return bool
     */
    public function wasOutputTouched() {
      return $this->_outputTouched;
    }
  }