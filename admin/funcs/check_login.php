<?

// check logout
if ($_GET['logout_token'] != '' && $_GET['logout_token'] == $_SESSION['logout_token']) {
	unset($_SESSION['logout_token']);
	setcookie('user', '', time() - 60*60*3, _HTTP_ROOT.'/');
	unset($_SESSION['user']);
	header('Location: index.php');
	exit();
} 

// check session
if (!empty($_SESSION['user'])) return;

// check cookie
if ($_COOKIE['user'] != '') {
	database::query('select * from `users` where `secret_code` = "'.mysql_real_escape_string($_COOKIE['user']).'" LIMIT 1');
	if (!empty(database::$arr_list)) {
		$_SESSION['user']=$_COOKIE['user'];
		setcookie('user', $_COOKIE['user'], time() + 60*60*3, _HTTP_ROOT.'/');
		return;
	}
}

// check form POST
if ($_POST['login_token'] != '' && $_POST['login_token'] == $_SESSION['login_token']) {
	unset($_SESSION['login_token']);
	database::query('select * from `users`
		where `username` = "'.mysql_real_escape_string($_POST['username']).'" and 
			password = "'.md5($_POST['password']).'" LIMIT 1');
	if (!empty(database::$arr_list)) {
		$_SESSION['user']=database::$arr_list[0]['secret_code'];
		setcookie('user', database::$arr_list[0]['secret_code'], time() + 60*60*3, _HTTP_ROOT.'/');
		return;
	}
}

?>
<style>
	dl {padding:10px}
	dt,dd {display:inline;padding:0px;margin:5px 0px 5px;}
	dt {float:left;width:80px;text-align:right;padding-right:10px;}
	dd {float:left;width:200px;text-align:left;}
</style>
<div style='text-align:center'>
	<div style='margin:200px auto 20px;width:350px'>
	<fieldset>
		<legend> Login form </legend>
		<form action='' method='post'>
		<input type='hidden' name='login_token' value='<?=($_SESSION['login_token'] = md5(rand(0,10000)))?>'/>
		<dl>
			<dt>Username</dt>
			<dd><input type='text' name='username' maxlength=100 size=20 /></dd>
			<dt>Password</dt>
			<dd><input type='password' name='password' maxlength=100 size=20 /></dd>
			<dt>&nbsp;</dt>
			<dd><input type='submit' value='Login'/></dd>
		</dl>
		</form>
	</fieldset>
	</div>
</div>
</body>
</html>
<?exit();?>