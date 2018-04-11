<?php
  use prosys\core\common\Agents,
      prosys\core\mapper\SqlFilter;
  
  /**
   * Rekurzivne vygeneruje XML kategorii.
   * 
   * @param \DOMElement $parent
   * @param prosys\model\SKCZ_CategoryEntity[] $children
   */
  function joinChildrenRecursive(\DOMElement $parent, $children, \DOMDocument $xml, $nameSuffix) {
    foreach ($children as $child) {
      $categoryXML = $xml->createElement('Category');
        $categoryXML->appendChild($xml->createElement('Name', $child->{'name' . $nameSuffix}));
        
        $children = $xml->createElement('Children');
        if ($child->children) {
          joinChildrenRecursive($children, $child->children, $xml, $nameSuffix);
        }

        $categoryXML->appendChild($children);
      $parent->appendChild($categoryXML);
    }
  }
  
  /* @var $partnerDao \prosys\model\PartnerDao */
  $partnerDao = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);
  
  /* @var $partner prosys\model\PartnerEntity */
  $partner = $partnerDao->load($_IDENTIFICATION);
  $nameSuffix = (($partner->styleplusPartner->engine == 'SK') ? 'SK' : 'CZ');

  $_XML = new DOMDocument('1.0', 'utf-8');
    $_ROOT = $_XML->createElement('ResponseData');
      $_ROOT->appendChild($_XML->createElement('DateTime', date('Y-m-dTH:i:s')));
      
      if ($partner->isNew()) {
        $status = $_XML->createElement('Status');
        $status->appendChild($_XML->createElement('Code', 401));
        $status->appendChild($_XML->createElement('Message', 'ACCESS DENIED'));

        $_ROOT->appendChild($status);
      } else {
        /* @var $dao prosys\model\SKCZ_CategoryDao */
        $dao = Agents::getAgent('SKCZ_CategoryDao', Agents::TYPE_MODEL);
        
        joinChildrenRecursive(
          $_ROOT,
          $dao->loadRecords(
            SqlFilter::create()->comparise('id_rodice', '=', '0'),
            [['column' => 'poradi', 'direction' => 'ASC']]
          ),
          $_XML,
          $nameSuffix
        );
      }
    $_XML->appendChild($_ROOT);
  
  // return XML on output
  header('Content-type: text/xml; charset=UTF-8');

  $_XML->formatOutput = TRUE;
  echo $_XML->saveXML();
  exit();
