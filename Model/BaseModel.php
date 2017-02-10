<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 18:27
 */

namespace Model;

use dbhelper\DB as DB;

class BaseModel
{
    protected $db = '';
    protected $table = '';

    public function __construct($table)
    {
        $this->db = new DB();
        $this->table = $table;

    }

    public function select($columns, $map)
    {
        $map = '1=1 AND '. $map;
        return $this->db->select($this->table, $columns, $map);
    }

    public function update($data, $where)
    {
        return $this->db->update($this->table, $data, $where);
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }
}
