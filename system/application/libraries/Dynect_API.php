<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Dynect API SOAP Library
 *
 * Interfaces with the Dynect DNS service to query and modfiy DNS records.
 *
 * @category	Libraries
 * @author      Will Bradley, based on Dynect API examples.
 * @link        http://www.zyphon.com
*/

class Dynect_API {

    //private
    var $CI;
    var $base_url = 'https://api2.dynect.net/wsdl/2.0.0/Dynect.wsdl'; // The Base Dynect API2 URL
    var $client; // The SOAP client
    var $token; // Dynect login token
    var $user_name = 'jstrebel'; // set by config
    var $customer_name = 'demo-pagely'; // set by config
    var $password = '1234test'; // set by config
    
    
    /**
     * Constructor
     *
     * @access	public
     */	
    function Dynect_API() {
        $this->CI =& get_instance();

        $this->client = new SoapClient($this->base_url, array('cache_wsdl' => 0)); //Connect to the WSDL
    	
        log_message('debug', 'Dynect_API Class Initialized');
    }

	// --------------------------------------------------------------------


    function login() {

      /* ##########################

      Logging In
      ------------
      To log in to the dynect API you must call SessionLogin with customer name, username and password

      Some Returned Values
      status - success or failure
      data->token - to be used with all other commands

      ** Complete Documentations can be found at
      https://manage.dynect.net/help/docs/api2/soap/

      ########################## */

      if(!isset($this->user_name) || !isset($this->customer_name) || !isset($this->password)) {
                show_error('You must set your username, customer name, and password in application/config/dynect_api.php in order to login.');
      }
      else {

        $parameters = array(
          'parameters' => array(
            'user_name'=> $this->user_name,
            'customer_name' => $this->customer_name,
            'password' => $this->password
          )
        );

        $result = $this->client->__soapCall('SessionLogin',$parameters);

        if(is_soap_fault($result)){
          trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
          die();
        }

        if($result->status == 'success'){
          $this->token = $result->data->token;
          return true;
        } else {
          log_message('error', 'Dynect_API could not log in. Result status: '.$result->status); 
          die();
        }

      } // end if isset user_name
    } // end login
 
    function get_all_records($zone, $fqdn) {

      /* ##########################

      Getting All Records on a zone
      ------------
      To get a list of all records send a GetANYRecords command with the token, zone, and fqdn as paramters

      Some Returned Values
      status - success or failure
      data - object containing a list record type containers each with the rdata, fqdn, record_type, ttl and zone

      ** Complete Documentations can be found at
      https://manage.dynect.net/help/docs/api2/soap/

      ########################## */

      $parameters = array(
        'parameters' => array(
          'token'=> $this->token, 
          'zone' => $zone,
          'fqdn' => $fqdn
        )
      );

      echo '<b>Retrieving all Records</b><br/>';
      echo '--------------------------<br/>';
      $result = $this->client->__soapCall('GetANYRecords',$parameters);

      if(is_soap_fault($result)){
        trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
        die();
      }

      if($result->status == 'success'){
        return $result->data;
      } else {
        die('Unable to Get records');
      }

    } // end get_record
 

    /**
     * Create a Zone
     *
     * @access  public
     * @param Name of the zone
     * @param Administrative contact for this zone
     * @param Default TTL (in seconds) for records in the zone
     * @return  void
     */	
    function create_zone($zone, $rname, $ttl = 3600) {

      $parameters = array(
        'parameters' => array(
          'token'=> $this->token,
	        'zone' => $zone,
	        'rname' => $rname,
	        'ttl' => $ttl
        )
      );

      try{
      $result = $this->client->__soapCall('CreateZone',$parameters);
      }
      catch (SoapFault $ex) {
          trigger_error("SOAP Fault: ( ".var_export($ex->detail,true)." )", E_USER_ERROR);
          die();
      }

      if($result->status == 'success'){
        return $result->data;
      } else {
        die('Unable to create zone');
      }

    } // end create_zone
 
    /**
     * Delete a Zone
     *
     * @access  public
     * @param Name of the zone
     * @return  boolean (true = success)
     */	
    function delete_zone($zone) {

      $parameters = array(
        'parameters' => array(
          'token'=> $this->token,
	        'zone' => $zone
        )
      );

      try{
      $result = $this->client->__soapCall('DeleteOneZone',$parameters);
      }
      catch (SoapFault $ex) {
          trigger_error("SOAP Fault: ( ".var_export($ex->detail,true)." )", E_USER_ERROR);
          die();
      }

      if($result->status == 'success'){
        return true;
      } else {
        die('Unable to delete zone');
      }

    } // end delete_zone
 

    /**
     * Publish a Zone
     *
     * @access  public
     * @param Name of the zone
     * @return  boolean (true = success)
     */	
    function publish_zone($zone) {

      $parameters = array(
        'parameters' => array(
          'token'=> $this->token,
	        'zone' => $zone
        )
      );

      try{
      $result = $this->client->__soapCall('PublishZone',$parameters);
      }
      catch (SoapFault $ex) {
          trigger_error("SOAP Fault: ( ".var_export($ex->detail,true)." )", E_USER_ERROR);
          die();
      }

      if($result->status == 'success'){
        return true;
      } else {
        die('Unable to publish zone');
      }

    } // end publish_zone


