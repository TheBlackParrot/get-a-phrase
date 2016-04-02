<?php
	$color_list = file(dirname(__FILE__) . "/lists/colors.txt");
	$noun_list = file(dirname(__FILE__) . "/lists/nouns.txt");
	$str = "";
	$i = 1;
	foreach($color_list as $row) {
		$colors = explode(" ", $row);
		$str .= '<div class="main" style="width: 100%;">';
		$str .= '<div style="width: 50%; background-color: ' . $colors[0] . '; color: ' . $colors[1] . ';">' . $i . '. ' . trim($noun_list[array_rand($noun_list)]) . '</div>';
		$str .= '<div style="width: 50%; background-color: ' . $colors[1] . '; color: ' . $colors[0] . ';">' . $i . '. ' . trim($noun_list[array_rand($noun_list)]) . '</div>';
		$str .= '</div>';
		$i++;
	}
?>

<html>
<head>
	<style>
		body {
			padding: 0;
			margin: 0;
		}
		div {
			height: 48px;
		}
		.main {
			display: inline-flex;
		}
		.main > div {
			line-height: 48px;
			text-align: center;
			font-size: 18pt;
			font-family: "Roboto";
		}
	</style>
</head>

<body>
	<?php echo $str; ?>
</body>
</html>