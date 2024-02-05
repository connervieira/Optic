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
            <a class="button" role="button" href="./settingsinstanceadvanced.php">Advanced</a>
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
                echo "<p class=\"error\">The current instance configuration file appears to be for Predator Fabric, not vanilla Predator. " . $config["product_name"] . " is designed to be used with full Predator, not modified variants.</p>";
                exit();
            } else { // If neither of the statements above are true, then it is likely that the configuration file is corrupt.
                echo "<p class=\"error\">The instance configuration appears to be incomplete. Please ensure that your Predator configuration file is valid.</p>";
                exit();
            }





            // Load the values from the input form.

            $input_values = array();
            $input_values["dashcam"]["saving"]["unsaved_history_length"] = intval($_POST["dashcam>saving>unsaved_history_length"]);
            $input_values["dashcam"]["parked"]["enabled"] = $_POST["dashcam>parked>enabled"];
            $input_values["dashcam"]["parked"]["conditions"]["speed"] = intval($_POST["dashcam>parked>conditions>speed"]);
            $input_values["dashcam"]["parked"]["conditions"]["time"] = intval($_POST["dashcam>parked>conditions>time"]);
            $input_values["dashcam"]["capture"]["resolution"] = $_POST["dashcam>capture>resolution"];
            $input_values["dashcam"]["capture"]["segment_length"] = floatval($_POST["dashcam>capture>segment_length"]);
            $input_values["dashcam"]["stamps"]["size"] = floatval($_POST["dashcam>stamps>size"]);
            $input_values["dashcam"]["stamps"]["main"]["date"]["enabled"] = $_POST["dashcam>stamps>main>date>enabled"];
            $input_values["dashcam"]["stamps"]["main"]["time"]["enabled"] = $_POST["dashcam>stamps>main>time>enabled"];
            $input_values["dashcam"]["stamps"]["main"]["message_1"] = $_POST["dashcam>stamps>main>message_1"];
            $input_values["dashcam"]["stamps"]["main"]["message_2"] = $_POST["dashcam>stamps>main>message_2"];
            $input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"] = $_POST["dashcam>stamps>gps>location>enabled"];
            $input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] = $_POST["dashcam>stamps>gps>altitude>enabled"];
            $input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"] = $_POST["dashcam>stamps>gps>speed>enabled"];
            $input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"] = strtolower(strval($_POST["dashcam>stamps>gps>speed>unit"]));


            // Validate the values from the input form.

            if ($_POST["submit"] == "Submit") { // Check to see if the form has been submitted.
                $valid = true; // By default, assume the configuration is valid until an invalid value is found.


                if ($input_values["dashcam"]["saving"]["unsaved_history_length"] < 2) { echo "<p class='error'>The <b>dashcam>saving>unsaved_history_length</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>saving>unsaved_history_length is within the expected range.


                if (strtolower($input_values["dashcam"]["parked"]["enabled"]) == "on") { $input_values["dashcam"]["parked"]["enabled"] = true; } else { $input_values["dashcam"]["parked"]["enabled"] = false; } // Convert the dashcam>parked>enabled value to a boolean.
                if ($input_values["dashcam"]["parked"]["conditions"]["speed"] < 0 or $input_values["dashcam"]["parked"]["conditions"]["speed"] > 10) { echo "<p class='error'>The <b>dashcam>parked>conditions>speed</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>parked>conditions>speed is within the expected range.
                if ($input_values["dashcam"]["parked"]["conditions"]["time"] < 10 or $input_values["dashcam"]["parked"]["conditions"]["speed"] > 600) { echo "<p class='error'>The <b>dashcam>parked>conditions>speed</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>parked>conditions>time is within the expected range.


                if ($input_values["dashcam"]["capture"]["resolution"] !== "426x240" and $input_values["dashcam"]["capture"]["resolution"] !== "640x360" and $input_values["dashcam"]["capture"]["resolution"] !== "640x480" and $input_values["dashcam"]["capture"]["resolution"] !== "960x540" and $input_values["dashcam"]["capture"]["resolution"] !== "1280x720" and $input_values["dashcam"]["capture"]["resolution"] !== "1920x1080" and $input_values["dashcam"]["capture"]["resolution"] !== "2560x1440" and $input_values["dashcam"]["capture"]["resolution"] !== "3840x2160" and $input_values["dashcam"]["capture"]["resolution"] !== "7680x4320") { echo "<p class='error'>The <b>dashcam>capture>resolution</b> value is not a recognized option.</p>"; $valid = false; } // Validate that the dashcam>capture>resolution is an expected option.

                if ($input_values["dashcam"]["capture"]["segment_length"] < 0 or $input_values["dashcam"]["capture"]["segment_length"] > 360) { echo "<p class='error'>The <b>dashcam>capture>segment_length</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>capture>segment_length value is within the expected range.



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


                // Update the instance configuration file.
                if ($valid == true) { // Check to see if all configuration values were validated.
                    $instance_config["dashcam"]["saving"]["unsaved_history_length"] = intval($input_values["dashcam"]["saving"]["unsaved_history_length"]);
                    $instance_config["dashcam"]["parked"]["enabled"] = $input_values["dashcam"]["parked"]["enabled"];
                    $instance_config["dashcam"]["parked"]["conditions"]["speed"] = floatval($input_values["dashcam"]["parked"]["conditions"]["speed"]);
                    $instance_config["dashcam"]["parked"]["conditions"]["time"] = floatval($input_values["dashcam"]["parked"]["conditions"]["time"]);
                    $instance_config["dashcam"]["capture"]["resolution"]["width"] = floatval(explode("x", $input_values["dashcam"]["capture"]["resolution"])[0]);
                    $instance_config["dashcam"]["capture"]["resolution"]["height"] = floatval(explode("x", $input_values["dashcam"]["capture"]["resolution"])[1]);
                    $instance_config["dashcam"]["capture"]["segment_length"] = $input_values["dashcam"]["capture"]["segment_length"];
                    $instance_config["dashcam"]["stamps"]["size"] = $input_values["dashcam"]["stamps"]["size"];
                    $instance_config["dashcam"]["stamps"]["main"]["date"]["enabled"] = $input_values["dashcam"]["stamps"]["main"]["date"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["main"]["time"]["enabled"] = $input_values["dashcam"]["stamps"]["main"]["time"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["main"]["message_1"] = strval($input_values["dashcam"]["stamps"]["main"]["message_1"]);
                    $instance_config["dashcam"]["stamps"]["main"]["message_2"] = strval($input_values["dashcam"]["stamps"]["main"]["message_2"]);
                    $instance_config["dashcam"]["stamps"]["gps"]["location"]["enabled"] = $input_values["dashcam"]["stamps"]["gps"]["location"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] = $input_values["dashcam"]["stamps"]["gps"]["altitude"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["gps"]["speed"]["enabled"] = $input_values["dashcam"]["stamps"]["gps"]["speed"]["enabled"];
                    $instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] = strtolower($input_values["dashcam"]["stamps"]["gps"]["speed"]["unit"]);

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
                }
            }

            ?>
            <form method="post">
                <div class="buffer">
                    <h3>Video</h3>
                    <div class="buffer">
                        <h4>Capture</h4>
                        <label for="dashcam>capture>resolution" title="The resolution at which Predator should capture video.">Resolution:</label>
                        <select id="dashcam>capture>resolution" name="dashcam>capture>resolution">
                            <option value="426x240" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 240) { echo "selected"; } ?>>240p</option>
                            <option value="640x360" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 360) { echo "selected"; } ?>>360p</option>
                            <option value="640x480" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 480) { echo "selected"; } ?>>480p</option>
                            <option value="960x540" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 540) { echo "selected"; } ?>>540p</option>
                            <option value="1280x720" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 720) { echo "selected"; } ?>>720p</option>
                            <option value="1920x1080" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 1080) { echo "selected"; } ?>>1080p</option>
                            <option value="2560x1440" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 1440) { echo "selected"; } ?>>1440p</option>
                            <option value="3840x2160" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 2160) { echo "selected"; } ?>>2160p</option>
                            <option value="7680x4320" <?php if (intval($instance_config["dashcam"]["capture"]["resolution"]["height"]) == 4320) { echo "selected"; } ?>>4320p</option>
                        </select><br><br>
                    </div>
                    <div class="buffer">
                        <h4>Saving</h4>
                        <label for="dashcam>capture>segment_length" title="The length of a video segment, in seconds, before another segment is started.">Segment Length: </label><input type="number" class="compactinput" id="dashcam>capture>segment_length" name="dashcam>capture>segment_length" step="1" min="0" max="3600" value="<?php echo $instance_config["dashcam"]["capture"]["segment_length"]; ?>"> seconds<br><br>
                        <label for="dashcam>saving>unsaved_history_length" title="The number of segments that can be recorded before unsaved segments start to be overwritten.">Unsaved History Length: </label><input type="number" class="compactinput" id="dashcam>saving>unsaved_history_length" name="dashcam>saving>unsaved_history_length" step="1" min="2" max="10000" value="<?php echo $instance_config["dashcam"]["saving"]["unsaved_history_length"]; ?>"> segments
                    </div>
                    <div class="buffer">
                        <h4>Parking</h4>
                        <label for="dashcam>parked>enabled" title="Determines whether or not Predator will go into parking mode when the vehicle is stopped for extended periods of time.">Enabled: </label><input type="checkbox" id="dashcam>parked>enabled" name="dashcam>parked>enabled" <?php if ($instance_config["dashcam"]["parked"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                        <div class="buffer">
                            <h5>Conditions</h5>
                            <label for="dashcam>parked>conditions>speed" title="The speed below which Predator considers the vehicle to be stopped.">Speed: </label><input type="number" class="compactinput" id="dashcam>parked>conditions>speed" name="dashcam>parked>conditions>speed" step="0.1" min="0" max="5" value="<?php echo $instance_config["dashcam"]["parked"]["conditions"]["speed"]; ?>"> m/s<br><br>
                            <label for="dashcam>parked>conditions>time" title="The length of time the vehicle needs to be stopped before entering into parking mode.">Time: </label><input type="number" class="compactinput" id="dashcam>parked>conditions>time" name="dashcam>parked>conditions>time" step="10" min="10" max="600" value="<?php echo $instance_config["dashcam"]["parked"]["conditions"]["time"]; ?>"> seconds<br><br>
                        </div>
                    </div>
                    <div class="buffer">
                        <h4>Overlays</h4>
                        <label for="dashcam>stamps>size" title="The font size of all overlay stamps.">Size: </label><input type="number" class="compactinput" id="dashcam>stamps>size" name="dashcam>stamps>size" step="0.1" min="0.2" max="3" value="<?php echo $instance_config["dashcam"]["stamps"]["size"]; ?>"><br><br>
                        <div class="buffer">
                            <h5>Main</h5>
                            <label for="dashcam>stamps>main>date>enabled">Date: </label><input type="checkbox" id="dashcam>stamps>main>date>enabled" name="dashcam>stamps>main>date>enabled" <?php if ($instance_config["dashcam"]["stamps"]["main"]["date"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            <label for="dashcam>stamps>main>time>enabled">Time: </label><input type="checkbox" id="dashcam>stamps>main>time>enabled" name="dashcam>stamps>main>time>enabled" <?php if ($instance_config["dashcam"]["stamps"]["main"]["time"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            <label for="dashcam>stamps>main>message_1">License Plate: </label><input type="text" id="dashcam>stamps>main>message_1" name="dashcam>stamps>main>message_1" pattern="[a-zA-Z0-9-_ ]{0,12}" value="<?php echo $instance_config["dashcam"]["stamps"]["main"]["message_1"]; ?>"><br><br>
                            <?php
                            if (pro_flat()) {
                                if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                                    echo '<label for="dashcam>stamps>main>message_2">Custom Message: </label><input type="text" id="dashcam>stamps>main>message_2" name="dashcam>stamps>main>message_2" pattern="[a-zA-Z0-9-_ ]{0,50}" value="' . $instance_config["dashcam"]["stamps"]["main"]["message_2"] . '"><br><br>';
                                } else {
                                    echo '<div style="color:#aaaaaa;"><label for="dashcam>stamps>main>message_2">Custom Message: </label><input type="text" id="dashcam>stamps>main>message_2" value="' . $instance_config["dashcam"]["stamps"]["main"]["message_2"] . '" disabled style="color:inherit"></div><br><br>';
                                }
                            } else {
                                echo '<div style="color:#aaaaaa;"><label for="dashcam>stamps>main>message_2">Custom Message: </label><input type="text" id="dashcam>stamps>main>message_2" value="' . $instance_config["dashcam"]["stamps"]["main"]["message_2"] . '" disabled style="color:inherit"></div>(Optic Pro only)<br><br>';
                            }
                            ?>
                        </div>
                        <div class="buffer">
                            <h5>GPS</h5>
                            <div class="buffer">
                                <h6>Location</h6>
                                <label for="dashcam>stamps>gps>location>enabled">Enabled: </label><input type="checkbox" id="dashcam>stamps>gps>location>enabled" name="dashcam>stamps>gps>location>enabled" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["location"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            </div>
                            <div class="buffer">
                                <h6>Altitude</h6>
                                <label for="dashcam>stamps>gps>altitude>enabled">Enabled: </label><input type="checkbox" id="dashcam>stamps>gps>altitude>enabled" name="dashcam>stamps>gps>altitude>enabled" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["altitude"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                            </div>
                            <div class="buffer">
                                <h6>Speed</h6>
                                <label for="dashcam>stamps>gps>speed>enabled">Enabled: </label><input type="checkbox" id="dashcam>stamps>gps>speed>enabled" name="dashcam>stamps>gps>speed>enabled" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                                <label for="dashcam>stamps>gps>speed>unit" title="The unit the speed stamp is displayed in.">Units:</label>
                                <select id="dashcam>stamps>gps>speed>unit" name="dashcam>stamps>gps>speed>unit">
                                    <option value="mph" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "mph") { echo "selected"; } ?>>miles per hour</option>
                                    <option value="kph" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "kph") { echo "selected"; } ?>>kilometers per hour</option>
                                    <option value="mps" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "mps") { echo "selected"; } ?>>meters per second</option>
                                    <option value="fps" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "fps") { echo "selected"; } ?>>feet per second</option>
                                    <option value="knot" <?php if ($instance_config["dashcam"]["stamps"]["gps"]["speed"]["unit"] == "knot") { echo "selected"; } ?>>knots</option>
                                </select><br><br>
                            </div>
                        </div>
                    </div>
                </div>

                <br><br><input type="submit" id="submit" name="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
