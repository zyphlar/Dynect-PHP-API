CodeIgniter-Dynect API
=========================

Connecting to the Dynect API with CodeIgniter (PHP)
This is an open-source work in progress, and your contribution is appreciated!

TODO: 
    The Dynect API doesn't appear to support everything needed, but this is a decently-complete implementation and should get anyone trying to interface with Dynect via PHP a good head start.
    The code is very hackish -- there's a lot of copy-paste and the CodeIgniter section is just a repurposed welcome.php -- but the good news is that sections are quite usable on their own. There's no config files, dependencies, or a significant MVC to be concerned about, and so should be decent as PHP sample code of the Dynect API implementation.

Installation
------------

1.  Copy system/application/libraries/Dynect_API.php to your CodeIgniter app's application/libraries folder (if applicable, otherwise just use the PHP file as-is.)
2.  Review the sample code in system/application/controllers/welcome.php for usage examples.
3.  Reference Dynect's API documentation for further information.

Config
------

Ideally, there would be a config file in system/application/config but I haven't gotten it to work properly yet.

In the system/application/libraries/Dynect_API.php file, edit these lines as necessary based on info provided by Dynect:

    var $user_name = '';
    var $customer_name = '';
    var $password = '';


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

