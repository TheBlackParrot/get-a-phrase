<?php

	// ** PHRASES **

	$vowels = array('a','e','i','o','u');

	// get the noun list
	$noun_list = file(dirname(__FILE__) . "/lists/nouns.txt");
	$noun = $noun_list[array_rand($noun_list)];
	$noun = trim($noun);

	// chance of getting a descriptive noun
	$compound = "";
	if(!mt_rand(0, 12)) {
		$compound = str_replace("*", "", trim($noun_list[array_rand($noun_list)])) . " ";
	}
	
	// determine if the word should be plural
	// words with "*" at the beginning are never to be plural
	$ending = "";
	$plural = 0;
	if(!mt_rand(0, 1)) {
		if(substr($noun, 0, 1) != "*") {
			$plural = 1;
			if(substr($noun, strlen($noun)-2, 2) != "ch" && substr($noun, strlen($noun)-2, 2) != "sh") {
				switch(substr($noun, strlen($noun)-1)) {
					case "o":
					case "x":
					case "s":
						$ending = "es";
						break;

					case "y":
						if(in_array(substr($noun, strlen($noun)-2, 1),$vowels)) {
							$ending = "s";
						} else {
							$ending = "ies";
							$noun = substr($noun, 0, strlen($noun)-1);
						}
						break;

					default:
						$ending = "s";
						break;
				}
			} else {
				$ending = "es";
			}
		} else {
			$plural = 0;
			$noun = substr($noun, 1);
		}
	}
	$noun = str_replace("*", "", trim($noun));

	// chance of getting a quantity at the beginning of the phrase
	$amount = "";
	if(!mt_rand(0, 5)) {
		if($plural) {
			$amount_list = file(dirname(__FILE__) . "/lists/amounts.txt");
		} else {
			$amount_list = file(dirname(__FILE__) . "/lists/amount.txt");
		}
		$amount = $amount_list[array_rand($amount_list)] . " ";
	}
	$amount = trim($amount);
	
	// get the adjective list
	$adj_list = file(dirname(__FILE__) . "/lists/adjectives.txt");

	// get 1 or 2 adjectives
	$adjs = mt_rand(1, 2);
	for($i = 0; $i < $adjs; $i++) {
		if(!isset($adj[0])) {
			$adj[0] = $adj_list[array_rand($adj_list)];
		} else {
			$adj[1] = $adj_list[array_rand($adj_list)];
			while($adj[0] == $adj[1]) {
				$adj[1] = $adj_list[array_rand($adj_list)];
			}
		}
	}

	// check for vowels on "a"
	if($amount == "a" && in_array(substr($adj[0], 0, 1), $vowels)) {
		$amount = "an";
	}
	$amount .= " ";

	// create the phrase
	$string = $amount;
	foreach ($adj as $word) {
		$string .= "$word ";
	}
	$string = $string . $compound . $noun . $ending;
	$string = str_replace(array("\r","\n"), "", trim($string));


	if(isset($_GET['raw'])) {
		if(htmlspecialchars($_GET['raw']) == 1) {
			header("Content-Type: text/plain");
			die($string);
		}
	}


	// ** COLORS **

	// get the color list
	$color_list = file(dirname(__FILE__) . "/lists/colors.txt");
	$color = explode(" ", $color_list[array_rand($color_list)]);

	// chance of swapping the fg/bg colors (assumed inverses will look fine)
	if(!mt_rand(0,1)) {
		$color = array_reverse($color);
	}


	// ** FONTS **

	// get the font list (honestly screw SQL for this)
	$font_list = file(dirname(__FILE__) . "/lists/fonts.txt");
	$font = explode(",", $font_list[array_rand($font_list)]);

	// encode it for CSS stuff
	$font_url = str_replace(" ", "+", $font[0]);
	// font+family:weight
	$font_str = $font_url . ":" . $font[1];


	// ** CSS **

	// http://stackoverflow.com/a/11951022
	function adjustBrightness($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) <= 3) {
			echo($hex);
			$hex = str_repeat(substr($hex,0,1), 2) . str_repeat(substr($hex,1,1), 2) . str_repeat(substr($hex,2,1), 2);
		}
		$hex = substr($hex, 0, 6);

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}

	// shadow effects
	$css = [];
	$rand = mt_rand(1, 13);
	//$rand = 10;
	switch($rand) {
		case 1: // standard shadow
			$css[] = "text-shadow: 0px 3px 8px rgba(0,0,0,0.5);";
			break;
		
		case 2: // outline
			$amount = mt_rand(1, 3);
			$css[] = "text-shadow: -{$amount}px -{$amount}px 0 #000, {$amount}px -{$amount}px 0 #000, -{$amount}px {$amount}px 0 #000, {$amount}px {$amount}px 0 #000;";
			break;

		case 3: // inset
			$css[] = "text-shadow: 0px 1px 0px rgba(255,255,255,0.5);";
			break;

		case 4: // emboss
			$css[] = "text-shadow: -1px -1px 0px rgba(255,255,255,0.4), 1px 1px 0px rgba(0,0,0,0.8);";
			break;

		case 5: // retro
			$spacing = mt_rand(2, 4);
			$direction = mt_rand(-1, 1);

			$offset = $spacing*$direction;

			$offset_double = $offset*2;
			$spacing_double = $spacing*2;

			$css[] = "text-shadow: {$offset}px {$spacing}px 0px {$color[0]}, {$offset_double}px {$spacing_double}px 0px rgba(0,0,0,0.5);";
			break;

		case 6: // glow
			for($i=0;$i<3;$i++) {
				$col[] = base_convert(substr($color[1], 1+($i*2), 2), 16, 10);
			}
			$amount = mt_rand(7, 13);
			$css[] = "text-shadow: 0px 0px {$amount}px rgba({$col[0]}, {$col[1]}, {$col[2]}, 0.75);";
			break;

		case 7: // 3D effect
			$str = "text-shadow: ";
			$length = mt_rand(5, 8);
			for($i=0;$i<$length;$i++) {
				$scaled = adjustBrightness($color[1], (-25)-($i*6));

				$offset = $i+1;
				$str .= "0px {$offset}px 0px $scaled, ";
			}
			$str .= "0 4px 1px rgba(0,0,0,.2),0 0 5px rgba(0,0,0,.2),0 1px 3px rgba(0,0,0,.4),0 2px 5px rgba(0,0,0,.3),0 4px 10px rgba(0,0,0,.35),0 7px 10px rgba(0,0,0,.3),0 15px 20px rgba(0,0,0,.25);";
			$css[] = $str;
			break;

		case 8: // RGB effect
			if(!mt_rand(0, 2)) {
				$amount = mt_rand(3, 6);
				$css[] = "text-shadow: -{$amount}px -{$amount}px 0px rgba(255, 0, 0, 0.75), {$amount}px -{$amount}px 0px rgba(0, 255, 0, 0.75), 0px {$amount}px 0px rgba(0, 0, 255, 0.75);";
			}
			break;

		case 9: // tail effect
			$str = "text-shadow: ";
			
			$fade = mt_rand(0, 1);
			$scaled = adjustBrightness($color[0], -1*mt_rand(36, 45));
			$length = mt_rand(16, 36);
			$direction = mt_rand(-1, 1);
			
			for($i=0;$i<$length;$i++) {
				$offset_horiz = ($i+1)*$direction;
				$offset_vert = $i+1;

				$str .= "{$offset_horiz}px {$offset_vert}px 0px $scaled, ";

				if($fade) {
					$scaled = adjustBrightness($scaled, 1);
				}
			}

			$css[] = substr($str, 0, strlen($str)-2) . ";";
			break;

		case 10: // superhero
			$str = "text-shadow: ";
			
			$scaled = adjustBrightness($color[0], -1*mt_rand(10, 18));
			$length = mt_rand(1, 2);
			$direction = mt_rand(-1, 1);
			
			for($i=0;$i<$length;$i++) {
				$offset_horiz = (($i+1)*$direction)*10;
				$offset_vert = ($i+1)*10;

				$str .= "{$offset_horiz}px {$offset_vert}px 0px $scaled, ";

				$scaled = adjustBrightness($scaled, -10);
			}

			$css[] = substr($str, 0, strlen($str)-2) . ";";
			break;

	}

	// text style effects
	if(!mt_rand(0, 6)) {
		$css[] = "font-style: italic;";
	}
	if(!mt_rand(0, 6)) {
		$css[] = "letter-spacing: " . mt_rand(1, 4) . "px;";
	}
	if(!mt_rand(0, 6)) {
		$css[] = "font-variant: small-caps;";
	}

	// background effects
	if(!mt_rand(0, 7)) {
		if(!mt_rand(0, 1)) {
			$wrap_css[] = "background: linear-gradient(transparent,rgba(0,0,0,0.33));";
		} else {
			$wrap_css[] = "background: linear-gradient(transparent,rgba(255,255,255,0.33));";
		}
	}
