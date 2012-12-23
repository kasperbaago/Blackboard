<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * MOBILE CONTROLLER
 */

class Mobile extends CI_Controller {
	public function index()
	{
		$this->load->view('welcome_message');
	}
}