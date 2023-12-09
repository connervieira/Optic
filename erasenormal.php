<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";




$instance_config = load_instance_config($config);
$working_directory_files = $instance_config["general"]["working_directory"]; // Scan all files in the Predator working directory.


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Storage</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./storagenormal.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Erase - Normal</h2>
        <br>
        <main>
            <?php
            pro();
            if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                if (time() - $_GET["confirmation"] < 0) {
                    echo "<p>The confirmation timestamp is in the future. If you clicked an external link to get here, it is possible someone is attempt to manipulate you into erasing your dashcam video. No videos were deleted.</p>";
                } else if (time() - $_GET["confirmation"] < 10) {
                    $erase_path = $instance_config["general"]["working_directory"] . "/" . "predator_dashcam*";
                    $erase_path = str_replace(" ", "\\ ", $erase_path);
                    $erase_path = str_replace("'", "\\'", $erase_path);
                    $erase_path = str_replace('"', '\\"', $erase_path);
                    shell_exec("sudo -u " . $config["exec_user"] . " rm " . $erase_path);
                    echo "<p>Erased all unlocked dashcam video.</p>";
                } else {
                    echo "<p>Are you sure you would like to permanently delete all unsaved dashcam videos?</p>";
                    echo "<a class='button' href='?confirmation=" . time() . "'>Confirm</a>";
                }
            }
            ?>
        </main>
    </body>
</html>
