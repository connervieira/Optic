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
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./logout.php">Logout</a>
            <a class="button" role="button" href="./settings.php">Settings</a><br>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Dashboard</h2>
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
                file_put_contents("./start.sh", "python3 " . $config["instance_directory"] . "/main.py 3 &"); // Update the start script.
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
            shell_exec("sudo killall python3"); // Kill all Python executables.
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
                if (is_alive($config) == true) {
                    $lock_button = '<a class="lockbutton" role="button" id="lockbutton" style="color:#ffffff;background:#770000;" role="button" href="?action=lock">Lock</a><br><br><br><br>';
                    $start_button = '<a class="button" role="button" id="startbutton" style="color:#ffffff" role="button" href="?action=start">Restart</a>';
                    $stop_button = '<a class="button" role="button" id="stopbutton" style="color:#ffffff" role="button" href="?action=stop">Stop</a>';
                } else {
                    $lock_button = '<a class="lockbutton" role="button" id="lockbutton" style="color:#aaaaaa;background:#773333;" role="button" href="#">Lock</a><br><br><br><br>';
                    $start_button = '<a class="button" role="button" id="startbutton" style="color:#ffffff" role="button" href="?action=start">Start</a>';
                    $stop_button = '<a class="button" role="button" id="stopbutton" style="color:#aaaaaa" role="button" href="#">Stop</a>';
                }

                echo $lock_button;
                echo $start_button;
                echo $stop_button;
                ?>
                <br><br>
                <iframe id="statusframe" title="Status Frame" src="./status.php"></iframe>
            </div>
        </main>
        <audio id="notice_sound" src="./assets/audio/notice.mp3" preload="auto"></audio>
        <audio id="alert_sound" src="./assets/audio/alert.mp3" preload="auto"></audio>
    </body>
    <?php
    if ($config["auto_refresh"] == "client") {
        echo "
        <script>
            setInterval(() => {
                document.getElementById('statusframe').contentWindow.location.reload(true);
            }, 1000);
        </script>
        ";
    }
    ?>
    <script>
        var previous_latest_error = 0;
        const fetch_info = async () => {
            //console.log("Fetching instance status");
            const response = await fetch('./jsrelay.php'); // Fetch the status information using the JavaScript relay page.
            const result = await response.json(); // Parse the JSON data from the response.

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
                document.getElementById("lockbutton").style.background = "#773333";
                document.getElementById("lockbutton").href = "#";
                document.getElementById("startbutton").style.color = "#ffffff";
                document.getElementById("startbutton").href = "?action=start";
                document.getElementById("startbutton").innerHTML = "Start";
                document.getElementById("stopbutton").style.color = "#aaaaaa";
                document.getElementById("stopbutton").href = "?action=stop";
            }
            if (result.latest_error !== null) {
                if (result.latest_error[0] !== previous_latest_error) {
                    if (result.latest_error[1] == "warn") {
                        document.getElementById('notice_sound').play();
                    } else if (result.latest_error[1] == "error") {
                        document.getElementById('alert_sound').play();
                    }
                }
            }
            previous_latest_error = result.latest_error[0];
        }

        setInterval(() => { fetch_info(); }, 500); // Execute the instance fetch script every 500 milliseconds.
    </script>
</html>
