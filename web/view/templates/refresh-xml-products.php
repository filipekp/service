<?php
  use prosys\core\common\Agents,
      prosys\core\common\Settings;
  
  define('HASH', 'cCB2SUQGtYNNG2HNyCKJsJt4h2AXtBB3');
  if (filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_STRING) === HASH) {
    /* @var $skczHandler \prosys\core\common\MySqlConnection */
    $skczHandler = Agents::getAgent(
      'MySqlConnection',
      Agents::TYPE_COMMON,
      [
        Settings::SKCZ_DB_SERVER,
        Settings::SKCZ_DB_USER,
        Settings::SKCZ_DB_PASSWORD,
        Settings::SKCZ_DB_DATABASE,
        Settings::SKCZ_DB_PREFIX
      ],
      'SKCZ_MySqlConnection'
    );
    
    $skczHandler->callProcedureSelect('refreshXmlProducts');

    exit('<h1>OK</h1>');
  } else {
    header('Status: 401');
    header('HTTP/1.1 401 Unauthorized');
    
    exit('<h1>ACCESS DENIED</h1>');
  }
