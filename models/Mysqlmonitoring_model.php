<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mysqlmonitoring_model extends CI_Model
{
    /**
     * Mysqlmonitoring_model constructor.
     */
    public function __construct()
    {
        parent::__construct(); // construct the Model class
        $this->load->database();
    }

    /**
     * @return mixed
     */
    public function processList()
    {
        return $this->db->query('SHOW PROCESSLIST')->result();
    }

    /**
     * @return mixed
     */
    public function globalStatus()
    {
        return $this->db->query('SHOW GLOBAL STATUS')->result();
    }
}