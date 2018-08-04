<?php
class Home extends Model {
    public static $TABLE_NAME=false;
	public static $validation = [];
	public static $ACTION_RULE = [];
	public static $INPUT_RULE = [
		'id' => ['type'=>'INTEGER'],
		'host' => ['type'=>'HOST', 'length'=>[1,50]],
 		'user' => ['type'=>'ALPHA_NUM_', 'length'=>[1,50]],
 		'pass' => ['type'=>'ALPHA_NUM_', 'length'=>[0,50]],
 		'db' => ['type'=>'ALPHA_NUM_', 'length'=>[1,50]],
 	];
	
	public static $ACTION_AUTH_REQ = [
		'setup' => ['level' => 0, 'else' => USER_ENTRY],
		'setmysql' => ['level' => 0, 'else' => USER_ENTRY],
	];
	
	public static function index(){
		return [
			'layout' => 'default',
		];
	}

	public static function setup(){
		
		if (file_exists(DB_CONFIG)) {
			return [
				'layout' => 'default',
				'view' => 'Home.index',
			];			
		}
		
		$data = static::$data;
		return [
			'layout' => 'setup',
		];
	}
	
	public static function setmysql(){

		if (file_exists(DB_CONFIG)) {
			return [
				'layout' => 'default',
				'view' => 'Home.index',
			];			
		}
	
		if (static::$request['method'] == "GET") {
			return [
				'layout' => 'setup',
			];
		}
		
		$data = static::$data;
		$status = false;
		$messages = [];
		$requiredData = ['host','pass','user','db'];
		$validData = count(array_intersect(array_keys($data), array_values($requiredData))) == count($requiredData);

		if ($validData) {
			$con = mysqli_connect($data["host"],$data["user"],$data["pass"]);
			if (mysqli_connect_errno()) {
				array_push($messages, ["Failed to connect to MySQL: " . mysqli_connect_error(), 'error']);
			} else {
				if (!mysqli_select_db($con, $data["db"])){
					// sql query with CREATE DATABASE
					$sql = "CREATE DATABASE `{$data["db"]}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
					// Performs the $sql query on the server to create the database
					if (mysqli_query($con, $sql) === TRUE) {
						$status = true;
						array_push($messages, ['Database successfully created', true]);
					} else {
						array_push($messages, ['Database creation failed!', false]);
					}
					mysqli_select_db($con, $data["db"]);
				}else{
					array_push($messages, ['Database exist and useable!', true]);
					$status = true;
				}
				
				if ($status) {
					// check if table exist
					$sql = "DESCRIBE `{USER_TABLE}`";
					if (mysqli_query($con, $sql) !== TRUE) {
						$sql = "CREATE TABLE varga_zsolt_users (
							  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
							  status tinyint(4) UNSIGNED NOT NULL DEFAULT '1',
							  rank int(11) UNSIGNED NOT NULL DEFAULT '0',
							  email varchar(255) DEFAULT NULL,
							  name varchar(255) DEFAULT NULL,
							  password varchar(255) NOT NULL,
							  created datetime DEFAULT NULL,
							  updated datetime DEFAULT NULL
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
						if (!mysqli_query($con, $sql) === TRUE) {
							var_dump(mysqli_connect_error());
							var_dump(mysqli_error($con));
							$status = false;
							array_push($messages, ['Table not exist and cannot be created!', false]);
						}						
					}
				}
			}
			mysqli_close($con);
			
			if ($status) {
				$_SESSION['owner'] = true;
				$myfile = fopen(DB_CONFIG, "w") or die("Unable to open file!");
				fwrite($myfile, json_encode($data));
				fclose($myfile);
				header('Location: ?controller=user&action=signup');
				exit();
			}
			
		} else {
			array_push($messages, ['Missing or invalid field(s): '.(implode(', ', array_diff($requiredData, array_keys($data)))), false]);
		}
		
		return [
			'layout' => 'setup',
			'view' => 'Home.setmysql',
			'status' => $status,
			'messages' => $messages
		];
	}
}