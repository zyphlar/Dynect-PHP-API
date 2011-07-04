<?php

/**
* Dynect API SOAP Library
*
* Interfaces with the Dynect DNS service to query and modfiy DNS records.
*
* @category Libraries
* @author Will Bradley, based on Dynect API examples.
* @link http://www.zyphon.com
* @link https://github.com/zyphlar/Dynect-PHP-API
*/

class Dynect_API {

  protected $base_url = 'https://api2.dynect.net/wsdl/current/Dynect.wsdl';
  protected $client; // The SOAP client
  protected $token = null; // Dynect login token
  protected $messages = array();
  protected $error_function = null;

  protected $allowed_records = array('A', 'AAAA', 'CNAME', 'DNSKEY', 'DS', 'KEY', 'LOC', 'MX', 'NS', 'PTR', 'RP', 'SOA', 'SRV', 'TXT');

  public function __construct($url = null) {

    if($url) {
      $this->base_url = $url;
    }

    $this->client = new SoapClient($this->base_url, array('cache_wsdl' => 1)); //Connect to the WSDL
  }

  public function setErrorFunction($error_function) {
    $this->error_function = $error_function;
  }

  protected function error($message) {
    $this->messages[] = $message;

    // If we've been given a custom error function, use it.
    // Eg for Drupal, you can pass in drupal_set_message to setErrorFunction()
    if($this->error_function) {
      $error_function = $this->error_function;
      $error_function($message);
    }
  }

  public function getErrors() {
    return $this->messages;
  }

  public function login($customer_name, $user_name, $password) {

    $parameters = array(
      'parameters' => array(
        'user_name'=> $user_name,
        'customer_name' => $customer_name,
        'password' => $password,
      ),
    );

    $result = $this->soapCall('SessionLogin', $parameters);

    if($result && ($result->status == 'success')){
      $this->token = $result->data->token;
      return true;
    }
    else {
      // This case seems to be handled adequately by catching the soapfault above.
      // Dynect's servers return HTTP 500 on failed login -> soapfault.
    }
  }

  function get_all_records($zone, $fqdn) {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'zone' => $zone,
        'fqdn' => $fqdn,
      )
    );

    $result = $this->soapCall('GetANYRecords', $parameters);

    if($result && ($result->status == 'success')){
      return $result->data;
    }
    else {
      return false;
    }
  }

    /**
* Create a Zone
*
* @access public
* @param Name of the zone
* @param Administrative contact for this zone
* @param Default TTL (in seconds) for records in the zone
* @return void
*/
  public function create_zone($zone, $rname, $ttl = 3600) {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'zone' => $zone,
        'rname' => $rname,
        'ttl' => $ttl,
      ),
    );

    $result = $this->soapCall('CreateZone', $parameters);

    if($result && ($result->status == 'success')) {
      return $result->data;
    }
    else {
      return false;
    }
  }

  public function get_node_list($zone) {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'zone' => $zone,
      ),
    );

    $result = $this->soapCall('GetNodeList', $parameters);

    if($result && ($result->status == 'success')) {
      return $result->data;
    }
    else {
      return false;
    }
  }

  public function delete_zone($zone) {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'zone' => $zone,
      ),
    );

    $result = $this->soapCall('DeleteOneZone', $parameters);
    return ($result && ($result->status == 'success'));
  }

/**
 * Publish a Zone
 *
 * @access public
 * @param Name of the zone
 * @return boolean (true = success)
*/
  public function publish_zone($zone) {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'zone' => $zone,
      )
    );

    $result = $this->soapCall('PublishZone', $parameters);
    return ($result && ($result->status == 'success'));
  }


