<?php
	$root = dirname(__FILE__);

	$remove_asterisks = function($val) {
		return str_replace("*", "", $val);
	};

	function isValidInput($val) {
		$val = str_replace(array("-", " "), "", $val);
		if(!ctype_alnum($val)) {
			return 0;
		}

		return 1;
	}

	function checkFlood() {
		session_start();
		if(isset($_SESSION['last_visit'])) {
			$visited = $_SESSION['last_visit'];
			if(microtime(1) - $visited < 1.5) {
				die();
			}
		}
		$_SESSION['last_visit'] = microtime(1);
	}

	checkFlood();

	if(!empty($_POST)) {
		//if(!empty($_POST['noun']) || !empty($_POST['adjective'])) { }
		if(!empty($_POST['noun'])) {
			$val = $_POST['noun'];
			if(!isValidInput($val)) {
				break;
			}

			$data = file_get_contents("$root/lists/nouns.txt") . "\n";
			$data = str_replace("\r\n", "\n", $data);

			if(file_exists("$root/lists/sug_nouns.txt")) {
				$data .= file_get_contents("$root/lists/sug_nouns.txt");
			}

			$data = explode("\n", $data);
			$data = array_map($remove_asterisks, $data);

			if(!in_array($val, $data)) {
				file_put_contents("$root/lists/sug_nouns.txt", $val . "\n", FILE_APPEND | LOCK_EX);
				die("1");
			} else {
				die("0");
			}
		}

		if(!empty($_POST['adjective'])) {
			$val = $_POST['adjective'];
			if(!isValidInput($val)) {
				break;
			}

			$data = file_get_contents("$root/lists/adjectives.txt") . "\n";
			$data = str_replace("\r\n", "\n", $data);

			if(file_exists("$root/lists/sug_adj.txt")) {
				$data .= file_get_contents("$root/lists/sug_adj.txt");
			}

			$data = explode("\n", $data);
			$data = array_map($remove_asterisks, $data);

			if(!in_array($val, $data)) {
				file_put_contents("$root/lists/sug_adj.txt", $val . "\n", FILE_APPEND | LOCK_EX);
				die("1");
			} else {
				die("0");
			}
		}

		die();
	}
?>

<html>

<head>
	<title>suggest words for the phrase generator!</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css">

	<style>
		@import url(https://fonts.googleapis.com/css?family=Roboto:400,700);
		
		body {
			font-family: "Roboto", sans-serif;
			line-height: 21px;
		}
		.wrapper {
			padding: 8px;
			width: calc(100% - 16px);
		}
		.section {
			margin-bottom: 24px;
		}
		strong {
			font-weight: 700;
		}

		h1 {
			font-size: 14pt;
			font-weight: 700;
		}
		.textinput {
			font-family: "Roboto", sans-serif;
			padding: 4px;
		}
		.submitbutton {
			border: 1px solid #07c;
			outline: none;
			font-family: "Roboto";
			background-color: #0af;
			color: #fff;
			text-transform: uppercase;
			font-weight: 700;
			padding: 5px;
			margin-left: 3px;
		}
		.submitbutton:hover {
			background-color: #3cf;
		}
		form {
			margin-bottom: 12px;
		}
		.status {
			margin-top: 16px;
			color: #999;
			font-size: 8pt;
			line-height: 14px;
		}
	</style>

	<script type="text/javascript" src="js/jquery.js"></script>
</head>

<body>
	<div class="wrapper">
		<div class="section">
			<h1>Suggest a word</h1>
			<p>Noun</p>
			<div type="noun">
				<input class="textinput" type="text" name="noun">
				<input class="submitbutton" type="submit" going="0">
			</div>
			<p>Adjective</p>
			<div type="adjective">
				<input class="textinput" type="text" name="adjective">
				<input class="submitbutton" type="submit" going="0">
			</div>

			<div class="status"></div>
		</div>

		<div class="section">
			<h1>Current lists</h1>
			<a href="lists/nouns.txt">Nouns</a><br/>
			<a href="lists/adjectives.txt">Adjectives</a><br/>
			<a href="lists/sug_nouns.txt">Suggested Nouns</a><br/>
			<a href="lists/sug_adj.txt">Suggested Adjectives</a><br/>
		</div>
	</div>

	<script>
		function submitSuggestion(element) {
			var parent = element.parent();
			var type = parent.attr("type");
			var input = parent.children(".textinput");
			var value = input.val();

			console.log("Clicked " + type + ": " + value);

			var data = {};
			if(type == "noun") {
				data.noun = value;
			}
			if(type == "adjective") {
				data.adjective = value;
			}

			$.ajax({
				method: "POST",
				url: "suggest.php",
				data: data
			}).done(function(result){
				switch(parseInt(result)) {
					case 0:
						$(".status").append('<span style="color: #e22;"><strong>' + value + '</strong> has been suggested or is already in use for ' + type + 's.</span><br/>');
						break;

					case 1:
						$(".status").append('<span style="color: #183;"><strong>' + value + '</strong> has been added to the ' + type + ' suggestions.</span><br/>');
						break;
				}
			});

			input.val("");
		}

		$(".submitbutton").on("click", function(event){
			submitSuggestion($(this));
			event.preventDefault();
		});

		$(".textinput").keypress(function(event){
			if(event.which == 13) {
				submitSuggestion($(this));
				event.preventDefault();
			}
		});
	</script>
</body>

</html>
