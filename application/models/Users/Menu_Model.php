<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	 
    class Menu_Model extends CI_Model
    {
        private $table = 'master_lms_menu';
        private $table_url = 'master_access_url';
        
		public function menusList($where)
		{
			return $this->db->select('*')->where($where)->from($this->table)->order_by('menu_order', 'asc')->get();
			
		}
                
                public function urlsList($where)
		{
			return $this->db->select('*')->where($where)->from($this->table_url)->order_by('mau_role_id', 'asc')->get();
			
		}
    }
?>