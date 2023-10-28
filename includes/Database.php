<?php
/**
 * ====================================================================================
 *                           PRESTBIT UG (c) Alen O. Raul
 * ----------------------------------------------------------------------------------
 * @copyright Created by PRESTBIT UG. If you have downloaded this
 *  but not from author or received it from third party, then you are engaged
 *  in an illegal activity. 
 *  You must delete this immediately or contact the legal author / owner for a proper
 *  license. More infos at:  https://www.prestbit.de.
 *
 *  Thank you :)
 * ====================================================================================
 *
 * @author PRESTBIT UG (https://www.prestbit.de)
 * @link https://www.prestbit.de 
 * @license https://www.prestbit.de/license
 * @package Doc-Site
 */
namespace App\includes;
use PDO;

class Database{

    protected $config = array(), $isPage, $dbConn, $dbPrefix, $db;
	public $db_error, $query, $tblPrefix;
	public $rowCount, $rowCountAll;
	public $num_queries=0, $show_query="", $object=FALSE;

    public function __construct($config, $dbinfo){
      $this->config = $config;
      $this->dbConn = $dbinfo;
      $this->tblPrefix = $dbinfo["dbPrefix"];
        if(!isset($this->db)){
            $dsn = "mysql:host=".$this->dbConn["dbHost"].";dbname=".$this->dbConn["dbName"].";port=".$this->dbConn["dbPort"].";charset=".$this->dbConn["dbcharset"]."";
            $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          //  PDO::ATTR_EMULATE_PREPARES   => false,
            ];


            
            try {
                $conn = new PDO($dsn, $this->dbConn["dbUsername"], $this->dbConn["dbPassword"], $options);
                $this->db = $conn;
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
    }



    /*public function makeConn(){
        if(!isset($this->db)){
            $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            if($conn->connect_error){
                die("Exit");
            }else{
                $conn->set_charset("utf8");
                $this->db = $conn;
            }
        }
    }*/

 
    /*
    * Returns JOINS on SQL Queries
    * @param 
    */
    public function joins($join){
        switch($join){
            case 'left-join':
                $data = ' LEFT JOIN ';
                break;
            case 'right-join':
                $data = ' RIGHT JOIN ';
                break;
            default:
                $data = ' JOIN ';
        }
    return $data;
    }
    /*
     * Returns rows from the database based on the conditions
     * @param string name of the table
     * @param array select, where, order_by, limit and return_type conditions
     * @return_type: all(as default array): $foo[0]['id'], single: $foo['id'], single-object: $foo->id, all-object: $foo[0]->id
     */
    public function get($table, $conditions = array()){
      $sql = 'SELECT ';
      $sql .= array_key_exists("select", $conditions) && !empty($conditions['select'])?$conditions['select']:'*';
      $sql .= ' FROM '.$table;

      if (array_key_exists("join", $conditions)) {
			foreach ($conditions["join"] as $key => $value) {
                $i = 0;
                $sql .= ' '. $this->joins($value["type"]).' ';
                $sql .= ' '. $value["table"].' ';
                $sql .= ' ON ';
                foreach ($value["on"] as $key => $value) {
                    $pre = ($i > 0)?' AND ':'';
                    $sql .= $pre.$key." = ".$value."";
                    $i++;
                }
			}
		}


        if(array_key_exists("where", $conditions)){
            $sql .= ' WHERE ';
            $i = 0;
            foreach($conditions['where'] as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }

        if (array_key_exists("where-like", $conditions)) {
            $sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' OR ';
			$i = 0;
			foreach ($conditions['where-like'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." LIKE '%$value%'";
				$i++;
			}
		}

        if (array_key_exists("where-or-and", $conditions)) {
			$sql .= ' OR ';
			$i = 0;
			foreach ($conditions['where-or-and'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
			}
		}


        if (array_key_exists("where-or", $conditions)) {
			$sql .= ' OR ';
			$i = 0;
			foreach ($conditions['where-or'] as $key => $value) {
				$pre = ($i > 0)?' OR ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
			}
		}


        if (array_key_exists("and-where-or", $conditions)) {
			$sql .= ' AND ';
			$i = 0;
			foreach ($conditions['and-where-or'] as $key => $value) {
				$pre = ($i > 0)?' OR ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
			}
		}


        if (array_key_exists("where-not",$conditions)) {
			$sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$i = 0;
			foreach ($conditions['where-not'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." != '".$value."'";
				$i++;
			}
		}


        if (array_key_exists("bigger", $conditions)) {
			$sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$i = 0;
			foreach ($conditions['bigger'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." > ".$value."";
				$i++;
			}
		}


        if (array_key_exists("big-equal", $conditions)) {
            $sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$i = 0;
			foreach ($conditions['big-equal'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." >= '".$value."'";
				$i++;
			}
		}


        if (array_key_exists("small-equal", $conditions)) {
			$sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$i = 0;
			foreach ($conditions['small-equal'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." <= '".$value."'";
				$i++;
			}
		}


        if (array_key_exists("where-in-and", $conditions)) {
            $sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$i = 0;
			foreach ($conditions['where-in-and'] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." IN ".implode(',', $value)." ";
				$i++;
			}
		}

        if (array_key_exists("where-in-or", $conditions)) {
            $sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$i = 0;
			foreach ($conditions['where-in-or'] as $key => $value) {
				$pre = ($i > 0)?' OR ':'';
				$sql .= $pre.$key." IN ".implode(',', $value)." ";
				$i++;
			}
		}


        /// "between"=>array($ip_num, array("start"=>"end")), 
        if (array_key_exists("between", $conditions)) {  
            $sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
			$sql .= $conditions['between'][0]. ' BETWEEN ';
			$i = 0;
			foreach ($conditions['between'][1] as $key => $value) {
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." AND ".$value;
				$i++;
			}
		}

          /// created 2021-01-07    from 2021-01-08   to 2021-01-12
        if (array_key_exists("timeinterval", $conditions)) {
			$sql .= (strpos($sql, 'WHERE') === false)?' WHERE ':' AND ';
            $sql .= $conditions['timeinterval']['column'].' >= '.$conditions['timeinterval']['fromDate'].' AND '.$conditions['timeinterval']['column'].' < '.$conditions['timeinterval']['toDate'].'  ';
		}//'timeinterval'=>array('column'=>'created', 'fromDate'=>'2021-01-08', 'toDate'=>'2021-01-12')



        if(array_key_exists("and", $conditions) && array_key_exists("where", $conditions)){
            $sql .= ' AND ';
            $i = 0;
            foreach($conditions['and'] as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }


        if (array_key_exists("group",$conditions)) {
			$sql .= ' GROUP BY '.$conditions['group'];
		}


        if(array_key_exists("order_by",$conditions)){
            $sql .= ' ORDER BY '.$conditions['order_by'];   
        }

        
        if(array_key_exists("start", $conditions) && array_key_exists("limit",$conditions)){
            $sql .= ' LIMIT '.$conditions['start'].','.$conditions['limit']; 
        }elseif(!array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
            $sql .= ' LIMIT '.$conditions['limit']; 
        }

        $query = $this->db->prepare($sql); 
        $query->execute();

        if(array_key_exists("return_type",$conditions) && $conditions['return_type'] != 'all'){
            switch($conditions['return_type']){
                case 'count':
                    $data = $query->rowCount();
                    break;
                case 'single':
                    $data = $query->fetch(PDO::FETCH_ASSOC);
                    break;
                case 'single-object':
                    $data = $query->fetch(PDO::FETCH_OBJ);
                    break;
                case 'object':
                    $data = $query->fetch(PDO::FETCH_OBJ);
                    break;    
                case 'all-object':
                    $data = $query->fetchAll(PDO::FETCH_OBJ);
                    break;
                default:
                    $data = '';
            }
        }else{
            if($query->rowCount() > 0){
                $data = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } 
        return !empty($data)?$data:FALSE; 

       // return $sql;
    }

    

    /* "INSERT INTO users (name, prename) VALUES("flori", "Olteanu")";
     * Insert data into the database  
     * @param string name of the table
     * @param array the data for inserting into the table
     */
    public function insert($table, $data, $created=FALSE, $return_type = FALSE){
        if(!empty($data) && is_array($data)){
            $columns = '';
            $values  = '';
            $i = 0;

            if (!$created) {
				if (!array_key_exists('created', $data)) {
					$data['created'] = date("Y-m-d H:i:s");
				}
				if (!array_key_exists('modified',$data)) {
					$data['modified'] = date("Y-m-d H:i:s");
				}
		    }

            $columnString = implode(',', array_keys($data)); /// numele, prenumele
            $valueString = ":".implode(',:', array_keys($data));
            $sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
            $query = $this->db->prepare($sql);
            foreach($data as $key => $val){
                $val = strip_tags($val);  
                $query->bindValue(':'.$key, $val);
            }

            $insert = $query->execute();
            if($insert){
                if(!$return_type){
                    return $this->db->lastInsertId();
                }
              $data['lastid'] = $this->db->lastInsertId();
              return $data;
            }else{
                return false;  // $this->db->errorInfo()
            }
        }else{
            return false;
        }
    }

    public function getLastInsertId($table, $idField='crt'){
        $sql = "SELECT $idField  FROM `$table` ORDER BY `crt` DESC LIMIT 1";

        $query = $this->db->prepare($sql);
        $query->execute();

        $id = 0;
        if($query->rowCount() > 0){
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            $id = $data[0][$idField];
        }
        return (int)$id;
    }


    /*
     * Replace data into the database
     * @param string name of the table
     * @param array the data for replacing into the table
     * @param array where condition on replace data
     */

    public function replace($table, $data, $created = FALSE){
        if(!empty($data) && is_array($data)){
            $columns = '';
            $values  = '';
            $i = 0;

            if (!$created) {
				if (!array_key_exists('created', $data)) {
					$data['created'] = date("Y-m-d H:i:s");
				}
				if (!array_key_exists('modified',$data)) {
					$data['modified'] = date("Y-m-d H:i:s");
				}
		    }

            $columnString = implode(',', array_keys($data));
            $valueString = ":".implode(',:', array_keys($data));
            $sql = "REPLACE INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
            $query = $this->db->prepare($sql);
            foreach($data as $key => $val){
                $val = htmlspecialchars(strip_tags($val));
                $query->bindValue(':'.$key, $val);
            }

            $insert = $query->execute();
            if($insert){
              return true;
            }else{
              return false;
            }
        }
        return false;
    }
 

    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     */

    public function update($table, $data, $conditions, $created = FALSE){
        if(!empty($data) && is_array($data)){
            $colvalSet = '';
            $whereSql = '';
            $i = 0;


            if (!$created) {
				if(!array_key_exists('modified',$data)){
                    $data['modified'] = date("Y-m-d H:i:s");
                }
		    }

            foreach($data as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $val = strip_tags($val);
                $colvalSet .= $pre.$key."='". preg_replace('/(\'|&#0*39;)/', '`', $val)."'";
                $i++;
            }

            if(!empty($conditions) && is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$value."'";
                    $i++;
                }
            }

            $sql = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
            $query = $this->db->prepare($sql);
            $update = $query->execute();
            return $update?$query->rowCount():$this->db->errorInfo();
        }else{
            return false;
        }
    }



    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     */

    public function htmlupdate($table, $data, $conditions, $created = FALSE){
        if(!empty($data) && is_array($data)){
            $colvalSet = '';
            $whereSql = '';
            $i = 0;

            if (!$created) {
				if(!array_key_exists('modified',$data)){
                    $data['modified'] = date("Y-m-d H:i:s");
                }
		    }

            foreach($data as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $val = htmlspecialchars($val);
                $colvalSet .= $pre.$key."='".$val."'";
                $i++;
            }

            if(!empty($conditions) && is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$value."'";
                    $i++;
                }
            }

            $sql = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
            $query = $this->db->prepare($sql);
            $update = $query->execute();
            return $update?$query->rowCount():$this->db->errorInfo();
        }else{
            return false;
        }
    }


    /*
     * Delete data from the database
     * @param string name of the table
     * @param array where condition on deleting data
     */

    public function delete($table, $conditions){
        $whereSql = '';

        if(!empty($conditions)&& is_array($conditions)){
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $value = htmlspecialchars(strip_tags($value));
                $whereSql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }

        $sql = "DELETE FROM ".$table.$whereSql;
        $delete = $this->db->exec($sql);
        return $delete?$delete:false;
    }


    public function drivers(){
        return PDO::getAvailableDrivers();
    }

  /**
	 * Get App configuration
	**/
	public function get_config($table="setting"){
		// Get Config	
		$data=$this->get($table);
		foreach ($data as $key) {
			$config[$key['config']] = stripslashes($key['var']);
		}
		return $config+$this->config;
	}

	/**
	 * Get settings (Web-App ... )
	 **/
	public function get_settings($table){
		$data=$this->get($table);
		foreach ($data as $key) {
			$config[$key['config']] = stripslashes($key['var']);
		}
		return $config;
	}

    /**
	 * Get pages Widgets
	**/    
  public function getWigs($page){
    if($w = $this->get("widgets", array("select"=>"wigs", "where"=>array("pagename"=>$page), "limit"=>1, "return_type"=>"single"))){
        return json_decode($w['wigs'], true);
    }
    return false;
  }

  /**
	 * do Query
	**/    
  public function getConnection(){
		return $this->db;
	}
	/**
	* do Query
	**/    
    public function doQuery($q){
		$d = $this->db->query($q);
		return $d?$d:$this->db->errorInfo();
	}


    public function doQueryCount($q){
		$d = $this->db->query($q);
		$row = $d->fetch(PDO::FETCH_ASSOC);
        return $row['crt']?$row['crt']:FALSE;
	}

  // Generate Placeholders
	private  function ph(array $a){
		$b=array();
		foreach ($a as $key => $value) {
			$b[str_replace(":", "", $key)]="$key";
		}
		return $b;
	}
	// Check if there is an error
	private function error_message($error){
		if(!empty($error[2])){
			return $error[2];
		}
		return FALSE;
	}
	// Check if quotes are needed
	private function quote($string,$param=''){	
		if(empty($param)){
			return "'$string'";
		}
		return $string;
	}



}
?>