<?php

class T {

	public static function load($file) {

		ob_start();
		include $file;
		$text = ob_get_contents();
		ob_end_clean();

		return $text;
	}

}

class TestParameters {

	public static function getTestID() {
		return isset($_SESION['test_id'])
				? $_SESSION['test_id']
				: false;
	}

	public static function getTestType($DB) {

		if (!isset($_SESSION['test_type'])) {

			$_SESSION['test_type'] = $DB->select("
			SELECT `type` 
			FROM `flight_user_test`
			WHERE `id` = ?			
		", [
						$_SESSION['test_id']
					])[0]['type'];
		};

		return $_SESSION['test_type'];
	}

	public static function getTimeOfTest($DB) {

		return self::getTestType($DB) == 'trial'
				? 180
				: 300;
	}

	public static function getColor($remain, $DB) {
		$per = $remain / self::getTimeOfTest($DB) * 100;

		if ($per < 10) {
			return 'red';
		} else if ($per < 30) {
			return 'yellow';
		} else {
			return 'lime';
		}
	}

	public static function getSpeedRange() {
		return 5;
	}

	public static function getAltitudeRange() {
		return 50;
	}

	public static function getCourseRange() {
		return 5;
	}

	public static function reset() {
		unset($_SESSION['start']);
		unset($_SESSION['time']);
		unset($_SESSION['flight_mission']);
		unset($_SESSION['task']);
		unset($_SESSION['checktime']);
		unset($_SESSION['test_results']);
		unset($_SESSION['test_end_time']);
		unset($_SESSION['level']);
		unset($_SESSION['used_questions']);
		unset($_SESSION['used_mission']);
		unset($_SESSION['test_id']);
		unset($_SESSION['test_type']);
	}

}

class FlightParameters {

	public static function getMission($s, $r) {

		$new_course = $_SESSION['flight_mission']['rotation'];

		if ($new_course < 10) {
			$new_course = '00' . $new_course;
		} elseif ($new_course < 100) {
			$new_course = '0' . $new_course;
		}

		return join(', ', [
			$_SESSION['flight_mission']['altitude'] == $r['altitude'] * 1
					? 'Stay at ' . $r['altitude'] * 1 . 'ft'
					: ($_SESSION['flight_mission']['altitude'] > $r['altitude'] * 1
							? 'Climb to '
							: 'Descend to ') . $_SESSION['flight_mission']['altitude'] . 'ft',
			$_SESSION['flight_mission']['speed'] == $r['speed'] * 1
					? 'hold speed at ' . $_REQUEST['speed'] * 1 . 'kts'
					: ($_SESSION['flight_mission']['speed'] > $_REQUEST['speed'] * 1
							? 'increase speed to '
							: 'reduce speed to ') . $_SESSION['flight_mission']['speed'] . 'kts',
			$_SESSION['flight_mission']['rotation'] == $r['rotation'] * 1
					? 'fly heading ' . $new_course . ''
					: ($_SESSION['flight_mission']['rotation'] > $r['rotation'] * 1
							? 'fly heading '
							: 'fly heading  ') . $new_course . ''
				]
		);
	}

