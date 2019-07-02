var Plane = {
	Pmax: 233398.26 * 0.7, //N
	S: 125, //m2
	Scocpit: function() {
		return Math.PI * (3.76 / 2) * (3.76 / 2);
	},
	m: 79010, //kg
	minV: 210, //kts - speed when flaps are off
	Cx: function(angle) {
		var cx = 0.0235;
		return cx;
	},
	Cy: function(angle) {
		var cy = 0.3525;
		return cy;
	},
	CxR: function() {

		return 1.175 / 10; // * (Dencity.value(Altitude.value) / Dencity.value(2000));

		var v = Speed.valueMS();
		return (7.23847528930214 / 1000000 * v * v - 0.0024186184 * v + 0.2293247085);
	},
	CyR: function() {

		return 1.175 * Math.pow(Dencity.value(Altitude.value) / Dencity.value(2000), 3);

		var v = Speed.valueMS();
		return 1.01 * (0.0001152688 * v * v - 0.0427509463 * v + 4.3404605542);// * Math.pow(Dencity.value(Altitude.value) / Dencity.value(0), 1);
	},
	CzR: function() {
		return 10 * Dencity.value(Altitude.value);
	},
	Ccocpit: function() {
		//return 0.05 * Dencity.value(Altitude.value); 
		return this.CxR() * 5;
	}
};

var Dencity = {
	value: function(h) {
		var h = h * 0.3048; //ft -> m
		var y = 1220;
		if (h < 1000) {
			y = (1110 - 1220) / (1000) * h + (1110) - (1110 - 1220) / (1000) * 1000;
		} else if (h < 2000 && h >= 1000) {
			y = (1010 - 1110) / 1000 * h + (1010) - (1010 - 1110) / (1000) * 2000;
		} else if (h < 3000 && h >= 2000) {
			y = (910 - 1010) / 1000 * h + (910) - (910 - 1010) / (1000) * 3000;
		} else if (h < 4000 && h >= 3000) {
			y = (820 - 910) / 1000 * h + (820) - (820 - 910) / (1000) * 4000;
		} else if (h < 5000 && h >= 4000) {
			y = (740 - 820) / 1000 * h + (740) - (740 - 820) / (1000) * 5000;
		} else if (h < 6000 && h >= 5000) {
			y = (660 - 740) / 1000 * h + (660) - (660 - 740) / (1000) * 6000;
		} else if (h < 8000 && h >= 6000) {
			y = (520 - 660) / 2000 * h + (520) - (520 - 660) / (2000) * 8000;
		} else if (h < 10000 && h >= 8000) {
			y = (410 - 520) / 2000 * h + (410) - (410 - 520) / (2000) * 10000;
		} else if (h < 15000 && h >= 10000) {
			y = (193 - 410) / 5000 * h + (193) - (193 - 410) / (5000) * 15000;
		} else if (h > 15000) {
			y = (193 - 410) / 5000 * h + (193) - (193 - 410) / (5000) * 15000;
		}

		return y;
	}
};

var Common = {
	timer: 100,
	/**
	 * 
	 * @param {type} input = {
	 *		action
	 *		d
	 *		maxd
	 *		minValue
	 *		speed
	 *		slowSpeed
	 *		value
	 *		maxValue
	 * }
	 * @returns {undefined}
	 */
	change: function(i) {

		var input = i;

		if (input.action === 'increase' || input.action === 'decrease') {
			input.speed += input.d;
			if (input.speed > input.maxd) {
				input.speed = input.maxd;
			}
		} else if (input.action === 'slow_increase' || input.action === 'slow_decrease') {
			input.speed -= input.slowSpeed;
			if (input.speed <= 0) {
				input.speed = 0;
				input.action = false;
			}
		}
		if (input.action) {
			input.value += (input.action === 'increase' || input.action === 'slow_increase'
					? 1
					: -1) * input.speed;

			if (input.value > input.maxValue) {
				input.value = input.maxValue;
			}

			if (input.value < input.minValue) {
				input.value = input.minValue;
			}

		}

		return input;
	}
};



