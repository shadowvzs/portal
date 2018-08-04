<?php
class Model {
	protected static $request;
	protected static $data;
	protected static $PATTERN = [
		'EMAIL' => '/^([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$)$/',
		'NAME_HUN' => '/^([a-zA-Z0-9 ÁÉÍÓÖŐÚÜŰÔÕÛáéíóöőúüűôõû]+)$/',
		'NAME' => '/^([a-zA-Z0-9 ]+)$/',
		'INTEGER' => '/^([0-9]+)$/',
		'SLUG' => '/^[a-zA-Z0-9-_]+$/',
		'ALPHA_NUM' => '/^([a-zA-Z0-9]+)$/',
		'ALPHA_NUM_' => '/^([a-zA-Z_0-9]+)$/',
		'STR_AND_NUM' => '/^([0-9]+[a-zA-Z]+|[a-zA-Z]+[0-9]+|[a-zA-Z]+[0-9]+[a-zA-Z]+)$/',
		'LOWER_UPPER_NUM' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/',
		'HOST' => '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/',
	];
	
	public function __construct($action = null, $ajax=false) {
		
		if (!empty(static::$ACTION_AUTH_REQ) && !empty(static::$ACTION_AUTH_REQ[$action]) && !$ajax && $action) {
			$req = static::$ACTION_AUTH_REQ[$action];
			$rank = getSession('Auth') ? getSession('Auth')['rank'] : 0;

			if (($req['level'] == 0 && $rank != 0) || ($req['level'] > 0 && $rank < $req['level'])) {
				redirect($req['else']);
			}
		}
		
		static::$request['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
		static::$request['data'] = $_GET;
		
		//the token for be sure if it was submitted from right place :)
		if (static::$request['method'] == "POST") {
			if (empty($_POST['token']) || htmlspecialchars(trim($_POST['token']), ENT_QUOTES) !== getToken()) {
				echo 'Invalid token';
				die;
			}
			static::$request['data'] = array_merge($_POST, static::$request['data']);
		}
		// GET or POST data will be in param
		if (!isset(static::$request['data']['param'])) {
			static::$request['data']['param'] = null;
		}else{
			if (!empty(static::$INPUT_RULE)) {
				static::requestValidation();
			}
		}
		
		static::$data = isset(static::$request['data']['param']) 
			? static::$request['data']['param'] 
			: null;


	}
	
	protected static function requestValidation() {
		$table_rules = static::$INPUT_RULE;
		$inputs = static::$request['data']['param'];
		$patterns = static::$PATTERN;
		$method = static::$request['data']['action'] ?? "unknown";

		
		if (!empty(static::$ACTION_RULE[$method])) {
			$table_rules = array_merge($table_rules, static::$ACTION_RULE[$method]);
		}
		foreach ($inputs as $field => $input) {
			if (empty($table_rules[$field])) { continue; }
			$input = trim($input);
			$rules = $table_rules[$field];
			$required = false;
			foreach ($rules as $rule => $cond) {
					
				if (!is_string($rule)) {
					$rule = $cond;
				}
				if ($rule === "require") {
					$required = true;
					if ($required && $input === "") {
						static::refuseData(ucfirst($field)."is required", $field);
					}
				} else if ($rule === "type") {
						if (!$required && $input === "") { continue; }
					if (!static::validateString($input, $cond)){
						static::refuseData("Invalid data format for ".ucfirst($field), $field);
					}
				} else if ($rule === "length") {
					if (!$required && $input === "") { continue; }
					$len = strlen($input); 
					if ($len < $cond[0] || $len > $cond[1]) {
						static::refuseData("Invalid length for ".ucfirst($field), $field);
					}
				} else if ($rule === "isUnique" && $cond) {
					if (static::countAll("email = '".$input."'")) {
						static::refuseData( ucfirst($field)." already exist!", $field);
					}
				} else if ($rule === "match") {
					if ($input !== $inputs[$cond]) {
						static::refuseData( ucfirst($field)." not match with ".ucfirst($cond), $field);
					} else {
						unset (static::$request['data']['param'][$field]);
					}
				}
			}
		}
	}
	
	protected static function getParam($str, $type='ALPHA_NUM', $default){
		if (empty(static::$request['data']['param']) || empty(static::$request['data']['param'][$str])) {
			return $default;
		}
		return $this->validateString(static::$request['data']['param'][$str], $type) 
			? static::$request['data']['param'][$str] 
			: $default;
	}
	
	protected static function getData($str, $default){
		if (empty(static::$request['data']) || empty(static::$request['data'][$str])) {
			return $default;
		}
		return $this->validateString(static::$request['data'][$str], 'ALPHA_NUM') 
			? static::$request['data'][$str] 
			: $default;
	}	
	
	protected static function validateString($str, $type="ALPHA_NUM") {
		return preg_match(static::$PATTERN[$type], htmlspecialchars(trim($str), ENT_QUOTES));
	}
	
	public static function refuseData($str, $field="") {
		if (empty(static::$request['error'])) {
			static::$request['error'] = [];
		}
		array_push(static::$request['error'], [$str, $field]);
		if (!empty(static::$request['data']['param'][$field])) {
			unset(static::$request['data']['param'][$field]);
		}
	}

	public static function toObject ($arr, $class){
		$obj = false;
		if (is_array($arr)){
			$obj = new $class();
			foreach ($arr as $key => $value){
				$obj->$key=$value;
			}
		}	
		return $obj;
	}
	
	public static function toArray ($obj){
		$arr = [];
		if (is_object($obj)){
			$arr = get_object_vars($obj);
		}	
		return !empty($arr) ? $arr : false;
	}
	
	public static function getConn() {
		static $conn = null;
		if ($conn===null) {
			$db = json_decode(file_get_contents(DB_CONFIG));
			
			$conn = mysqli_connect(
				$db->host,
				$db->user,
				$db->pass,
				$db->db
			);
			mysqli_set_charset($conn,"utf8");
			//if connection not exist then send error message
			if (!$conn) {
				die (PHP_EOL.'<b>error ['.mysqli_connect_errno().']:</b> <i>'.mysqli_connect_error().'</i>'.PHP_EOL);			
			}
		}
		return $conn;
	}	
	
    public static function find($id){
		return static::readRecords(sprintf('`id` = %u',$id), true);
		
    }
	
    public static function all(){
		return static::readRecords('1', true, true);
    }	

    public static function countAll($cond='1'){
		$query=sprintf("SELECT count(*) as c FROM `%s` WHERE %s",static::$TABLE_NAME, $cond);
		$result = static::execQuery($query);
		if (!empty($result)){
			return $result[0]['c'];
		}else{
			return false;
		}
    }		

    public static function getPage($index=0, $amount, $cond='1'){
    	return static::readRecords($cond, true, true, $index, $amount);   
        
    }

	public function save($data=null) {
		if (!$data) { return false; }
		$method = isset($this->id) ? 'update' : 'insert';
		$action = static::$request['data']['action'] ?? 'unknown';
		if (!empty(static::$AUTO_FILL[$action])) {
			$auto_fill = static::$AUTO_FILL[$action];
			foreach($auto_fill as $field => $value) {
				$data[$field] = $value;
			}
		}
		return $this->$method($data);
	}
	
	public function insert($data){
		$data['created'] = date("Y-m-d H:i:s");
		$keys = implode(', ',array_keys($data));
		$values = implode('", "',array_values($data));
		$query = sprintf('INSERT INTO `%s` ( %s ) VALUES ( "%s" )', static::$TABLE_NAME, $keys, $values);
		return static::execQuery($query);
	}
	
	public function update($data){
		$id = $data['id'];
		unset($data['id']);
		$data['updated'] = date("Y-m-d H:i:s");
		$pair = [];
		foreach ($data as $field => $value){
			$pair[] = $field.' = "'.$value.'"';
		}
		$query = sprintf("UPDATE `%s` SET %s WHERE id='%u'", static::$TABLE_NAME, implode(', ',$pair), $id);
		return static::execQuery($query);
	}	

    protected function setRecord ($record){
		foreach($record as $key => $value){
			if (!isset(static::$GUARD) || !in_array($key, static::$GUARD)) {
				$this->$key = $value;
			}
		}
    }		

    protected static function delete($id=0){
		if (empty(intval($id))) { return false;}
		return static::execQuery(sprintf("DELETE FROM `%s` WHERE id='%u'", static::$TABLE_NAME, intval($id)));
    }
    
    protected static function deleteRecords($conditons="0"){
		$query=sprintf("DELETE FROM `%s` WHERE %s",static::$TABLE_NAME,$conditons);
		return static::execQuery($query);
    }

	protected static function readRecords ($conditons="1", $returnData=false, $array=false, $pageIndex=0, $perPage=PHP_INT_MAX, $orderBy=false, $orderDesc=false){

		if ($perPage < 1) $perPage = 30;
		$orderBy = $orderBy ? sprintf("ORDER BY `%s` %s",$orderBy,$orderDesc ? "DESC" : "ASC") : "";
		$startPage = $pageIndex>-1 ? ($pageIndex*$perPage): 0;
		$endPage = $pageIndex>-1 ? $perPage : PHP_INT_MAX;
		$joinStr = "";
		$tableName = static::$TABLE_NAME;
		$query = sprintf("SELECT * FROM `%s` %s WHERE %s %s LIMIT %u, %u",$tableName,$joinStr, $conditons,$orderBy, $startPage,$endPage);
		$result = static::execQuery($query);

		// we check if we got result
		if (!empty($result)){
			// we check if we need return data
			if ($returnData!==false){
				$className = get_called_class();
				$out = [];
				//if we need 1 item then first block, if we need mor record then we use foreach
				if (!$array){
					$obj = new $className();
					$obj -> setRecord($result[0]);
					$out = $obj;
				}else{
					foreach ($result as $row) {
						$obj = new $className();
						$obj -> setRecord($row);
						$out[] = $obj;
					}	
				}
				return $out;
			}
			return true;
		}else{
			return false;
		}		
	}
	
	public function getInsertedId(){
		if (isset($this->id)) {
			return $this->id;
		}else{
			return mysqli_insert_id(static::getConn());
		}
		
	}
	
	protected static function execQuery ($query){
		$queryResult = mysqli_query(static::getConn(), $query);
		
		if (!$queryResult) {
			if (DEBUG) {
				echo "Failed query: ".PHP_EOL;
				var_dump($query);
			} else {
				die ('Problem with database, please contact with admin!'.PHP_EOL);			
			}
		}
		
		if (is_object($queryResult)){
			$result = [];
			while($row = mysqli_fetch_assoc($queryResult)){
				$result[] = $row;
			}; 
			mysqli_free_result($queryResult);
			return $result;
		}
		return $queryResult;
	}
	
}
