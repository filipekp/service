<?php
  $starttime = microtime(TRUE);
  
  use prosys\core\common\Agents;
  
  $partnerDao = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);  /* @var $partnerDao prosys\model\PartnerDao */
  $partner = $partnerDao->load('9kRQJugRJDzLiijB');                  /* @var $partner prosys\model\PartnerEntity */

  var_dump($partner->getProductPricesFromOrders());

  $endtime = microtime(TRUE);

  echo str_repeat(PHP_EOL, 3) . 'Doba zpracovani: ' . round($endtime - $starttime, 6) . ' sekund';
  exit();
