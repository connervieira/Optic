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
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Settings</h2>
        <main>
            <?php
            verify_permissions($config);

            $instance_configuration_file = $config["instance_directory"] . "/config.json";

            if (is_dir($config["instance_directory"]) == false) { // Check to see if the instance directory exists.
                echo "<p class='error'>The instance directory doesn't appear to exist. Please adjust the controller configuration.</p>";
                exit();
            }
            if (file_exists($instance_configuration_file) == false) { // Check to see if the Predator configuration file exists.
                echo "<p class='error'>The instance configuration couldn't be located. Please verify that the interface configuration points to the correct instance directory.</p>";
                exit();
            }
            if (is_writable($instance_configuration_file) == false) {
                echo "<p class='error'>Please make sure the instance configuration file is writable to make configuration modifications.</p>";
                exit();
            }

            $raw_instance_configuration = file_get_contents($instance_configuration_file);
            $instance_config = json_decode($raw_instance_configuration, true);



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
            $input_values["dashcam"]["capture"]["opencv"]["resolution"] = $_POST["dashcam>capture>opencv>resolution"];
            $input_values["dashcam"]["capture"]["opencv"]["framerate"] = floatval($_POST["dashcam>capture>opencv>framerate"]);
            $input_values["dashcam"]["capture"]["opencv"]["segment_length"] = floatval($_POST["dashcam>capture>opencv>segment_length"]);
            $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"] = $_POST["dashcam>capture>opencv>stamps>main>date>enabled"];
            $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"] = $_POST["dashcam>capture>opencv>stamps>main>time>enabled"];


            // Validate the values from the input form.

            if ($_POST["submit"] == "Submit") { // Check to see if the form has been submitted.
                $valid = true; // By default, assume the configuration is valid until an invalid value is found.


                if ($input_values["dashcam"]["capture"]["opencv"]["framerate"] < 0 or $input_values["dashcam"]["capture"]["opencv"]["framerate"] > 240) { echo "<p class='error'>The <b>dashcam>capture>opencv>framerate</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>capture>opencv>framerate value is within the expected range.

                if ($input_values["dashcam"]["capture"]["opencv"]["segment_length"] < 0 or $input_values["dashcam"]["capture"]["opencv"]["segment_length"] > 360) { echo "<p class='error'>The <b>dashcam>capture>opencv>segment_length</b> value is invalid.</p>"; $valid = false; } // Validate that the dashcam>capture>opencv>segment_length value is within the expected range.


                if ($input_values["dashcam"]["capture"]["opencv"]["resolution"] !== "960x540" and $input_values["dashcam"]["capture"]["opencv"]["resolution"] !== "1280x720" and $input_values["dashcam"]["capture"]["opencv"]["resolution"] !== "1920x1080" and $input_values["dashcam"]["capture"]["opencv"]["resolution"] !== "2560x1440" and $input_values["dashcam"]["capture"]["opencv"]["resolution"] !== "3840x2160") { echo "<p class='error'>The <b>dashcam>capture>opencv>resolution</b> value is not a recognized option.</p>"; $valid = false; } // Validate that the dashcam>capture>opencv>resolution is an expected option.

                if (strtolower($input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"]) == "on") { $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"] = true; } else { $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"] = false; } // Convert the dashcam>capture>opencv>stamps>main>date>enabled value to a boolean.
                if (strtolower($input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"]) == "on") { $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"] = true; } else { $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"] = false; } // Convert the dashcam>capture>opencv>stamps>main>time>enabled value to a boolean.


                // Update the instance configuration file.
                if ($valid == true) { // Check to see if all configuration values were validated.
                    $instance_config["dashcam"]["capture"]["opencv"]["resolution"]["width"] = floatval(explode("x", $input_values["dashcam"]["capture"]["opencv"]["resolution"])[0]);
                    $instance_config["dashcam"]["capture"]["opencv"]["resolution"]["height"] = floatval(explode("x", $input_values["dashcam"]["capture"]["opencv"]["resolution"])[1]);
                    $instance_config["dashcam"]["capture"]["opencv"]["framerate"] = $input_values["dashcam"]["capture"]["opencv"]["framerate"];
                    $instance_config["dashcam"]["capture"]["opencv"]["segment_length"] = $input_values["dashcam"]["capture"]["opencv"]["segment_length"];
                    $instance_config["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"] = $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"];
                    $instance_config["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"] = $input_values["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"];

                    if (json_encode($instance_config) == true) { // Verify that the data to be saved to the instance configuration file is valid.
                        if (is_writable($instance_configuration_file) == true) { // Verify that the instance configuration file is writable.
                            file_put_contents($instance_configuration_file, json_encode($instance_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); // Save the modified instance configuration to disk.
                            echo "<p>The configuration was updated successfully.<p>";
                        } else {
                            echo "<p class='error'>The instance configuration file doesn't appear to be writable. The configuration could not be saved.</p>";
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
                        <div class="buffer">
                            <label for="dashcam>capture>opencv>resolution">Resolution:</label>
                            <select id="dashcam>capture>opencv>resolution" name="dashcam>capture>opencv>resolution">
                                <option value="960x540" <?php if (intval($instance_config["dashcam"]["capture"]["opencv"]["resolution"]["height"]) == 540) { echo "selected"; } ?>>540p</option>
                                <option value="1280x720" <?php if (intval($instance_config["dashcam"]["capture"]["opencv"]["resolution"]["height"]) == 720) { echo "selected"; } ?>>720p</option>
                                <option value="1920x1080" <?php if (intval($instance_config["dashcam"]["capture"]["opencv"]["resolution"]["height"]) == 1080) { echo "selected"; } ?>>1080p</option>
                                <option value="2560x1440" <?php if (intval($instance_config["dashcam"]["capture"]["opencv"]["resolution"]["height"]) == 1440) { echo "selected"; } ?>>1440p</option>
                                <option value="3840x2160" <?php if (intval($instance_config["dashcam"]["capture"]["opencv"]["resolution"]["height"]) == 2160) { echo "selected"; } ?>>2160p</option>
                            </select><br><br>
                            <label for="dashcam>capture>opencv>framerate">Frame Rate: </label><input type="number" class="compactinput" id="dashcam>capture>opencv>framerate" name="dashcam>capture>opencv>framerate" step="0.1" min="0" max="240" value="<?php echo $instance_config["dashcam"]["capture"]["opencv"]["framerate"]; ?>"> fps<br><br>
                            <label for="dashcam>capture>opencv>segment_length">Segment Length: </label><input type="number" class="compactinput" id="dashcam>capture>opencv>segment_length" name="dashcam>capture>opencv>segment_length" step="1" min="0" max="3600" value="<?php echo $instance_config["dashcam"]["capture"]["opencv"]["segment_length"]; ?>"> seconds
                        </div>
                        <h4>Overlays</h4>
                        <div class="buffer">
                            <h5>Main</h5>
                            <div class="buffer">
                                <label for="dashcam>capture>opencv>stamps>main>date>enabled">Date: </label><input type="checkbox" id="dashcam>capture>opencv>stamps>main>date>enabled" name="dashcam>capture>opencv>stamps>main>date>enabled" <?php if ($instance_config["dashcam"]["capture"]["opencv"]["stamps"]["main"]["date"]["enabled"] == true) { echo "checked"; } ?>><br><br>
                                <label for="dashcam>capture>opencv>stamps>main>time>enabled">Time: </label><input type="checkbox" id="dashcam>capture>opencv>stamps>main>time>enabled" name="dashcam>capture>opencv>stamps>main>time>enabled" <?php if ($instance_config["dashcam"]["capture"]["opencv"]["stamps"]["main"]["time"]["enabled"] == true) { echo "checked"; } ?>>
                            </div>
                        </div>
                    </div>
                </div>


                <br><br><input type="submit" id="submit" name="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
