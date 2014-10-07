<?php
namespace oxide\helper;
use Zend\Mail\Message as MailMessage;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail;


/**
 * Helper class to send email
 *
 * @package oxide
 * @subpackage util
 */
abstract class _mailer
{   
   const
      TO = 'To',
      REPLY_TO = 'Reply-To',
      BCC = 'Bcc',
      CC = 'Cc';
  
   /**
    * sends mail
    * $to array keys 'to', 'bcc', 'cc'
    *    each of these keys can have array as value
    *
    * @param mixed $from
    * @param array $to
    * @param string $subject
    * @param string $message
    * @param mixed $attach
    */
   public static function send($from, $to, $subject, $message, $smtp_auth = null, $attach = null)
   {      
      $text = new MimePart(strip_tags($message));
      $text->type = 'text/plain';
      
      $html = new MimePart($message);
      $text->type = 'text/html';
      
      $body = new MimeMessage();
      $body->setParts(array($text, $html));
      
      
      $mail = new MailMessage();
      $mail->setBody($body);
      $mail->setTo($to);
      $mail->setFrom($from);
      $mail->setSubject($subject);
      $mail->setEncoding('UTF-8');
     
      
      if($smtp_auth) {
         
      } else {
         $transporter = new Sendmail();
      }
      
      return $transporter->send($mail);
   }
   
   /**
    * Send mail using default mail() function
    * 
    * $tos param can be in follwoing
    *    string   ie.   'someone@domain.com', or 'Some one <someone@domain.com>'
    *    array    ie.   array('someone@domain.com, 'Other <other@domain.com>'
    *    array    ie.   array(
    *                      'to' => 'someone@domain.com',
    *                      'replay' => 'replayhere@domain.com',
    *                      'cc'  => 'other@domain.com',
    *                      'bcc' => array('one@domina.com', 'two@domain.com')
    *                      )
    *                   
    * $message can be string or array.  If array, it must follow the follwoing
    *    array(
    *       'text' => 'some plain text',
    *       'html' => 'some formatted <b>message</b>'
    *       );
    * @param string $from
    * @param mixed $tos
    * @param string $subject
    * @param mixed $message
    * @param type $attach
    * @return type
    */
   public static function mail($from, $tos, $subject, $html = null, $plain = null)
   {
      $boundary = md5(time());
      $plain_text = $plain;
      $html_text = $html;
      
      $to = self::generateToAddressString($tos);      

      $headers = array(
         'MIME-Version: 1.0',
         'From: ' . $from,
         "Content-Type: multipart/alternative; boundary={$boundary}"
      );
         
      self::additionalToHeaders($tos, $headers);
         
      $body = array(
         "--{$boundary}",
         'Content-Type: text/plain; charset=ISO-8859-1',
         false,
         $plain_text,
         false,
         "--{$boundary}",
         'Content-Type: text/html; charset=ISO-8859-1',
         false,
         $html_text,
         false,
         "--{$boundary}--"
      );

      return mail($to,$subject,implode("\r\n",$body),implode("\r\n",$headers));
   }
   
   protected static function additionalToHeaders($tos, &$headers)
   {
      if(!is_array($tos)) {
         return;
      }
      
      $tokeys = array(self::REPLY_TO, self::BCC, self::CC);
      foreach($tokeys as $tokey) {
         if(isset($tos[$tokey])) {
         $replytos = (array) $tos[$tokey];
         $headers[] = "{$tokey}: " . implode(',', $replytos);
         }
      }
   }


   protected static function generateToAddressString($tos)
   {
      $toarray = array();
      
      if(!is_array($tos)) {
         $toarray[] = $tos;
         goto done;
      }
      
      if(isset($tos[self::TO])) {
         // this is complex array
         $topart = $tos[self::TO];
         if(is_array($topart)) {
            goto toarray;
         } else {
            $toarray[] = $topart;
            goto done;
         }
      }
      
      toarray:
      foreach ($tos as $ato) {
         $toarray[] = $ato;
      }
      
      done:
         if(empty($toarray)) {
            throw new \Exception('To address information not found/valid');
         }
         return implode(', ', $toarray);
   }
}