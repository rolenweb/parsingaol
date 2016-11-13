<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use Simplon\Mysql\Mysql;

if (empty($_GET['name'])) {
	echo "Name is null";
}else{
	$data[0] = [
		'name' => trim($_GET['name']),
		'created_at' => time(),
		'updated_at' => time(),
	];
	insertTable('theme_keyword',$data);
	redirect('setting.php');
}

function connectDb()
{
	require 'config/db.php';
	
	return new Mysql(
	    $config['host'],
	    $config['user'],
	    $config['password'],
	    $config['database']
	);
}

function insertTable($table,$data)
{
	return connectDb()->insertMany($table, $data);	
}

function redirect($page)
{
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/$page");
	exit;
}
?>