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

$directory_files = scandir($instance_config["general"]["working_directory"]); // Scan all files in the Predator working directory.
$filtered_directory_files = array(); // This is a placeholder that will hold a list of all relevant files from the Predator working directory.
foreach ($directory_files as $file) {
    if (strpos($file, "predator_dashcam") !== false and (substr($file, -3) == "mkv" or substr($file, -3) == "avi" or substr($file, -3) == "m4v" or substr($file, -3) == "mp4")) { // Check to see if this file is a dashcam video.
        if ($_GET["show"] == "normal") { // Check to see if only normal videos should be displayed.
            if (strtoupper(explode("_", $file)[5][0]) == "N") { // Check to see if this segment is a normal video.
                array_push($filtered_directory_files, $file); // Add this file to the list of filtered files.
            }
        } else if ($_GET["show"] == "parked") { // Check to see if this segment is a parked video.
            if (strtoupper(explode("_", $file)[5][0]) == "P") { // Check to see if this segment is a parked video.
                array_push($filtered_directory_files, $file); // Add this file to the list of filtered files.
            }
        } else { // Otherwise, show all video segments.
            array_push($filtered_directory_files, $file); // Add this file to the list of filtered files.
        }
    }
}

$processed_videos = array(); // This array will hold each video and its processed information.
$current_video = 0; // This is a placeholder that will hold the starting time of the first video segment from each continuous video.
foreach ($filtered_directory_files as $file) { // Iterate through each file in the working directory.
    $processed_videos[$file]["size"] = filesize($instance_config["general"]["working_directory"] . "/" . $file);
    $processed_videos[$file]["time"] = explode("_", $file)[2];
    $processed_videos[$file]["device"] = explode("_", $file)[3];
    $processed_videos[$file]["video"] = $processed_videos[$file]["time"]; // This determines the full continuous video that this segment is a part of.
    $processed_videos[$file]["segment"] = intval(explode("_", $file)[4]);
    $processed_videos[$file]["mode"] = strtoupper(explode("_", $file)[5][0]);

    # Check to see if there is an audio file associated with this video file.
    $base_filename = $instance_config["general"]["working_directory"] . "/" . substr($file, 0, -3);
    if (file_exists($base_filename . "wav")) { $processed_videos[$file]["audio"] = substr($file, 0, -3) . "wav";
    } else if (file_exists($base_filename . "mp3")) { $processed_videos[$file]["audio"] = substr($file, 0, -3) . "mp3";
    } else if (file_exists($base_filename . "flac")) { $processed_videos[$file]["audio"] = substr($file, 0, -3) . "flac";
    } else if (file_exists($base_filename . "ogg")) { $processed_videos[$file]["audio"] = substr($file, 0, -3) . "ogg";
    }

    $time_since_previous = $processed_videos[$file]["time"] - $last_video_time; // Calculate the time difference between this segment's timestamp and the last segment's timestamp.
    if (($time_since_previous > $segment_length + 1 or $time_since_previous < $segment_length - 1) and $time_since_previous !== 0) { // Check to see if this segment is immediately after the previous segment, plus a margin of error.
        $current_video = $processed_videos[$file]["time"]; // Make this segment the start of a new video set.
    }
    $processed_videos[$file]["video"] = $current_video; // Set this segment to be part of the current video set.

    $last_video_time = $processed_videos[$file]["time"];
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
                                foreach ($segment as $device) {
                                    if (isset($device["audio"])) {
                                        echo " <span>" . $device["device"] . " (</span>";
                                        echo "<a href='./downloadnormal.php?video=" . $device["file"] . "'>video</a>";
                                        echo "<span>/</span>";
                                        echo "<a href='./downloadnormal.php?video=" . $device["audio"] . "'>audio</a>";
                                        echo "<span>)</span>";
                                    } else {
                                        echo " <a href='./downloadnormal.php?video=" . $device["file"] . "'>" . $device["device"] .  "</a>";
                                    }
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
