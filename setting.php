<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use Simplon\Mysql\Mysql;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Setting</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    
<?php


$list_theme = connectDb()->fetchRowMany('
	SELECT theme_keyword.id,theme_keyword.name,theme_keyword.created_at
	FROM theme_keyword
	');

?>
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				    	Theme
				    	<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
						<li><a href="index.php">Home</a></li>
						<li role="separator" class="divider"></li>
						<?php foreach ($list_theme as $theme): ?>
				    	<li><a href="index.php?theme=<?= $theme['id'] ?>"><?= $theme['name'] ?></a></li>
				    	<?php endforeach; ?>
				    	<li role="separator" class="divider"></li>
				    	<li><a href="log.php">Logs</a></li>
				    	<li role="separator" class="divider"></li>
				    	<li><a href="setting.php">Setting</a></li>
				  	</ul>
				</div>
			</div>
			<!--<div class="col-sm-12">
				<h3>Add theme</h3>
				<form action="save-theme.php">
				  <div class="form-group">
				    <label for="exampleInputEmail1">Name of Theme</label>
				    <input type="text" class="form-control" placeholder="Theme" name="name", required="required">
				  </div>
				  
				  <button type="submit" class="btn btn-default">Submit</button>
				</form>
			</div> -->

			<div class="col-sm-12">
				<h3>List of themes</h3>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>ID</th>
							<th>Title</th>
							<th>Created</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($list_theme) === false) :?>
							<?php foreach ($list_theme as $item): ?>
							<tr>
								<td>
									<?= $item['id'] ?>
								</td>
								<td>
									<?= $item['name'] ?>
								</td>
								
								<td>
									<?= date("Y-m-d H:i:s",$item['created_at']) ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		
	</div>
	

	
	</table>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
<?php
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
?>