<?php
    // Get cURL resource
    $curl = curl_init();
    
      // set some options
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://service.styleplus.cz/get-products.php',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query(
          array(
            'identification' => 'hV47v1GLO4btGL7S',       // identifikator firmy
            'modified_from' => '2000-01-01 00:00:01',     // jen vyrobky od tohoto data vcetne budou do vypisu zahrnuty
            'category_type' => 'category'                 // typ vypisu kategorie: [category / path] (category - vypise jen kategorii, path - vypise celou cestu)
          )
        )
      ));
      // Send the request & save response to $resp
      $response = curl_exec($curl);
    
    // Close request to clear up some resources
    curl_close($curl);
    
    // save it into the file
    header('Content-type: text/xml; charset=UTF-8');
    echo $response;
    exit();
