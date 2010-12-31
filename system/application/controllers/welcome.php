<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();	
	}
	
	function index()
	{
    $this->load->library('Dynect_API');
    $dyn = new Dynect_API();
    $dyn->login();
//    print_r($dyn->get_all_records('foobo.com','test.foobo.com'));
//    print_r($dyn->get_zones());
    print_r($dyn->create_zone("feebeetest1.com", "admin@feebeetest1.com", 3600));
    print_r($dyn->publish_zone("feebeetest1.com"));
    print_r($dyn->delete_zone("feebeetest1.com"));
//    print_r($dyn->get_records('A','foobo.com','test.foobo.com'));
//    print_r($dyn->create_record('A', 'feebeetest1.com', 'test.feebeetest1.com', array('address' => '127.0.0.1')));
//    print_r($dyn->delete_records('A', 'foobo.com', 'test.foobo.com'));

    $dyn->logout();

	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
