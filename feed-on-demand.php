<?php
  /* STARTS SESSION */
  session_start();
  
  /* SET MB_ENCODING  */
  mb_internal_encoding('UTF-8');
  
  /* AUTOLOAD */
  require_once 'web/__load__.php';
  
  /* USING NAMESPACE */
  use prosys\core\common\Agents,
      prosys\model\PartnerEntity,
      prosys\core\common\Settings,
      prosys\core\common\Functions,
      prosys\core\common\Mailer;
  
  define('FILE_ROOT', __DIR__ . '/xml/demanded/');
  define('FILE_EXPIRATION', 12);
  define('DOWNLOAD_FREQUENCY', 4);
  define('POST_EXPIRATION', 10);
  
  // promaze expirovane soubory
  $expiration = strtotime('-' . FILE_EXPIRATION . ' hours');
  foreach (glob(FILE_ROOT . '*') as $file) {
    if (!is_dir($file) && filemtime($file) < $expiration) {
      unlink($file);
    }
  }
  
  // stahne identifikaci
  $_IDENTIFICATION = filter_input(INPUT_GET, 'identification');
  
  // reloadne 10 minut stary post - nahodne odeslani postu po otevreni prohlizece
  if (($generatedAt = filter_input(INPUT_POST, 'generated_at')) && $generatedAt < strtotime('-10 minutes')) {
    header("Location: http://service.styleplus.cz/FeedOnDemand/{$_IDENTIFICATION}");
    exit();
  }

  /* @var $partnerDao \prosys\model\PartnerDao */
  $partnerDao = Agents::getAgent('PartnerDao', Agents::TYPE_MODEL);
  
  /* @var $partner \prosys\model\PartnerEntity */
  $partner = $partnerDao->load($_IDENTIFICATION);
  
  // vystup
  header('Content-type: text/html; charset=UTF-8');
  
  if ($partner->isNew() || $partner->type == PartnerEntity::TYPE_REGULAR) {
    header('HTTP/1.0 401 Unauthorized', FALSE, 401);

    echo '<h1>Nepovolený přístup</h1>';
    echo '<h2>Máte-li k dispozici vygenerovaný odkaz pro vygenerování feedu na vyžádání, použijte jej, prosím.<br />V opačném případě si zažádejte o vygenerování funkčního odkazu k Vašemu feedu.</h2>';
    exit();
  } else if ($email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
    define('URL', 'http://service.styleplus.cz/get-products.php?identification=%s&output_type=%s');
    
    $outputType = Functions::item($_POST, 'output', 'excel');
    $ext = (($outputType == 'excel') ? 'xlsx' : $outputType);
    
    $filename = Functions::seoTypeConversion($partner->name) . '.' . $ext;
    $filepath = FILE_ROOT . $filename;

    ob_start();
      echo '
<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Proclient s.r.o., Aksamitova 1071/1, 779 00 Olomouc, http://www.proclient.cz, e-mail: info@proclient.cz" />

    <title>Vygenerování feedu na vyžádání</title>
    <link type="image/x-icon" rel="shortcut icon" href="http://admin.styleplus.cz/admin/resources/images/favicon.ico" />
        
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous" />
    <link rel="stylesheet" href="http://service.styleplus.cz/web/view/css/styles.css" />

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </head>

  <body>
    <div id="feed-on-demand" class="container">
      <h1>Požadavek byl zaregistrován</h1>
      <h4>Můžete zavřít prohlížeč. Po vygenerování Vám bude odeslán e-mail s odkazem pro stažení souboru.</h4>
    </div>

    <footer class="footer">
      <div class="container">
        <span class="text-muted">&copy; STYLE PLUS s.r.o.</span>
      </div>
    </footer>
  </body>
</html>';
    ob_flush();
    flush();
    ob_end_clean();

    // smaze expirovany soubor
    if (file_exists($filepath) && filemtime($filepath) < strtotime('-' . DOWNLOAD_FREQUENCY . ' hours')) {
      unlink($filepath);
    }
    
    
    // pokud soubor neexistuje, je znovu vygenerovan
    if (!file_exists($filepath)) {
      $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(URL, $partner->hashCode, $outputType));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
      curl_close($ch);			
        
      file_put_contents(
        $filepath,
//        file_get_contents(sprintf(URL, $partner->hashCode, $outputType))
        $result
      );

      chmod($filepath, 0775);
    }
    
    
    $url = "http://service.styleplus.cz/vyzadany-feed/{$filename}";
    Mailer::sendMail(
      'Vyžádaný odkaz na stažení feedu společnosti STYLE PLUS s.r.o.',
      "<b>Požadovaný soubor byl úspěšně vygenerován a uložený na serveru. Platnost souboru je " . FILE_EXPIRATION . " hodin.</b><br /><br />
       Soubor můžete stáhnout kliknutím na tento odkaz: <a href=\"{$url}\">{$url}</a><br /><br />
       Na zprávu neodpovídejte. Zpráva byla automaticky vygenerována serverem service.styleplus.cz.",
      ['service@styleplus.cz' => 'Služby STYLE PLUS s.r.o.'],
      [$email => $partner->name],
      [],
      ['podpora@proclient.cz' => 'Podpora PRO CLIENT s.r.o.']
    );
    
    exit();
  }
?>
<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Proclient s.r.o., Aksamitova 1071/1, 779 00 Olomouc, http://www.proclient.cz, e-mail: info@proclient.cz" />

    <title>Vygenerování feedu na vyžádání</title>
    <link type="image/x-icon" rel="shortcut icon" href="http://admin.styleplus.cz/admin/resources/images/favicon.ico" />
        
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous" />
    <link rel="stylesheet" href="http://service.styleplus.cz/web/view/css/styles.css" />

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
  </head>

  <body>
    <div id="feed-on-demand" class="container">
      <h1><?= $partner->name ?></h1>
      <h2>Vygenerování feedu na vyžádání</h2>

      <form method="post">
        <div class="form-body">
          <div class="row">
            <div class="col-12 col-md-12 form-group">
              <label for="email">E-mailová adresa  <span class="badge badge-primary">(povinné pole)</span></label>
              <div class="input-group">
                <span class="input-group-addon" id="basic-addon1">@</span>
                <input id="email" class="form-control" type="email" name="email" value="<?= $partner->user->email ?>" aria-describedby="emailHelp" placeholder="jmeno@domena.cz" aria-label="E-mailová adresa" aria-describedby="basic-addon1" required />
              </div>

              <small id="emailHelp" class="form-text text-muted">
                Zadejte e-mailovou adresu, na kterou bude odeslán vygenerovaný odkaz pro stažení feedu.
              </small>
            </div>
          </div>

          <div class="row">
            <div class="col-4 col-md-4 form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="radio" name="output" value="excel" checked="checked" />
                Excel (.xlsx)
              </label>
            </div>
            <div class="col-4 col-md-4 form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="radio" name="output" value="csv" />
                CSV (.csv)
              </label>
            </div>
          </div>
        </div>
      
        <div class="form-actions text-right">
          <input type="hidden" name="generated_at" value="<?= time() ?>" />
          <input class="btn btn-success" type="submit" value="Vyžádat feed" />
        </div>
      </form>
    </div>

    <footer class="footer">
      <div class="container">
        <span class="text-muted">&copy; STYLE PLUS s.r.o.</span>
      </div>
    </footer>
  </body>
</html>