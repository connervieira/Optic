<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";


$instance_config = load_instance_config($config);
$indexed_videos = index_videos($instance_config["general"]["working_directory"], $instance_config);
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
            <div class="navbarleft" role="navigation">
                <a class="button" role="button" href="./index.php">Back</a>
            </div>
            <div class="navbarright" role="navigation">
                <a class="button" role="button" href="./storagesaved.php">Saved</a>
            </div>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Storage - Normal</h2>
        <p>This page allows you to manage all unlocked dashcam video.</p>
        <br><a class="button" role="button" style="background:#ff4444;color:black;" href="./erasenormal.php">Erase</a><br><br>
        <?php
        if ($instance_config["dashcam"]["parked"]["enabled"] == true) { // Check to see if parking mode is enabled before showing the filter options.
            echo "<br>";
            echo '<a class="button" role="button" href="?show=normal"'; if ($_GET["show"] == "normal") { echo "style='border:solid white 5px;'"; } echo '>Normal</a>';
            echo '<a class="button" role="button" href="?show=parked"'; if ($_GET["show"] == "parked") { echo "style='border:solid white 5px;'"; } echo '>Parked</a>';
            echo '<a class="button" role="button" href="storagenormal.php" '; if ($_GET["show"] !== "normal" and $_GET["show"] !== "parked") { echo "style='border:solid white 5px;'"; } echo'>All</a>';
        }
        ?>
        <main>
            <div class="list">
                <?php
                pro();
                if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                    if (sizeof($indexed_videos) > 0) {
                        foreach ($indexed_videos as $timestamp => $video) {
                            echo "<hr>";
                            echo "<h4>" . date("Y-m-d H:i:s", $timestamp + (3600*$config["timestamp_offset"])) . "</h4>";
                            echo "<ul>";
                            foreach ($video as $time => $segment) {
                                echo "<li>" . date("H:i:s", $time + (3600*$config["timestamp_offset"])) . " -";
                                $audio = ""; // This is a placeholder that will be replaced with this segment's audio file (if one exists).
                                foreach ($segment as $device) {
                                    if (isset($device["audio"])) {
                                        $audio = $device["audio"];
                                    }
                                    echo " <a href='./downloadnormal.php?video=" . $device["file"] . "'>" . $device["device"] . "</a>";
                                }
                                if (strlen($audio) > 0) { // Check to see if there is an associated audio file.
                                    echo " <a href='./downloadnormal.php?video=" . $audio . "'>(audio)</a>";
                                }
                                echo "</li>";
                            }
                            echo "</ul>";
                        }
                    } else {
                        if ($_GET["show"] == "normal") {
                            echo "<p><i>No normal dashcam video has been recorded.";
                        } else if ($_GET["show"] == "parked") {
                            echo "<p><i>No parked dashcam video has been recorded.";
                        } else {
                            echo "<p><i>No dashcam video has been recorded.";
                        }
                        echo "</i></p>";
                    }
                }
                ?>
            </div>
        </main>
    </body>
</html>
