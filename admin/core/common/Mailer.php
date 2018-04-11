<?php
  namespace prosys\core\common;
  
  require_once dirname(__FILE__) . '/../../resources/libs/swift-mailer/swift_required.php';

  /**
   * This class serves for sending emails
   * 
   * @author Pavel Filípek
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class Mailer
  {
    const TYPE_HTML = 'text/html';
    const TYPE_PLAIN = 'text/plain';
    
    /**
     * Function for send email by function mail
     * 
     * @param string $subject subject of email
     * @param string $message text of email
     * @param array $from sender array('example@google.com' => 'Test sender')
     * @param array $to recipient array('test@google.com', 'example@google.com' => 'Test sender', ...)
     * @param array $cc recipient array('test@google.com', 'example@google.com' => 'Test sender', ...)
     * @param array $bcc recipient array('test@google.com', 'example@google.com' => 'Test sender', ...)
     * @param string $type text/html, text/plain
     * @param MailerAttachment[] $attachments
     * 
     * @return boolean
     */
    public static function sendMail($subject, $messageText, $from, $to, array $cc = array(), array $bcc = array(), $type = self::TYPE_HTML, array $attachments = array()) {
      
      // create the Transport
      $transport = \Swift_SmtpTransport::newInstance('localhost', 25);
      
      // create the Mailer using your created Transport
      $mailer = \Swift_Mailer::newInstance($transport);
      
      $messageText = <<<MESSAGE
  <html>
  <head>
    <meta charset="utf-8" />
    <title>{$subject}</title>
  </head>
  <body>
    {$messageText}
  </body>
  </html>
MESSAGE;
      
      
      // Create a message
      $message = \Swift_Message::newInstance($subject)
        ->setFrom((($from) ? $from : array(Settings::MAIL_SENDER => Settings::WEB_NAME)))
        ->setTo($to)
        ->setBody($messageText, $type);
      
      // copy and blind copy
      if ($cc)  { $message->setCc($cc);   }
      if ($bcc) { $message->setBcc($bcc); }
      
      // attachments
      foreach ($attachments as /* @var $attachment MailerAttachment */ $attachment) {
        /* @var $mailAttachment \Swift_Attachment */
        $mailAttachment = (($attachment->getPath()) ? \Swift_Attachment::fromPath($attachment->getPath()) : \Swift_Attachment::newInstance());
        $mailAttachment->setFilename($attachment->getName())
                       ->setContentType($attachment->getContentType());
        
        if (!is_null($attachment->getData())) {
          $mailAttachment->setBody($attachment->getData());
        }
        
        $message->attach($mailAttachment);
      }
      
      if ($mailer->send($message)) {
        return TRUE;
      }
      
      return FALSE;
    }
  }