var Altitude = {
	value: 2000, //ft
	speed: 0,
	vy: 0, //normal speed 
	speedMS: function() {
		return this.speed / 196.85 * 1000;
	},
	speedFS: function(value) {
		return value * 1000 / 60;
	},
	speedTFM: function(value) {
		return value * 196.85 / 1000;
	},
	speedOutput: function() {

		var v = this.speed;

		if (v >= 0) {
			var n = 160 - (-10 / (v + 10 / 7) + 7) * 27;
		} else {
			var n = 160 + (-10 / (Math.abs(v) + 10 / 7) + 7) * 27;
		}
		Line.draw($('.line'), 100, 160, -5, n);
	},
	output: function() {
		$('.altitude_scale').css({
			'margin-top': (-2 + this.value * 0.7) + 'px'
		});
		var alt_v = Math.round(this.value);
		var th = Math.floor(this.value / 1000);
		var remain = alt_v - th * 1000;
		if (remain < 10) {
			remain = '00' + remain;
		} else if (remain < 100) {
			remain = '0' + remain;
		}
		;
		$('.precesious_altitude').html((this.value < 10000
				? '<span style="color:lime;">▧</span>'
				: '') + '<span style=font-size:1.4rem;>' + th + '</span> ' + remain);
		this.speedOutput();
	}
};

var Speed = {
	action: false,
	speed: 0,
	aux: 0,
	value: 200, //Math.sqrt(2 * Plane.m * 9.8/ Dencity.value(Altitude.value) / Plane.Cy(0) / Plane.S) / 0.5144444444444,
	valueMS: function() {
		return this.value / 1.94384;
	},
	valueKnot: function(v) {
		return v * 1.94384;
	},
	output: function() {
		var that = this;
		$('.speed_scale').css({
			'margin-top': (-2 + that.value * 4) + 'px'
		});
		$('.precesious_speed_value').html(Math.round(that.value));

		$('.auxilary').css({
			height: (that.aux < 0
					? Math.min(Math.abs((that.aux * 4)), 274)
					: that.aux * 4) + 'px',
			'margin-top': (that.aux >= 0
					? (-that.aux * 4)
					: 0) + 'px'
		});

	}
};

var Thrust = {
	action: false,
	speed: 0,
	value: 1.5 * Plane.CxR() * Speed.valueMS() * Speed.valueMS() / 2 * Plane.S,
	fv: function() { //max thrust f(v) in kts -> m/s
		//return -140*Speed.valueMS() + Plane.Pmax; 
		return Plane.Pmax;
	},
	throttle: 1.5 * Plane.CxR() * Speed.valueMS() * Speed.valueMS() / 2 * Plane.S / (Plane.Pmax) * 100,
	time: (new Date()).getTime(),
	change: function() {
		var that = this;

		var result = Common.change({
			action: that.action,
			d: 0.1,
			maxd: 10,
			speed: that.speed,
			slowSpeed: 0.5,
			value: that.throttle,
			maxValue: 100,
			minValue: 0
		});

		that.action = result.action;
		that.speed = result.speed;
		that.throttle = result.value;

		that.value = (that.throttle / 100) * that.fv();

		var newTime = (new Date()).getTime();

		var dt = (newTime - that.time) / 1000;

		//сила сопротивления кокпита
		var Fcocpit = Plane.Ccocpit() * Speed.valueMS() * Speed.valueMS() / 2 * Plane.Scocpit();

		//сила сопротивления крыла
		var Fwing = Plane.CxR() * Speed.valueMS() * Speed.valueMS() / 2 * Plane.S;

		//Подъемная сила
		var Ffly = Plane.CyR() * Speed.valueMS() * Speed.valueMS() * Plane.S / 2 * Math.cos(Rotation.angleRAD());

		//Ffly = 0;

		var Pup = that.value - Fwing - Fcocpit; //избыток тяги

		var dx = (Pup - Plane.m * Math.sin(Pitch.angleRAD()) * 9.8) / Plane.m;

		Speed.value += (dx * dt / 0.5144444444444);

		Speed.aux = dx * 60 / 0.5144444444444; //ms2  - knot per minute

		$('.N_indicator').html((Math.round(that.throttle * 100) / 100) + '%');

		//F - сопротивление при подъеме
		var F = ((Altitude.vy > 0)
				? -1
				: 1) * Plane.CzR() * Altitude.vy * Altitude.vy * Plane.S * 1.5 / 2;

		//F = 0;


		//var dy = (Ffly * Math.cos(Pitch.angleRAD()) - Plane.m * 9.8 + F + Pup * Math.sin(Pitch.angleRAD())) / Plane.m; //ms2

		var dy = (Ffly - Plane.m * 9.8 * Math.cos(Pitch.angleRAD()) + F) / Plane.m / 5; // m/s2

		Altitude.vy += dy * dt; //normal speed m/s

		//Altitude.speed += Altitude.speedTFM(dy * dt); //ft x 1000 / minute 

		var Vy = 3.28084 * Speed.value * Math.sin(Pitch.angleRAD()) / 2; //вертикальная составляющая скорости f/s

		Altitude.speed = (3.28084 * Altitude.vy * Math.cos(Pitch.angleRAD()) + Vy) * 60 / 1000; // 1000 ft/min
		Altitude.value += (3.28084 * Altitude.vy * Math.cos(Pitch.angleRAD()) + Vy) * dt;   //ускорением пренебрегаем т.к. dt мал

		that.time = newTime;

		return that;
	},
	output: function() {

	},
	control: function(keyCode) {
		if (keyCode == -81 || keyCode == -87) {
			if (this.action == 'increase') {
				this.action = 'slow_increase';
			} else if (this.action == 'decrease') {
				this.action = 'slow_decrease';
			}
		} else if (keyCode == 87) {
			this.action = 'increase';
		} else if (keyCode == 81) {
			this.action = 'decrease';
		}
	}
};

