<?php
  function val($array, $index) { return $array[$index]; }

  $_ID = 'xFbJQ5D7DYRaQyGr';      // vaše ID
  $_URL = 'http://service.styleplus.cz/get-products.php';     // adresa, kde běží služba

  $starttime = microtime(TRUE);
  if (filter_input(INPUT_GET, 'identification') === $_ID) {
    // Get cURL resource
    $curl = curl_init();
    
      // Set some options - we are passing in a useragent too here
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $_URL,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query(
          array(
            'identification' => $_ID,                     // identifikator firmy
            'modified_from' => '2000-01-01 00:00:00',     // jen vyrobky od tohoto data vcetne budou do vypisu zahrnuty
            'category_type' => 'category'                 // typ vypisu kategorie: [category / path] (category - vypise jen kategorii, path - vypise celou cestu)
          )
        )
      ));
      // Send the request & save response to $resp
      $response = curl_exec($curl);
    
    // Close request to clear up some resources
    curl_close($curl);

    // print XML out
    header('Content-type: text/xml; charset=UTF-8');
    echo $response;
    exit();
    
    // save it into the file
//    file_put_contents('../../../../xml/mitacek15.xml', $response);
  } else {
    echo 'Vaše id nesedí.';
  }
  $endtime = microtime(TRUE);

  echo 'Doba zpracovani: ' . round($endtime - $starttime, 6) . ' sekund';
  exit();
