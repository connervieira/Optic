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
        <title><?php echo $config["product_name"]; ?> - Service</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./management.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Service</h2>
        <p>This page allows you to manage Predator's dashcam functionality as a SystemD service. This is useful if you want Predator to automatically start recording dashcam video when the system boots.</p>
        <br>
        <main>
            <?php
                pro();
                if (strtolower(trim(shell_exec("ps -p 1 -o comm="))) !== "systemd") { // Check to see if this system isn't running SystemD.
                    echo "<p class=\"error\">This platform is not running SystemD. This feature is only compatible with systems using SystemD as an init system.</p>";
                    exit();
                } else if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                    if (isset($_GET["action"])) {
                        echo "<hr>";
                        if ($_GET["action"] == "create") {
                            if (pro_flat()) {
                                if (file_exists("/etc/systemd/system/predatordashcam.service")) { shell_exec('timeout 2 sudo rm -f /etc/systemd/system/predatordashcam.service'); } // Remove the existing Predator dashcam service file, if it exists.
                                shell_exec('timeout 2 sudo echo -e "[Unit]\nDescription=V0LT Predator Dashcam Daemon\nAfter=multi-user.target\n\n[Service]\nEnvironment=\"XDG_RUNTIME_DIR=/run/user/1000\"\nUser=' . $config["exec_user"] . '\nExecStart=python3 ' . str_replace("//", "/", $config["instance_directory"] . "/main.py") . ' 3\n\n[Install]\nWantedBy=multi-user.target" | sudo tee -a /etc/systemd/system/predatordashcam.service');
                                if (file_exists("/etc/systemd/system/predatordashcam.service")) {
                                    echo "<p class='success'>Successfully created Predator dashcam SystemD service file.</p>";
                                } else {
                                    echo "<p class='error'>Failed to create Predator dashcam SystemD service file.</p>";
                                }
                                echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                            }
                        } else if ($_GET["action"] == "delete") {
                            shell_exec('timeout 4 sudo systemctl disable predatordashcam.service'); // Disable the Predator dashcam SystemD service.
                            if (file_exists("/etc/systemd/system/predatordashcam.service")) {
                                shell_exec('timeout 2 sudo rm -f /etc/systemd/system/predatordashcam.service'); // Remove the Predator dashcam service file.
                                if (!file_exists("/etc/systemd/system/predatordashcam.service")) { // Check to see if the Predator dashcam service file was actually removed.
                                    echo "<p class='success'>Successfully erased Predator dashcam SystemD service file.</p>";
                                } else {
                                    echo "<p class='error'>Failed to erase Predator dashcam SystemD service file.</p>";
                                }
                            } else {
                                echo "<p class='error'>The Predator dashcam SystemD service file does not exist..</p>";
                            }
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        } else if ($_GET["action"] == "enable") {
                            shell_exec('timeout 4 sudo systemctl enable predatordashcam.service');
                            echo "<p class=\"success\">Enabled the Predator dashcam SystemD service.</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        } else if ($_GET["action"] == "disable") {
                            shell_exec('timeout 4 sudo systemctl disable predatordashcam.service');
                            echo "<p class=\"success\">Disabled the Predator dashcam SystemD service.</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        } else if ($_GET["action"] == "start") {
                            shell_exec('timeout 4 sudo systemctl start predatordashcam.service');
                            echo "<p class=\"success\">Started the Predator dashcam SystemD service.</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        } else if ($_GET["action"] == "stop") {
                            shell_exec('timeout 4 sudo systemctl stop predatordashcam.service');
                            echo "<p class=\"success\">Stopped the Predator dashcam SystemD service.</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        } else if ($_GET["action"] == "log") {
                            $log_text = shell_exec('timeout 3 sudo systemctl status predatordashcam.service | tail');
                            echo "<p style='text-align:left;'>" . str_Replace("\n", "<br>", $log_text) . "</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        } else if ($_GET["action"] == "view") {
                            $log_text = shell_exec('timeout 2 cat /etc/systemd/system/predatordashcam.service');
                            echo "<p style='text-align:left;'>" . str_Replace("\n", "<br>", $log_text) . "</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"" . basename($_SERVER['PHP_SELF']) . "\">Clear</a>";
                        }
                        echo "<hr>";
                    }
                }
            ?>
            <a class="button" role="button" href="?action=create" title="Create a SystemD service file for Predator's dashcam functionality.">Create</a>
            <a class="button" role="button" href="?action=delete" title="Delete the Predator dashcam SystemD service file.">Delete</a><br><br><br>
            <a class="button" role="button" href="?action=enable" title="Enable the Predator dashcam SystemD service so it starts at boot.">Enable</a>
            <a class="button" role="button" href="?action=disable" title="Disable the Predator dashcam SystemD service so it doesn't start at boot.">Disable</a><br><br><br>
            <a class="button" role="button" href="?action=start" title="Manually start the Predator dashcam SystemD service.">Start</a>
            <a class="button" role="button" href="?action=stop" title="Manually stop the Predator dashcam SystemD service.">Stop</a><br><br><br>
            <a class="button" role="button" href="?action=log" title="View the logs for the Predator dashcam SystemD service.">Logs</a>
            <a class="button" role="button" href="?action=view" title="View the contents of the Predator dashcam service file.">View</a><br><br><br>
        </main>
    </body>
</html>
