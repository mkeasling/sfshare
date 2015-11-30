<?php
/**
 *
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 2:39 PM
 */
ob_start();

require_once '_bootstrap.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?php echo \Sfshare\Config::instance()->site['title']; ?></title>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">
    <!-- Site theme -->
    <link rel="stylesheet" href="/public/sfshare.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <?php $auth->js_block(); ?>
</head>
<body role="document">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div>
                <a class="navbar-brand" href="#">SF Share</a>
                <?php render($auth->is_logged ? 'header/logout' : 'header/login'); ?>
            </div>
        </div>
    </nav>
    <div class="container">
        <?php render(get_route(), 'index'); ?>
    </div>
</body>
</html>
<?php
ob_end_flush();