	public static function getAvailableMissions($DB) {

		$DB->query("CREATE TABLE IF NOT EXISTS `flight_missions`(
			`id` int(11) unsigned NOT NULL auto_increment,
			`altitude` int default 0,
			`speed` int default 0,
			`heading` int default 0,
			`type` enum('trial','real') default 'trial',
			PRIMARY KEY  (`id`)
		)", []);

		$count = $DB->select("SELECT count(*) as `count` FROM `flight_missions`", [])[0]['count'];

		if (empty($count)) {

			$default = [
				[
					'altitude'	 => 1500,
					'speed'		 => 185,
					'heading'	 => 160,
				],
				[
					'altitude'	 => 3000,
					'speed'		 => 275,
					'heading'	 => 275,
				],
				[
					'altitude'	 => 4500,
					'speed'		 => 230,
					'heading'	 => 095,
				],
				[
					'altitude'	 => 2400,
					'speed'		 => 230,
					'heading'	 => 095,
				],
				[
					'altitude'	 => 2400,
					'speed'		 => 200,
					'heading'	 => 320,
				],
				[
					'altitude'	 => 1000,
					'speed'		 => 170,
					'heading'	 => 180,
				]
			];


			$h = [];
			$h2 = [];

			foreach ($default as $record) {
				$h[] = '(' . $record['altitude'] . ', ' . $record['speed'] . ', ' . $record['heading'] . ",'" . 'trial' . "')";
				$h[] = '(' . $record['altitude'] . ', ' . $record['speed'] . ', ' . $record['heading'] . ",'" . 'real' . "')";
			}

			$DB->query("INSERT INTO `flight_missions` 
				(`altitude`, `speed`, `heading`, `type`) 
				VALUES " . join(',', $h), []);

			return $default;
		} else {

			$data = $DB->select("SELECT * FROM `flight_missions`", []);

			$return = [];
			foreach ($data as $record) {
				$return[] = [
					'altitude'	 => $record['altitude'],
					'heading'	 => $record['heading'],
					'speed'		 => $record['speed']
				];
			}

			return $return;
		}
	}

	public static function getMissionNow($DB) {
		if (!isset($_SESSION['used_mission'])) {
			$_SESSION['used_mission'] = 0;
		}

		$missions = self::getAvailableMissions($DB);

		if ($_SESSION['used_mission'] > count($missions) - 1) {
			$_SESSION['used_mission'] = 0;
		}

		$_SESSION['used_mission'];

		$mission = $missions[$_SESSION['used_mission']];

		$_SESSION['used_mission'] += 1;

		return $mission;
	}

	public static function getAvailableTasks($DB) {

		$DB->query("CREATE TABLE IF NOT EXISTS `flight_tasks`(
			`id` int(11) unsigned NOT NULL auto_increment,
			`question` tinytext,
			`answer` tinytext,
			`type` enum ('trial', 'real') default 'trial',
			PRIMARY KEY  (`id`)
		)", []);

		$count = $DB->select("SELECT count(*) as `count` FROM `flight_tasks`", [])[0]['count'];

		if (empty($count)) {

			$default = [
				'11 + 15'																								 => 26,
				'24 - 6'																								 => 18,
				'6 multiplied by 7'																						 => 42,
				'20% of 80'																								 => 16,
				'41 + 38'																								 => 79,
				'50 divided by 5'																						 => 10,
				'75 + 35'																								 => 110,
				'140 multiplied by 3'																					 => 420,
				'411 + 275'																								 => 686,
				'333 - 115'																								 => 218,
				'How many minutes are there in 5 hours'																	 => 300,
				'<span style="font-size:0.8rem;">If you remove 40 minutes from 3 hours how many minute are left</span>'	 => 140,
				'100cm = 1 meter 7.6 meters = how many cm'																 => 760,
				'75% of 600'																							 => 450,
				'X = 3, y =6  4X multiplied by 4Y'																		 => 288,
				'42 - 0'																								 => 42,
				'667 - 411'																								 => 256,
				'20% of 100 multiplied by 10'																			 => 200,
				'11 - 4 multiplied by 6'																				 => 42,
				'3211 + 4 - 11'																							 => 3204,
				'32 + 11 + 5 - 21'																						 => 27,
				'75 - 99'																								 => -24,
				'6 multiplied by 10 divided by 6'																		 => 10
			];

			$h = [];

			foreach ($default as $key => $val) {
				$h[] = "('" . $key . "','" . $val . "','" . 'trial' . "')";
				$h[] = "('" . $key . "','" . $val . "','" . 'real' . "')";
			}

			$DB->query("INSERT INTO `flight_tasks` 
				(`question`, `answer`, `type`) 
				VALUES " . join(',', $h), []);

		} 

			$data = $DB->select("SELECT * FROM `flight_tasks`", []);

			$_SESSION['ids'] = [];
			
			$return = [];
			foreach ($data as $record) {
				$return[$record['question']] = $record['answer'] * 1;
				$_SESSION['ids'][$record['question']] = $record['id'];
			}

			return $return;
	}

	public static function getTask($DB) {

		if (!isset($_SESSION['used_questions'])) {
			$_SESSION['used_questions'] = [];
		}

		$tasks = self::getAvailableTasks($DB);

		foreach ($tasks as $key => $val) {
			if (!isset($_SESSION['used_questions'][$key])) {
				$_SESSION['used_questions'][$key] = true;
				return [$key => $val];
			}
		}
	}

	public static function showResults($results, $DB) {
		
		$return = [];
		
		foreach ($results as $mission) {

			$no = [];
			$ok = [];

			foreach ($mission['tasks'] as $task) {
				if ($task['result'] > 0) {
					$ok[] = [
						'question_id' => $task['question_id'],
						'task'	 => $task['question'],
						'correct_answer' => $task['correct_answer'],
						'answer' => $task['answer']
					];
				} else {
					$no[] = [
						'question_id' => $task['question_id'],
						'task'	 => $task['question'],
						'correct_answer' => $task['correct_answer'],
						'answer' => $task['answer']
					];
				}
			}
			
			$return[] = [
				'title'		 => 'Altitude: ' . $mission['altitude'] . ', Speed: ' . $mission['speed'] . ', Course: ' . $mission['rotation'],
				'achieved'	 => $mission['status'] == 'completed'
						? 'yes'
						: null,
				'holded'	 => $mission['ok'] + $mission['no'] == 0
						? 0
						: round(100 * $mission['ok'] / ($mission['ok'] + $mission['no'])),
				'tasks'		 => [
					'success'	 => $ok,
					'failed'	 => $no
				]
			];
			
			$html = '';

			$h = [];
			$i = 0;

			$completed = 0;
			$failed = 0;

			foreach ($return as $mission) {

				$color = 'red';

				if ($mission['achieved'] == 'yes') {
					$completed += 1;
					if ($mission['holded'] * 1 > 70) {
						$color = 'green';
					}
				} else {
					$failed += 1;
				}

				$total_tasks = count($mission['tasks']['success']) + count($mission['tasks']['failed']);

				$success = $total_tasks == 0
						? 0
						: round(100 * count($mission['tasks']['success']) / $total_tasks);

				$h[] = '<p style="color: ' . $color . '; text-align:left;"><b>Mission ' . ($i + 1) . ': ' . $mission['title '] . ' </b></p>';
				$h[] = '<div style="padding-left:20px; text-align:left;">';
				$h[] = '<p style="color:' . ($mission['achieved'] == 'yes'
								? 'green'
								: 'red') . ';">' . ($mission['achieved'] == 'yes'
								? 'Mission completed'
								: 'Mission is not completed') . '</p>';
				$h[] = '<p style="color:' . ($mission['holded'] * 1 > 70
								? 'green'
								: 'red') . ';">Hold parameters for ' . $mission['holded'] . '% of time</p>';
				$h[] = '<p style="color: ' . ($success > 70
								? 'green'
								: 'red') . ';">Correct answers: ' . $success . '%</p>';
				$h[] = '</div>';
			}

			$html = '<p style="text-align:center">' . $completed . ' missions completed, ' . $failed . ' missions failed.</p>' . join('', $h);
		}
		
		$test_type = $DB->select("
			SELECT `type` 
			FROM `flight_user_test`
			WHERE `user_id` = ?
			AND `id` = ?
		", [
					$_SESSION['user_id'],
					$_SESSION['test_id']
				])[0]['type'];

		if ($test_type === 'real') {

			$DB->query("
			UPDATE `flight_user_test`
			SET `answer` = ?, 
				`json` = ?
			WHERE `user_id` = ?
			AND `id` = ?
		", [
				$html,
				json_encode($return),
				$_SESSION['user_id'],
				$_SESSION['test_id']
			]);

			return false;
		} else {
			return $return;
		}
	}