var Pitch = {
	action: false,
	angle: 0,
	speed: 0,
	angleRAD: function() {
		return this.angle * Math.PI / 180;
	},
	rotate: function(angle) {
		var that = this;
		that.speed += Math.abs(angle) / 100;
		that.angle += Math.min(angle * (that.speed), 0.5);
		//that.output();
	}
};
var Rotation = {
	action: false,
	angle: 0,
	speed: 0,
	total_rotate: 0,
	angleRAD: function() {
		return this.angle * Math.PI / 180;
	},
	getCourse: function() {
		var angle = -this.total_rotate;

		if (angle >= 360) {
			angle = angle - 360;
		}
		if (angle < 0) {
			angle = 360 + angle;
		}

		//this.total_rotate = angle;

		return angle;
	},
	rotate: function(angle) {
		var that = this;
		that.speed += Math.abs(angle) / 10;
		if (angle > 0) {
			that.angle += Math.min(angle * (that.speed), 0.5);
		} else {
			that.angle += Math.max(angle * (that.speed), -0.5);
		}

		console.log(that.angle);

		if (Math.abs(that.angle) < 55) {
			$('.rollout_indicator').css({
				'border-color': 'white',
				background: 'white'
			});
		} else if (Math.abs(that.angle) < 60) {
			$('.rollout_indicator').css({
				'border-color': 'yellow',
				background: 'yellow'
			});
		} else {
			$('.rollout_indicator').css({
				'border-color': 'red',
				background: 'red'
			});
		}

	},
	output: function(degree) {
		var that = this;
		$('.horizon').css({
			transform: 'rotate(' + (that.angle) + 'deg)'
		});
		$('.scale').css({
			transform: 'rotate(' + (that.angle) + 'deg)'
		});
		$('.compass_scale').animate({
			myRotationProperty: degree
		}, {
			step: function(now) {
				$('.compass_scale').css({
					transform: 'rotate(' + now + 'deg)'
				});
			}
		});
	}
};

var FlightMission = {
	show: function(message, type) {
		var message = '<div class="blink_host"><div class="blink" style="display:none;"></div></div>' + message;
		$('.flight_mission').removeClass('normal').removeClass('error').removeClass('success').html(message).addClass(type).fadeIn(function() {

			$('.blink', $('.flight_mission')).fadeOut({
				complete: function() {
					$('.blink', $('.flight_mission')).fadeIn({
						complete: function() {
							$('.blink', $('.flight_mission')).fadeOut();
						},
						duration: 500
					});
				},
				duration: 500
			});

		});
	},
	hide: function(callback) {
		$('.flight_mission').fadeOut(function() {
			if (callback) {
				callback();
			}
			$('flight_mission').html('');
		});
	}
};

