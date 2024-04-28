<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";

pro();
if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
    $instance_config = load_instance_config($config);


    $file_to_transfer = $instance_config["general"]["working_directory"] . "/" . $_GET["video"];
    $file_extension = pathinfo($file_to_transfer, PATHINFO_EXTENSION); // Get the file extension of the video to copy.
    $video_timestamp = date("Y-m-d His", explode("_", $_GET["video"])[2] + (3600*$config["timestamp_offset"])); // Generate a human-readable timestamp for the video to copy.
    $destination_name = "./transfers/" . strval($video_timestamp) . " " . explode("_", $_GET["video"])[3] . "." . $file_extension; // Determine the destination for the video to copy.

    $existing_transferred_files = array_diff(scandir("./transfers/"), array('..', '.')); // List the files that already exist in the transfers directory.
    foreach ($existing_transferred_files as $file) { // Iterate through each file in the working directory.
        if (time() - filemtime("./transfers/" . $file) > 30 * 60) { // Check to see if this file is older than 30 minutes.
            shell_exec("rm \"./transfers/" . $file . "\""); // Remove this file.
        }
    }

    if (is_file($file_to_transfer)) { // Check to see if the file to copy exists, and is not a directory.
        shell_exec("cp '" . $file_to_transfer . "' '" . $destination_name . "'"); // Copy the file to a location accessible to the webserver.
        header("Location: " . $destination_name); // Redirect to the copied file.
    } else {
        echo "<p class='error'>The specified file does not seem to exist.</p>";
    }
}
?>
