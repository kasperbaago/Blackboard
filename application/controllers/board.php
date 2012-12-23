<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * BOARD CONTROLLER
 */

class Board extends CI_Controller {
	public function index()
	{
		$this->load->view('page/main', array("title" => $this->config->item('title'), "css" => $this->config->item('cssFiles'), "js" => $this->config->item('jsFiles')));
	}
        
        public function loadForm() {
            $this->load->view('page/form');
        }
        
        public function memTest() {
            $this->load->driver('cache');
            $this->cache->memcached->save('foo', 'bar', 500);
            //$id = $this->cache->memcached->get('foo');
            var_dump($this->cache->file->is_supported());
            
        }
}