	public static function canPass($DB) {

		$DB->query("CREATE TABLE IF NOT EXISTS `flight_user_test`(
			`id` int(11) unsigned NOT NULL auto_increment,
			`user_id` int(11) unsigned NOT NULL,
			`answer` text,
			`json` text,
			`when` timestamp default CURRENT_TIMESTAMP,
			`type` enum ('real','trial') default 'trial',
			PRIMARY KEY  (`id`)
		)", []);

		$count = $DB->select("
			SELECT count(*) as `count`
			FROM `flight_user_test`
			WHERE `user_id` = ?
		", [
					$_SESSION['user_id']
				])[0]['count'];

		//return $count;
		
		return 0;
	}

	public static function tryToStart($DB) {

		if (empty($_SESSION['user_id'])) {
			throw new Exception('User not logged');
		}

		if (!empty($_SESSION['start'])) {
			return false;
		} else {
			$_SESSION['start'] = true;
		}

		$passed = self::canPass($DB);
	
		if ($passed >= 2) {
			return 'Test already passed';
		}

		$test_id = $DB->query("
				INSERT INTO `flight_user_test`
				SET `user_id` = ?,
				`type` = ?
		", [
			$_SESSION['user_id'],
			$passed == 0
					? 'trial'
					: 'real'
		]);

		$_SESSION['test_id'] = $test_id;

		return false;
	}

	public static function getDiapason($ofWhat) {
		if ($ofWhat === 'speed') {
			return TestParameters::getSpeedRange();
		} elseif ($ofWhat === 'rotation') {
			return TestParameters::getCourseRange();
		} elseif ($ofWhat === 'altitude') {
			return TestParameters::getAltitudeRange();
		}
	}

	public static function isAchieved() {

//check situation when angle is 359.99
		$rotation = $_REQUEST['rotation'] * 1 >= 360
				? $_REQUEST['rotation'] * 1 - 360
				: ($_REQUEST['rotation'] * 1 < 0
						? 360 - $_REQUEST['rotation'] * 1
						: $_REQUEST['rotation'] * 1);

		//die($_SESSION['flight_mission']['rotation'] . ', ' . $rotation*1);

		if (abs($_SESSION['flight_mission']['speed'] - $_REQUEST['speed'] * 1) < self::getDiapason('speed') &&
				abs($_SESSION['flight_mission']['rotation'] - $rotation * 1) < self::getDiapason('rotation') &&
				abs($_SESSION['flight_mission']['altitude'] - $_REQUEST['altitude'] * 1) < self::getDiapason('altitude')) {
			return true;
		} else {
			return false;
		}
	}

	public static function checkAnswer($answer) {

		return $answer * 1 === $_SESSION['task']['correct_answer'] * 1
				? true
				: false;

		if ($_SESSION['task']['operation'] === '+') {
			return $_SESSION['task']['a'] + $_SESSION['task']['b'] == $answer * 1;
		} elseif ($_SESSION['task']['operation'] === '-') {
			return $_SESSION['task']['a'] - $_SESSION['task']['b'] == $answer * 1;
		} else if ($_SESSION['task']['operation'] === '*') {
			return $_SESSION['task']['a'] * $_SESSION['task']['b'] == $answer * 1;
		}
	}

}

