<?php

require_once "library.php";

//var_dump($DB);

session_start();

$com = isset($_REQUEST['com'])
		? $_REQUEST['com']
		: false;

$return = false;

if ($com == 'message') {

	if (empty($_REQUEST['type']) || $_REQUEST['type'] == 'main') {

		$passed = FlightParameters::canPass($DB);

		if ($passed >= 2) {
			$return = [
				'message'	 => T::load('passed.html'),
				'action'	 => 'test_passed',
				'type'		 => 'error'
			];
			unset($_SESSION['start']);
		} else {

			$return = [
				'message' => str_replace('{{test_type}}', ($passed == 1
								? 'REAL'
								: 'TRIAL'), T::load('start.html'))
			];
		}
	} elseif ($_REQUEST['type'] == 'fly_red') {

		if (isset($_SESSION['test_results'])) {
			$_SESSION['test_results']['fly_out_of_modes'] = 1;
		}

		$return = [
			'message'	 => T::load('fail.html'),
			'type'		 => 'error'
		];
	} else {
		$return = [
			'message' => $_REQUEST['type']
		];
	}
} elseif ($com == 'flight_parameters') {

	if (FlightParameters::tryToStart($DB)) {

		unset($_SESSION['start']);

		die(json_encode([
			'message'	 => T::load('passed.html'),
			'action'	 => 'test_passed',
			'type'		 => 'error'
		]));
	}

	if (empty($_SESSION['test_end_time'])) {
		$_SESSION['test_end_time'] = time() + TestParameters::getTimeOfTest($DB);
	}

	if (empty($_SESSION['test_results'])) {
		$_SESSION['test_results'] = [];
	}

	if ($_SESSION['test_end_time'] - time() <= 0) {

		if (isset($_SESSION['flight_mission'])) {
			$_SESSION['test_results'][] = $_SESSION['flight_mission'];
		}

		$results = FlightParameters::showResults($_SESSION['test_results'], $DB);

		$return = [
			'message'	 => empty($results)
					? T::load('completed.html')
					: T::load('trial_test_completed.html'),
			'action'	 => 'test_completed',
			'results'	 => $results,
		];

		die(json_encode($return));
	}

	$error = false;
	if ($_REQUEST['speed'] * 1 <= 140 || $_REQUEST['speed'] * 1 >= 700) {
		$error = 'Allowed speed 140 - 700 kts';
	}

	if ($_REQUEST['altitude'] * 1 <= 400) {
		$error = 'Allowed altitude not less than 400 ft';
	}

	if (abs($_REQUEST['pitch'] * 1) > 25) {
		$error = 'Allowed pitch angle not more than 25 degrees';
	}

	if (abs($_REQUEST['roll'] * 1) > 60) {
		$error = 'Allowed bank not more than 60 degrees';
	}

	if (abs($_REQUEST['altspeed'] * 1) > 6) {
		$error = 'Change altitude speed not more than 6000 ft/min';
	}

	if (!empty($error)) {

		$return = [
			'message'	 => '<p><b>TEST FAILED</b></p>Flight out of allowed modes!<br/><br/>' . $error . '<br/><br/><a href="pilot_home.php">Click to continue</a></p><br/>',
			'type'		 => 'error'
		];

		$DB->query("
			UPDATE `flight_user_test`
			SET `answer` = ?, 
				`json` = ?
			WHERE `user_id` = ?
			AND `id` = ?
		", [
			$return['message'],
			json_encode($return),
			$_SESSION['user_id'],
			$_SESSION['test_id']
		]);
	} else {//check for a time
		$time = time();

		$return = [];
		$task = [];

//check data

		if (!isset($_SESSION['checktime']) || ($time - $_SESSION['checktime'] > 20)) {//create new task
//TODO: set level of task
			$_SESSION['checktime'] = $time;

			if (!isset($_SESSION['level'])) {
				$_SESSION['level'] = 1;
			}

			/*
			  $operation = mt_rand(0, $_SESSION['level'] == 1
			  ? 1
			  : 2); //+ - *

			  $_SESSION['task'] = [
			  'a'			 => mt_rand(0, 10 * $_SESSION['level']),
			  'operation'	 => $operation == 0
			  ? '+'
			  : ($operation == 1
			  ? '-'
			  : '*'),
			  'b'			 => mt_rand(0, 10 * $_SESSION['level']),
			  'result'	 => 0
			  ];


			  $task = [
			  'task' => $_SESSION['task']['a'] . ' ' . $_SESSION['task']['operation'] . ' ' . $_SESSION['task']['b'] . ' = <span class="answer">?</span>'
			  ];
			 */

			if (isset($_SESSION['task']) && isset($_SESSION['flight_mission'])) {
				$_SESSION['flight_mission']['tasks'][] = $_SESSION['task'];
			}

			$question = FlightParameters::getTask($DB);

			$_SESSION['task'] = [
				'question'		 => key($question),
				'question_id'	 => isset($_SESSION['ids']) && isset($_SESSION['ids'][key($question)])
						? $_SESSION['ids'][key($question)]
						: false,
				'correct_answer' => current($question),
				'answer'		 => false,
				'result'		 => 0
			];

			$task = [
				'task' => $_SESSION['task']['question'] . ' = <span class="answer">?</span>'
			];
		} else if (isset($_SESSION['task']) && isset($_REQUEST['answer']) && $_REQUEST['answer'] != 'idontknow') { //check answer
			$_SESSION['task']['result'] = FlightParameters::checkAnswer($_REQUEST['answer'])
					? 1
					: -1;

			$_SESSION['task']['answer'] = $_REQUEST['answer'];

			if (isset($_SESSION['flight_mission'])) {
				$_SESSION['flight_mission']['tasks'][] = $_SESSION['task'];
			}

			unset($_SESSION['task']);
		}

//check flight parameters

		if (isset($_SESSION['flight_mission']) &&
				FlightParameters::isAchieved() &&
				$_SESSION['flight_mission']['status'] != 'completed'
		) { //complete flight mission
			$_SESSION['flight_mission']['status'] = 'completed';

			$_SESSION['flight_mission']['ok'] = 0;
			$_SESSION['flight_mission']['no'] = 0;

			$return = [
				'message'	 => 'Hold flight parameters: altitude ' . $_SESSION['flight_mission']['altitude'] * 1 . 'ft , speed ' . $_SESSION['flight_mission']['speed'] . 'kts, fly heading ' . $_SESSION['flight_mission']['rotation'] . '',
				'type'		 => 'flight_mission_hold'
			];

			$_SESSION['time'] = $time;
		} elseif (isset($_SESSION['flight_mission']) && $_SESSION['flight_mission']['status'] == 'completed' && isset($_SESSION['time']) && ($time - $_SESSION['time'] < 10)) {

			$course = $_SESSION['flight_mission']['rotation'];

			if ($course < 10) {
				$course = '00' . $course;
			} else if ($course < 100) {
				$course = '0' . $course;
			}

			if (!FlightParameters::isAchieved()) {

				$_SESSION['flight_mission']['no'] += 1;

				$return = [
					'message'	 => 'Hold flight parameters: altitude ' . $_SESSION['flight_mission']['altitude'] * 1 . 'ft , speed ' . $_SESSION['flight_mission']['speed'] . 'kts, fly heading ' . $course,
					'type'		 => 'flight_mission_error'
				];
			} else {

				$_SESSION['flight_mission']['ok'] += 1;

				$return = [
					'message'	 => 'Hold flight parameters: altitude ' . $_SESSION['flight_mission']['altitude'] * 1 . 'ft , speed ' . $_SESSION['flight_mission']['speed'] . 'kts, fly heading ' . $course,
					'type'		 => 'flight_mission_hold'
				];
			}
		} else if (isset($_SESSION['flight_mission']) && $_SESSION['flight_mission']['status'] == 'completed' && isset($_SESSION['time']) && ($time - $_SESSION['time'] >= 10)) {

			$return = [
				'action' => 'hide_flight_mission'
			];

			$_SESSION['test_results'][] = $_SESSION['flight_mission'];

			if (!isset($_SESSION['level'])) {
				$_SESSION['level'] = 1;
			} else {
				$_SESSION['level'] += 1;
			}

			unset($_SESSION['flight_mission']);
		} else if (isset($_SESSION['flight_mission']) && (time() - $_SESSION['flight_mission']['start_time'] > 60)) {

			$return = [
				'action' => 'hide_flight_mission'
			];

			$_SESSION['test_results'][] = $_SESSION['flight_mission'];

			if (!isset($_SESSION['level'])) {
				$_SESSION['level'] = 1;
			} else {
				$_SESSION['level'] += 1;
			}

			unset($_SESSION['flight_mission']);

			$_SESSION['time'] = 0;
		} else if ((!isset($_SESSION['flight_mission']) && (!isset($_SESSION['time']) || ($time - $_SESSION['time']) >= 10))) { //there is no mission yet

			/* if ($_SESSION['level'] < 0) {
			  $heading = round($_REQUEST['rotation'] / 10, 0) * 10;
			  } else {
			  $heading = round(($_REQUEST['rotation'] * 1 + mt_rand(0, 30)) / 10, 0) * 10;
			  if ($heading < 0) {
			  $heading = 360 - $heading;
			  } elseif ($heading >= 360) {
			  $heading = $heading - 360;
			  }
			  } */

			$mission = FlightParameters::getMissionNow($DB);

			$_SESSION['flight_mission'] = [
				'speed'		 => $mission['speed'],
				'rotation'	 => $mission['heading'],
				'altitude'	 => $mission['altitude'],
				'status'	 => 'not_completed',
				'tasks'		 => [],
				'start_time' => time()
			];

			/*
			  $_SESSION['flight_mission'] = [
			  'speed'		 => $_SESSION['level'] >= 0
			  ? round((max(180, $_REQUEST['speed'] * 1 + (20 - mt_rand(0, 40)) / 100 * $_REQUEST['speed'])) / 10, 0) * 10
			  : round($_REQUEST['speed'], 0),
			  'rotation'	 => $heading,
			  'altitude'	 => $_SESSION['level'] >= 0
			  ? round((max(500, $_REQUEST['altitude'] * 1 + (20 - mt_rand(0, 40)) / 100 * $_REQUEST['altitude'])) / 10, 0) * 10
			  : round($_REQUEST['altitude'], 0),
			  'status'	 => 'not_completed',
			  'tasks'		 => []
			  ]; */

			$return = [
				'message'	 => FlightParameters::getMission($_SESSION, $_REQUEST),
				'type'		 => 'new_flight_mission'
			];
		} else {
			$return = [
				'status' => 'ok',
				'data'	 => $_REQUEST,
				'time'	 => $_SESSION['time']
			];
		}

		$return = array_merge($return, $task);
	}

	$remain = $_SESSION['test_end_time'] - time();

	$return = array_merge($return, [
		'remain'	 => $remain,
		'color'		 => TestParameters::getColor($remain, $DB),
		'test_type'	 => TestParameters::getTestType($DB)
	]);
}

die(json_encode($return));
