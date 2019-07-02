<?php
session_start();

$_SESSION['user_id'] = 1;

require_once "library.php";

TestParameters::reset();
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
	<head>
		<title>Pilot aptitude test</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" href="main.css"/>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script src="script.js"></script>
	</head>
	<body>

		<div style="text-align:center; max-height:100%; margin-top:20px;" class="pr">

			<div class="main_message_host" style="display:none;">
				<table>
					<tr>
						<td>
							<div class="message normal main_message">TEST</div>
						</td>
					</tr>
				</table>
			</div>

			<div style="width:auto; position:relative; height:auto; display:inline-block; width:719px; height:730px; margin-top:-80px;">

				<div class="time_left pa">
					<div style="color:white;" class="ac">Time left:</div>
					<div class="time_remain"></div>
				</div>

				<div class="message_host">
					<div class="flight_mission message normal pa">Descend to 1765ft, slow speed to 185kts, fly heading 160</div>
					<div class="mathematics_task message normal pa"><span style="font-size:1rem;">Please calculate: </span>25 + 17 = ?</div>
				</div>

				<div class="N_indicator pa"></div>
				<div class="N_inscription pa">Engine thrust</div>

				<div class="mscreen pa">

					<div class="horizon pa">
						<div class="air pa"></div>
						<div class="ground pa"></div>
					</div>

					<div class="scale_host pa">
						<div class="scale pa">
							<div class="pa" style="left:50%; top:50%;">
								<?php
								for ($i = 0; $i <= 20; $i++) {
									$x = $i * 21;
									$w = ($i % 2 == 0)
											? ($i % 4 == 0
													? 150
													: 100)
											: 50;
									$m = round($w / 2);
									$n = ($i * 2.5);
									?>
									<div style="border-top:2px solid <?php
								echo abs($n) >= 25
										? 'red'
										: 'white';
									?>; width:<?php echo $w; ?>px; height:10px; left:50%; margin-left:-<?php echo $m; ?>px; top:<?php echo $x; ?>px" class="pa">
										 <?php if ($n % 10 == 0 && $n != 0) { ?>
											<div class="pa" style="color:<?php
									 echo abs($n) >= 25
											 ? 'red'
											 : 'white';
											 ?>; left:-25px; top:-10px; width:20px; height:20px;">
												 <?php echo $n; ?>
											</div>
											<div class="pa" style="color:<?php
										 echo abs($n) >= 25
												 ? 'red'
												 : 'white';
												 ?>; left:155px; top:-10px; width:20px; height:20px;">
												 <?php echo $n; ?>
											</div>
										<?php } ?>
									</div>
									<div style="border-top:2px solid <?php
										echo abs($n) >= 25
												? 'red'
												: 'white';
										?>; width:<?php echo $w; ?>px; height:10px; left:50%; margin-left:-<?php echo $m; ?>px; top:-<?php echo $x; ?>px" class="pa">
										 <?php if ($n % 10 == 0 && $n != 0) { ?>
											<div class="pa" style="color:<?php
									 echo abs($n) >= 25
											 ? 'red'
											 : 'white';
											 ?>; left:-25px; top:-10px; width:20px; height:20px;">
												 <?php echo $n; ?>
											</div>
											<div class="pa" style="color:<?php
										 echo abs($n) >= 25
												 ? 'red'
												 : 'white';
												 ?>; left:155px; top:-10px; width:20px; height:20px;">
												 <?php echo $n; ?>
											</div>
										<?php } ?>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>


					<div class="pitch_host pa">
						<div class="left_pitch pa">
							<div class="pa" style="left:0px; top:0px; width:100%; height:6px; border:3px solid white; background:black;"></div>
							<div class="pa" style="left:100%; top:0px; margin-left:-6px; height:100%; width:6px; border:3px solid white; background:black;"></div>
							<div class="pa" style="background:black; width:6px ;height:6px; left:100%; top:3px; margin-left:-8px;"></div>
						</div>
						<div class="central_pitch pa"></div>
						<div class="right_pitch pa">
							<div class="pa" style="left:0px; top:0px; width:73px; height:6px; border:3px solid white; background:black;"></div>
							<div class="pa" style="left:100%; top:0px; margin-left:-79px; height:100%; width:6px; border:3px solid white; background:black;"></div>
							<div class="pa" style="background:black; width:6px ;height:6px; left:100%; top:3px; margin-left:-70px;"></div>
						</div>
					</div>

					<div class="roll_host pa">
						<?php
						for ($i = 0; $i < 8; $i++) {
							$deg = $i * 10;
							/* if ($i > 6) {
							  $deg = $deg - 5;
							  }
							  if ($i < 4 || $i % 2 != 0) { */
							?>
							<div class="pa" style="height:100%; width:100%; left:0px; top:0px; transform: rotate(<?php echo $deg; ?>deg);">
								<div class="pa" style="left:50%; top:0px; width:30px;">
									<div class="pa" style="border-left:2px solid <?php
						echo $i >= 6
								? 'red'
								: 'white';
							?>;; left:0px; top:0px; height:20px; width:30px;"></div>
								</div>
							</div>

							<div class="pa" style="height:100%; width:100%; left:0px; top:0px; transform: rotate(-<?php echo $deg; ?>deg);">
								<div class="pa" style="left:50%; top:0px; width:30px;">
									<div class="pa" style="border-left:2px solid <?php
								echo $i >= 6
										? 'red'
										: 'white';
							?>; left:0px; top:0px; height:20px; width:30px;"></div>
								</div>
							</div>
							<?php
							//}
						}
						?>
						<div class="pa arrow-down" style="left:50%; top:0px; margin-left:-19px;"></div>
					</div>

					<div class="roll_indicator pa">
						<div class="pa arrow-up" style="left:50%; top:50%; margin-left:-17px; margin-top:-172px;"></div>
						<div class="pa rollout_indicator" ></div>
					</div>

					<?php /* ?>
					  <div class="pa course_pointer">
					  <div class="round pa" style="width:10px; height:10px; left:50%; top:50%; margin-left:-8px; margin-top:-8px; border:3px solid white;"></div>
					  <div class="pa" style="border-top:3px solid white; left:-3px; top:50%; margin-top:-2px; width:5px; height:1px;"></div>
					  <div class="pa" style="border-top:3px solid white; left:18px; top:50%; margin-top:-2px; width:5px; height:1px;"></div>
					  <div class="pa" style="border-left:3px solid white; left:50%; top:-2px; width:1px; height:5px; margin-left:-2px;"></div>
					  </div>
					  <?php */ ?>
					<!--
										<div class="pa flight_director_h"></div>
					
										<div class="pa flight_director_v"></div>
					-->
				</div>


				<div class="speed_screen pa">
					<div class="pa" style="width:100%; height:100%; overflow:hidden;">
						<div class="speed_scale pa">
							<?php
							for ($i = 0; $i < 100; $i++) {
								?>
								<div class="pa" style="font-size:1.2rem; border-top:2px solid white; left:100%; width:10px; margin-left:-10px; top:<?php echo -$i * 40; ?>px">
									<?php if ($i % 2 == 0) { ?>
										<div class="pa" style="width:10px; text-align:right; color:<?php
										echo $i * 10 <= 150
												? 'red'
												: ($i * 10 <= 200
														? 'yellow'
														: 'white');
										?>; left:-50px; top:-12px;"><?php echo $i * 10; ?></div>
										 <?php } ?>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<div class="precesious_speed pa">
						<div class="pa" style="left:100%; width:20px; height:20px; border:2px solid white; border-left:none; border-bottom:none; top:50%; background:black; margin-top:-12px; margin-left:-10px; transform:rotate(45deg);"></div>
						<div class="pa precesious_speed_value">000</div>
						<div class="pa auxilary"></div>
					</div>
				</div>


				<div class="altitude_screen pa">
					<div class="pa" style="width:100%; height:100%; overflow:hidden;">
						<div class="altitude_scale pa">
							<?php
							for ($i = 0; $i < 500; $i++) {
								$alt = $i * 100;
								?>
								<div class="pa" style="font-size:1.2rem; border-top:2px solid <?php
							echo $alt < 400
									? 'red'
									: ($alt < 1000
											? 'yellow'
											: 'white');
								?>; left:0px; width:20px; top:<?php echo -$i * 70; ?>px">
									 <?php if ($i % 2 == 0) { ?>
										<div class="pa" style="width:10px; text-align:right; color:<?php
								 echo $alt < 400
										 ? 'red'
										 : ($alt < 1000
												 ? 'yellow'
												 : 'white');
										 ?>; left:25px; top:-12px;">
											 <?php if ($alt >= 1000) { ?>
												<span style="font-size:1.3rem; margin-right:5px;"><?php echo floor($alt / 1000); ?></span><span style="font-size:1.1rem;"><?php
									 $val = $alt - floor($alt / 1000) * 1000;
									 echo ($val == 0
											 ? '000'
											 : ($val < 10
													 ? '00' . $val
													 : ($val < 100
															 ? '0' . $val
															 : ($val))));
												 ?></span>
													<?php
											} else {
												echo $alt;
											}
											?>
										</div>	
									<?php } ?>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<div class="precesious_alt pa">
						<div class="pa" style="width:10px; height:10px; left:0px; top:50%; margin-top:-6px; margin-left:-8px;">
							<div class="pa" style="left:0px; width:100%; height:100%; border:2px solid white; border-right:none; border-top:none; background:black; transform:rotate(45deg);"></div>
						</div>
						<div class="pa precesious_alt_value"><span class="precesious_altitude">0000</span></div>
					</div>

					<div class="height_speed_host pa">
						<div class="pa" style="top:98px; left:-17px;">
							<div class="arrow-up" style="border-width:14px; transform:rotate(45deg); border-color:dimgray;"></div>
						</div>
						<div class="pa" style="top:208px; left:-17px;">
							<div class="arrow-up" style="border-width:14px; transform:rotate(135deg); border-color:dimgray;"></div>
						</div>
						<div class="pa" style="background:dimgray; width:18px; height:100px; left:-18px; top:0px;"></div>
						<div class="pa" style="background:dimgray; width:18px; height:100px; left:-18px; top:220px;"></div>
						<div class="pa" style="left:10px; top:-12px; width:80px; height:60px; background:black; transform: rotate(60deg);"></div>
						<div class="pa" style="left:20px; top:260px; width:60px; height:80px; background:black; transform: rotate(30deg);"></div>

						<div class="pa height_speed_scale">
							<?php
							/*
							  function sc($val){
							  return 10 / (7 - $val) - 10/7;
							  }
							 */
							$scale = [
								0,
								0.5,
								1,
								1.5,
								2,
								4,
								6
							];

							$digit = [
								1,
								2,
								6
							];

							//foreach ($scale as $n) {

							for ($n = 0; $n <= 12; $n++) {
								$val = -10 / ($n / 2 + 10 / 7) + 7;
								if (in_array(round($n / 2, 1), $scale)) {
									?>
									<div class="pa" style="left:0px; top:50%; width:5px; height:1px; border-top:2px solid <?php
							echo $n == 12
									? 'yellow'
									: 'white';
									?>; margin-top:-<?php echo $val * 27; ?>px">
										<div class="pa" style="width:10px; height:10px; margin-left:-20px; left:100%; top:50%; margin-top:-5px; font-size:10px; color:<?php
							echo $n == 12
									? 'yellow'
									: 'white';
									?>;">
											 <?php
											 echo in_array(round($n / 2, 1), $digit)
													 ? round($n / 2, 0)
													 : '';
											 ?>
										</div>
									</div>
									<div class="pa" style="left:0px; top:50%; width:5px; height:1px; border-top:2px solid <?php
									 echo $n == 12
											 ? 'yellow'
											 : 'white';
											 ?>; margin-top:<?php echo $val * 27; ?>px">
										<div class="pa" style="width:10px; height:10px; margin-left:-20px; left:100%; top:50%; margin-top:-5px; font-size:10px; color:<?php
							echo $n == 12
									? 'yellow'
									: 'white';
											 ?>;">
											 <?php
											 echo in_array(round($n / 2, 1), $digit)
													 ? round($n / 2, 0)
													 : '';
											 ?>
										</div>
									</div>
									<?php
								}
							}
							?>
						</div>


						<div class="pa" style="width:90%; height:100%; left:10%; overflow:hidden;">
							<div class="line pa">
								<div class="pa"></div>
							</div>
						</div>

					</div>


				</div>

				<div class="compass_host pa">
					<div class="pa" style="width:30px; height:30px; left:50%; top:0px; margin-top:-10px; margin-left:-15px; transform:scaleX(0.75);">
						<div class="pa" style="left:0px; width:100%; height:100%; border:3px solid white; border-left:none; border-top:none; background:black; transform:rotate(45deg);"></div>
					</div>

					<div class="compass_back pa round">
						<div class="compass_scale pa">
							<?php
							for ($i = 0; $i < 36; $i++) {
								?>
								<div class="pa" style="width:500px; height:500px; left:50%; top:50%; margin-left:-250px; margin-top:-250px; transform:rotate(<?php echo $i * 10; ?>deg);">
									<div class="pa" style="width:1px; height:20px; border-left:2px solid white; top:0px; left:50%;"></div>
									<div class="pa" style="width:20px; height:20px; left:50%; top:25px; margin-left:-10px; color:white;"><?php echo $i; ?></div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<!--<img src="device1.png" alt="" style=""/>-->

				<div class="keyboard_help pa ac" style="color:white;">
					<div>Use the arrow keys to change aircraft pitch and roll settings:</div>
					<div style="text-align:center;">
						<div>▲</div>
						<div>◄▼►</div>
					</div>
				</div>

				<div class="keyboard_help2 pa ac" style="color:white;">
					<div>Q to REDUCE THRUST</div>
					<div>W to INCREASE THRUST</div>
				</div>

			</div>


		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				Control.init();
			});
		</script>

	</body>
</html>
