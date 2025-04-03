<?php
include "./config.php";


function load_instance_config($config) {
    $instance_configuration_file = $config["instance_directory"] . "/config.json";
    if (file_exists($instance_configuration_file)) {
        $raw_instance_configuration = file_get_contents($instance_configuration_file);
        $instance_config = json_decode($raw_instance_configuration, true);
    } else {
        $instance_config = array();
    }

    return $instance_config;
}


// The `latest_error` function returns the most recent error, if one has occurred recently.
function latest_error($config) {
    $instance_config = load_instance_config($config);
    $error_file_path = $instance_config["general"]["interface_directory"] . "/errors.json";
    if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
        if (file_exists($error_file_path) == true) { // Check to see if the error file exists.
            $error_log = json_decode(file_get_contents($error_file_path), true); // Load the error file from JSON data.
        } else { // If the error file doesn't exist, then load a blank placeholder instead.
            $error_log = array(); // Set the error log to an empty array.
        }
    } else { 
        $error_log = array(); // Set the error log to an empty array.
    }

    $error_log = array_reverse($error_log, true); // Reverse the error log, so that more recent errors are at the top.
    $most_recent_error = array_key_first($error_log);

    if (time() - floatval($most_recent_error) < 10) { // Check to see if this error occurred less than 10 seconds ago.
        return array($most_recent_error, $error_log[$most_recent_error]["type"], $error_log[$most_recent_error]["msg"]);
    } else {
        return null;
    }
}


// The `is_alive` function checks to see if the linked instance is running, based on its heartbeat.
function is_alive($config) {
    $instance_config = load_instance_config($config);
    $heartbeat_file_path = $instance_config["general"]["interface_directory"] . "/heartbeat.json";
    if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
        if (file_exists($heartbeat_file_path)) { // Check to see if the heartbeat file exists.
            $heartbeat_log = json_decode(file_get_contents($heartbeat_file_path), true); // Load the heartbeat file from JSON data.
        } else { // If the heartbeat file doesn't exist, then load a blank placeholder instead.
            $heartbeat_log = array(0); // Set the heartbeat log to an empty array.
        }
    } else { // If the heartbeat file doesn't exist, then load a blank placeholder instead.
        $heartbeat_log = array(0); // Set the heartbeat log to an empty array.
    }

    $last_heartbeat = time() - floatval(end($heartbeat_log)); // Calculate how many seconds ago the last heartbeat was.

    if ($last_heartbeat < $config["heartbeat_threshold"]) { // Only consider the system online if it's last heartbeat was within a certain number of seconds ago.
        return true;
    } else { // If the last heartbeat exceeded the time to be considered online, display a message that the system is offline.
        return false;
    }
}



function pro(){ global $config; if ($config["\141\165\164\x68"]!==true){ echo "\74\160\76\x54\x68\151\x73\x20\146\x65\x61\164\x75\x72\x65\x20\151\163\40\157\156\154\x79\40\x61\166\x61\x69\154\x61\142\154\x65\x20\151\x6e\x20\117\x70\x74\151\143\40\x50\162\x6f\x2e\x3c\x2f\x70\x3e"; die;}}
function pro_flat(){ global $config; if ($config["\141\165\164\x68"]!==true){return false;}else{return true;}}



