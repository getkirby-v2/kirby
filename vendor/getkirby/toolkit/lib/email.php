<?php

/**
 * Email
 *
 * A simple email handling class which supports
 * multiple email services. Check out the email subfolder
 * for all available services
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Email extends Obj {

  const ERROR_INVALID_RECIPIENT = 0;
  const ERROR_INVALID_SENDER = 1;
  const ERROR_INVALID_REPLY_TO = 2;
  const ERROR_INVALID_SUBJECT = 3;
  const ERROR_INVALID_BODY = 4;
  const ERROR_INVALID_SERVICE = 5;
  const ERROR_DISABLED = 6;

  public static $defaults = array(
    'service' => 'mail',
    'options' => array(),
    'to'      => null,
    'from'    => null,
    'replyTo' => null,
    'subject' => null,
    'body'    => null
  );

  public static $services = array();
  public static $disabled = false;

  public $error;
  public $service;
  public $options;
  public $to;
  public $from;
  public $replyTo;
  public $subject;
  public $body;

  public function __construct($params = array()) {
    $options = a::merge(static::$defaults, $params);
    parent::__construct($options);
  }

  public function __set($key, $value) {
    $this->$key = $value;
  }

  /**
   * Validates the constructed email
   * to make sure it can be sent at all
   */
  public function validate() {
    if(!v::email($this->extractAddress($this->to)))      throw new Error('Invalid recipient', static::ERROR_INVALID_RECIPIENT);
    if(!v::email($this->extractAddress($this->from)))    throw new Error('Invalid sender', static::ERROR_INVALID_SENDER);
    if(!v::email($this->extractAddress($this->replyTo))) throw new Error('Invalid reply address', static::ERROR_INVALID_REPLY_TO);
    if(empty($this->subject))    throw new Error('Missing subject', static::ERROR_INVALID_SUBJECT);
    if(empty($this->body))       throw new Error('Missing body', static::ERROR_INVALID_BODY);
  }

  /**
   * Public getter for the error exception
   *
   * @return Exception
   */
  public function error() {
    return $this->error;
  }

  /**
   * Extracts the email address from an address string
   *
   * @return string
   */
  protected function extractAddress($string) {
    if(v::email($string)) return $string;
    preg_match('/<(.*?)>/i', $string, $array);
    return (empty($array[1])) ? $string : $array[1];
  }

  /**
   * Sends the constructed email
   *
   * @param array $params Optional way to set values for the email
   * @return boolean
   */
  public function send($params = null) {

    try {

      // fail silently if sending emails is disabled
      if(static::$disabled) throw new Error('Sending emails is disabled', static::ERROR_DISABLED);

      // overwrite already set values
      if(is_array($params) && !empty($params)) {
        foreach(a::merge($this->toArray(), $params) as $key => $val) {
          $this->set($key, $val);
        }
      }

      // reset all errors  
      $this->error = null;

      // default service
      if(empty($this->service)) $this->service = 'mail';

      // if there's no dedicated reply to address, use the from address
      if(empty($this->replyTo)) $this->replyTo = $this->from;

      // validate the email
      $this->validate();

      // check if the email service is available
      if(!isset(static::$services[$this->service])) {
        throw new Error('The email service is not available: ' . $this->service, static::ERROR_INVALID_SERVICE);
      }

      // run the service
      call(static::$services[$this->service], $this);

      // reset the error
      $this->error = null;
      return true;

    } catch(Exception $e) {
      $this->error = $e;
      return false;
    }

  }

}


/**
 * Default mail driver
 */
email::$services['mail'] = function($email) {

  $headers = array(
    'From: ' . $email->from,
    'Reply-To: ' . $email->replyTo,
    'Return-Path: ' . $email->replyTo,
    'Message-ID: <' . time() . '-' . $email->from . '>',
    'X-Mailer: PHP v' . phpversion(),
    'Content-Type: text/plain; charset=utf-8',
    'Content-Transfer-Encoding: 8bit',
  );

  ini_set('sendmail_from', $email->from);
  $send = mail($email->to, str::utf8($email->subject), str::utf8($email->body), implode(PHP_EOL, $headers));
  ini_restore('sendmail_from');

  if(!$send) {
    throw new Error('The email could not be sent');
  }

};

