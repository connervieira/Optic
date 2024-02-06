<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";


$instance_config = load_instance_config($config);
if (isset($instance_config["dashcam"]["saving"]["segment_length"])) { // This is for compatibility with Predator V10.
    $segment_length = $instance_config["dashcam"]["saving"]["segment_length"];
} else if (isset($instance_config["dashcam"]["capture"]["opencv"]["segment_length"])) { // This is for compatibility with pre-release versions of Predator V10.
    $segment_length = $instance_config["dashcam"]["capture"]["opencv"]["segment_length"];
}
$directory_files = scandir($instance_config["general"]["working_directory"] . "/" . $instance_config["dashcam"]["saving"]["directory"]); // Scan all files in the saved dashcam video directory.

$processed_videos = array(); // This array will hold each video and its processed information.
$current_video = 0; // This is a placeholder that will hold the starting time of the first video segment from each continuous video.
foreach ($directory_files as $file) { // Iterate through each file in the working directory.
    if (strpos($file, "predator_dashcam") !== false) { // Check to see if this file is a dashcam video.
        $processed_videos[$file]["size"] = filesize($instance_config["general"]["working_directory"] . "/" . $file);
        $processed_videos[$file]["time"] = explode("_", $file)[2];
        $processed_videos[$file]["device"] = explode("_", $file)[3];
        $processed_videos[$file]["video"] = $processed_videos[$file]["time"];
        $processed_videos[$file]["segment"] = intval(explode("_", $file)[4]);
        $processed_videos[$file]["mode"] = explode("_", $file)[5];

        $time_since_previous = $processed_videos[$file]["time"] - $last_video_time; // Calculate the time difference between this segment's timestamp and the last segment's timestamp.
        if ($time_since_previous > $segment_length + 3 or $time_since_previous < $segment_length - 3) { // Check to see if this segment is immediately after the previous segment, plus a margin of error.
            $current_video = $processed_videos[$file]["time"]; // Make this segment the start of a new video set.
        }
        $processed_videos[$file]["video"] = $current_video; // Set this segment to be part of the current video set.

        $last_video_time = $processed_videos[$file]["time"];
    }
}

$indexed_videos = array(); // This array will hold each continuous video and its individual segments.
foreach ($processed_videos as $filename => $video) {
    $video["file"] = $filename;
    if (!isset($indexed_videos[$video["video"]])) {
        $indexed_videos[$video["video"]] = array();
    }
    if (!isset($indexed_videos[$video["video"]][$video["time"]])) {
        $indexed_videos[$video["video"]][$video["time"]] = array();
    }
    $indexed_videos[$video["video"]][$video["time"]][$video["device"]] = $video;
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
                            echo "<h4>" . date("Y-m-d H:i:s", $timestamp) . "</h4>";
                            echo "<ul>";
                            foreach ($video as $time => $segment) {
                                echo "<li>" . date("H:i:s", $time) . " -";
                                foreach ($segment as $device) {
                                    echo " <a href='./downloadsaved.php?video=" . $device["file"] . "'>" . $device["device"] .  "</a>";
                                }
                                echo "</li>";
                            }
                            echo "</ul>";
                        }
                    } else {
                        echo "<p><i>No dashcam video has been recorded.</i></p>";
                    }
                }
                ?>
            </div>
        </main>
    </body>
</html>