var Message = {
	cash: {},
	get: function(message, callback) {
		var that = this;
		if (that.cash[message]) {
			callback(message);
		} else {
			$.get('ajax.php', {
				com: 'message',
				type: message
			}, function(response) {
				if (typeof response != 'object') {
					try {
						var response = $.parseJSON(response);
					} catch (e) {
						console.error(e);
					}
				}


				that.cash[message] = response.message;
				callback(response.message, response.type
						? response.type
						: false);
			});
		}
	},
	show: function(m, type1) {
		var that = this;
		var message = m
				? m
				: 'main';
		that.get(message, function(message, type) {

			//console.log(type, type1);

			that.output(message, type1
					? type1
					: type);
		});
	},
	output: function(message, type) {
		$('.main_message').removeClass('normal').removeClass('error');
		if (!type || type == 'normal') {
			$('.main_message').addClass('normal');
		} else if (type == 'error') {
			$('.main_message').addClass('error');
		}
		$('.main_message').html(message);
		$('.main_message_host').fadeIn();
	},
	hide: function() {
		$('.main_message_host').fadeOut(function() {
			$('.main_message').html('');
		});
	}
}

var Control = {
	action: function() {
		this.begin();
	},
	init: function() {
		var that = this;

		Message.show('main');
		
		//$('.flight_mission').html('').fadeIn();

		$(window).unbind('keydown').keydown(function(ev) {

			ev.preventDefault();

			if (ev.keyCode == 38) {
				Pitch.action = 'dn';
			} else if (ev.keyCode == 40) {
				Pitch.action = 'up';
			} else if (ev.keyCode == 39) {
				Rotation.action = 'lt';
			} else if (ev.keyCode == 37) {
				Rotation.action = 'rt';
			} else if (ev.keyCode == 13 ||
					ev.keyCode == 8 ||
					ev.keyCode == 27 ||
					ev.keyCode == 189 ||
					(ev.keyCode >= 48 && ev.keyCode <= 57)
					) {
				Task.setAnswer(ev.keyCode);
			}

			Thrust.control(ev.keyCode * 1);

			if (ev.keyCode == 32 && that.stop) {
				that.action();
			}

		});
		$(window).unbind('keyup').keyup(function(ev) {

			if (ev.keyCode == 38 || ev.keyCode == 40) {
				if (Pitch.action === 'up') {
					Pitch.action = 'up_stop';
				} else if (Pitch.action === 'dn') {
					Pitch.action = 'dn_stop';
				}
			}

			if (ev.keyCode == 37 || ev.keyCode == 39) {
				if (Rotation.action === 'lt') {
					Rotation.action = 'lt_stop';
				} else if (Rotation.action === 'rt') {
					Rotation.action = 'rt_stop';
				}
			}

			Thrust.control(-1 * ev.keyCode);
			/*
			 if (ev.keyCode == 81 || ev.keyCode == 87) {
			 if (Speed.action == 'increase') {
			 Speed.action = 'increase_stop';
			 } else if (Speed.action == 'decrease') {
			 Speed.action = 'decrease_stop';
			 }
			 }*/

		});
		//that.main();
	},
	stop: true,
	begin: function() {
		this.stop = false;
		$('.flight_mission').hide();
		$('.mathematics_task').hide();
		Message.hide();
		FlightRecorder.process();
		this.main();
	},
	pause: function(message, type) {
		this.stop = true;
		if (message) {
			Message.show(message, type);
		}
	},
	output: function() {
		var that = this;
		$('.horizon').css({
			transform: 'rotate(' + (Rotation.angle) + 'deg) translateY(' + (8.5 * Pitch.angle) + 'px)'
		});
		$('.scale').css({
			transform: 'rotate(' + (Rotation.angle) + 'deg) translateY(' + (8.5 * Pitch.angle) + 'px)'
		});
		$('.roll_indicator').css({
			transform: 'rotate(' + (Rotation.angle) + 'deg)'
		});
		$('.compass_scale').css({
			transform: 'rotate(' + Rotation.total_rotate + 'deg)'
		});
	},
	//main operation
	main: function() {
		var that = this;

		if (that.stop) {
			return false;
		}

		setTimeout(function() {
			if (Pitch.action === 'up' || Pitch.action === 'dn') {
				Pitch.rotate(Pitch.action === 'up'
						? 1
						: -1);
			} else if (Pitch.action === 'up_stop') {
				Pitch.speed = Math.max(0, Pitch.speed - 0.05);
				Pitch.angle += Math.min(1 * (Pitch.speed), 0.5);
				if (Pitch.speed == 0) {
					Pitch.action == false;
				}
			} else if (Pitch.action === 'dn_stop') {
				Pitch.speed = Math.max(0, Pitch.speed - 0.05);
				Pitch.angle += Math.max(-1 * (Pitch.speed), -0.5);
				if (Pitch.speed == 0) {
					Pitch.action == false;
				}
			}

			if (Rotation.action === 'lt' || Rotation.action === 'rt') {
				Rotation.rotate(Rotation.action === 'lt'
						? -1
						: 1);
			} else if (Rotation.action === 'lt_stop') {
				Rotation.speed = Math.max(0, Rotation.speed - 0.5);
				Rotation.angle += Math.max(-1 * (Rotation.speed), -0.5);
				if (Rotation.speed == 0) {
					Rotation.action == false;
				}
			} else if (Rotation.action === 'rt_stop') {
				Rotation.speed = Math.max(0, Rotation.speed - 0.5);
				Rotation.angle += Math.min(1 * (Rotation.speed), 0.5);
				if (Rotation.speed == 0) {
					Rotation.action == false;
				}
			}

			//rotate plane left
			if (Rotation.angle !== 0) {
				Rotation.total_rotate += (Rotation.angle / 200 * Math.cos(Pitch.angle / 180 * Math.PI));
			}

			//climb/descend
			Altitude.output();
			Thrust.change().output();
			//Speed.change();
			Speed.output();
			that.output();
			that.main();
		}, Common.timer);
	}
};
var Line = {
	draw: function(obj, x1, y1, x2, y2) {
		var length = Math.sqrt((x1 - x2) * (x1 - x2) + (y1 - y2) * (y1 - y2));
		var angle = Math.atan2(y2 - y1, x2 - x1) * 180 / Math.PI;
		obj.css({
			width: (2 * length) + 'px',
			left: (x1 - length) + 'px',
			top: y1 + 'px',
			transform: 'rotate(' + angle + 'deg)'
		});
	}
};

