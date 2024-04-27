<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";


// Verify the theme from the form input, and apply it now so that the newly selected theme is reflected by the theme that loads when the page is displayed. This process is repeated during the actual configuration validation process later.
if ($_POST["theme"] == "dark"  or $_POST["theme"] == "light") { // Make sure the theme input matches one of the expected options.
    $config["theme"] = $_POST["theme"]; // Update the configuration array.
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Controller Settings</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <a class="button" role="button" href="./settings.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Controller Settings</h2>
        <br>
        <main>
            <?php
            $valid = true;
            if (isset($_POST["interface_password"])) { // Check to see if the form has been submitted.
                if (preg_match("/^[A-Za-z0-9]*$/", $_POST["interface_password"])) { // Check to see if all of the characters in the submitted password are alphanumeric.
                    if (strlen($_POST["interface_password"]) <= 100) { // Check to make sure the submitted password is not an excessive length.
                        if (strlen($_POST["interface_password"]) == 0 and strlen($config["interface_password"]) != 0) { // Check to see if the interface password was changed to be blank.
                            echo "<p class='warning'>The interface password was left blank. This will completely disable authentication. Anyone with network access to this instance will be able to start/stop video capture, download/erase files, and re-configure the system.</p>";
                        }
                        $config["interface_password"] = $_POST["interface_password"]; // Save the submitted interface password to the configuration array.
                    } else {
                        echo "<p class='error'>The interface password can only be 100 characters or less.</p>";
                        $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                    }
                } else {
                    echo "<p class='error'>The interface password can only contain alpha-numeric characters.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if ($_POST["auto_refresh"] == "server" or $_POST["auto_refresh"] == "client" or $_POST["auto_refresh"] == "off") { // Make sure the auto-refresh input matches one of the expected options.
                    $config["auto_refresh"] = $_POST["auto_refresh"]; // Save the submitted auto-refresh option to the configuration array.
                } else {
                    echo "<p class='error'>The auto-refresh option is not an expected option.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if (strtolower($_POST["photosensitive"]) == "on") { $config["photosensitive"] = true;
                } else { $config["photosensitive"] = false; }

                if ($_POST["heartbeat_threshold"] >= 1 and $_POST["heartbeat_threshold"] <= 60) { // Make sure the heartbeat threshold input is within reasonably expected bounds.
                    $config["heartbeat_threshold"] = intval($_POST["heartbeat_threshold"]); // Save the submitted heartbeat threshold option to the configuration array.
                } else {
                    echo "<p class='error'>The heartbeat threshold option is not within expected bounds.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if ($_POST["theme"] == "dark"  or $_POST["theme"] == "light") { // Make sure the theme input matches one of the expected options.
                    $config["theme"] = $_POST["theme"]; // Save the submitted theme option to the configuration array.
                } else {
                    echo "<p class='error'>The theme option is not an expected option.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if (strtolower($_POST["advanced"]) == "on") { $config["advanced"] = true;
                } else { $config["advanced"] = false; }

                $config["timestamp_offset"] = intval($_POST["timestamp_offset"]); // Convert the timestamp offset to an integer number.
                if ($config["timestamp_offset"] < -12 or $config["timestamp_offset"] > 12) {
                    echo "<p class='error'>The timestamp offset is invalid.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }




                if (preg_match("/^[A-Za-z0-9]*$/", $_POST["exec_user"])) { // Check to see if all of the characters in the submitted execution user are alphanumeric.
                    if (strlen($_POST["exec_user"]) <= 100) { // Check to make sure the submitted execution user is not an excessive length.
                        $config["exec_user"] = $_POST["exec_user"]; // Save the submitted execution user to the configuration array.
                    } else {
                        echo "<p class='error'>The execution user can only be 100 characters or less.</p>";
                        $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                    }
                } else {
                    echo "<p class='error'>The execution user can only contain alpha-numeric characters.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }

                if (is_dir($_POST["instance_directory"])) { // Make sure the root directory input is actually a directory.
                    $config["instance_directory"] = $_POST["instance_directory"]; // Save the submitted root directory option to the configuration array.
                } else {
                    echo "<p class='error'>The specified instance root directory does not exist.</p>";
                    $valid = false; // Indicate that the configuration is not valid, and shouldn't be saved.
                }



                if ($valid == true) { // Check to see if the entered configuration is completely valid.
                    if (is_writable($optic_config_database_name)) { // Check to make sure the configuration file is writable.
                        file_put_contents($optic_config_database_name, serialize($config)); // Save the modified configuration to disk.
                        echo "<p class='success'>Successfully updated configuration.</p>";
                    } else {
                        echo "<p class='error'>The configuration file is not writable.</p>";
                    }
                } else {
                    echo "<p class='error'>The configuration was not updated.</p>";
                }
            }
            ?>
            <form method="post">
                <h3>Interface Settings</h3>
                <label for="interface_password" title="This is the password that needs to be entered to unlock the Optic interface.">Password:</label> <input type="text" id="interface_password" name="interface_password" placeholder="password" pattern="[a-zA-Z0-9]{0,100}" value="<?php echo $config["interface_password"]; ?>"><br><br>
                <label for="auto_refresh" title="This controls the method by which the interface is refreshed.">Auto Refresh:</label>
                <select id="auto_refresh" name="auto_refresh">
                    <option value="server" <?php if ($config["auto_refresh"] == "server") { echo "selected"; } ?>>Server</option>
                    <option value="client" <?php if ($config["auto_refresh"] == "client") { echo "selected"; } ?>>Client</option>
                    <option value="off" <?php if ($config["auto_refresh"] == "off") { echo "selected"; } ?>>Manual</option>
                </select><br><br>
                <label for="photosensitive" title="When enabled, photosensitive mode disables flashing effects in the interface.">Photosensitive Mode:</label> <input type="checkbox" id="photosensitive" name="photosensitive" <?php if ($config["photosensitive"]) { echo "checked"; }; ?>><br><br>
                <label for="heartbeat_threshold">Heartbeat Threshold:</label> <input type="number" id="heartbeat_threshold" name="heartbeat_threshold" placeholder="5" min="1" max="20" value="<?php echo $config["heartbeat_threshold"]; ?>"> <span>seconds</span><br><br>
                <label for="theme" title="This determines the style that the interface will be displayed in.">Theme:</label>
                <select id="theme" name="theme">
                    <option value="dark" <?php if ($config["theme"] == "dark") { echo "selected"; } ?>>Dark</option>
                    <option value="light" <?php if ($config["theme"] == "light") { echo "selected"; } ?>>Light</option>
                </select><br><br>
                <label for="advanced" title="When enabled, potentially destructive tools for advanced users are enabled.">Advanced Mode:</label> <input type="checkbox" id="advanced" name="advanced" <?php if ($config["advanced"]) { echo "checked"; }; ?>><br><br>
                <label for="timestamp_offset" title="Set an offset to apply to video segment timestamps in the storage manager.">Timestamp Offset:</label>
                <select id="timestamp_offset" name="timestamp_offset">
                    <?php 
                    for ($x = -12; $x <= 12; $x++) {
                        echo "<option value='$x'";
                        if ($config["timestamp_offset"] == $x) { echo " selected";
                        } else if (!isset($config["timestamp_offset"]) and $x == 0) { echo " selected"; }
                        echo ">$x Hours</option>";
                    }
                    ?>
                </select>

                <br><br><h3>Connection Settings</h3>
                <label for="exec_user" title="This determines the user on the system that will be used to start the Predator process.">Execution User:</label> <input type="text" id="exec_user" name="exec_user" placeholder="Username" pattern="[a-zA-Z0-9]{1,100}" value="<?php echo $config["exec_user"]; ?>"><br><br>
                <label for="instance_directory" title="This is the Predator directory, containing main.py and the other support files.">Instance Directory:</label> <input type="text" id="instance_directory" name="instance_directory" placeholder="/home/pi/Software/Predator/" value="<?php echo $config["instance_directory"]; ?>"><br><br>


                <br><br><input type="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
