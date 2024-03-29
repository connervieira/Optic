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
        <title><?php echo $config["product_name"]; ?> - Management</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./settings.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Management</h2>
        <p>This page contains tools that allow advanced users to control, update, and manage the connected instance.</p>
        <br>
        <main>
            <a class="button" role="button" href="service.php">Service</a><br><br><br>
            <?php
            if ($config["advanced"] == true) {
                echo '<a class="button" role="button" href="files.php">Files</a><br><br><br>';
                echo '<a class="button" role="button" href="update.php">Update</a><br><br><br>';
                echo '<a class="button" role="button" href="permissions.php">Permissions</a><br><br><br>';
            } else {
                echo "<p><i>To access advanced tools, enable 'Advanced Mode' in the controller settings.</i></p>";
            }
            ?>
        </main>
    </body>
</html>