var Task = {
	current: false,
	answer: '?',
	send: false,
	show: function(task) {
		var that = this;
		if (that.current != task) {
			that.current = task;
			that.send = false;
			that.answer = '?';
			$('.mathematics_task').fadeOut(function() {
				$(this).html('<div class="blink_host"><div class="blink" style="display:none;"></div></div><span style="font-size:1rem;">What is the correct answer to:</span><br/>' + task).fadeIn();

				$('.blink', $('.mathematics_task')).hide().fadeIn({
					complete: function() {
						$('.blink', $('.mathematics_task')).fadeOut({
							complete: function() {
								$('.blink', $('.mathematics_task')).fadeIn({
									complete: function() {
										$('.blink', $('.mathematics_task')).fadeOut();
									},
									duration: 500
								});
							},
							duration: 500
						});
					},
					duration: 500
				});

			});
		}
	},
	setAnswer: function(keyCode) {
		var that = this;

		if (that.send) {
			return;
		}

		if ((keyCode >= 48 && keyCode <= 57)) {
			var number = String.fromCharCode((96 <= keyCode && keyCode <= 105)
					? keyCode - 48
					: keyCode);
			if (that.answer == '?') {
				that.answer = '';
			}

			that.answer = that.answer + number + '';

		} else if (keyCode == 189) {

			if (that.answer == '?') {
				that.answer = '';
			}

			if (that.answer.indexOf('-') === -1) {
				that.answer = '-' + that.answer;
			} else {
				that.answer = that.answer.split('-')[1];
			}

		} else if (keyCode == 8) {
			/*			if (that.answer != '?' && that.answer != '') {
			 that.answer = that.answer.substring(0, that.answer.length - 1);
			 }
			 
			 if (that.answer == '') {
			 that.answer = '?';
			 }*/
		} else if (keyCode == 27) {
			//that.answer = '?';
		} else if (keyCode == 13) {
			that.send = that.answer;
			$('.mathematics_task').hide();
		}
		$('.answer', $('.mathematics_task')).html(that.answer);
	}
};

