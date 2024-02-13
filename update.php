<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";


if ($config["advanced"] == false) {
    echo "<p>This tool is only available when advanced mode is enabled.</p>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Update</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./management.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Update</h2>
        <p>This page allows you to forcefully update <?php echo $config["product_name"]; ?> or the instance it controls.</p>
        <br>
        <main>
            <a class="button" role="button" href="update.php?component=predator">Update Predator</a><br><br><br>
            <a class="button" role="button" href="update.php?component=optic">Update Optic</a>
        </main>
    </body>
</html>
