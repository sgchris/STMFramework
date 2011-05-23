<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<title><?=$_SERVER['HTTP_HOST']._HTTP_ROOT?> | Admin Panel</title>
	<meta http-equiv='content-type' content='text/html;charset=utf-8'>
	<script type='text/javascript' src='../Js/jquery.min.js'></script>
	<link rel="stylesheet" href="styles/styles.css" type="text/css">
</head>
<body>
    <div class='header'>
        <div style='width:1080px'>
            <div style='float:right;'><a class='w' href='index.php?logout_token=<?=($_SESSION['logout_token']=md5(rand(0,100000)))?>'>Logout</a></div>
        </div>
        <h1 style='padding:0px;margin:0px 20px;letter-spacing: -3px;'><a class="w" href='?page=home'>Content Management System</a></h1>
    </div>
    <? require_once(dirname(__FILE__) . '/left_menu.php'); ?>
    <div class='main_frame_div'>
        <?
        $filename = trim(strtolower($_GET['page']));
        $included = false;
        if (!empty($filename)) {
            if (file_exists('pages/'.$filename.'.php')) {
                $included = true;
                require_once('pages/'.$filename.'.php');
            } else {
                echo '<div style="text-align:left;padding-left:5px;color:red;font-size:12px">Error in parameter ('.$filename.')</div>';
            }
        }

        // error in parameter
        if (!$included) {
                require_once(dirname(__FILE__).'/home.php');
        }
        ?>
    </div>
</body>
</html>