var FlightRecorder = {
	currentType: false,
	record: function() {

		var that = this;
		$.post('ajax.php', {
			com: 'flight_parameters',
			speed: Speed.value,
			altitude: Altitude.value,
			pitch: Pitch.angle,
			roll: Rotation.angle,
			altspeed: Altitude.speed,
			rotation: Rotation.getCourse(),
			answer: Task.send === 0 || Task.send
					? Task.send
					: 'idontknow'
		}, function(response) {

			if (typeof response != 'object') {
				try {
					var response = $.parseJSON(response);
				} catch (e) {
					console.error(e);
				}
			}
			

			if (response && response.action && response.action == 'test_passed') {

				Control.action = function() {
					//location.reload();
					console.log('Test only once');
				};
				Control.pause(response.message, 'error');

			}

			if (response && response.action && response.action == 'test_completed') {
						
				if (response.results) {

					//console.log(response.results);

					var h = [];

					var completed = 0;
					var failed = 0;

					for (var i in response.results) {
						var mission = response.results[i];

						var color = 'red';

						if (mission.achieved == 'yes') {
							completed += 1;
							if (mission.holded > 70) {
								color = 'green';
							}
						} else {
							failed += 1;
						}

						var total_tasks = mission.tasks.success.length + mission.tasks.failed.length;

						var success = total_tasks == 0
								? 0
								: Math.round(100 * mission.tasks.success.length / total_tasks);

						h.push('<p style="color:' + color + '; text-align:left;"><b>Mission ' + (i + 1) + ': ' + mission.title + ' </b></p>');
						h.push('<div style="padding-left:20px; text-align:left;">');
						h.push('<p style="color:' + (mission.achieved == 'yes'
								? 'green'
								: 'red') + ';">' + (mission.achieved == 'yes'
								? 'Mission completed'
								: 'Mission is not completed') + '</p>');
						h.push('<p style="color:' + (mission.holded > 70
								? 'green'
								: 'red') + ';">Hold parameters for ' + mission.holded + '% of time</p>');
						h.push('<p style="color: ' + (success > 70
								? 'green'
								: 'red') + ';">Correct answers: ' + success + '%</p>');
						h.push('</div>');

					}

					var h0 = '<p style="text-align:center">' + completed + ' missions completed, ' + failed + ' missions failed.</p>';

					setTimeout(function() {
						$('.test_results').html(h0 + h.join('')).show();
					}, 2000);

					Control.action = function() {
						location.reload();
					};

				} else {
					$('.test_results').html('').hide();

					Control.action = function() {
						console.log('Test only once');
					};

				}

				Control.pause(response.message, response.type);

				return;
			}

			if (response && response.action && response.action == 'hide_flight_mission') {

				that.currentType = false;

				FlightMission.hide();
			}

			if (response && response.remain) {

				var t = response.remain;

				var min = Math.floor(t / 60);
				var sec = t - min * 60;

				min = min + '';

				if (min.length < 2) {
					min = '0' + min;
				}

				sec = sec + '';

				if (sec.length < 2) {
					sec = '0' + sec;
				}

				$('.time_remain').css({
					color: response.color
							? response.color
							: 'lime'
				}).html(min + ':' + sec);
			}

			//math task
			if (response && response.task) {
				Task.show(response.task);
			}

			//flight out ok modes	
			if (response && response.type && response.type == 'error') {
				Control.action = function() {
					//location.reload();
					console.log('Test only once');
				};
				Control.pause(response.message, response.type);
				return;
			}

			//change flight mission
			if (response && response.type && that.currentType !== response.type) {

				//console.log(response);

				that.currentType = response.type;

				FlightMission.hide(function() {
					FlightMission.show(response.message,
							response.type === 'new_flight_mission'
							? 'normal'
							: (
									response.type === 'flight_mission_hold' || response.type === 'flight_mission_completed'
									? 'success'
									: 'error'
									)
							);
				});
			}

			that.process();
		});
	},
	process: function() {
		var that = this;

		if (Control.stop) {
			return false;
		}

		setTimeout(function() {

			if (Control.stop) {
				return false;
			}

			that.record();
		}, 1000);

	}
};