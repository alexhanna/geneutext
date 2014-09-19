<?

$q = trim($_GET['q']);

// include 'StanfordNLP/Base.php';
// include 'StanfordNLP/Exception.php';
// include 'StanfordNLP/StanfordTagger.php';
// include 'StanfordNLP/POSTagger.php';

// $pos = new StanfordNLP\POSTagger(
// 	'/home/ahanna/sandbox/stanford-postagger-2014-08-27/models/english-left3words-distsim.tagger',
// 	'/home/ahanna/sandbox/stanford-postagger-2014-08-27/stanford-postagger-3.4.1.jar'
// );

// $result = $pos->tag(explode(' ', $q));
// print_r($result);

function preserveCase($m, $new) {
	// Adapted from the following
	// http://perldoc.perl.org/perlfaq6.html#How-do-I-substitute-case-insensitively-on-the-LHS-while-preserving-case-on-the-RHS%3f

	$old = $m[1];

	$state  = 0;
	$len    = $oldlen < $newlen ? $oldlen : $newlen;

	## for two word phrases
	$old_array = explode(" ", $old);
	$new_array = explode(" ", $new);

	$oldlen = strlen($old_array[0]);
	$newlen = strlen($new_array[0]);

	for ($i = 0; $i < strlen($old_array[0]); $i++) {
		$c = $old_array[0][$i];

		## non-alpha character
		if(preg_match('/[\W\d_]/', $c)) {
			$state = 0;
		} else if (strtolower($c) == $c) {
			$new_array[0][$i] = strtolower($new_array[0][$i]);
			$state = 1;
		} else {
			$new_array[0][$i] = strtoupper($new_array[0][$i]);
			$state = 2;
		}
	}

	if ($newlen > $oldlen) {
		if ($state == 1) {
			$new_array[0] = substr_replace( $new_array[0], strtolower(substr($new_array[0], $oldlen)), $oldlen );
		} else if ($state == 2) {
			$new_array[0] = substr_replace( $new_array[0], strtoupper(substr($new_array[0], $oldlen)), $oldlen );
		}
	}

	// add anything else that may have come along
	if (count($m) > 2) {
		// unless this is a contraction
		if (strpos($m[1], "'" !== true)) {
			array_push($new_array, trim($m[2]));
		}
	}

	$n = implode(" ", $new_array);
	return $n;
}

## hierarchy
## s/he is  -> they are
## s/he has -> they have
## s(he)    -> they
## him/her  -> them
## his/her  -> their
## himself/herself -> themself

$a = $q;

$res = array(
	'/\b((s){0,1}he is)\b/i'    => 'they are',
	"/\b((s){0,1}he\'s)/i"      => "they're",
	'/\b((s){0,1}he was)\b/i'   => 'they were',
	'/\b((s){0,1}he has)\b/i'   => 'they have',
	'/\b((s){0,1}he)\b/i'       => 'they',
	"/\b(her)(\s+and|\s+or)\b/" => 'them',  // a bit of a hack for now
	"/\b(her)\./"               => 'them.', // a bit of a hack for now
	"/\b(her),/"                => 'them,', // a bit of a hack for now
	'/\b(him)\b/i'              => 'them',
	'/\b(his|her)\b/i'          => 'their',
	'/\b(himself|herself)\b/i'  => 'themself'
	);

foreach ($res as $pattern => $replacement) {
	//$a = preg_replace($pattern, $replacement, $a);
	$a = preg_replace_callback($pattern, 
		function ( $m ) use ( $replacement ) {
			return preserveCase($m, $replacement);
		}, $a);
}

## TK: algorithm
## check capitalization for each item and store
## run each regex

?>
<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <title>Text Gender Neutralizer</title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="description" content="">
	    <meta name="author" content="Alexander Hanna">

		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		<link rel="shortcut icon" href="static/ico/favicon.png">
		<style type="text/css">

		</style>
	</head>
	<body>
	<div class="container">
		<h2>Text Gender Neutralizer</h2>
		<p class="text-muted">Down with cisnormativity. Up with the singular they.</p>
		<div class="row">
			<form role="form">
				<div class="col-xs-6">
					<div class="form-group">
						<p><label for="q">Put gendered text here</label></p>
						<p><textarea class="form-control" name="q" id="q" rows="6"><? echo $q; ?></textarea></p>
						<p><button type="submit" class="btn btn-default">Submit</button></p>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<p><label for="a">Gender-neutral text</label></p>
						<p><textarea class="form-control" rows="6"><?php echo $a; ?></textarea></p>
					</div>
				</div>
			</form>
		</div>
		<div class="row">
			<div class="col-xs-9">
				<ul><h4>A short list of stuff that doesn't quite work (yet?):</h4>
					<li><s>Capitalization</s> <span class="text-primary">Working!</span></li>
					<li>Discerning between possessive and objective "her": "<i>Her</i> bicycle is so cool" vs. "Those boots look so good on <i>her</i>". <span class="text-warning">Kind of a hack right now!</span></li>
					<li>Non-"to be" verb agreement: "She <i>works</i> hard for the money" => "They <i>work</i> hard for the money". <span class="text-danger">Not at all working :(</span></li>
				</ul>
			</div>
  	    </div>		
	</div>
	    <div id="footer">
	      <div class="container">
	        <p class="text-muted"><small>This site was written hastily by <a href="http://alex-hanna.com" target="_blank">Alex Hanna</a> (they/them/theirs).</small></p>
	      </div>
	    </div>		
	</body>
</html>