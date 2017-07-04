<?php

error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

$config = parse_ini_file(__DIR__ . '/config.ini');

?><!DOCTYPE html>
<html>
<head>
<title>Gitstats</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body style="margin-left: 20px;">

<h1>Gitstats</h1>

<h2>Tools</h2>
<ul>
    <li>
		<a href="adminer.php">Create views</a>
		(
			DB: <?= htmlentities($config['mysql_db']) ?>,
			user: <?= htmlentities($config['mysql_user']) ?>,
			pass: <?= htmlentities($config['mysql_pass']) ?>
		)
	</li>
    <li><a href="weave.html">Create new dashboard</a></li>
</ul>

<h2>Statistics</h2>
<ul>
    <?php foreach (glob(__DIR__ . '/*.weave') as $file) { ?>
    <li><a href="weave.html?file=<?= htmlentities(rawurlencode(basename($file))) ?>">
        <?= htmlentities(pathinfo($file, PATHINFO_FILENAME)) ?>
    </a></li>
    <?php } ?>
</ul>

</body>
</html>