    /**
     * Delete Records - Deletes all records at fqdn of type
     *
     * @access  public
     * @param Type of record to delete (A, AAAA, CNAME, DNSKEY, DS, KEY, LOC, MX, NS, PTR, RP, SOA, SRV, TXT)
     * @param Name of zone to delete records from
     * @param Name of node to delete records from
     * @return bool (true=success)
     */	
    function delete_records($type, $zone, $fqdn) {
      if(in_array($type, array('A', 'AAAA', 'CNAME', 'DNSKEY', 'DS', 'KEY', 'LOC', 'MX', 'NS', 'PTR', 'RP', 'SOA', 'SRV', 'TXT'))) {

        $parameters = array(
          'parameters' => array(
            'token'=> $this->token,
            'fqdn' => $fqdn,
	          'zone' => $zone
          )
        );

        try{
        $result = $this->client->__soapCall('Delete'.$type.'Records',$parameters);
        }
        catch (SoapFault $ex) {
            trigger_error("SOAP Fault: ( ".var_export($ex->detail,true)." )", E_USER_ERROR);
            die();
        }

        if($result->status == 'success'){
          return true;
        } else {
          die('Unable to create '.$type.' record.');
        }

      }
    } // end delete_records


    /**
     * Create Record
     *
     * @access  public
     * @param Type of record to create (A, AAAA, CNAME, DNSKEY, DS, KEY, LOC, MX, NS, PTR, RP, SOA, SRV, TXT)
     * @param Name of zone to add the record to
     * @param Name of node to add the record to
     * @param RData defining the record to add
     * @return array data
        string fqdn Fully qualified domain name of a node in the zone
        hash rdata RData defining the record
         (response data)
        string record_type The RRType of the record
        string ttl TTL for the record.
        string zone Name of the zone
     */	
    function create_record($type, $zone, $fqdn, $rdata) {
      if(in_array($type, array('A', 'AAAA', 'CNAME', 'DNSKEY', 'DS', 'KEY', 'LOC', 'MX', 'NS', 'PTR', 'RP', 'SOA', 'SRV', 'TXT'))) {

        $parameters = array(
          'parameters' => array(
            'token'=> $this->token,
            'fqdn' => $fqdn,
	          'zone' => $zone,
            'rdata' => $rdata
          )
        );

        try{
        $result = $this->client->__soapCall('Create'.$type.'Record',$parameters);
        }
        catch (SoapFault $ex) {
            trigger_error("SOAP Fault: ( ".var_export($ex->detail,true)." )", E_USER_ERROR);
            die();
        }

        if($result->status == 'success'){
          return $result->data;
        } else {
          die('Unable to create '.$type.' record.');
        }

      }
    } // end create_record


    /**
     * Get Records
     *
     * @access  public
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
    function get_records($type, $zone, $fqdn) {
      if(in_array($type, array('A', 'AAAA', 'CNAME', 'DNSKEY', 'DS', 'KEY', 'LOC', 'MX', 'NS', 'PTR', 'RP', 'SOA', 'SRV', 'TXT'))) {

        $parameters = array(
          'parameters' => array(
            'token'=> $this->token,
            'fqdn' => $fqdn,
	          'zone' => $zone
          )
        );

        try{
        $result = $this->client->__soapCall('Get'.$type.'Records',$parameters);
        }
        catch (SoapFault $ex) {
            trigger_error("SOAP Fault: ( ".var_export($ex->faultstring,true)." )", E_USER_ERROR);
            die();
        }

        if($result->status == 'success'){
          return $result->data;
        } else {
          die('Unable to get '.$type.' records.');
        }

      }
    } // end get_records
 

    /**
     * Get all Zones
     *
     * @access  public
     * @return  zone data
     */	
    function get_zones() {

      $parameters = array(
        'parameters' => array(
          'token'=> $this->token
        )
      );

      $result = $this->client->__soapCall('GetZones',$parameters);

      if(is_soap_fault($result)){
        trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
        die();
      }

      if($result->status == 'success'){
        return $result->data;
      } else {
        die('Unable to get zones');
      }

    } // end get_zones
 
 
    function logout() {
      /* ##########################

      Logging Out
      ------------
      To log in to the dynect API you must call SessionLogout with the token received at login

      Some Returned Values
      status - success or failure

      ** Complete Documentations can be found at
      https://manage.dynect.net/help/docs/api2/soap/

      ########################## */

      $parameters = array(
        'parameters' => array(
          'token'=> $this->token
        )
      );

      $result = $this->client->__soapCall('SessionLogout',$parameters);

      if(is_soap_fault($result)){
        trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})", E_USER_ERROR);
        die();
      }

      $message = $result->msgs;

      if($result->status != 'success'){
        log_message('error','Dynect_API unable to log out.');
      }
    } // end logout


} // end class
