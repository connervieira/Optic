<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";


$instance_config = load_instance_config($config);
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
            <a class="button" role="button" href="./storagesaved.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Erase - Saved</h2>
        <br>
        <main>
            <?php
            pro();
            if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                if (time() - $_GET["confirmation1"] < 0) {
                    echo "<p>The confirmation timestamp is in the future. If you clicked an external link to get here, it is possible someone is attempting to manipulate you into erasing your dashcam video. No videos were deleted.</p>";
                } else if (time() - $_GET["confirmation1"] < 30) {
                    if (time() - $_GET["confirmation2"] < 0) {
                        echo "<p>The confirmation timestamp is in the future. If you clicked an external link to get here, it is possible someone is attempting to manipulate you into erasing your dashcam video. No videos were deleted.</p>";
                    } else if (time() - $_GET["confirmation2"] < 20) {
                        $erase_path = $instance_config["general"]["working_directory"] . "/" . $instance_config["dashcam"]["saving"]["directory"] . "/" . "* Predator *";
                        $erase_path = str_replace(" ", "\\ ", $erase_path);
                        $erase_path = str_replace("'", "\\'", $erase_path);
                        $erase_path = str_replace('"', '\\"', $erase_path);
                        shell_exec("sudo -u " . $config["exec_user"] . " rm " . $erase_path);
                        echo "<p>Erased all saved dashcam video.</p>";
                    } else {
                        echo "<p>Are you sure? This will remove all videos that you've manually saved using the 'Lock' button, not just the normal unlocked dashcam video segments.</p>";
                        echo "<a class='button' href='?confirmation1=" . $_GET["confirmation1"] . "&confirmation2=" . time() . "'>Confirm</a>";
                    }
                } else {
                    echo "<p>Are you sure you would like to permanently delete all saved dashcam videos?</p>";
                    echo "<p>This operation will erase videos that have been saved using the 'Lock' button.</p>";
                    echo "<a class='button' href='?confirmation1=" . time() . "'>Confirm</a>";
                }
            }
            ?>
        </main>
    </body>
</html>
