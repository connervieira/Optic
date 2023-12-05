<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";


$instance_config = load_instance_config($config);
$directory_files = scandir($instance_config["general"]["working_directory"]); // Scan all files in the Predator working directory.

$processed_videos = array(); // This array will hold each video and its processed information.
$current_video = 0; // This is a placeholder that will hold the starting time of the first video segment from each continuous video.
foreach ($directory_files as $file) { // Iterate through each file in the working directory.
    if (strpos($file, "predator_dashcam") !== false) { // Check to see if this file is a dashcam video.
        $processed_videos[$file]["size"] = filesize($instance_config["general"]["working_directory"] . "/" . $file);
        $processed_videos[$file]["time"] = explode("_", $file)[2];
        $processed_videos[$file]["device"] = explode("_", $file)[3];
        if (intval(explode("_", $file)[4]) == 0) { // Check to see if this segment is at the start of a new video.
            $current_video = $processed_videos[$file]["time"]; // Update the current video.
        }
        $processed_videos[$file]["video"] = $current_video;
    }
}

$indexed_videos = array(); // This array will hold each continuous video and its individual segments.
foreach ($processed_videos as $filename => $video) {
    $video["file"] = $filename;
    if (!isset($indexed_videos[$video["video"]])) {
        $indexed_videos[$video["video"]] = array();
    }
    array_push($indexed_videos[$video["video"]], $video);
}
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
        <p>This page allows you to manage all normally captured dashcam video.</p>
        <br><a class="button" role="button" style="background:#ff4444;color:black;" href="./erasenormal.php">Erase</a><br>
        <br>
        <main>
            <div class="list">
                <?php
                if (sizeof($indexed_videos) > 0) {
                    foreach ($indexed_videos as $timestamp => $video) {
                        echo "<hr>";
                        echo "<h4>" . date("Y-m-d H:i:s", $timestamp) . "</h4>";
                        echo "<ul>";
                        foreach ($video as $number => $segment) {
                            echo "<li><a href='./downloadnormal.php?video=" . $segment["file"] . "'>" . date("H:i:s", $segment["time"]) . "</a></li>";
                        }
                        echo "</ul>";
                    }
                } else {
                    echo "<p><i>No dashcam video has been recorded.</i></p>";
                }
                ?>
            </div>
        </main>
    </body>
</html>
