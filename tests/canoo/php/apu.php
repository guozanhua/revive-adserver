<?php
    $webPath = substr($_SERVER['REQUEST_URI'], 0, 
        strlen($_SERVER['REQUEST_URI']) - strlen('apu.php')) . 'delivery/apu.php';
?>
<html>
<head>
<title>Example Web Page</title>
</head>
<body>
<script type='text/javascript' src='http://<?php echo $_SERVER['HTTP_HOST'].$webPath; ?>?n=&bannerid=2&cb=<?php echo rand(1, 123456);?>'></script>
</body>
</html>