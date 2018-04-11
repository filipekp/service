<?php
  namespace prosys\core\common;
  use prosys\core\common\Settings;

  /**
   * Global settings of the application.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class Functions
  {
    /**
     * Zpracuje zpravu ulozenou v session pro admin.
     * 
     * @param string $type
     * @return string
     */
    public static function handleMessagesAdmin($type = NULL) {
      // handle message of one type
      $handleMessage = function($type) {
        if (array_key_exists($type, $_SESSION) && $_SESSION[$type]) {
          $sessionMsg = $_SESSION[$type];

          if (is_array($sessionMsg)) {
            $message = '<ul><li>' . addslashes(implode('</li><li>', $sessionMsg)) . '</li></ul>';
          } else {
            if ($type === Settings::MESSAGE_EXCEPTION) {
              $message = '<ul>' . $sessionMsg . '</ul>';
            } else {
              $message = '<ul><li>' . addslashes($sessionMsg) . '</li></ul>';
            }
          }
          
          unset($_SESSION[$type]);
          
          return array($type => addslashes($message));
        }
        
        return array();
      };
      
      // handle messages
      if (is_null($type)) {
        return "<script>getStatusMessage('" . json_encode(
               array_merge($handleMessage(Settings::MESSAGE_ERROR),
               $handleMessage(Settings::MESSAGE_EXCEPTION),
               $handleMessage(Settings::MESSAGE_WARNING),
               $handleMessage(Settings::MESSAGE_INFO),
               $handleMessage(Settings::MESSAGE_SUCCESS))) .
               "');</script>";
      } else {
        return $handleMessage($type);
      }
    }
    
    /**
     * Zpracuje zpravu ulozenou v session pro front end.
     * 
     * @param string $type
     * @return string
     */
    public static function handleMessagesWeb($type = NULL) {
      // handle message of one type
      $handleMessage = function($type) {
        if (array_key_exists($type, $_SESSION) && $_SESSION[$type]) {
          $sessionMsg = $_SESSION[$type];
          
          switch ($type) {
            case Settings::MESSAGE_INFO:
              $class = 'message_info';
              $admClass = 'info';
            break;
            case Settings::MESSAGE_EXCEPTION:
              $class = 'message_exception';
              $admClass = 'error';
            break;
            case Settings::MESSAGE_WARNING:
              $class = 'message_warning';
              $admClass = 'warning';
            break;
            case Settings::MESSAGE_SUCCESS:
              $class = 'message_success';
              $admClass = 'success';
            break;
            case Settings::MESSAGE_ERROR:
            default:
              $class = 'message_error';
              $admClass = 'error';
            break;
          }

          if (is_array($sessionMsg)) {
            $message = '<ul class="' . $class . '"><li>' .
                       addslashes(implode('</li><li>', $sessionMsg)) .
                       '</li></ul>';
          } else {
            if ($type === Settings::MESSAGE_EXCEPTION) {
              $message = '<ul class="' . $class . '">' .
                         addslashes($sessionMsg) .
                         '</ul>';
            } else {
              $message = '<ul class="' . $class . '"><li>' .
                         addslashes($sessionMsg) .
                         '</li></ul>';
            }
          }
          
          unset($_SESSION[$type]);

          return addslashes($message);
        }
      };
      
      // handle messages
      if (is_null($type)) {
        return "<script>getStatusMessage('" .
               $handleMessage(Settings::MESSAGE_ERROR) .
               $handleMessage(Settings::MESSAGE_EXCEPTION).
               $handleMessage(Settings::MESSAGE_WARNING) .
               $handleMessage(Settings::MESSAGE_INFO) .
               "');</script>";
      } else {
        return $handleMessage($type);
      }
    }
    
    /**
     * Converts date into the storage (resp. human friendly) format.
     * 
     * @param string $date PHP date string
     * @param bool $toStorage
     * 
     * @return string
     */
    public static function dateConvert($date, $toStorage = TRUE) {
      $format = (($toStorage) ? 'Y-m-d' : 'd.m.Y' );
      return (($date && !is_null($date)) ? date($format, strtotime($date)) : '');
    }
    
    /**
     * Return the first element of the array.
     * 
     * @param array $param
     * return mixed
     */
    public static function first(array $param, $getKey = FALSE) {
      $value = reset($param);      
      return (($getKey) ? key($param) : (($value) ? $value : NULL));
    }
    
    /**
     * Trims the array.
     * 
     * @param array $array
     */
    public static function trimArray(array $array) {
      array_walk_recursive($array, function(&$item) {
        if (is_string($item)) {
          $item = trim($item);
        }
      });
      
      return $array;
    }
    
    /**
     * Zkontroluje, zda je objekt pozadovaneho typu.
     * 
     * @param object $object
     * @param string $type
     * 
     * @return bool
     */
    public static function isType($object, $type) {
      switch ($type) {
        case 'string':  return is_string($object);
        case 'int':
        case 'integer': return is_int($object);
        case 'float':
        case 'double':  return is_float($object);
        case 'bool':
        case 'boolean': return is_bool($object);

        default:        return is_a($object, $type);
      }
    }
    
    /**
     * Recasts given value to required data type.
     * 
     * @param mixed $object
     * @param string $type
     * 
     * @return mixed
     */
    public static function retype($object, $type) {
      switch (strtolower($type)) {
        case 'datetime':  return ((is_string($object)) ? new \DateTime(date('Y-m-d H:i:s', strtotime($object))) : new \DateTime());
        case 'int':
        case 'integer':   return (int)$object;
        case 'float':
        case 'double':
        case 'real':      return (float)((is_string($object)) ? str_replace(',', '.', $object) : $object);
        case 'bool':
        case 'boolean':   return (bool)$object;
        case 'array':     return (array)$object;
        case 'object':    return (object)$object;
        case 'null':      return (unset)$object;

        default:          return (string)$object;
      }
    }
    
    /**
     * Pretypuje objekt na entitu.
     * 
     * @param object $object
     * @param string $type
     * 
     * @return mixed
     * @throws AppException jestlize dany typ neni podporovan
     */
    public static function retypeToEntity($object, $type) {
      /* @var $dao \prosys\model\DataAccessObject */
      $dao = Agents::getAgent(str_replace('Entity', 'Dao', $type), Agents::TYPE_MODEL);
      $type = Agents::getNamespace(Agents::TYPE_MODEL) . $type;

      // pro prazdny objekt vygeneruje prazdnou instanci entity
      if (!$object) {
        $object = $dao->load();
      }
      
      // je-li treba pretypovat
      if (!is_a($object, $type)) {
        $object = $dao->load(array(Settings::SHOW_DELETED_PREDICATE => TRUE, $type::PRIMARY_KEY() => $object));
        if ($object->isNew()) {
          throw new AppException("Entity '{$type}' failed to be created from the object '{$object}'.");
        }
      }

      return $object;
    }
    
    /**
     * Prevede objekt na textovy retezec.
     * 
     * @param mixed $object
     * @param mixed $caller
     * 
     * @return string
     * @throws AppException jestlize se nepodarilo objekt prevest
     */
    public static function toString($object, $caller = NULL) {
      $objectType = gettype($object);
      $caller = ((is_null($caller)) ? '' : get_class($caller) . ': ');
      
      switch ($objectType) {
        case 'boolean':
          $object = (int)$object;
        case 'integer':
        case 'double':
        case 'string': return (string)$object;
        case 'NULL':   return 'NULL';
        case 'array':  return implode('|', array_map(array($object, 'toString'), $object));

        case 'object':
          if (is_a($object, 'DateTime')) {
            return $object->format('Y-m-d H:i:s');
          } else {
            try {
              $rc = new \ReflectionClass($object);

              if ($rc->hasMethod('__toString')) {
                return (string)$object;
              } else {
                throw new AppException($caller . "The object of type '{$rc->getName()}' has not implemented __toString method.");
              }
            } catch (\ReflectionException $e) { printf('%s', $e->getMessage()); }
          }

        default:
          // entita by misto: get_class($caller) mela zavolat get_class($caller)::classname()
          throw new AppException($caller . "The type '" . $objectType . "' of the object cannot be converted to the string.");
      }
    }
    
    /**
     * Array filter recursive.
     * 
     * @param mixed $input
     * @param function $callback
     * 
     * @return mixed
     */
    public static function array_filter_recursive($input, $callback = null) { 
      foreach ($input as &$value) { 
        if (is_array($value)) { 
          $value = array_filter_recursive($value, $callback); 
        } 
      } 

      return array_filter($input, $callback); 
    }
    
    /**
     * Returns a string composed by entire words specified by length.
     * 
     * @param string $string
     * @param int $limit
     * 
     * @return string
     */
    public static function trimEntireWords($string, $limit = 40) {
      // return string when lenght is < then limit
      if (mb_strlen($string, 'UTF-8') < $limit) {
        return $string;
      }

      $regex = '/(.{1, $limit})\b/';
      $matches = array('', '');
      
      preg_match($regex, $string, $matches);
      return $matches[1] . ' ...';
    }
    
    /**
     * Remove or add key to query string.
     * 
     * @param string $query
     * @param array $remove
     * @param array $add
     * 
     * @return string
     */
    public static function modifyHttpQuery($query, $remove = array(), $add = array()) {
      $parsed = array();
      parse_str($query, $parsed);
      
      // remove wanted
      $removed = array_diff_key($parsed, array_flip($remove));
      $added = $removed + $add;

      return http_build_query($added);
    }
    
    /**
     * Odstrani diakritiku z retezce.
     * 
     * @param string $text
     * @return string
     */
    public static function removeDiacritics($text) {
      // remove diacritics
      $conversionTable = Array(
        'ä'=>'a', 'Ä'=>'A', 'á'=>'a', 'Á'=>'A', 'à'=>'a', 'À'=>'A', 'ã'=>'a', 'Ã'=>'A', 'â'=>'a', 'Â'=>'A', 'ą'=>'a', 'Ą'=>'A', 'ă'=>'a', 'Ă'=>'A',
        'č'=>'c', 'Č'=>'C', 'ć'=>'c', 'Ć'=>'C', 'ç'=>'c', 'Ç'=>'C',
        'ď'=>'d', 'Ď'=>'D', 'đ'=>'d', 'Đ'=>'D',
        'ě'=>'e', 'Ě'=>'E', 'é'=>'e', 'É'=>'E', 'ë'=>'e', 'Ë'=>'E', 'è'=>'e', 'È'=>'E', 'ê'=>'e', 'Ê'=>'E', 'ę'=>'e', 'Ę'=>'E',
        'í'=>'i', 'Í'=>'I', 'ï'=>'i', 'Ï'=>'I', 'ì'=>'i', 'Ì'=>'I', 'î'=>'i', 'Î'=>'I',
        'ľ'=>'l', 'Ľ'=>'L', 'ĺ'=>'l', 'Ĺ'=>'L', 'ł'=>'l', 'Ł'=>'L',
        'ń'=>'n', 'Ń'=>'N', 'ň'=>'n', 'Ň'=>'N', 'ñ'=>'n', 'Ñ'=>'N',
        'ó'=>'o', 'Ó'=>'O', 'ö'=>'o', 'Ö'=>'O', 'ô'=>'o', 'Ô'=>'O', 'ò'=>'o', 'Ò'=>'O', 'õ'=>'o', 'Õ'=>'O', 'ő'=>'o', 'Ő'=>'O',
        'ř'=>'r', 'Ř'=>'R', 'ŕ'=>'r', 'Ŕ'=>'R',
        'š'=>'s', 'Š'=>'S', 'ś'=>'s', 'Ś'=>'S', 'ş'=>'s', 'Ş'=>'S',
        'ť'=>'t', 'Ť'=>'T', 'ţ'=>'t', 'Ţ'=>'T',
        'ú'=>'u', 'Ú'=>'U', 'ů'=>'u', 'Ů'=>'U', 'ü'=>'u', 'Ü'=>'U', 'ù'=>'u', 'Ù'=>'U', 'ũ'=>'u', 'Ũ'=>'U', 'û'=>'u', 'Û'=>'U', 'ű'=>'u', 'Ű'=>'U',
        'ý'=>'y', 'Ý'=>'Y',
        'ž'=>'z', 'Ž'=>'Z', 'ź'=>'z', 'Ź'=>'Z', 'ż'=>'z', 'Ż'=>'Z'
      );
      
      return strtr($text, $conversionTable);
    }
    
    /**
     * Conversion text to lower charakter, remove diacritics with delimiter.
     * 
     * @param string $text
     * @param string $delimiter
     * @return string
     */
    public static function seoTypeConversion($text, $delimiter = '-') {
      $noDiacritics = self::removeDiacritics($text);

      // convert to lower
      $lower = strtolower($noDiacritics);

      // replace everything with dashes - excluding upper letters, lower letters, numbers and dashes
      $pattern = '/[^0-9a-zA-Z' . $delimiter . ']/';
      $multipleDelimiters = preg_replace($pattern, $delimiter, $lower);

      // replace multiple dashes to one dash
      $seo = preg_replace('/' . str_replace('.', '\\.', $delimiter) . '+/', $delimiter, $multipleDelimiters);

      // return dash trimmed seo
      return trim($seo, $delimiter);
    }
    
    /**
     * Is date holiday return TRUE else FALSE.
     * 
     * @param type $date
     * @return bolean
     */
    public static function isHoliday($date) {
      $time = strtotime($date);
      $isWeekend = function($time) {
        return (date('N', $time) >= 6);
      };

      $isEaster = function($time) {
        return $time == (easter_date(date('Y', $time)) + 86400);
      };

      $isHoliday = FALSE;
      $holidays = array(
        array(1,1,'Den obnovy samostatného českého státu'),
        array(1,5,'Svátek práce'),
        array(8,5,'Den vítězství'),
        array(5,7,'Den slovanských věrozvěstů Cyrila a Metoděje'),
        array(6,7,'Den upálení mistra Jana Husa'), 
        array(28,9,'Den české státnosti'),
        array(28,10,'Den vzniku samostatného československého státu'),
        array(17,11,'Den boje za svobodu a demokracii'),
        array(24,12,'Štědrý den'),
        array(25,12,'1. svátek vánoční'),
        array(26,12,'. svátek vánoční')
      );
      foreach ($holidays as $holiday) {
        if ($holiday[0] . '.' . $holiday[1] . '.' == date('j.n.', $time)) {
          $isHoliday = TRUE;
          break;
        }
      }   
      return ($isHoliday || $isWeekend($time) || $isEaster($time));
    }

    /**
     * Add work day by input.
     * 
     * @param string $date
     * @param int $numOfDays (+1 or -2)
     * @return int
     */
    public static function addBusinessDays($date, $numOfDays) {
      $direction = (($numOfDays < 0) ? '-' : '+');
      $date = date('Y-m-d', strtotime($direction . abs($numOfDays) . 'days', strtotime($date)));

      while (self::isHoliday($date) === TRUE) {
        $date = date('Y-m-d', strtotime($direction . '1days', strtotime($date)));
      }
      return strtotime($date);
    }
    
    /**
     * According to given locale prints out the number in the price format.
     * 
     * @param float $number
     * @param string $locale
     * @param int $decimals
     * 
     * @return string
     */
    public static function priceFormat($number, $locale = 'cs_CZ', $decimals = NULL) {      
      switch ($locale) {
        case 'sk_SK':
          $decimals = ((is_null($decimals)) ? Settings::PRICE_DECIMALS : $decimals);
          return number_format(round($number, $decimals), $decimals, ',', ' ') . ' €';
        case 'cs_CZ':
        default:
          $decimals = ((is_null($decimals)) ? Settings::PRICE_DECIMALS : $decimals);
          return number_format(round($number, $decimals), $decimals, ',', ' ') . ' Kč';
      }
    }
      
    /**
     * Recalculates numbers into the percents, to create one hundred unit in sum.<br />
     * Preserves associative array keys.
     * 
     * @param array $data
     * @param float $total [optional=100] the number to which should be recalculated (default value is used for percents)
     * @param int $round [optional=FALSE]
     * 
     * @return array
     */
    public static function recalculateByRatio($data, $total = 100, $round = FALSE) {
      $sum = array_sum($data);
      
      if ($sum) {
        $fraction = $total / $sum;
        $returnArray = array_map(function($item) use($fraction, $round) {
          $item = $fraction * $item;
          return (($round === FALSE) ? $item : round($item, $round));
        }, $data);
        
        // adjustment of the output field to the sum of the values ​​was 100
        $sumResult = array_sum($returnArray);
        $difference = abs($total - $sumResult);
        if ($sumResult != $total) {
          if ($sumResult > $total) {
            $maxKey = array_keys($returnArray, max($returnArray));
            $returnArray[$maxKey[0]] = $returnArray[$maxKey[0]] - $difference;
          } else if ($sumResult < $total) {
            $minKey = array_keys($returnArray, min($returnArray));
            $returnArray[$minKey[0]] = $returnArray[$minKey[0]] + $difference;
          }
        }
        
        return $returnArray;
      } else {
        return $data;
      }
    }
    
    /**
     * Function inflection by number
     * 
     * @param float $number
     * @param type $allOther word form for number greater than 5 or equal 0
     * @param type $once word form for number equal 1
     * @param type $oneToFive word form for number greater than 1 or smaller than 5
     * @param type $float word form for float number
     * 
     * @return string '$number word form' => '1 hrnek'
     */
    public static function inflection($number, $allOther, $once, $oneToFive, $float) {
        $string = '';
        if ((int)$number != $number) {
          $string .= $float;
        } elseif ($number == 0 OR $number >= 5) {
          $string .= $allOther;
        } elseif ($number == 1) {
          $string .= $once;
        } elseif ($number < 5) {
          $string .= $oneToFive;
        }

        return $string;
    }
    
    /**
     * Vygeneruje náhodný řetězec pro zadanou délku.
     * 
     * @param int $length
     * @return string
     * 
     * @example <pre>randomString(16, array('charlist' => '$#'))</pre>
     */
    public static function randomString($length = 16, $config = array('numbers' => TRUE, 'lcase' => TRUE, 'ucase' => TRUE, 'special' => FALSE, 'charlist' => '')) {
      $default = array('numbers' => TRUE, 'lcase' => TRUE, 'ucase' => TRUE, 'special' => TRUE, 'charlist' => '');
      $config = array_merge($default, $config);
      
      // string pro generovani
      $pool = array($config['charlist']);
      
      if ($config['numbers']) { $pool[] = '0123456789';                }
      if ($config['lcase'])   { $pool[] = 'abcdefghijkmnopqrstuvwxyz'; }
      if ($config['ucase'])   { $pool[] = 'ABCDEFGHJKLMNOPQRSTUVWXYZ'; }
      if ($config['special']) { $pool[] = '*-+@()/';          }
      
      $charlist = implode('', $pool);

      // zde bude výsledný řetězec uložen
      $resString = '';
      for ($i = 0; $i < $length; $i++) {
          $resString .= substr($charlist, mt_rand(0, strlen($charlist) -1), 1);
      }

      return $resString;
    }
    
    /**
     * "Bezpecne" odebere prvek z pole -> zkontroluje existenci.
     * 
     * @param array $array
     * @param string $key
     * 
     * @return mixed
     */
    public static function unsetItem(array &$array, $key) {
      if (array_key_exists($key, $array)) {
        $value = ((is_object($array[$key])) ? clone $array[$key] : $array[$key]);
        unset($array[$key]);
        
        return $value;
      }
      
      return NULL;
    }
    
    /**
     * Zjisti, zda pozadovany offset existuje v danem objektu.
     * 
     * @param mixed $offset
     * @param mixed $object
     * 
     * @return bool
     */
    private static function offsetExists($offset, $object) {
      return ((is_a($object, '\ArrayAccess')) ? $object->offsetExists($offset) : ((is_array($object)) ? array_key_exists($offset, $object) : FALSE));
    }
    
    /**
     * "Bezpecne" ziska prvek z pole (celou cestu) -> zkontroluje existenci.
     * 
     * @param array|\ArrayAccess $array
     * @param string|array $path
     * @param mixed $default
     * 
     * @return mixed
     */
    public static function item($array, $path, $default = NULL) {
      $current = $array;
      foreach ((array)$path as $key) {
        if (self::offsetExists($key, $current)) {
          $current = $current[$key];
        } else {
          return $default;
        }
      }

      return $current;
    }
    
    /**
     * Rekurzivne prohleda prvek a najde pozadovanou hodnotu.
     * 
     * @param mixed $needle
     * @param mixed $haystack
     * @param array $callbacks pole obsahujici callback pro ziskani hodnoty (identita jako vychozi) a callback pro ziskani potomku (klic 'children' jako vychozi)
     * 
     * @return mixed vrati nalezeny prvek, v pripade, ze prvek nenalezne, vrati FALSE
     */
    public static function arraySearchRecursive($needle, $haystack, array $callbacks = ['value' => NULL, 'children' => NULL]) {
      $identity = function($item) { return $item; };
      $children = function($item) { return $item['children']; };
      
      $callbacks['value'] = ((is_null($callbacks['value'])) ? $identity : $callbacks['value']);
      $callbacks['children'] = ((is_null($callbacks['children'])) ? $children : $callbacks['children']);
      
      foreach($haystack as $item) {
        if ($callbacks['value']($item) === $needle) {
          return $item;
        } elseif (($recursive = self::arraySearchRecursive($needle, $callbacks['children']($item), $callbacks)) !== FALSE) {
          return $recursive;
        }
      }
      
      return FALSE;
    }
    
    /**
     * Vrati IP adresy proxy serveru - existuje-li a je-li odlisna od adresy klienta.
     * 
     * @return string
     */
    public static function getProxyIpAddress() {
      $proxyIp = (string)Functions::item($_SERVER, 'HTTP_CLIENT_IP');
      $remoteAddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
      
      if (!$proxyIp) {
        $proxyIp = (string)Functions::item($_SERVER, 'HTTP_X_FORWARDED_FOR');
      }
      
      if (!$proxyIp) {
        $proxyIp = (string)Functions::item($_SERVER, 'HTTP_VIA');
      }

      return (($proxyIp == $remoteAddr) ? '' : $proxyIp);
    }
  
  
  
    /**
     * Vytvoří rekurzivně adresářovou strukturu.
     *
     * @param string $dir
     * @param bool   $recursive
     */
    public static function mkDir($dir, $recursive = TRUE) {
      $r = FALSE;
      $oldUmask = umask(0);
      $r = mkdir($dir, 0777, TRUE);
      umask($oldUmask);
    
      return $r;
    }
  
    /**
     * Smaže složku včetně podložek a souborů.
     *
     * @param $dirPath
     */
    public static function deleteDir($dirPath) {
      if (!is_dir($dirPath)) { throw new InvalidArgumentException("{$dirPath} must be a directory"); }
      if (substr($dirPath, strlen($dirPath) -1, 1) != '/') { $dirPath .= '/'; }
    
      $files = glob($dirPath . '*', GLOB_MARK);
      foreach ($files as $file) {
        if (is_dir($file)) {
          self::deleteDir($file);
        } else {
          unlink($file);
        }
      }
    
      rmdir($dirPath);
    }
  }
