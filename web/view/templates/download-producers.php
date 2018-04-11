<?php
  use prosys\core\common\Agents,
      prosys\core\common\Settings;
  
  define('HASH', 'AASwaZ7bHdFScNJkD3UCePRuE9nKBsfN8C9aek4G');
  if (filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_STRING) === HASH) {
    /* @var $skczHandler \prosys\core\common\MySqlConnection */
    $skczHandler = Agents::getAgent(
      'MySqlConnection', Agents::TYPE_COMMON,
      [Settings::SKCZ_DB_SERVER, Settings::SKCZ_DB_USER, Settings::SKCZ_DB_PASSWORD, Settings::SKCZ_DB_DATABASE, Settings::SKCZ_DB_PREFIX],
      'SKCZ_MySqlConnection'
    );
    
    /* @var $serviceHandler \prosys\core\common\MySqlConnection */
    $serviceHandler = Agents::getAgent(
      'MySqlConnection', Agents::TYPE_COMMON,
      [Settings::DB_SERVER, Settings::DB_USER, Settings::DB_PASSWORD, Settings::DB_DATABASE, Settings::DB_PREFIX],
      'Service_MySqlConnection'
    );
    
    // stahne vyrobce ze stylovych koupelen
    $skczHandler->select(
      ['id', 'kod_s3', 'nazev', 'poradi'], 'vyrobci',
      [
        'where'     => 'mimo_shop = ? AND odstranen = ? AND nezobrazovat = ?',
        'bindings'  => ['0', '0', '0']
      ]
    );

    // projde vyrobce a ulozi je do databaze service.styleplus.cz
    try {
      foreach ($skczHandler->fetchObjects() as $producer) {
        $serviceHandler->insert('producers', [
          'id'          => $producer->id,
          'code'        => $producer->kod_s3,
          'name'        => $producer->nazev,
          'sort_order'  => $producer->poradi,
        ], TRUE);
      }
    } catch (Exception $e) {
      exit("<h1>DOSLO K CHYBE</h1><p>{$e->getCode()} ({$e->getLine()}: {$e->getFile()}): {$e->getMessage()}</p><p>{$e->getTraceAsString()}</p>");
    }

    exit('<h1>OK</h1>');
  } else {
    header('Status: 401');
    header('HTTP/1.1 401 Unauthorized');
    
    exit('<h1>ACCESS DENIED</h1>');
  }