/**
 * Amazon mail driver
 */
email::$services['amazon'] = function($email) {

  if(empty($email->options['key']))    throw new Error('Missing Amazon API key');
  if(empty($email->options['secret'])) throw new Error('Missing Amazon API secret');

  $setup = array(
    'Action'                           => 'SendEmail',
    'Destination.ToAddresses.member.1' => $email->to,
    'ReplyToAddresses.member.1'        => $email->replyTo,
    'ReturnPath'                       => $email->replyTo,
    'Source'                           => $email->from,
    'Message.Subject.Data'             => $email->subject,
    'Message.Body.Text.Data'           => $email->body
  );

  $params = array();

  foreach($setup as $key => $value) {
    $params[] = $key . '=' . str_replace('%7E', '~', rawurlencode($value));
  }

  sort($params, SORT_STRING);

  $host      = a::get($email->options, 'host', 'email.us-east-1.amazonaws.com');
  $url       = 'https://' . $host . '/';
  $date      = gmdate('D, d M Y H:i:s e');
  $signature = base64_encode(hash_hmac('sha256', $date, $email->options['secret'], true));
  $query     = implode('&', $params);
  $headers   = array();
  $auth      = 'AWS3-HTTPS AWSAccessKeyId=' . $email->options['key'];
  $auth     .= ',Algorithm=HmacSHA256,Signature=' . $signature;

  $headers[] = 'Date: ' . $date;
  $headers[] = 'Host: ' . $host;
  $headers[] = 'X-Amzn-Authorization: '. $auth;
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';

  $email->response = remote::post($url, array(
    'data'    => $query,
    'headers' => $headers
  ));

  if(!in_array($email->response->code(), array(200, 201, 202, 204))) {
    throw new Error('The mail could not be sent!', $email->response->code());
  }

};

/**
 * Mailgun mail driver
 */
email::$services['mailgun'] = function($email) {

  if(empty($email->options['key']))    throw new Error('Missing Mailgun API key');
  if(empty($email->options['domain'])) throw new Error('Missing Mailgun API domain');

  $url  = 'https://api.mailgun.net/v2/' . $email->options['domain'] . '/messages';
  $auth = base64_encode('api:' . $email->options['key']);

  $headers = array(
    'Accept: application/json',
    'Authorization: Basic ' . $auth
  );

  $data = array(
    'from'       => $email->from,
    'to'         => $email->to,
    'subject'    => $email->subject,
    'text'       => $email->body,
    'h:Reply-To' => $email->replyTo,
  );

  $email->response = remote::post($url, array(
    'data'    => $data,
    'headers' => $headers
  ));

  if($email->response->code() != 200) {
    throw new Error('The mail could not be sent!');
  }

};

/**
 * Postmark mail driver
 */
email::$services['postmark'] = function($email) {

  if(empty($email->options['key'])) throw new Error('Invalid Postmark API Key');

  // reset the api key if we are in test mode
  if(a::get($email->options, 'test')) $email->options['key'] = 'POSTMARK_API_TEST';

  // the url for postmarks api
  $url = 'https://api.postmarkapp.com/email';

  $headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
    'X-Postmark-Server-Token: ' . $email->options['key']
  );

  $data = array(
    'From'     => $email->from,
    'To'       => $email->to,
    'ReplyTo'  => $email->replyTo,
    'Subject'  => $email->subject,
    'TextBody' => $email->body
  );

  // fetch the response
  $email->response = remote::post($url, array(
    'data'    => json_encode($data),
    'headers' => $headers
  ));

  if($email->response->code() != 200) {
    throw new Error('The mail could not be sent');
  }

};
