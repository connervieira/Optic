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
    if ($instance_config["interface_directory"] == "") {
        echo "<p class=\"warning\">The interface directory is disabled in Predator's configuration. Predator needs the interface directory to be enabled in order for the control interface to communicate with it.</p>";
    } else if (is_dir($instance_config["general"]["interface_directory"]) == false) { // Check to make sure the specified interface directory exists.
        //echo "<p class=\"warning\">The interface directory doesn't exist. Please verify that the correct interface directory is configured in the settings.</p>";
    } else if (is_writable($instance_config["general"]["interface_directory"]) == false) { // Check to see if the interface directory is writable.
        echo "<p class=\"warning\">The interface directory isn't writable. Please verify that the interface directory at " . $instance_config["general"]["interface_directory"] . " has the correct permissions.</p>";
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

?>
