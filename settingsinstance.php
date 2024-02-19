<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Instance Settings</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <a class="button" role="button" href="./settings.php">Back</a>
            <?php
            if ($config["advanced"] == true) {
                echo '<a class="button" role="button" href="./settingsinstanceadvanced.php">Advanced</a>';
            }
            ?>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Settings</h2>
        <br>
        <main>
            <?php
            verify_permissions($config);

            $instance_config = load_instance_config($config);


            if (isset($instance_config["general"]) and isset($instance_config["management"]) and isset($instance_config["prerecorded"]) and isset($instance_config["realtime"]) and isset($instance_config["dashcam"]) and isset($instance_config["developer"])) { // If this statement is true, then the configuration is likely vanilla Predator.
            } else if (isset($instance_config["general"]) and isset($instance_config["image"]) and isset($instance_config["alpr"]) and isset($instance_config["network"])) { // If this statement is true, then the configuration is likely Predator Fabric.
                echo "<p class=\"error\">The current instance configuration file appears to be for Predator Fabric, not vanilla Predator. " . $config["product_name"] . " is designed to be used only with full Predator, not modified variants.</p>";
                exit();
            } else { // If neither of the statements above are true, then it is likely that the configuration file is corrupt.
                echo "<p class=\"error\">The instance configuration appears to be incomplete. Please ensure that your Predator configuration file is valid.</p>";
                exit();
            }





            // Load the values from the input form.

            $input_values = array();
            $input_values["general"]["working_directory"] = $_POST["general>working_directory"];
            $input_values["general"]["interface_directory"] = $_POST["general>interface_directory"];
            $input_values["general"]["gps"]["enabled"] = $_POST["general>gps>enabled"];
            $input_values["general"]["gps"]["time_correction"]["enabled"] = $_POST["general>gps>time_correction>enabled"];
            $input_values["general"]["gps"]["time_correction"]["threshold"] = floatval($_POST["general>gps>time_correction>threshold"]);
            $input_values["dashcam"]["saving"]["looped_recording"]["mode"] = $_POST["dashcam>saving>looped_recording>mode"];
            $input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["minimum_free_percentage"] = floatval($_POST["dashcam>saving>looped_recording>automatic>minimum_free_percentage"]);
            $input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["max_deletions_per_round"] = intval($_POST["dashcam>saving>looped_recording>automatic>max_deletions_per_round"]);
            $input_values["dashcam"]["saving"]["looped_recording"]["manual"]["history_length"] = intval($_POST["dashcam>saving>looped_recording>manual>history_length"]);
            $input_values["dashcam"]["saving"]["segment_length"] = floatval($_POST["dashcam>saving>segment_length"]);
            $input_values["dashcam"]["parked"]["enabled"] = $_POST["dashcam>parked>enabled"];
            $input_values["dashcam"]["parked"]["conditions"]["speed"] = intval($_POST["dashcam>parked>conditions>speed"]);
            $input_values["dashcam"]["parked"]["conditions"]["time"] = intval($_POST["dashcam>parked>conditions>time"]);
            $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"] = floatval($_POST["dashcam>parked>recording>highlight_motion>enabled"]);
            $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["color"] = $_POST["dashcam>parked>recording>highlight_motion>color"];
            $input_values["dashcam"]["parked"]["recording"]["sensitivity"] = floatval($_POST["dashcam>parked>recording>sensitivity"]);
            $input_values["dashcam"]["parked"]["recording"]["timeout"] = floatval($_POST["dashcam>parked>recording>timeout"]);
            $input_values["dashcam"]["capture"]["video"]["resolution"] = $_POST["dashcam>capture>video>resolution"];
            $input_values["dashcam"]["stamps"]["size"] = floatval($_POST["dashcam>stamps>size"]);
            $input_values["dashcam"]["stamps"]["main"]["date"]["enabled"] = $_POST["dashcam>stamps>main>date>enabled"];
            $input_values["dashcam"]["stamps"]["main"]["time"]["enabled"] = $_POST["dashcam>stamps>main>time>enabled"];
            $input_values["dashcam"]["stamps"]["main"]["message_1"] = $_POST["dashcam>stamps>main>message_1"];
            $input_values["dashcam"]["stamps"]["main"]["message_2"] = $_POST["dashcam>stamps>main>message_2"];
            $input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"] = $_POST["dashcam>stamps>gps>location>enabled"];
            $input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] = $_POST["dashcam>stamps>gps>altitude>enabled"];
            $input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"] = $_POST["dashcam>stamps>gps>speed>enabled"];
            $input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] = strtolower(strval($_POST["dashcam>stamps>gps>speed>unit"]));
            $input_values["dashcam"]["capture"]["audio"]["enabled"]= $_POST["dashcam>capture>audio>enabled"];
            $input_values["dashcam"]["capture"]["audio"]["merge"]= $_POST["dashcam>capture>audio>merge"];


            // Validate the values from the input form.

            if ($_POST["submit"] == "Submit") { // Check to see if the form has been submitted.
                $valid = true; // By default, assume the configuration is valid until an invalid value is found.




                $original_device_count = sizeof($instance_config["dashcam"]["capture"]["video"]["devices"]); // Count the number of capture devices already in the instance configuration.
                $instance_config["dashcam"]["capture"]["video"]["devices"] = array(); // Reset the list of devices in the loaded instance configuration.
                for ($i = 0; $i <= $original_device_count + 1; $i++) { // Run once for each device in the configuration, plus one to account for the new entry.
                    $device_name = $_POST["dashcam>capture>video>devices>" . $i . ">name"]; // This will be the key for the capture device.
                    $device_index = intval($_POST["dashcam>capture>video>devices>" . $i . ">index"]); // This is the index ID of the capture device.
                    if ($_POST["dashcam>capture>video>devices>" . $i . ">flip"] == "on") { $device_flipped = true;
                    } else { $device_flipped = false; }
                    if (strlen($device_name) > 0) { // Check to see if the device name is set.
                        if ($device_index >= 0) { // Check to see if the device index if a valid number.
                            if (!in_array($device_index, $instance_config["dashcam"]["capture"]["video"]["devices"])) { // Check to make sure there is no capture device that already uses this device index.
                                $instance_config["dashcam"]["capture"]["video"]["devices"][$device_name]["index"] = $device_index;
                                $instance_config["dashcam"]["capture"]["video"]["devices"][$device_name]["flip"] = $device_flipped;
                            } else {
                                echo "<p class='error'>The index for <b>dashcam>capture>video>devices>" . $device_name . "</b> value is already used by another capture device.</p>";
                                $valid = false;
                            }
                        } else {
                            echo "<p class='error'>The index for <b>dashcam>capture>video>devices>" . $device_name . "</b> value is invalid.</p>";
                            $valid = false;
                        }
                    }
                }



                if (!is_dir($input_values["general"]["working_directory"])) { echo "<p class='error'>The <b>general>working_directory</b> does not point to a valid directory.</p>"; $valid = false; } // Validate that the general>working_directory points to an existing directory.
                if (strtolower($input_values["general"]["gps"]["enabled"]) == "on") { $input_values["general"]["gps"]["enabled"] = true; } else { $input_values["general"]["gps"]["enabled"] = false; } // Convert the general>gps>enabled value to a boolean.
                if (strtolower($input_values["general"]["gps"]["time_correction"]["enabled"]) == "on") { $input_values["general"]["gps"]["time_correction"]["enabled"] = true; } else { $input_values["general"]["gps"]["time_correction"]["enabled"] = false; } // Convert the general>gps>time_correction>enabled value to a boolean.
                if ($input_values["general"]["gps"]["time_correction"]["threshold"] < 0.2) { echo "<p class='error'>The <b>general>gps>time_correction>threshold</b> value is invalid.</p>"; $valid = false; } // Validate that the general>gps>time_correction>threshold is within the expected range.


                if ($input_values["dashcam"]["saving"]["looped_recording"]["mode"] !== "automatic" and $input_values["dashcam"]["saving"]["looped_recording"]["mode"] !== "manual" and $input_values["dashcam"]["saving"]["looped_recording"]["mode"] !== "disabled") { echo "<p class='error'>The <b>dashcam>saving>looped_recording>mode</b> is not an expected value.</p>"; $valid = false; }
                if ($input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["minimum_free_percentage"] <= 0 or $input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["minimum_free_percentage"] >= 1) { echo "<p class='error'>The <b>dashcam>saving>looped_recording>automatic>minimum_free_percentage</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>saving>looped_recording>automatic>minimum_free_percentage is within the expected range.
                if ($input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["max_deletions_per_round"] <= 0 or $input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["max_deletions_per_round"] >= 1000) { echo "<p class='error'>The <b>dashcam>saving>looped_recording>automatic>max_deletions_per_round</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>saving>looped_recording>automatic>max_deletions_per_round is within the expected range.
                if ($input_values["dashcam"]["saving"]["looped_recording"]["manual"]["history_length"] < 2) { echo "<p class='error'>The <b>dashcam>saving>looped_recording>manual>history_length</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>saving>looped_recording>manual>history_length is within the expected range.


                if (strtolower($input_values["dashcam"]["parked"]["enabled"]) == "on") { $input_values["dashcam"]["parked"]["enabled"] = true; } else { $input_values["dashcam"]["parked"]["enabled"] = false; } // Convert the dashcam>parked>enabled value to a boolean.
                if ($input_values["dashcam"]["parked"]["conditions"]["speed"] < 0 or $input_values["dashcam"]["parked"]["conditions"]["speed"] > 10) { echo "<p class='error'>The <b>dashcam>parked>conditions>speed</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>parked>conditions>speed is within the expected range.
                if ($input_values["dashcam"]["parked"]["conditions"]["time"] < 10 or $input_values["dashcam"]["parked"]["conditions"]["time"] > 600) { echo "<p class='error'>The <b>dashcam>parked>conditions>time</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>parked>conditions>time is within the expected range.
                if (strtolower($input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"]) == "on") { $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"] = true; } else { $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"] = false; } // Convert the dashcam>parked>recording>highlight_motion>enabled value to a boolean.
                $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["color"] = sscanf($input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["color"], "#%02x%02x%02x");
                if ($input_values["dashcam"]["parked"]["recording"]["sensitivity"] < 0.001 or $input_values["dashcam"]["parked"]["recording"]["sensitivity"] > 0.8) { echo "<p class='error'>The <b>dashcam>parked>recording>sensitivity</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>parked>recording>sensitivity is within the expected range.
                if ($input_values["dashcam"]["parked"]["recording"]["timeout"] < 0 or $input_values["dashcam"]["parked"]["recording"]["timeout"] > 60) { echo "<p class='error'>The <b>dashcam>parked>recording>timeout</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>parked>recording>timeout is within the expected range.


                if ($input_values["dashcam"]["capture"]["video"]["resolution"] !== "426x240" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "640x360" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "640x480" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "960x540" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "1280x720" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "1920x1080" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "2560x1440" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "3840x2160" and $input_values["dashcam"]["capture"]["video"]["resolution"] !== "7680x4320") { echo "<p class='error'>The <b>dashcam>capture>video>resolution</b> value is not a recognized option.</p>"; $valid = false; } // Validate that the dashcam>capture>video>resolution is an expected option.

                if ($input_values["dashcam"]["saving"]["segment_length"] < 0 or $input_values["dashcam"]["saving"]["segment_length"] > 360) { echo "<p class='error'>The <b>dashcam>saving>segment_length</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>saving>segment_length value is within the expected range.



                if ($input_values["dashcam"]["stamps"]["size"] < 0.2 or $input_values["dashcam"]["stamps"]["size"] > 5) { echo "<p class='error'>The <b>dashcam>stamps>size</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>stamps>size value is within the expected range.

                if (strtolower($input_values["dashcam"]["stamps"]["main"]["date"]["enabled"]) == "on") { $input_values["dashcam"]["stamps"]["main"]["date"]["enabled"] = true; } else { $input_values["dashcam"]["stamps"]["main"]["date"]["enabled"] = false; } // Convert the dashcam>stamps>main>date>enabled value to a boolean.
                if (strtolower($input_values["dashcam"]["stamps"]["main"]["time"]["enabled"]) == "on") { $input_values["dashcam"]["stamps"]["main"]["time"]["enabled"] = true; } else { $input_values["dashcam"]["stamps"]["main"]["time"]["enabled"] = false; } // Convert the dashcam>stamps>main>time>enabled value to a boolean.

                $allowed_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_- ";
                if (!character_whitelist($input_values["dashcam"]["stamps"]["main"]["message_1"], $allowed_characters)) { echo "<p class='error'>The <b>dashcam>stamps>main>message_1</b> value contains disallowed characters.</p>"; $valid = false; } // Check to see if the dashcam>stamps>main>message_1 value contains characters that are not in the character whitelist.
                if (strlen($input_values["dashcam"]["stamps"]["main"]["message_1"]) > 12) { echo "<p class='error'>The <b>dashcam>stamps>main>message_1</b> value is too long.</p>"; $valid = false; } // Check to see if the dashcam>stamps>main>message_1 value contains too many characters.
                if (!character_whitelist($input_values["dashcam"]["stamps"]["main"]["message_2"], $allowed_characters)) { echo "<p class='error'>The <b>dashcam>stamps>main>message_2</b> value contains disallowed characters.</p>"; $valid = false; } // Check to see if the dashcam>stamps>main>message_2 value contains characters that are not in the character whitelist.
                if (strlen($input_values["dashcam"]["stamps"]["main"]["message_2"]) > 50) { echo "<p class='error'>The <b>dashcam>stamps>main>message_2</b> value is too long.</p>"; $valid = false; } // Check to see if the dashcam>stamps>main>message_2 value contains too many characters.

                if (strtolower($input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"]) == "on") { $input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"] = true; } else { $input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"] = false; } // Convert the dashcam>stamps>gps>location>enabled value to a boolean.
                if (strtolower($input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"]) == "on") { $input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] = true; } else { $input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] = false; } // Convert the dashcam>stamps>gps>altitude>enabled value to a boolean.
                if (strtolower($input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"]) == "on") { $input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"] = true; } else { $input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"] = false; } // Convert the dashcam>stamps>gps>speed>enabled value to a boolean.
                if ($input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] !== "mph" and $input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] !== "kph" and $input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] !== "mps" and $input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] !== "fps" and $input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] !== "knot") { echo "<p class='error'>The <b>dashcam>stamps>gps>speed>unit</b> is not an expected value.</p>"; $valid = false; }


                if (strtolower($input_values["dashcam"]["capture"]["audio"]["enabled"]) == "on") { $input_values["dashcam"]["capture"]["audio"]["enabled"] = true; } else { $input_values["dashcam"]["capture"]["audio"]["enabled"] = false; } // Convert the dashcam>capture>audio>enabled value to a boolean.
                if (strtolower($input_values["dashcam"]["capture"]["audio"]["merge"]) == "on") { $input_values["dashcam"]["capture"]["audio"]["merge"] = true; } else { $input_values["dashcam"]["capture"]["audio"]["merge"] = false; } // Convert the dashcam>capture>audio>merge value to a boolean.


                // Update the instance configuration file.
                if ($valid == true) { // Check to see if all configuration values were validated.
                    $instance_config["general"]["working_directory"] = $input_values["general"]["working_directory"];
                    $instance_config["general"]["interface_directory"] = $input_values["general"]["interface_directory"];
                    $instance_config["general"]["gps"]["enabled"] = $input_values["general"]["gps"]["enabled"];
                    $instance_config["general"]["gps"]["time_correction"]["enabled"] = $input_values["general"]["gps"]["time_correction"]["enabled"];
                    $instance_config["general"]["gps"]["time_correction"]["threshold"] = floatval($input_values["general"]["gps"]["time_correction"]["threshold"]);
                    $instance_config["dashcam"]["saving"]["looped_recording"]["mode"] = $input_values["dashcam"]["saving"]["looped_recording"]["mode"];
                    $instance_config["dashcam"]["saving"]["looped_recording"]["automatic"]["minimum_free_percentage"] = floatval($input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["minimum_free_percentage"]);
                    $instance_config["dashcam"]["saving"]["looped_recording"]["automatic"]["max_deletions_per_round"] = intval($input_values["dashcam"]["saving"]["looped_recording"]["automatic"]["max_deletions_per_round"]);
                    $instance_config["dashcam"]["saving"]["looped_recording"]["manual"]["history_length"] = intval($input_values["dashcam"]["saving"]["looped_recording"]["manual"]["history_length"]);
                    $instance_config["dashcam"]["saving"]["segment_length"] = $input_values["dashcam"]["saving"]["segment_length"];
                    $instance_config["dashcam"]["parked"]["enabled"] = $input_values["dashcam"]["parked"]["enabled"];
                    $instance_config["dashcam"]["parked"]["conditions"]["speed"] = floatval($input_values["dashcam"]["parked"]["conditions"]["speed"]);
                    $instance_config["dashcam"]["parked"]["conditions"]["time"] = floatval($input_values["dashcam"]["parked"]["conditions"]["time"]);
                    $instance_config["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"] = $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"];
                    $instance_config["dashcam"]["parked"]["recording"]["highlight_motion"]["color"] = $input_values["dashcam"]["parked"]["recording"]["highlight_motion"]["color"];
                    $instance_config["dashcam"]["parked"]["recording"]["sensitivity"] = floatval($input_values["dashcam"]["parked"]["recording"]["sensitivity"]);
                    $instance_config["dashcam"]["parked"]["recording"]["timeout"] = floatval($input_values["dashcam"]["parked"]["recording"]["timeout"]);
                    $instance_config["dashcam"]["capture"]["video"]["resolution"]["width"] = floatval(explode("x", $input_values["dashcam"]["capture"]["video"]["resolution"])[0]);
                    $instance_config["dashcam"]["capture"]["video"]["resolution"]["height"] = floatval(explode("x", $input_values["dashcam"]["capture"]["video"]["resolution"])[1]);
                    $instance_config["dashcam"]["stamps"]["size"] = $input_values["dashcam"]["stamps"]["size"];
                    $instance_config["dashcam"]["stamps"]["main"]["date"]["enabled"] = $input_values["dashcam"]["stamps"]["main"]["date"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["main"]["time"]["enabled"] = $input_values["dashcam"]["stamps"]["main"]["time"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["main"]["message_1"] = strval($input_values["dashcam"]["stamps"]["main"]["message_1"]);
                    $instance_config["dashcam"]["stamps"]["main"]["message_2"] = strval($input_values["dashcam"]["stamps"]["main"]["message_2"]);
                    $instance_config["dashcam"]["stamps"]["gps"]["location"]["enabled"] = $input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] = $input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["gps"]["speed"]["enabled"] = $input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] = strtolower($input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"]);
                    $instance_config["dashcam"]["capture"]["audio"]["enabled"] = $input_values["dashcam"]["capture"]["audio"]["enabled"];
                    $instance_config["dashcam"]["capture"]["audio"]["merge"] = $input_values["dashcam"]["capture"]["audio"]["merge"];

                    if (json_encode($instance_config) == true) { // Verify that the data to be saved to the instance configuration file is valid.
                        $instance_configuration_file = $config["instance_directory"] . "/config.json";
                        if (is_writable($instance_configuration_file) == true) { // Verify that the instance configuration file is writable.
                            file_put_contents($instance_configuration_file, json_encode($instance_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); // Save the modified instance configuration to disk.
                            echo "<p class='success'>The configuration was updated successfully.<p>";
                        } else {
                            echo "<p class='error'>The instance configuration file at '" . $instance_configuration_file . "' doesn't appear to be writable. The configuration could not be saved.</p>";
                        }
                    } else {
                        echo "<p class='error'>The modified configuration couldn't be converted into JSON. This should never occur, and is likely a bug. The configuration could not be saved.</p>";
                    }
                } else { // If one or more configuration values is invalid, then don't update the configuration.
                    echo "<p class='error'>The configuration was not updated.<p>";
                    $instance_config = load_instance_config($config); // Reload the instance configuration so invalid information is not placed into the input fields.
                }
            }

            ?>
            <form method="post">
                <div class="buffer">
                    <h3>Capture</h3>
                    <div class="buffer">
                        <h4>Video</h4>
                        <label for="dashcam>capture>video>resolution" title="The resolution at which Predator should capture video.">Resolution:</label>
                        <select id="dashcam>capture>video>resolution" name="dashcam>capture>video>resolution">
                            <option value="426x240" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 240) { echo "selected"; } ?>>240p</option>
                            <option value="640x360" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 360) { echo "selected"; } ?>>360p</option>
                            <option value="640x480" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 480) { echo "selected"; } ?>>480p</option>
                            <option value="960x540" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 540) { echo "selected"; } ?>>540p</option>
                            <option value="1280x720" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 720) { echo "selected"; } ?>>720p</option>
                            <option value="1920x1080" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 1080) { echo "selected"; } ?>>1080p</option>
                            <option value="2560x1440" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 1440) { echo "selected"; } ?>>1440p</option>
                            <option value="3840x2160" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 2160) { echo "selected"; } ?>>2160p</option>
                            <option value="7680x4320" <?php if (intval($instance_config["dashcam"]["capture"]["video"]["resolution"]["height"]) == 4320) { echo "selected"; } ?>>4320p</option>
                        </select><br><br>
                        <div class="buffer">
                            <h4>Cameras</h4>
                            <?php
                            $displayed_cameras = 0;
                            foreach (array_keys($instance_config["dashcam"]["capture"]["video"]["devices"]) as $key) {
                                echo '<div class="buffer">';
                                echo '    <h5>Device "' . $key . '"</h5>';
                                echo '    <label for="dashcam>capture>video>devices>' . $displayed_cameras . '>name" title="The name that will be used as this capture device\'s ID.">Name: </label><input type="text" class="compactinput" id="dashcam>capture>video>devices>' . $displayed_cameras . '>name" name="dashcam>capture>video>devices>' . $displayed_cameras . '>name" min="0" max="10" value="' . $key . '"><br><br>';
                                echo '    <label for="dashcam>capture>video>devices>' . $displayed_cameras . '>index" title="The index number of the capture device on the system.">Index: </label><input type="number" class="compactinput" id="dashcam>capture>video>devices>' . $displayed_cameras . '>index" name="dashcam>capture>video>devices>' . $displayed_cameras . '>index" step="1" min="0" max="10" value="' . $instance_config["dashcam"]["capture"]["video"]["devices"][$key]["index"] . '"><br><br>';
                                echo '    <label for="dashcam>capture>video>devices>' . $displayed_cameras . '>flip" title="Determines if this camera\'s video output will be flipped 180 degrees..">Flip: </label><input type="checkbox" id="dashcam>capture>video>devices>' . $displayed_cameras . '>flip" name="dashcam>capture>video>devices>' . $displayed_cameras . '>flip" '; if ($instance_config["dashcam"]["capture"]["video"]["devices"][$key]["flip"] == true) { echo "checked"; } echo '>';
                                echo '</div>';
                                $displayed_cameras++;
                            }
                            ?>
                            <div class="buffer">
                                <h5>New Device</h5>
                                <label for="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>name" title="The name that will be used as this capture device's ID.">Name: </label><input type="text" class="compactinput" id="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>name" name="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>name" max="10"><br><br>
                                <label for="dashcam>capture>video>devices<?php echo $displayed_cameras; ?>>index" title="The index number of the capture device on the system.">Index: </label><input type="text" class="compactinput" id="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>index" name="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>index" step="1" min="0" max="10"><br><br>
                                <label for="dashcam>capture>video>devices<?php echo $displayed_cameras; ?>>flip" title="Determines if this camera\'s video output will be flipped 180 degrees.">Flip: </label><input type="checkbox" id="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>flip" name="dashcam>capture>video>devices><?php echo $displayed_cameras; ?>>flip">
                            </div>
                        </div>
                    </div>
                    <div class="buffer">
                        <h4>Audio</h4>
                        <label for="dashcam>capture>audio>enabled" title="Determines audio will be recorded during video capture.">Enabled: </label><input type="checkbox" id="dashcam>capture>audio>enabled" name="dashcam>capture>audio>enabled" <?php if ($instance_config["dashcam"]["capture"]["audio"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                        <label for="dashcam>capture>audio>merge" title="Determines if the separate audio and video file will be merged at the end of each segment.">Merge: </label><input type="checkbox" id="dashcam>capture>audio>merge" name="dashcam>capture>audio>merge" <?php if ($instance_config["dashcam"]["capture"]["audio"]["merge"] == true) { echo "checked"; } ?>>
                    </div>
                </div>
                <div class="buffer">
                    <h3>Parking</h3>
                    <label for="dashcam>parked>enabled" title="Determines whether or not Predator will go into parking mode when the vehicle is stopped for extended periods of time.">Enabled: </label><input type="checkbox" id="dashcam>parked>enabled" name="dashcam>parked>enabled" <?php if ($instance_config["dashcam"]["parked"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                    <div class="buffer">
                        <h4>Conditions</h4>
                        <label for="dashcam>parked>conditions>speed" title="The speed below which Predator considers the vehicle to be stopped.">Speed: </label><input type="number" class="compactinput" id="dashcam>parked>conditions>speed" name="dashcam>parked>conditions>speed" step="0.1" min="0" max="5" value="<?php echo $instance_config["dashcam"]["parked"]["conditions"]["speed"]; ?>"> m/s<br><br>
                        <label for="dashcam>parked>conditions>time" title="The length of time the vehicle needs to be stopped before entering into parking mode.">Time: </label><input type="number" class="compactinput" id="dashcam>parked>conditions>time" name="dashcam>parked>conditions>time" step="10" min="10" max="600" value="<?php echo $instance_config["dashcam"]["parked"]["conditions"]["time"]; ?>"> seconds
                    </div>
                    <div class="buffer">
                        <h4>Recording</h4>
                        <div class="buffer">
                            <h5>Highlight Motion</h5>
                            <label for="dashcam>parked>recording>highlight_motion>enabled" title="Determines if bounding boxes will be drawn around detected movement while parked.">Enabled: </label><input type="checkbox" id="dashcam>parked>recording>highlight_motion>enabled" name="dashcam>parked>recording>highlight_motion>enabled" <?php if ($instance_config["dashcam"]["parked"]["recording"]["highlight_motion"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            <label for="dashcam>parked>recording>highlight_motion>color" title="The color that motion bounding boxes will be drawn in.">Color: </label><input type="color" id="dashcam>parked>recording>highlight_motion>color" name="dashcam>parked>recording>highlight_motion>color" value="<?php echo sprintf("#%02x%02x%02x", $instance_config["dashcam"]["parked"]["recording"]["highlight_motion"]["color"][0], $instance_config["dashcam"]["parked"]["recording"]["highlight_motion"]["color"][1], $instance_config["dashcam"]["parked"]["recording"]["highlight_motion"]["color"][2]) ?>">
                        </div>
                        <label for="dashcam>parked>recording>sensitivity" title="The minimum fraction of the frame that needs to be in motion to trigger recording.">Sensitivity: </label><input type="number" class="compactinput" id="dashcam>parked>recording>sensitivity" name="dashcam>parked>recording>sensitivity" step="0.001" min="0.001" max="1" value="<?php echo $instance_config["dashcam"]["parked"]["recording"]["sensitivity"]; ?>"><br><br>
                        <label for="dashcam>parked>recording>timeout" title="The length of time that video will be recorded after motion is last detected.">Timeout: </label><input type="number" class="compactinput" id="dashcam>parked>recording>timeout" name="dashcam>parked>recording>timeout" step="1" min="0" max="60" value="<?php echo $instance_config["dashcam"]["parked"]["recording"]["timeout"]; ?>"> seconds
                    </div>
                </div>
                <div class="buffer">
                    <h3>Overlays</h3>
                    <label for="dashcam>stamps>size" title="The font size of all overlay stamps.">Size: </label><input type="number" class="compactinput" id="dashcam>stamps>size" name="dashcam>stamps>size" step="0.1" min="0.2" max="3" value="<?php echo $instance_config["dashcam"]["stamps"]["size"]; ?>"><br><br>
                    <div class="buffer">
                        <h4>Main</h4>
                        <label for="dashcam>stamps>main>date>enabled">Date: </label><input type="checkbox" id="dashcam>stamps>main>date>enabled" name="dashcam>stamps>main>date>enabled" <?php if ($instance_config["dashcam"]["stamps"]["main"]["date"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                        <label for="dashcam>stamps>main>time>enabled">Time: </label><input type="checkbox" id="dashcam>stamps>main>time>enabled" name="dashcam>stamps>main>time>enabled" <?php if ($instance_config["dashcam"]["stamps"]["main"]["time"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                        <label for="dashcam>stamps>main>message_1">License Plate: </label><input type="text" id="dashcam>stamps>main>message_1" name="dashcam>stamps>main>message_1" pattern="[a-zA-Z0-9-_ ]{0,8}" value="<?php echo $instance_config["dashcam"]["stamps"]["main"]["message_1"]; ?>"><br><br>
                        <?php
                        if (pro_flat()) {
                            if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                                echo '<label for="dashcam>stamps>main>message_2">Custom Message: </label><input type="text" id="dashcam>stamps>main>message_2" name="dashcam>stamps>main>message_2" pattern="[a-zA-Z0-9-_ ]{0,50}" value="' . $instance_config["dashcam"]["stamps"]["main"]["message_2"] . '">';
                            } else {
                                echo '<div style="color:#aaaaaa;"><label for="dashcam>stamps>main>message_2">Custom Message: </label><input type="text" id="dashcam>stamps>main>message_2" value="' . $instance_config["dashcam"]["stamps"]["main"]["message_2"] . '" disabled style="color:inherit"></div>';
                            }
                        } else {
                            echo '<div style="color:#aaaaaa;"><label for="dashcam>stamps>main>message_2">Custom Message: </label><input type="text" id="dashcam>stamps>main>message_2" value="' . $instance_config["dashcam"]["stamps"]["main"]["message_2"] . '" disabled style="color:inherit"></div>(Optic Pro only)';
                        }
                        ?>
                    </div>
                    <div class="buffer">
                        <h4>GPS</h4>
                        <div class="buffer">
                            <h5>Location</h5>
                            <label for="dashcam>stamps>gps>location>enabled" title="Determines if the current coordinates will be displayed in dashcam videos.">Enabled: </label><input type="checkbox" id="dashcam>stamps>gps>location>enabled" name="dashcam>stamps>gps>location>enabled" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["location"]["enabled"] == true) { echo "checked"; } ?>>
                        </div>
                        <div class="buffer">
                            <h5>Altitude</h5>
                            <label for="dashcam>stamps>gps>altitude>enabled" title="Determines if the current GPS altitude will be displayed in dashcam videos.">Enabled: </label><input type="checkbox" id="dashcam>stamps>gps>altitude>enabled" name="dashcam>stamps>gps>altitude>enabled" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] == true) { echo "checked"; } ?>>
                        </div>
                        <div class="buffer">
                            <h5>Speed</h5>
                            <label for="dashcam>stamps>gps>speed>enabled" title="Determines if the current GPS speed will be displayed in dashcam videos.">Enabled: </label><input type="checkbox" id="dashcam>stamps>gps>speed>enabled" name="dashcam>stamps>gps>speed>enabled" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            <label for="dashcam>stamps>gps>speed>unit" title="The unit the speed stamp is displayed in.">Units:</label>
                            <select id="dashcam>stamps>gps>speed>unit" name="dashcam>stamps>gps>speed>unit">
                                <option value="mph" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "mph") { echo "selected"; } ?>>miles per hour</option>
                                <option value="kph" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "kph") { echo "selected"; } ?>>kilometers per hour</option>
                                <option value="mps" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "mps") { echo "selected"; } ?>>meters per second</option>
                                <option value="fps" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "fps") { echo "selected"; } ?>>feet per second</option>
                                <option value="knot" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "knot") { echo "selected"; } ?>>knots</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="buffer">
                    <h3>Saving</h3>
                    <label for="dashcam>saving>segment_length" title="The length of a video segment, in seconds, before another segment is started.">Segment Length: </label><input type="number" class="compactinput" id="dashcam>saving>segment_length" name="dashcam>saving>segment_length" step="1" min="0" max="3600" value="<?php echo $instance_config["dashcam"]["saving"]["segment_length"]; ?>"> seconds<br><br>
                    <div class="buffer">
                        <h4>Looped Recording</h4>
                        <label for="dashcam>saving>looped_recording>mode" title="The method by which Predator decides when to delete old dashcam segments">Mode:</label>
                        <select id="dashcam>saving>looped_recording>mode" name="dashcam>saving>looped_recording>mode">
                            <option value="automatic" <?php if ($instance_config["dashcam"]["saving"]["looped_recording"]["mode"] == "automatic") { echo "selected"; } ?>>Automatic</option>
                            <option value="manual" <?php if ($instance_config["dashcam"]["saving"]["looped_recording"]["mode"] == "manual") { echo "selected"; } ?>>Manual</option>
                            <option value="disabled" <?php if ($instance_config["dashcam"]["saving"]["looped_recording"]["mode"] == "disabled") { echo "selected"; } ?>>Disabled</option>
                        </select><br><br>
                        <div class="buffer">
                            <h5>Automatic</h5>
                            <label for="dashcam>saving>looped_recording>automatic>minimum_free_percentage" title="The minimum free disk space before Predator starts erasing old video segments.">Minimum Free Disk: </label><input type="number" class="compactinput" id="dashcam>saving>looped_recording>automatic>minimum_free_percentage" name="dashcam>saving>looped_recording>automatic>minimum_free_percentage" step="0.01" min="0.05" max="0.9" value="<?php echo $instance_config["dashcam"]["saving"]["looped_recording"]["automatic"]["minimum_free_percentage"]; ?>"><br><br>
                            <label for="dashcam>saving>looped_recording>automatic>max_deletions_per_round" title="The maximum number of dashcam segments that Predator can erase at one time.">Max Deletions Per Round: </label><input type="number" class="compactinput" id="dashcam>saving>looped_recording>automatic>max_deletions_per_round" name="dashcam>saving>looped_recording>automatic>max_deletions_per_round" step="1" min="2" max="100" value="<?php echo $instance_config["dashcam"]["saving"]["looped_recording"]["automatic"]["max_deletions_per_round"]; ?>">
                        </div>
                        <div class="buffer">
                            <h5>Manual</h5>
                            <label for="dashcam>saving>looped_recording>manual>history_length" title="The number of segments that can be recorded before unsaved segments start to be overwritten.">History Length: </label><input type="number" class="compactinput" id="dashcam>saving>looped_recording>manual>history_length" name="dashcam>saving>looped_recording>manual>history_length" step="1" min="2" max="10000" value="<?php echo $instance_config["dashcam"]["saving"]["looped_recording"]["manual"]["history_length"]; ?>"> segments
                        </div>
                    </div>
                </div>
                <div class="buffer">
                    <h3>System</h3>
                    <label for="general>working_directory" title="The directory where Predator will store all semi-permanent files, including dashcam videos.">Working Directory: </label><input type="text" id="general>working_directory" name="general>working_directory" pattern="[a-zA-Z0-9-_ /]{0,300}" value="<?php echo $instance_config["general"]["working_directory"]; ?>"><br><br>
                    <label for="general>interface_directory" title="The directory where Predator places temporary files for communicating with Optic.">Interface Directory: </label><input type="text" id="general>interface_directory" name="general>interface_directory" pattern="[a-zA-Z0-9-_ /]{0,300}" value="<?php echo $instance_config["general"]["interface_directory"]; ?>"><br><br>
                    <div class="buffer">
                        <h4>GPS</h4>
                        <label for="general>gps>enabled" title="Determines globally whether GPS features are enabled.">Enabled: </label><input type="checkbox" id="general>gps>enabled" name="general>gps>enabled" <?php if ($instance_config["general"]["gps"]["enabled"]) { echo "checked"; } ?>><br><br>
                        <div class="buffer">
                            <h5>Time Correction</h5>
                            <label for="general>gps>time_correction>enabled" title="Determines if Predator will apply a time offset to match the GPS time if the threshold is exceeded.">Enabled: </label><input type="checkbox" id="general>gps>time_correction>enabled" name="general>gps>time_correction>enabled" <?php if ($instance_config["general"]["gps"]["time_correction"]["enabled"]) { echo "checked"; } ?>><br><br>
                            <label for="general>gps>time_correction>threshold" title="The minimum difference between the system time and GPS time before Predator consider the system to clock to have drifted.">Threshold: </label><input type="number" class="compactinput" id="general>gps>time_correction>threshold" name="general>gps>time_correction>threshold" step="1" min="1" max="3600" value="<?php echo $instance_config["general"]["gps"]["time_correction"]["threshold"]; ?>"> seconds<br><br>
                        </div>
                    </div>
                </div>

                <br><br><input type="submit" id="submit" name="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
