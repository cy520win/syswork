<?php
/*!
 * Medoo database framework
 * http://medoo.in
 * Version 0.8.1
 * 
 * Copyright 2013, Angel Lai
 * Released under the MIT license
 */
namespace dbhelper;

use dbhelper\helper as helper;
//use dbhelper\dbconfig;
require_once(dirname(__FILE__) . '/dbconfig.php');

class DB
{
    protected $database_type = 'mysql';
    protected $server = '';
    protected $username = '';
    protected $password = '';
    protected $port = '';

    // Optional
    protected $charset = '';

    public function __construct($database_name='')
    {
        global $db_helper_config;
        $this->server = $db_helper_config['mysql']['dbhost'];
        $this->username = $db_helper_config['mysql']['dbuser'];
        $this->password = $db_helper_config['mysql']['dbpwd'];
        $this->charset = $db_helper_config['mysql']['dblanguage'];
        $this->port = $db_helper_config['mysql']['port'];

        if (empty($database_name))
            $database_name = $db_helper_config['mysql']['dbname'];

        try {
            $type = strtolower($this->database_type);
            switch ($type) {
                case 'mysql':
                    $dbhost = $this->server;
                    $dbport = $this->port;
                    $dbuser = $this->username;
                    $dbpwd = $this->password;
                    //$opt = array (PDO::ATTR_PERSISTENT => true);
                    $this->pdo = new \PDO ("mysql:host=$dbhost;port=$dbport;dbname=$database_name", $dbuser, $dbpwd);
                    break;
                case 'pgsql':
                    $this->pdo = new \PDO(
                        $type . ':host=' . $this->server . ';dbname=' . $database_name,
                        $this->username,
                        $this->password
                    );
                    break;

                case 'mssql': break;
                case 'sybase':
                    $this->pdo = new \PDO(
                        $type . ':host=' . $this->server . ';dbname=' . $database_name . ',' .
                        $this->username . ',' .
                        $this->password
                    );
                    break;

                case 'sqlite':
                    $this->pdo = new \PDO(
                        $type . ':' . $database_name
                    );
                    break;
            }
            $this->pdo->exec('SET NAMES \'' . $this->charset . '\'');
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function query($query)
    {
        $this->queryString = $query;

        return $this->pdo->query($this->CheckSql($query));
    }

    public function exec($query)
    {
        $this->queryString = $query;

        return $this->pdo->exec($this->CheckSql($query));
    }

    public function quote($string)
    {
        return $this->pdo->quote($string);
    }

    protected function array_quote($array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->pdo->quote($value);
        }

        return implode($temp, ',');
    }

    protected function inner_conjunct($data, $conjunctor, $outer_conjunctor)
    {
        $haystack = array();
        foreach ($data as $value) {
            $haystack[] = '(' . $this->data_implode($value, $conjunctor) . ')';
        }

        return implode($outer_conjunctor . ' ', $haystack);
    }

    protected function data_implode($data, $conjunctor, $outer_conjunctor = null)
    {
        $wheres = array();
        foreach ($data as $key => $value) {
            if (($key == 'AND' || $key == 'OR') && is_array($value)) {
                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
                    '(' . $this->data_implode($value, ' ' . $key) . ')' :
                    '(' . $this->inner_conjunct($value, ' ' . $key, $conjunctor) . ')';
            } else {
                preg_match('/([\w]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>)\])?/i', $key, $match);
                if (isset($match[3])) {
                    if ($match[3] == '' || $match[3] == '!') {
                        $wheres[] = $match[1] . ' ' . $match[3] . '= ' . $this->quote($value);
                    } else {
                        if ($match[3] == '<>') {
                            if (is_array($value) && is_numeric($value[0]) && is_numeric($value[1])) {
                                $wheres[] = $match[1] . ' BETWEEN ' . $value[0] . ' AND ' . $value[1];
                            }
                        } else {
                            if (is_numeric($value)) {
                                $wheres[] = $match[1] . ' ' . $match[3] . ' ' . $value;
                            }
                        }
                    }
                } else {
                    if (is_int($key)) {
                        $wheres[] = $this->quote($value);
                    } else {
                        $wheres[] = is_array($value) ? $match[1] . ' IN (' . $this->array_quote($value) . ')' :
                            $match[1] . ' = ' . $this->quote($value);
                    }
                }
            }
        }

        return implode($conjunctor . ' ', $wheres);
    }

    public function where_clause($where)
    {
        $where_clause = '';
        if (is_array($where)) {
            $single_condition = array_diff_key($where, array_flip(
                array('AND', 'OR', 'GROUP', 'ORDER', 'HAVING', 'LIMIT', 'LIKE', 'MATCH')
            ));
            if ($single_condition != array()) {
                $where_clause = ' WHERE ' . $this->data_implode($single_condition, '');
            }
            if (isset($where['AND'])) {
                $where_clause = ' WHERE ' . $this->data_implode($where['AND'], ' AND ');
            }
            if (isset($where['OR'])) {
                $where_clause = ' WHERE ' . $this->data_implode($where['OR'], ' OR ');
            }
            if (isset($where['LIKE'])) {
                $like_query = $where['LIKE'];
                if (is_array($like_query)) {
                    if (isset($like_query['OR']) || isset($like_query['AND'])) {
                        $connector = isset($like_query['OR']) ? 'OR' : 'AND';
                        $like_query = isset($like_query['OR']) ? $like_query['OR'] : $like_query['AND'];
                    } else {
                        $connector = 'AND';
                    }

                    $clause_wrap = array();
                    foreach ($like_query as $column => $keyword) {
                        if (is_array($keyword)) {
                            foreach ($keyword as $key) {
                                $clause_wrap[] = $column . ' LIKE ' . $this->quote('%' . $key . '%');
                            }
                        } else {
                            $clause_wrap[] = $column . ' LIKE ' . $this->quote('%' . $keyword . '%');
                        }
                    }
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . '(' . implode($clause_wrap, ' ' . $connector . ' ') . ')';
                }
            }
            if (isset($where['MATCH'])) {
                $match_query = $where['MATCH'];
                if (is_array($match_query) && isset($match_query['columns']) && isset($match_query['keyword'])) {
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . ' MATCH (' . implode($match_query['columns'], ', ') . ') AGAINST (' . $this->quote($match_query['keyword']) . ')';
                }
            }
            if (isset($where['GROUP'])) {
                $where_clause .= ' GROUP BY ' . $where['GROUP'];
            }
            if (isset($where['ORDER'])) {
                $where_clause .= ' ORDER BY ' . $where['ORDER'];
                if (isset($where['HAVING'])) {
                    $where_clause .= ' HAVING ' . $this->data_implode($where['HAVING'], '');
                }
            }
            if (isset($where['LIMIT'])) {
                if (is_numeric($where['LIMIT'])) {
                    $where_clause .= ' LIMIT ' . $where['LIMIT'];
                }
                if (is_array($where['LIMIT']) && is_numeric($where['LIMIT'][0]) && is_numeric($where['LIMIT'][1])) {
                    $where_clause .= ' LIMIT ' . $where['LIMIT'][0] . ',' . $where['LIMIT'][1];
                }
            }
        } else {
            if ($where != null) {
                $where_clause .= ' where ' . $where;
            }
        }

        return $where_clause;
    }

    public function select($table, $columns, $where = null)
    {
        $sql = 'SELECT ' . (
            is_array($columns) ? implode(', ', $columns) : $columns
            ) . ' FROM ' . $table . $this->where_clause($where);
        //echo $sql.'::sql::';
        $query = $this->query($sql);
        #(is_string($columns) && $columns != '*') ? PDO::FETCH_COLUMN : PDO::FETCH_ASSOC
        return $query ? $query->fetchAll() : false;
    }

    public function insert($table, $data)
    {

        $keys = implode(',', array_keys($data));
        $values = array();
        foreach ($data as $key => $value) {
            $values[] = is_array($value) ? serialize($value) : $value;
        }
        $this->query('INSERT INTO ' . $table . ' (' . $keys . ') VALUES (' . $this->data_implode(array_values($values), ',') . ')');

        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where = null)
    {
        $fields = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $fields[] = $key . '=' . $this->quote(serialize($value));
            } else {
                preg_match('/([\w]+)(\[(\+|\-)\])?/i', $key, $match);
                if (isset($match[3])) {
                    if (is_numeric($value)) {
                        $fields[] = $match[1] . ' = ' . $match[1] . ' ' . $match[3] . ' ' . $value;
                    }
                } else {
                    $fields[] = $key . ' = ' . $this->quote($value);
                }
            }
        }
        //$sql = 'UPDATE ' . $table . ' SET ' . implode(',', $fields) . $this->where_clause($where); echo  $sql.'<br/>';
        return $this->exec('UPDATE ' . $table . ' SET ' . implode(',', $fields) . $this->where_clause($where));
    }

    public function delete($table, $where)
    {
        return $this->exec('DELETE FROM ' . $table . $this->where_clause($where));
    }

    public function replace($table, $columns, $search = null, $replace = null, $where = null)
    {
        if (is_array($columns)) {
            $replace_query = array();
            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replace_search => $replace_replacement) {
                    $replace_query[] = $column . ' = REPLACE(' . $column . ', ' . $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
                }
            }
            $replace_query = implode(', ', $replace_query);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replace_query = array();
                foreach ($search as $replace_search => $replace_replacement) {
                    $replace_query[] = $columns . ' = REPLACE(' . $columns . ', ' . $this->quote($replace_search) . ', ' . $this->quote($replace_replacement) . ')';
                }
                $replace_query = implode(', ', $replace_query);
                $where = $replace;
            } else {
                $replace_query = $columns . ' = REPLACE(' . $columns . ', ' . $this->quote($search) . ', ' . $this->quote($replace) . ')';
            }
        }

        return $this->exec('UPDATE ' . $table . ' SET ' . $replace_query . $this->where_clause($where));
    }

    public function get($table, $columns, $where = null)
    {
        if (is_array($where)) {
            $where['LIMIT'] = 1;
        }
        $data = $this->select($table, $columns, $where);

        return isset($data[0]) ? $data[0] : false;
    }

    public function has($table, $where)
    {
        return $this->query('SELECT EXISTS(SELECT 1 FROM ' . $table . $this->where_clause($where) . ')')->fetchColumn() === '1';
    }

    public function count($table, $where = null)
    {
        return 0 + ($this->query('SELECT COUNT(*) FROM ' . $table . $this->where_clause($where))->fetchColumn());
    }

    public function max($table, $column, $where = null)
    {
        return 0 + ($this->query('SELECT MAX(' . $column . ') FROM ' . $table . $this->where_clause($where))->fetchColumn());
    }

    public function min($table, $column, $where = null)
    {
        return 0 + ($this->query('SELECT MIN(' . $column . ') FROM ' . $table . $this->where_clause($where))->fetchColumn());
    }

    public function avg($table, $column, $where = null)
    {
        return 0 + ($this->query('SELECT AVG(' . $column . ') FROM ' . $table . $this->where_clause($where))->fetchColumn());
    }

    public function sum($table, $column, $where = null)
    {
        return 0 + ($this->query('SELECT SUM(' . $column . ') FROM ' . $table . $this->where_clause($where))->fetchColumn());
    }

    public function error()
    {
        return $this->pdo->errorInfo();
    }

    public function last_query()
    {
        return $this->queryString;
    }

    public function info()
    {
        return array(
            'server' => $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO),
            'client' => $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'driver' => $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'connection' => $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)
        );
    }


    private function CheckSql($db_string, $querytype = 'select')
    {
        return $db_string;
      /*  global $cfg_cookie_encode;
        $clean = '';
        $error = '';
        $old_pos = 0;
        $pos = -1;
        $log_file = '../log/sql_safe.txt';
        $userIP = \helper\GetIP();
        $getUrl = \helper\GetCurUrl();

        //�������ͨ��ѯ��䣬ֱ�ӹ���һЩ�����﷨
        if ($querytype == 'select') {
            $notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";

            //$notallow2 = "--|/\*";
            if (preg_match("/" . $notallow1 . "/", $db_string)) {
                fputs(fopen($log_file, 'a+'), "$userIP||$getUrl||$db_string||SelectBreak\r\n");
                exit("<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>");
            }
        }

        //������SQL���
        while (TRUE) {
            $pos = strpos($db_string, '\'', $pos + 1);
            if ($pos === FALSE) {
                break;
            }
            $clean .= substr($db_string, $old_pos, $pos - $old_pos);
            while (TRUE) {
                $pos1 = strpos($db_string, '\'', $pos + 1);
                $pos2 = strpos($db_string, '\\', $pos + 1);
                if ($pos1 === FALSE) {
                    break;
                } elseif ($pos2 == FALSE || $pos2 > $pos1) {
                    $pos = $pos1;
                    break;
                }
                $pos = $pos2 + 1;
            }
            $clean .= '$s$';
            $old_pos = $pos + 1;
        }
        $clean .= substr($db_string, $old_pos);
        $clean = trim(strtolower(preg_replace(array('~\s+~s'), array(' '), $clean)));

        //�ϰ汾��Mysql����֧��union�����õĳ�����Ҳ��ʹ��union������һЩ�ڿ�ʹ���������Լ����
        if (strpos($clean, 'union') !== FALSE && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "union detect";
        } //�����汾�ĳ�����ܱȽ��ٰ���--,#������ע�ͣ����Ǻڿ;���ʹ������
        elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== FALSE || strpos($clean, '#') !== FALSE) {
            $fail = TRUE;
            $error = "comment detect";
        } //��Щ�������ᱻʹ�ã����Ǻڿͻ������������ļ���down�����ݿ�
        elseif (strpos($clean, 'sleep') !== FALSE && preg_match('~(^|[^a-z])sleep($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "slown down detect";
        } elseif (strpos($clean, 'benchmark') !== FALSE && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "slown down detect";
        } elseif (strpos($clean, 'load_file') !== FALSE && preg_match('~(^|[^a-z])load_file($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "file fun detect";
        } elseif (strpos($clean, 'into outfile') !== FALSE && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~s', $clean) != 0) {
            $fail = TRUE;
            $error = "file fun detect";
        } //�ϰ汾��MYSQL��֧���Ӳ�ѯ�����ǵĳ��������Ҳ�õ��٣����ǺڿͿ���ʹ��������ѯ���ݿ�������Ϣ
        elseif (preg_match('~\([^)]*?select~s', $clean) != 0) {
            $fail = TRUE;
            $error = "sub select detect";
        }
        if (!empty($fail)) {
            fputs(fopen($log_file, 'a+'), "$userIP||$getUrl||$db_string||$error\r\n");
            exit("<font size='5' color='red'>Safe Alert: Request Error step 2!</font>");
        } else {
            return $db_string;
        }*/
    }
}

?>