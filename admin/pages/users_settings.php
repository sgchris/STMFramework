<?
$passField = new edit_table_field_password('Password', 'password', 150);
$sec_code = new edit_table_field_password('Secret Code', 'secret_code', '150');

try {
	$tbl =& new edit_table('users');
	$tbl->set_title('Administration Users Management')
		->set_links_prefix('?page=users_settings')
		->add_field(new edit_table_field('Login', 'username', '250'))
		->add_field($passField)
		->add_field(new edit_table_field('eMail', 'email', '250'))
                ->add_field($sec_code)
		->show();
} catch (Exception $e) {
	echo $e->getMessage();
}
?>