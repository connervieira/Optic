<?php
$optic_config_database_name = "./config.txt";


if (is_writable(".") == false) {
    echo "<p class=\"error\">The directory '" . realpath(".") . "' is not writable to PHP.</p>";
    exit();
}

// Load and initialize the database.
if (file_exists($optic_config_database_name) == false) { // Check to see if the database file doesn't exist.
    $optic_configuration_database_file = fopen($optic_config_database_name, "w") or die("Unable to create configuration database file."); // Create the file.

    $config["interface_password"] = "predator";
    $config["product_name"] = "Optic";
    $config["instance_directory"] = "/home/pi/Software/Predator/"; // This defines where the Predator directory can be found.
    $config["heartbeat_threshold"] = 2; // This is the number of seconds old the last heartbeat has to be before the system is considered to be offline.
    $config["auto_refresh"] = "client"; // This determines whether displays will automatically refresh.
    $config["theme"] = "light"; // This determines the supplmentary CSS file that will be used across the interface.
    $config["exec_user"] = "pi"; // This is the user on the system that will be used to control executables.
    $config["photosensitive"] = false; // This value disables effects that could harm people with photosensitivity problems.
    $config["auth"] = false;

    fwrite($optic_configuration_database_file, serialize($config)); // Set the contents of the database file to the placeholder configuration.
    fclose($optic_configuration_database_file); // Close the database file.
}

if (file_exists($optic_config_database_name) == true) { // Check to see if the item database file exists. The database should have been created in the previous step if it didn't already exists.
    $config = unserialize(file_get_contents($optic_config_database_name)); // Load the database from the disk.
} else {
    echo "<p class=\"error\">The configuration database failed to load</p>"; // Inform the user that the database failed to load.
    exit(); // Terminate the script.
}



?>
