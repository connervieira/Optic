<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";

$instance_config = load_instance_config($config);
$indexed_videos = index_videos($instance_config["general"]["working_directory"] . "/" . $instance_config["dashcam"]["saving"]["directory"], $instance_config);
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
        <h2>Storage - Saved</h2>
        <p>This page allows you to manage all dashcam video that has been saved using the 'Lock' button.</p>
        <br><a class="button" role="button" style="background:#ff4444;color:black;" href="./erasesaved.php">Erase</a><br>
        <br>
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
                        echo "<p><i>No dashcam video has been saved.</i></p>";
                    }
                }
                ?>
            </div>
        </main>
    </body>
</html>