?>

<html>

<head>
	<title>get a phrase</title>
	
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">

	<link rel="stylesheet" type="text/css" href="css/reset.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
	
	<script src="js/jquery.js"></script>
	<script src="js/jquery.textfill.min.js"></script>
	
	<style>
		@import url(https://fonts.googleapis.com/css?family=<?php echo $font_str; ?>);

		body {
			background-color: <?php echo $color[0]; ?>;
		}
		.container {
			font-family: "<?php echo $font[0]; ?>";
			font-weight: <?php echo $font[1]; ?>;
			color: <?php echo $color[1]; ?>;
			<?php
				foreach($css as $obj) {
					echo $obj;
				}
			?>
		}
		.wrapper {
			background-color: <?php echo $color[0]; ?>;
			<?php
				foreach($wrap_css as $obj) {
					echo $obj;
				}
			?>
		}
	</style>

	<script>
		$(document).ready(function(){
			$('.words').textfill({
				maxFontPixels: 120,
				innerTag: ".container"
			});
		});
	</script>
</head>

<body>
	<div class="wrapper">
		<div class="words">
			<span class="container"><?php echo $string; ?></span>
		</div>
	</div>
	<div class="footer">
		<div class="content">
			made by <a href="https://twitter.com/theblackparrot">@TheBlackParrot</a> :: refresh for a new phrase :: <a href="suggest.php">suggest new words!</a>
		</div>
	</div>
</body>

</html>