// The `verify_permissions` function checks to see if all permissions are set correctly, and that all files are in their expected locations.
function verify_permissions($config) {
    $valid = true; // This will be switched to false in a fatal issue is discovered.

    // Verify that PHP can execute commands with 'sudo'.
    $command_output = trim(shell_exec("sudo echo verify")); // Execute a command with sudo, and record its output.
    if ($command_output !== "verify") { // Check to see if the command output differs from the expected output.
        echo "<p class=\"error\">PHP does not have the necessary permissions to execute commands with sudo as the '" . shell_exec("whoami") . "' user.</p>"; // Display an error briefly explaining the problem.
        $valid = false;
    }


    // Verify that PHP can execute commands as the configured execution user.
    $user_verify_command = "sudo -u " . $config["exec_user"] . " echo verify"; // Prepare the command to verify permissions.
    $command_output = shell_exec($user_verify_command); // Execute the command, and record its output.
    $command_output = trim($command_output); // Remove whitespaces from the end and beginning of the command output.
    if ($command_output !== "verify") { // Check to see if the command output differs from the expected output.
        echo "<p class=\"error\">PHP does not have the necessary permissions to manage this system as '" . $config["exec_user"] . "' using the '" . shell_exec("whoami") . "' user.</p>"; // Display an error briefly explaining the problem.
        $valid = false;
    }

    if (!is_dir("./transfers/")) { // Check to see if the dashcam video transfers directory needs to be created.
        shell_exec("mkdir ./transfers/; chmod 777 ./transfers/"); // Create the directory, and make it writable to all processes.
    }
    if (!is_writable("./transfers/")) { // Check to make sure the dashcam video transfers directory is writable.
        echo "<p class=\"error\">PHP does not have the necessary permissions to write to the './transfers/' directory. These permissions are necessary to copy dashcam videos so they can be downloaded through the web interface.</p>"; // Display an error briefly explaining the problem.
    }



    if (is_writable("./") == false) { // Check to se if the controller interface's root directory is writable.
        echo "<p class=\"error\">The controller interface's root directory is not writable. Please verify the permissions of the " . getcwd() . " directory.</p>";
        $valid = false;
    } else if (file_exists("./start.sh") == true and is_writable("./start.sh") == false) { // Check to see if the controller interface's start script is writable (if it exists).
        echo "<p class=\"error\">The start.sh script in the " . getcwd() . " directory is not writable.</p>";
        $valid = false;
    }

    $instance_configuration_file = $config["instance_directory"] . "/config.json"; // This is the path to the instance configuration file.
    shell_exec("timeout 1 sudo chmod 777 '" . $instance_configuration_file . "'"); // Attempt to set the permissions on the Predator configuration file.
    if (is_dir($config["instance_directory"]) == false) { // Check to see if the root Predator instance directory exists.
        echo "<p class=\"error\">The instance directory doesn't appear to exist. Please adjust the controller configuration.</p>";
        $valid = false;
    } else if (file_exists($instance_configuration_file) == false) { // Check to see if the instance configuration file exists.
        echo "<p class=\"error\">The instance configuration couldn't be located at " . $instance_configuration_file . ". Please verify that the interface configuration points to the correct instance root directory.</p>";
        $valid = false;
    } else if (is_writable($instance_configuration_file) == false) { // Check to see if the instance configuration file is writable.
        echo "<p class=\"error\">The instance configuration isn't writable. Please verify that the instance configuration file at " . $instance_configuration_file . " has the correct permissions to be modified by external programs.</p>";
        $valid = false;
    } else if (!json_decode(file_get_contents($instance_configuration_file))) {
        echo "<p class=\"error\">The instance configuration doesn't appear to be valid JSON. Please verify that the instance configuration file at " . $instance_configuration_file . " is valid.</p>";
        $valid = false;
    }


    $instance_config = load_instance_config($config);
    if ($instance_config["general"]["interface_directory"] == "") {
        echo "<p class=\"warning\">The interface directory is disabled in Predator's configuration. Predator needs the interface directory to be enabled in order for the control interface to communicate with it.</p>";
    } else if (is_dir($instance_config["general"]["interface_directory"]) == false) { // Check to make sure the specified interface directory exists.
        //echo "<p class=\"warning\">The interface directory doesn't exist. Please verify that the correct interface directory is configured in the settings.</p>";
    } else if (is_writable($instance_config["general"]["interface_directory"]) == false) { // Check to see if the interface directory is writable.
        shell_exec("timeout 1 sudo chmod 777 '" . $instance_config["general"]["interface_directory"] . "'"); // Attempt to set the permissions on the Predator interface directory.
        if (is_writable($instance_config["general"]["interface_directory"]) == false) { // Check to see if the interface directory is writable.
            echo "<p class=\"warning\">The interface directory isn't writable. Please verify that the interface directory at " . $instance_config["general"]["interface_directory"] . " has the correct permissions.</p>";
        }
    }

    if ($valid == false) {
        exit();
    }
}



