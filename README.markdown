CodeIgniter-Dynect API
=========================

Connect to the Dynect API with CodeIgniter (PHP)

Installation
------------

1.  Copy system/application/libraries/Dynect_API.php to your application/libraries folder
2.  Review the sample code in system/application/controllers/welcome.php

Config
------


Usage
------

First, load the library and instantiate the class.

    $this->load->library('Dynect_API');
    $dyn = new Dynect_API();

Then, login. Make sure to logout at the end.

    $dyn->login();
    // do things
    $dyn->logout();	

What you do is up to you. Here's what I've written so far:

    print_r($dyn->get_all_records('foobo.com','test.foobo.com'));
    print_r($dyn->get_zones());
    print_r($dyn->create_zone("feebeetest1.com", "admin@feebeetest1.com", 3600));
    print_r($dyn->publish_zone("feebeetest1.com"));
    print_r($dyn->delete_zone("feebeetest1.com"));
    print_r($dyn->get_records('A','foobo.com','test.foobo.com'));
    print_r($dyn->create_record('A', 'feebeetest1.com', 'test.feebeetest1.com', array('address' => '127.0.0.1')));
    print_r($dyn->delete_records('A', 'foobo.com', 'test.foobo.com'));

