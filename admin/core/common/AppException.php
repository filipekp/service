<?php
  namespace prosys\core\common;

  /**
   * Exception extends Exception only because of need to create Exception with the array of messages.
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class AppException extends \Exception
  {
    public static $DELIMITER_TYPE = 'list';
    
    /**
     * Initializes messages.
     * @param string|array $messages
     */
    public function __construct($message, $code = 0, \Exception $previous = null) {
      $message = (array)$message;

      switch (self::$DELIMITER_TYPE) {
        case 'new_line':
          $message = implode('<br />', $message);
        break;

        case 'new_line_strong':
          $message = '<b>' . implode('<br />', $message) . '</b>';
        break;
      
        case 'list':
        default:
          array_walk($message, function(&$item, $key) {
            $item = "<li data-key=\"{$key}\">{$item}</li>";
          });

          $message = implode('', $message);
        break;
      }

      // call parent's constructor
      parent::__construct($message, $code, $previous);
    }
  }