// This function converts a value in bytes to a human readable format (MiB, GiB, etc.)
function bytes_to_human_readable($bytes) {
    $si_prefix = array('B', 'K', 'M', 'G', 'T', 'P', 'E', 'Y', 'Z');
    $base = 1024;
    $class = min(intval(log($bytes , $base)), count($si_prefix) - 1);
    return number_format($bytes/pow($base,$class), 2) . $si_prefix[$class];
}



function disk_usage($config) {
    $instance_config = load_instance_config($config);
    if (is_dir($instance_config["general"]["working_directory"] . "/" . $instance_config["dashcam"]["saving"]["directory"])) {
        $saved_dashcam_disk_usage = bytes_to_human_readable(1024 * floatval(explode("\t", trim(shell_exec("timeout 1 du -s '" . $instance_config["general"]["working_directory"] . "/" . $instance_config["dashcam"]["saving"]["directory"] .  "'")))[0])); // Execute the command, and record its output.
    } else {
        $saved_dashcam_disk_usage = "0B";
    }
    $working_directory_disk_usage = bytes_to_human_readable(1024 * floatval(explode("\t", trim(shell_exec("timeout 1 du -s '" . $instance_config["general"]["working_directory"] . "'")))[0])); // Execute the command, and record its output.

    return array("saved" => $saved_dashcam_disk_usage, "working" => $working_directory_disk_usage, "free" => bytes_to_human_readable(disk_free_space(".")), "total" => bytes_to_human_readable(disk_total_space(".")));
}


// This function returns true if the $input_string contains only characters found in the $whitelist.
function character_whitelist($input_string, $whitelist) {
    $output_string = "";
    $input_string_array = str_split($input_string); // Convert the post text string into an array of characters.
    foreach ($input_string_array as $input_string_character) {
        if (strpos($whitelist, $input_string_character) !== false) {
            $output_string = $output_string . $input_string_character;
        }
    }

    if ($input_string == $output_string) {
        return true;
    } else {
        return false;
    }
}

function predator_state($config) {
    $instance_config = load_instance_config($config);
    $state_file_path = $instance_config["general"]["interface_directory"] . "/state.json";
    if (is_dir($instance_config["general"]["interface_directory"]) == true) { // Check to make sure the specified interface directory exists.
        if (file_exists($state_file_path)) { // Check to see if the heartbeat file exists.
            $state = json_decode(file_get_contents($state_file_path), true); // Load the heartbeat file from JSON data.
        } else { // If the heartbeat file doesn't exist, then load a blank placeholder instead.
            $state = json_decode('{"mode": "", "gps": 0}'); // Set the system state to placeholder.
        }
    } else { // If the heartbeat file doesn't exist, then load a blank placeholder instead.
        $state = json_decode('{"mode": "", "gps": 0}'); // Set the system state to placeholder.
    }

    return $state;
}