class Database {

	private $db_user;
	private $db_pass;
	private $db_name;
	private $db_host;
	public $db;

	function __construct($db_user, $db_pass, $db_name, $db_host) {
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		$this->db_name = $db_name;
		$this->db_host = $db_host;
		//$this->connect();
	}

	/*
	 * Connect to database
	 */

	private function connect() {
		$mysqli = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);

		if ($mysqli->connect_error) {
			die('Connect Error (' . $mysqli->connect_errno . ') '
					. $mysqli->connect_error);
		}

		if (mysqli_connect_error()) {
			die('Connect Error (' . mysqli_connect_errno() . ') '
					. mysqli_connect_error());
		}

		$this->db = $mysqli;
	}

	public static function refValues($arr) {
		if (strnatcmp(phpversion(), '5.3') >= 0) { //Если версия PHP >=5.3 (в младших версиях все проще)
			$refs = array();
			foreach ($arr as $key => $value) {
				$refs[$key] = &$arr[$key]; //Массиву $refs присваиваются ссылки на значения массива $arr
			}
			return $refs; //Массиву $arr присваиваются значения массива $refs
		}
		return $arr; //return $arr
	}

	public function getByQuery($query, $param, $ifempty = false) {
		return $this->getP($query, $param, $ifempty);
	}

	/*	 * пуе
	 * fill paramters by database data
	 * @param type $query, $param (id f.e)
	 * @param $ifempty = false    if no object found -> false
	 * 				   = 'exception' throw exception
	 * 				   = 'empty' return empty object (same instance but no parameters)
	 */

	public function getP($query, $param, $ifempty = false) {
		$db = $this->select($query, $param);

		if (empty($db)) {
			if (empty($ifempty)) {
				return false;
			} elseif ($ifempty == 'exception') {
				throw new Exception("Unable to find object " . get_class() . " by " . join(', ', $param));
			} elseif ($ifempty == 'empty') {
				return $this;
			} elseif (is_array($ifempty)) { //create object with necessary parameters
				//TODO: this
			}
		}

		return $this->setP($db[0]);
	}

	/**
	 * 
	 * //TODO: add associative placeholders
	 * 
	 * @param type $query = "SELECT * FROM `table` where `a` = ? and `b` = ?"
	 * @param type [a,b]
	 * @param $key - main field for assoc array
	 * @param $val - return only this value
	 * @param $merge - add something for each record
	 * @throws Exception
	 */
	public function select($query, $param = false, $key = false, $val = false, $merge = []) {

		$this->connect();

		if (empty($param)) { //we have no any parameters
			$query_result = $this->db->query($query);

			if (empty($query_result)) {
				$this->disconnect();
				return [];
			}

			$array = [];
			$i = 0;
			while ($record = $query_result->fetch_assoc()) {

				if (empty($key) || !isset($record[$key])) {
					if (empty($val) || !isset($record[$val])) {
						$array[$i] = array_merge($record, $merge);
					} else {
						$array[$i] = $record[$val];
					}
					$i++;
				} else {
					if (empty($val) || !isset($record[$val])) {
						$array[$record[$key]] = array_merge($record, $merge);
					} else {
						$array[$record[$key]] = $record[$val];
					}
				}
			}

			$this->disconnect();
			return $array;
		}

		//We have some parameters
		if ($stmt = $this->db->prepare($query)) {

			//preparing bind_param	
			call_user_func_array(array(
				$stmt,
				'bind_param'), self::refValues(array_merge([join('', array_fill(0, count($param), 's'))], $param)));
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows == 0) {
				$this->disconnect();
				return [];
			}

			$meta = $stmt->result_metadata();
			$variables = [];
			$data = [];

			while ($field = $meta->fetch_field()) {
				$variables[] = &$data[$field->name];
			}

			call_user_func_array([
				$stmt,
				'bind_result'], $variables);

			$i = 0;
			while ($stmt->fetch()) {

				if (empty($key) || !isset($data[$key])) {
					$array[$i] = [];
					foreach ($data as $k => $v) {
						if (empty($val) || !isset($data[$val])) {
							$array[$i][$k] = $v;
						} else {
							if ($k == $val) {
								$array[$i] = $v;
								break;
							}
						}
					}
					if (is_array($array[$i])) {
						$array[$i] = array_merge($array[$i], $merge);
					}
					$i++;
				} else {
					$array[$data[$key]] = [];

					foreach ($data as $k => $v) {
						if (empty($val) || !isset($data[$val])) {
							$array[$data[$key]][$k] = $v;
						} else {
							if ($k == $val) {
								$array[$data[$key]] = $v;
								break;
							}
						}
					}

					if (is_array($array[$data[$key]])) {
						$array[$data[$key]] = array_merge($array[$data[$key]], $merge);
					}
				}

				// don't know why, but when I tried $array[] = $data, I got the same one result in all rows
			}
			$stmt->free_result();
			$stmt->close();
			$this->disconnect();
			return $array;
		} else {
			$this->disconnect();
			throw new Exception('Error during prepare request' . $query . ' with parameters ' . json_encode($param));
		}
	}

	public function query($query, $param = []) {

		$this->connect();

		if (empty($param)) {
			$query_result = $this->db->query($query);

			if (!empty($this->db->error)) {

				var_dump($this->db);

				$this->disconnect();

				die('stop');

				throw new Exception($this->db->error);
			}
		} else {//we have some parameters
			if ($stmt = $this->db->prepare($query)) {
				//preparing bind_param	
				call_user_func_array(array(
					$stmt,
					'bind_param'), self::refValues(array_merge([join('', array_fill(0, count($param), 's'))], $param)));
				$stmt->execute();
				//TODO: return something
				$stmt->close();
			} else {
				$this->disconnect();
				throw new Exception('Error during prepare request' . $query);
			}
		}

		$return = strpos(strtoupper($query), 'INSERT') !== false
				? $this->db->insert_id
				: false;
		$this->disconnect();
		return $return;
	}

	/**
	 * create new object in database and return
	 * @param type $what
	 */
	public static function create($what) {

		$param = [];
		$query = [];
		foreach ($what as $key => $val) {
			$query[] = "`" . urlencode($key) . "` = ?";
			$param[] = $val;
		}

		$id = (new static())->query("
			INSERT INTO `click_" . mb_strtolower(get_called_class()) . "`
			SET " . join(', ', $query) . "
		", $param);

		return static::getBy([
					mb_strtolower(get_called_class()) . '_id' => $id
		]);
	}

	/*
	 * Kill database connection
	 */

	public function disconnect() {
		$this->db->close();
		return true;
	}

	/*
	 * Check if connection to database is established
	 */

	public function is_connected() {
		return mysqli_ping($this->fb);
	}

	public function toArray() {
		return $this->p;
	}

	public function toObject() {
		return json_decode(json_encode($this->p), FALSE);
	}

	/**
	 * set properties
	 */
	public function setP($what) {
		foreach ($what as $key => $val) {
			$this->p[$key] = empty($val)
					? false
					: $val;
		}
		return $this;
	}

	/**
	 * convert p to named properties
	 * @return \Database
	 */
	public function pToProperties() {
		if (!empty($this->p)) {
			foreach ($this->p as $key => $val) {
				$this->$key = $val;
			}
		}
		return $this;
	}

	/**
	 * Return 1 object by selected parameters
	 * 
	 * expected 
	 * 
	 * [
	 * 		'banner_id' => 1
	 * 		'approved' => 0
	 * 
	 * 		'_notfound'
	 * 
	 * 		'_return'
	 * 
	 * ]
	 * 
	 * always return array of objects
	 * 
	 */
	public static function getBy($what) {

		if (empty($what)) {
			//TODO: return from cash here
			return [];
		}

		$input = [];
		$param = [];
		foreach ($what as $key => $val) {
			if (substr($key, 0, 1) !== '_') {

				if ($val === '_null') {
					$input[] = "`" . urlencode($key) . "` is null ";
				} elseif ($val === '_notnull') {
					$input[] = "`" . urlencode($key) . "` is not null ";
				} else {
					$input[] = "`" . urlencode($key) . "` = ?";
					$param[] = $val;
				}
			}
		}

		if (!empty($what['_return']) && $what['_return'] == 'count') {

			$db = (new static())->select("
			SELECT count(*) as `count` 
			FROM `click_" . mb_strtolower(get_called_class()) . "`
			WHERE " . join(' AND ', $input), $param);

			return empty($db)
					? 0
					: $db[0]['count'];
		}


		$control_k = empty($what['_return']) || !is_array($what['_return'])
				? null
				: (
				is_numeric(key($what['_return']))
						? null
						: key($what['_return'])
				);

		$order = !empty($what['_order']) && is_array($what['_order'])
				? ' ORDER BY `' . join('`,`', array_keys($what['_order'])) . "` " . current($what['_order']) . " "
				: '';

		$limit = isset($what['_return']) && is_array($what['_return'])
				? ""
				: " LIMIT 1";


		$db = (new static())->select("
			SELECT * 
			FROM `click_" . mb_strtolower(get_called_class()) . "`
			WHERE " . join(' AND ', $input) . $order . $limit
				, $param, $control_k);

		if (empty($db)) {
			if (empty($what['_notfound'])) {
				return false;
			} elseif (is_array($what['_notfound'])) { //create object with necessary parameters
				return static::create($what['_notfound']);
			} elseif (is_string($what['_notfound'])) {
				throw new Exception($what['_notfound']);
			} else {
				throw new Exception("Unable to find any objects of class " . get_called_class() . " by " . json_encode($what));
			}
		}

		if (!empty($what['_return']) && is_array($what['_return'])) {
			$return = [];
			foreach ($db as $key => $record) {
				$obj = (new static())->setP($record);
				if (isset($what['_load']) && is_array($what['_load'])) {
					if (array_values($what['_load']) === $what['_load']) { //a pack of loadings
						foreach ($what['_load'] as $pack) {
							foreach ($pack as $method => $param) {
								$obj->$method($param);
							}
						}
					} else {
						foreach ($what['_load'] as $method => $param) {
							$obj->$method($param);
						}
					}
				}
				$return[$key] = current($what['_return']) == 'array'
						? $obj->p
						: $obj;
			}
			return $return;
		} else {
			return (new static())->setP($db[0]);
		}
	}

	/**
	 * getter
	 * @param type $what
	 */
	public function get($what) {

		if ($what == '_table') {
			return 'click_' . mb_strtolower(get_called_class());
		}

		if ($what == '_id') {
			return mb_strtolower(get_called_class()) . '_id';
		}

		if (ucfirst($what) == $what && class_exists($what) && isset($this->p[strtolower($what) . '_id'])) {
			if (empty($this->p[$what])) {
				$this->p[$what] = call_user_func([
					$what,
					'getBy'
						], [
					strtolower($what) . '_id'	 => $this->p[strtolower($what) . '_id'],
					'_notfound'					 => true
				]);
			}
			return $this->p[$what];
		}

		return $this->p[$what];
	}

	public function load($what) {
		$this->get($what);
		return $this;
	}

	/**
	 * setter
	 * @param type $what
	 * 
	 * expected:
	 * 
	 * [
	 * 		'field' => value
	 * ]
	 * 
	 */
	public function set($what) {

		$properties = [];
		$input = [];

		//TODO: remove id

		foreach ($what as $key => $val) {
			if (isset($this->p[$key])) {
				$this->p[$key] = $val;
				$input[] = $val;
				$properties[] = "`" . urlencode($key) . "` = ?";
			}
		}

		if (empty($properties) || empty($input)) {
			return $this;
		}

		$input[] = $this->get($this->get('_id'));

		$this->query("
					UPDATE `" . $this->get('_table') . "`
					SET " . join(', ', $properties) . "
					WHERE `" . $this->get('_id') . "` = ?
				", $input);

		return $this;
	}

	public function remove() {
		$this->query("
			DELETE FROM `click_" . mb_strtolower(get_called_class()) . "`
			WHERE `" . mb_strtolower(get_called_class()) . "_id` = ?
		", [
			$this->get($this->get('_id'))
		]);
	}

	public function f($what) {
		return htmlspecialchars($this->get($what));
	}

	/**
	 * set custom order
	 */
	public static function setCustomOrder($className, $parent_id, $sortOrder = null) {

		if (!class_exists($className)) {
			throw new Exception('Uncknown class ' . $className);
		}

		if (empty($sortOrder)) {
			$sortOrder = call_user_func([
				$className,
				'getSortOrder'
					], []);
		}

		if ((!empty($sortOrder) && key($sortOrder) == 'order')) {

			switch ($className) {
				case 'Campaign':
					$parent_key = 'user_id';
					break;
				case 'Rotator':
					$parent_key = 'campaign_id';
					break;
				case 'Banner':
					$parent_key = 'rotator_id';
					break;
			}

			$count = call_user_func([
				$className,
				'getBy'
					], [
				$parent_key	 => $parent_id,
				'order'		 => '_notnull',
				'_return'	 => 'count'
			]);

			$notNumerated = call_user_func([
				$className,
				'getBy'
					], [
				$parent_key	 => $parent_id,
				'order'		 => '_null',
				'_return'	 => [0 => 'object']
			]);

			if (!empty($notNumerated)) {

				foreach ($notNumerated as $object) {
					$object->set([
						'order' => $count
					]);
					$count += 1;
				}
			}
		}
	}

}

require_once "config.php";

$DB = new Database(DB_USER, DB_PASS, DB_NAME, DB_HOST);
