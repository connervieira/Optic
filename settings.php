<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Settings</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbarleft" role="navigation">
            <a class="button" role="button" href="./index.php">Back</a>
        </div>
        <div class="navbarright" role="navigation">
            <a class="button" role="button" href="./management.php">Management</a><br>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Settings</h2>
        <br>
        <main>
            <a class="button" role="button" href="settingscontroller.php">Controller Settings</a><br><br><br>
            <a class="button" role="button" href="settingsinstance.php">Instance Settings</a>
        </main>
    </body>
</html>