// This function reads through all files in the given directory, identifies dash-cam videos, and parses their information.
function index_videos($directory, $instance_config) {
    if (isset($instance_config["dashcam"]["saving"]["segment_length"])) { // This is for compatibility with Predator V10 and above.
        $segment_length = $instance_config["dashcam"]["saving"]["segment_length"];
    } else if (isset($instance_config["dashcam"]["capture"]["opencv"]["segment_length"])) { // This is for compatibility with older Predator versions (although this is not officially supported.
        $segment_length = $instance_config["dashcam"]["capture"]["opencv"]["segment_length"];
    }

    $directory_files = scandir($directory); // Scan all files in the Predator working directory.
    $filtered_directory_files = array(); // This is a placeholder that will hold a list of all relevant files from the Predator working directory.
    foreach ($directory_files as $file) {
        if (strpos($file, " Predator ") !== false and (strtolower(substr($file, -3)) == "mkv" or strtolower(substr($file, -3)) == "avi" or strtolower(substr($file, -3)) == "m4v" or strtolower(substr($file, -3)) == "mp4")) { // Check to see if this is a Predator dash-cam video (version V12 and later)
            if ($_GET["show"] == "normal") { // Check to see if only normal videos should be displayed.
                if (strtoupper(explode(" ", $file)[4][0]) == "N") { // Check to see if this segment is a normal video.
                    array_push($filtered_directory_files, $file); // Add this file to the list of filtered files.
                }
            } else if ($_GET["show"] == "parked") { // Check to see if this segment is a parked video.
                if (strtoupper(explode("_", $file)[4][0]) == "P") { // Check to see if this segment is a parked video.
                    array_push($filtered_directory_files, $file); // Add this file to the list of filtered files.
                }
            } else { // Otherwise, show all video segments.
                array_push($filtered_directory_files, $file); // Add this file to the list of filtered files.
            }
        } else if (strpos($file, "predator_dashcam") !== false and (substr($file, -3) == "mkv" or substr($file, -3) == "avi" or substr($file, -3) == "m4v" or substr($file, -3) == "mp4")) { // Check to see if this file is a dashcam video (version V11 and earlier).
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
        if (substr($file, 0, 16) == "predator_dashcam") { // Check to see if this file is from Predator V11 or earlier.
            $processed_videos[$file]["size"] = filesize($instance_config["general"]["working_directory"] . "/" . $file);
            $processed_videos[$file]["time"] = explode("_", $file)[2];
            $processed_videos[$file]["device"] = explode("_", $file)[3];
            $processed_videos[$file]["video"] = $processed_videos[$file]["time"]; // This determines the full continuous video that this segment is a part of.
            $processed_videos[$file]["segment"] = intval(explode("_", $file)[4]);
            $processed_videos[$file]["mode"] = strtoupper(explode("_", $file)[5][0]);
        } else if (explode(" ", $file)[2] == "Predator") { // Check to see if this file is from Predator V12 or later.
            $exploded = explode(" ", $file);
            $processed_videos[$file]["time"] = strtotime($exploded[0] . " " . $exploded[1]);
            $processed_videos[$file]["device"] = $exploded[3];
            $processed_videos[$file]["video"] = $processed_videos[$file]["time"]; // This determines the full continuous video that this segment is a part of. This is a placeholder that will be updated later.
            $processed_videos[$file]["mode"] = strtoupper($exploded[4][0]);
        } else { // Otherwise, the version of the file could not be identified.
            echo "<p class=\"error\">Failed to identify the Predator version associated with a file (" . $file . "). This should never happen, and likely indicates a bug.</p>";
        }

        # Check to see if there is an audio file associated with this video file.
        $base_filename = $instance_config["general"]["working_directory"] . "/" . explode(" ", $file)[0] . " " . explode(" ", $file)[1] . " " . explode(" ", $file)[2];
        if (file_exists($base_filename . ".wav")) { $processed_videos[$file]["audio"] = basename($base_filename) . ".wav";
        } else if (file_exists($base_filename . ".mp3")) { $processed_videos[$file]["audio"] = basename($base_filename) . ".mp3";
        } else if (file_exists($base_filename . ".flac")) { $processed_videos[$file]["audio"] = basename($base_filename) . ".flac";
        } else if (file_exists($base_filename . ".ogg")) { $processed_videos[$file]["audio"] = basename($base_filename) . ".ogg";
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

    return $indexed_videos;
}

?>
