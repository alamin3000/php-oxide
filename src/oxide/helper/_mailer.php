<?php
namespace oxide\helper;
use Zend\Mail\Message as MailMessage;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail;


class _mailer_message {
   public
      $fromEmail = null,
      $fromName = null,
      $bodyText = null,
      $bodyHtml = null,
      $subject = null;
           
   protected 
      $emails = [];
   
   
   public function add($type, $email, $name = null) {
      $this->emails[$type][] = [$email, $name];
   }
   
   public function set($type, $emails) {
      if(is_array($emails)) {
         foreach($emails as $email) {
            if(is_array($email)) {
               list($email, $name) = $email;
               $this->add($type, $email, $name);
            } else {
               $this->add($type, $email);
            }
         }
      } else {
         $this->add($type, $emails);
      }
   }
   
   public function addTo($email, $name = null) {
      $this->add(_mailer::TO, $email, $name);
   }
   
   public function addCC($email, $name = null) {
      $this->add(_mailer::CC, $email, $name);
   }
   
   public function addBCC($email, $name = null) {
      $this->add(_mailer::BCC, $email, $name);
   }
   
   public function getTos() {
      return (isset($this->emails[_mailer::TO])) ? $this->emails[_mailer::TO] : null;
   }
   
   public function getCCs() {
      return (isset($this->emails[_mailer::CC])) ? $this->emails[_mailer::TO] : null;
   }
   
   public function getBCCs() {
      return (isset($this->emails[_mailer::BCC])) ? $this->emails[_mailer::TO] : null;      
   }
   
   public function clear($type = null) {
      if(isset($this->emails[$type])) {
         $this->emails[$type] = null;
         unset($this->emails[$type]);
      }
   }
}

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
    * Creates an empty email message object
    * 
    * @return \oxide\helper\_mailer_message
    */
   public static function create_message() {
      return new _mailer_message();
   }
   
   /**
    * sends mail using Zend framework
    *
    * 
    * @param string|array $from Email address or associative array Name => Email
    * @param string|array $to Email address or associative array Name => Email. Does not support 
    * @param string $subject
    * @param string $message
    * @param mixed $attach
    */
   public static function send($from, $tos, $subject, $message, $smtp_auth = null, $attach = null)
   {      
      $text = new MimePart(strip_tags($message));
      $text->type = 'text/plain';
      
      $html = new MimePart($message);
      $text->type = 'text/html';
      
      $body = new MimeMessage();
      $body->setParts(array($text, $html));
      
      
      $mail = new MailMessage();
      $mail->setBody($body);
      
      if(is_array($tos)) {
         foreach($tos as $to) {
            if(is_array($to)) {
               list($temail, $tname) = $to;
               $mail->addTo($temail, $tname);
            } else {
               $mail->addTo($to);
            }
         }
      } else {
         $mail->setTo($tos);
      }
      
      
      
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
         return $tos;
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