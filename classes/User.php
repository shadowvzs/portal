<?php
class User extends Model {
    public static $TABLE_NAME='varga_zsolt_users';
    public $id;
    public $name;
    public $email;
    public $password;
	public static $validation = [];
	public static $INPUT_RULE = [
		'id' => ['type'=>'INTEGER'],
		'status' => ['type'=>'INTEGER'],
		'name' => ['type'=>'NAME_HUN', 'length'=>[6,64]],
 		'email' => ['type'=>'EMAIL', 'length'=>[6,64], 'isUnique' => true],
 		'password' => ['type'=>'ALPHA_NUM', 'length'=>[6,64]],
 	];	
	public static $ACTION_RULE = [
		'login' => ['email' => ['isUnique' => false]],
		'signup' => ['password2' => ['match' => 'password']],
	];	
	
	//if user not have permission then we redirect him :D
	public static $ACTION_AUTH_REQ = [
		'welcome' => ['level' => 1, 'else' => GUEST_ENTRY],
		'index' => ['level' => 2, 'else' => GUEST_ENTRY],
		'change_status' => ['level' => 2, 'else' => GUEST_ENTRY],
		'change_rank' => ['level' => 2, 'else' => GUEST_ENTRY],
		'list' => ['level' => 2, 'else' => GUEST_ENTRY],
		'login' => ['level' => 0, 'else' => USER_ENTRY],
		'signup' => ['level' => 0, 'else' => USER_ENTRY],
		'logout' => ['level' => 1, 'else' => GUEST_ENTRY],
	];
	
	protected static $GUARD = ['password'];
    
    public static function getUser($email, $pass){
        $cond = sprintf("email='%s' AND password='%s'", $email, md5(SECRET_KEY.$pass));
        $result = static::readRecords($cond, true, false);
		return empty($result) ? false : $result;
    }

	public static function index() {
			return [
				'layout' => 'admin',
			];		
	}
	
	public static function list() {
		die (json_encode([
			'success' => true,
			'data' => static::all()
		]));
	}
	
	public static function change_status() {
		$data = static::$data ?? false;
		$status = false;
		$message = "Param validation failed!";
		if (isset($data['id']) && isset($data['status'])) {
			$user = static::find($data['id']);
			$Auth = getSession('Auth');
			if ($Auth['rank'] <= $user->rank) {
				$message = $user->name." same or  have higher rank than you!";
			} else {
				if ($data['status'] == 4) {
					$result = static::delete($data['id']);
				}else{
					$result = $user->save([
						'id'=>$data['id'],
						'status'=>$data['status']
					]);
				}
				$status = !empty($result);
				$message = $status ? "" : "We cannot save it!";
			}
		}
		die(json_encode([
			'success' => $status,
			'message' => $message,
			'data' => $data
		]));
	}
	
	public static function change_rank() {
		$data = static::$data ?? false;
		$status = false;
		$message = "Param validation failed!";
		if (isset($data['id'])) {
			$user = static::find($data['id']);
			$Auth = getSession('Auth');
			if ($Auth['rank'] <= $user->rank && $Auth['id']) {
				$message = $user->name." same or higher rank than you!";
			} else {
				$user_rank = $user->rank;
				$new_rank = $user_rank == 1 ? 2 : 1;
				$result = $user->save([
					'id' => $data['id'],
					'rank' => $new_rank
				]);

				$status = !empty($result);
				$data['rank'] = $status ? $new_rank : $user_rank;
				$message = $status ? "" : "We cannot save it!";
			}
		}
		die(json_encode([
			'success' => $status,
			'message' => $message,
			'data' => $data
		]));		
	}
	
	public static function welcome() {
		// if you want you can change somthing but not needed for this page
	}
	
    public static function login() {
		if (static::$request['method'] != "GET") {
			$data = static::$data;
			if ($data['email'] && $data['password']) {
				$user = static::getUser($data['email'], $data['password']);
				if (!empty($user)) {
					$user->save([
						'id'=> $user->id,
						'updated'=>date("Y-m-d H:i:s")
					]);
					$user = static::toArray($user);
					setSession('Auth', $user);
					redirect($user['rank'] > 1 ? ADMIN_ENTRY : USER_ENTRY);
				}
			}
			return [
				'messages' => [
					['Wrong password or email!', false]
				]
			];	
		}
    }

    public static function signup() {
		if (static::$request['method'] != "GET") {
			$data = static::$data;
			if (empty($data['name']) || empty($data['password']) || empty($data['email'])) {
				return [
					'messages' => [['Inalid or missing information!', false]]
				];				
			}
			
			$password = md5(SECRET_KEY.$data['password']);
			
			$newUser = new User();
			$result = $newUser->save([
				'email' => $data['email'],
				'name' => $data['name'],
				'password' => $password,
				'rank' => !empty($_SESSION['owner']) ? 3 : 1,
			]);
			
			if (!$result) {
				return [
					'messages' => [['Account creation failed!', false]]
				];				
			}			

			if (!empty($_SESSION['owner'])) {
				unset($_SESSION['owner']);
			}

			return [
				'view' => 'User.login',
				'autofill' => [
					'email' => $data['email'],
					'password' => $data['password'],
					'auto_send' => 'login_Form'
				],
				'messages' => [
					['Account created!', true]
				]
			];			
		}
    }	

    
    public static function logout(){
		setSession('Auth', null);
		redirect(GUEST_ENTRY);
    }    
}