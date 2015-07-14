<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <meta name="author" content="IslandFuture">
    <meta name="generator" content="Simple FrameWork" />

    <title><?=\IslandFuture\Sfw\Application::one()->getTitle() ?></title>
    <!-- Mobile Specific Metas
  ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</head>
<body>

<div class="container">
    <h1><?=\IslandFuture\Sfw\Application::one()->getTitle() ?></h1>

	<?=\IslandFuture\Sfw\Application::one()->sPageContent ?>
</div>

<?php
$this->block('common.counter', array(), array());
?>

</body>
</html>