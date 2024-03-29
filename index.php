<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";

$instance_config = load_instance_config($config);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Dashboard</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body id="body">
        <div class="navbarleft" role="navigation">
            <a class="button" role="button" href="./logout.php">Logout</a>
            <a class="button" role="button" href="./settings.php">Settings</a><br>
        </div>
        <div class="navbarright" role="navigation">
            <a class="button" role="button" href="./storagenormal.php">Storage</a><br>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <?php
        verify_permissions($config); // Verify that PHP has all of the appropriate permissions.
        $instance_config = load_instance_config($config);

        $action = $_GET["action"];
        if ($action == "lock") {
            touch($instance_config["general"]["interface_directory"] . "/" . $instance_config["dashcam"]["saving"]["trigger"]); // Create the dashcam lock trigger file in the interface directory.
        } else if ($action == "start") {
            shell_exec("sudo killall python3"); // Kill all existing Python3 processes.
            if (!file_exists("./start.sh")) {
                file_put_contents("./start.sh", ""); // Create the start script.
            }
            if (is_writable("./start.sh")) {
                file_put_contents("./start.sh", "export XDG_RUNTIME_DIR=/run/user/1000; python3 " . $config["instance_directory"] . "/main.py 3 --headless"); // Update the start script.
            } else {
                echo "<p class=\"error\">The start.sh script is not writable.</p>";
                exit();
            }
            if (file_exists("./start.sh")) { // Verify that the start script exists.
                $start_command = "sudo -u " . $config["exec_user"] . " sh ./start.sh"; // Prepare the command to start an instance.
                shell_exec($start_command . ' > /dev/null 2>&1 &'); // Start an instance.
                header("Location: ."); // Reload the page to remove any arguments from the URL.
            } else {
                echo "<p class=\"error\">The start script doesn't appear to exist.</p>";
                echo "<p class=\"error\">The program could not be started.</p>";
            }
        } else if ($action == "stop") {
            shell_exec("sudo killall python3; sudo killall arecord"); // Kill all Predator related processes.
            header("Location: ."); // Reload the page to remove any arguments from the URL.
        }


        $disk_usage = disk_usage($config);
        echo "<p style='margin-bottom:-18px;'>Saved/Total Video</p>";
        echo "<p id='diskusagedashcam'>" . $disk_usage["saved"] . "/" . $disk_usage["working"] . "</p>";
        echo "<p style='margin-bottom:-18px;'>Available/Total Space</p>";
        echo "<p id='diskusagefull'>" . $disk_usage["free"] . "/" . $disk_usage["total"] . "</p>";
        ?>
        <main>
            <div class="display">
                <?php
                if ($config["auto_refresh"] == "client") {
                    echo '<div style="height:90px;">
                        <img class="statusicon" id="statusgps" title="GPS Status" src="./assets/image/icons/gps.svg">
                        <img class="statusicon" id="statuscamera" title="Camera Status" src="./assets/image/icons/camera.svg"><br>
                    </div>';
                }
                
                if (is_alive($config) == true) {
                    $lock_button = '<a class="lockbutton" role="button" id="lockbutton" style="color:#ffffff;background:#770000;" role="button" href="?action=lock">Lock</a><br><br><br><br>';
                    $start_button = '<a class="button" role="button" id="startbutton" style="color:#ffffff" role="button" href="?action=start">Restart</a>';
                    $stop_button = '<a class="button" role="button" id="stopbutton" style="color:#ffffff" role="button" href="?action=stop">Stop</a>';
                } else {
                    $lock_button = '<a class="lockbutton" role="button" id="lockbutton" style="color:#aaaaaa;background:#775555;" role="button" href="#">Lock</a><br><br><br><br>';
                    $start_button = '<a class="button" role="button" id="startbutton" style="color:#ffffff" role="button" href="?action=start">Start</a>';
                    $stop_button = '<a class="button" role="button" id="stopbutton" style="color:#aaaaaa" role="button" href="#">Stop</a>';
                }

                echo $lock_button;
                echo $start_button;
                echo $stop_button;
                ?>
                <br><br>
                <div class="iframeholder" id="statusview"><iframe id="statusframe" title="Status Frame" src="./status.php"></iframe></div>
            </div>
        </main>
        <audio id="notice_sound" src="./assets/audio/notice.mp3" preload="auto"></audio>
        <audio id="alert_sound" src="./assets/audio/alert.mp3" preload="auto"></audio>
    </body>
    <?php
    if ($config["auto_refresh"] == "client") {
        echo "
        <script>
            const refresh_status_view = async () => {
                const response = await fetch('./status.php'); // Fetch the content from the status page.
                const result = await response.text(); // Parse the JSON data from the response.
                document.getElementById('statusview').innerHTML = result;
            }

            setInterval(() => { refresh_status_view(); }, 1000); // Execute the status refresh script every 500 milliseconds.
        </script>
        ";
    }
    ?>
    <script>
        var previous_latest_error = 0; // This holds the Unix timestamp of when the most recent error of the last cycle occured.
        var last_alarm_time = 0; // This holds the Unix timestamp of the last time an alarm was played.

        function sleep(milliseconds) {
            return new Promise(resolve => {
                setTimeout(resolve, milliseconds);
            });
        }

        const fetch_info = async () => {
            //console.log("Fetching instance status");
            const response = await fetch('./jsrelay.php'); // Fetch the status information using the JavaScript relay page.
            const result = await response.json(); // Parse the JSON data from the response.

            document.getElementById("diskusagedashcam").innerHTML = result.disk_usage["saved"] + "/" + result.disk_usage["working"] 
            document.getElementById("diskusagefull").innerHTML = result.disk_usage["free"] + "/" + result.disk_usage["total"] 


            // Update the control buttons based on the instance status.
            if (result.is_alive) {
                document.getElementById("lockbutton").style.color = "#ffffff";
                document.getElementById("lockbutton").style.background = "#770000";
                document.getElementById("lockbutton").href = "?action=lock";
                document.getElementById("startbutton").style.color = "#ffffff";
                document.getElementById("startbutton").href = "?action=start";
                document.getElementById("startbutton").innerHTML = "Restart";
                document.getElementById("stopbutton").style.color = "#ffffff";
                document.getElementById("stopbutton").href = "?action=stop";
            } else {
                document.getElementById("lockbutton").style.color = "#aaaaaa";
                document.getElementById("lockbutton").style.background = "#775555";
                document.getElementById("lockbutton").href = "#";
                document.getElementById("startbutton").style.color = "#ffffff";
                document.getElementById("startbutton").href = "?action=start";
                document.getElementById("startbutton").innerHTML = "Start";
                document.getElementById("stopbutton").style.color = "#aaaaaa";
                document.getElementById("stopbutton").href = "?action=stop";
            }


            if (result.is_alive) {
                if (result.state.gps == 3) {
                    document.getElementById("statusgps").style.background = "#559955";
                    document.getElementById("statusgps").title = "GPS Status (3D fix)";
                } else if (result.state.gps == 2) {
                    document.getElementById("statusgps").style.background = "#bb9933";
                    document.getElementById("statusgps").title = "GPS Status (2D fix)";
                } else if (result.state.gps == 1) {
                    document.getElementById("statusgps").style.background = "#995555";
                    document.getElementById("statusgps").title = "GPS Status (no fix)";
                } else {
                    document.getElementById("statusgps").style.background = "#444444";
                    document.getElementById("statusgps").title = "GPS Status (offline)";
                }

                if (result.state.mode == "dashcam/normal") {
                    document.getElementById("statuscamera").style.background = "#559955";
                    document.getElementById("statuscamera").title = "Camera Status (normal)";
                } else if (result.state.mode == "dashcam/parked_active") {
                    document.getElementById("statuscamera").style.background = "#bb9933";
                    document.getElementById("statuscamera").title = "Camera Status (parked, active)";
                } else if (result.state.mode == "dashcam/parked_dormant") {
                    document.getElementById("statuscamera").style.background = "#4444cc";
                    document.getElementById("statuscamera").title = "Camera Status (parked, dormant)";
                } else {
                    document.getElementById("statuscamera").style.background = "#444444";
                    document.getElementById("statuscamera").title = "Camera Status (offline)";
                }
            } else {
                document.getElementById("statuscamera").style.background = "#111111";
                document.getElementById("statusgps").style.background = "#111111";
            }




            if (result.latest_error !== null) { // Check to see if there is a recent error.
                if (result.latest_error[0] != previous_latest_error) { // Check to see if this error is new.
                    if (Math.floor(Date.now()/1000) - last_alarm_time > 1) { // Check to make sure it has been at least 1 second since the last alarm was played.
                        last_alarm_time = Math.floor(Date.now()/1000) // Update the last alarm time.

                        // Play the appropriate warning sound.
                        if (result.latest_error[1] == "warn") {
                            document.getElementById('notice_sound').play();
                        } else if (result.latest_error[1] == "error") {
                            document.getElementById('alert_sound').play();
                        }

                        // Flash the screen several times as part of the alert, if configured to do so.
                        <?php
                        if ($config["photosensitive"] == false) { // Check to see if photosensitivity mode is disabled before loading this part of the script.
                            echo '
                            var original_body_color = document.getElementById("body").style.background
                            for (let i = 0; i < 5; i++) { 
                                if (result.latest_error[1] == "warn") {
                                    document.getElementById("body").style.background = "#ffff00";
                                } else if (result.latest_error[1] == "error") {
                                    document.getElementById("body").style.background = "#ff0000";
                                }
                                await sleep(50);
                                document.getElementById("body").style.background = original_body_color;
                                await sleep(50);
                            }';
                        }
                        ?>
                    }
                }
                previous_latest_error = result.latest_error[0];
            }
        }

        setInterval(() => { fetch_info(); }, 500); // Execute the instance fetch script every 500 milliseconds.
    </script>
</html>