/**
  * Delete Records - Deletes all records at fqdn of type
  *
  * @access public
  * @param Type of record to delete (A, AAAA, CNAME, DNSKEY, DS, KEY, LOC, MX, NS, PTR, RP, SOA, SRV, TXT)
  * @param Name of zone to delete records from
  * @param Name of node to delete records from
  * @return bool (true=success)
*/
  public function delete_records($type, $zone, $fqdn) {

    if(!in_array($type, $this->allowed_records)) {
      $this->error('Supplied record type ' . $type . ' is not in the allowed list.');
      return false;
    }

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'fqdn' => $fqdn,
        'zone' => $zone,
      ),
    );

    $result = $this->soapCall('Delete'.$type.'Records', $parameters);
    return ($result && ($result->status == 'success'));
  }


/**
 * Create Record
 *
 * @access public
 * @param Type of record to create (A, AAAA, CNAME, DNSKEY, DS, KEY, LOC, MX, NS, PTR, RP, SOA, SRV, TXT)
 * @param Name of zone to add the record to
 * @param Name of node to add the record to
 * @param RData defining the record to add
 *
 * @return array data
      string fqdn Fully qualified domain name of a node in the zone
      hash rdata RData defining the record
      (response data)
      string record_type The RRType of the record
      string ttl TTL for the record.
      string zone Name of the zone
 */

  public function create_record($type, $zone, $fqdn, $rdata) {

    if(!in_array($type, $this->allowed_records)) {
      $this->error('Supplied record type ' . $type . ' is not in the allowed list.');
      return false;
    }

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'fqdn' => $fqdn,
        'zone' => $zone,
        'rdata' => $rdata,
      ),
    );

    $result = $this->soapCall('Create'.$type.'Record', $parameters);

    if($result && ($result->status == 'success')){
      return $result->data;
    }
    else {
      return false;
    }
  }

/**
  * Get Records
  *
  * @access public
  * @param Type of record to get (A, AAAA, CNAME, DNSKEY, DS, KEY, LOC, MX, NS, PTR, RP, SOA, SRV, TXT)
  * @param Name of zone to get the record of
  * @param Name of node to get the record of
  * @return array data
string fqdn Fully qualified domain name of a node in the zone
hash rdata RData defining the record
(response data)
string record_type The RRType of the record
string ttl TTL for the record.
string zone Name of the zone
  */

  public function get_records($type, $zone, $fqdn) {

    if(!in_array($type, $this->allowed_records)) {
      $this->error('Supplied record type ' . $type . ' is not in the allowed list.');
      return false;
    }

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
        'fqdn' => $fqdn,
        'zone' => $zone,
      )
    );

    $result = $this->soapCall('Get'.$type.'Records', $parameters);

    if($result && ($result->status == 'success')) {
      return $this->rtn($result->data);
    }
    else {
      return false;
    }
  }

 /**
  * Get all Zones
  *
  * @access public
  * @return zone data
  */

  public function get_zones() {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
      ),
    );

    $result = $this->soapCall('GetZones', $parameters);

    if($result && ($result->status == 'success')) {
      // Normalise records to an array of objects.
      return $this->rtn($result->data);
    }
    else {
      return false;
    }
  }

  public function logout() {

    $parameters = array(
      'parameters' => array(
        'token'=> $this->token,
      ),
    );

    $result = $this->soapCall('SessionLogout', $parameters);
    return ($result && ($result->status == 'success'));
  }

  /**
   * A wrapper around $this->client->__soapCall that handles exceptions and logs
   * them as messages.
   * @param string $method
   * @param array $parameters
   */

  protected function soapCall($method, $parameters) {
    try{
      $result = $this->client->__soapCall($method, $parameters);
    }
    catch (SoapFault $ex) {
      // Log all the messages:
      foreach($ex->detail->ErrorResponse->enc_value->msgs as $message) {
        $this->error($message->info);
      }
      return false;
    }
    return $result;
  }

  /**
   * DynECT's API has a habit of returning a single object for a single result,
   * or an array of objects for multiple results. This is kind of annoying as
   * we can't safely interate over the result. This method normalises the result
   * to an array.
   */

  protected function rtn($return) {
    if(is_array($return)) {
      return $return;
    }
    else {
      return array($return);
    }
  }
}

