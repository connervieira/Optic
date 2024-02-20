<?php
include "./config.php";

$force_login_redirect = true;
include "./authentication.php";

include "./utils.php";


if ($config["advanced"] == false) {
    echo "<p>This tool is only available when advanced mode is enabled.</p>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $config["product_name"]; ?> - Update</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./management.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Update</h2>
        <p>This page allows you to forcefully update <?php echo $config["product_name"]; ?> or the instance it controls.</p>
        <br>
        <main>
            <?php
            $optic_directory = dirname(__FILE__);
            if ($_GET["confirm"] == "true") {
                if ($_GET["component"] == "predator") {
                    $backup_command = "sudo -u " . $config["exec_user"] . " rm -rf '/dev/shm/predator_backup'; sudo -u " . $config["exec_user"] . " cp -rf '" . $config["instance_directory"] . "' '/dev/shm/predator_backup'";
                    exec($backup_command, $output, $return);
                    if ($return == 0) { // Check to make sure the backup was successful before updating Predator.
                        echo "<p>The old Predator install has been temporarily backed up to '/dev/shm/predator_backup/'.</p>";
                        $update_command = "sudo -u cvieira git -C '" . $config["instance_directory"] . "' reset --hard HEAD; sudo -u " . $config["exec_user"] . " git -C '" . $config["instance_directory"] . "' pull";
                        exec($update_command, $output, $return);

                        echo "<p>Update process output: <span style='opacity:0.6;'>";
                        foreach ($output as $line) {
                            echo $line;
                        }
                        echo "</span></p>";
                    } else {
                        echo "<p>Predator could not be backed up before updating. The update has been cancelled to avoid data loss.</p>";
                    }
                    echo '<a class="button" role="button" href="update.php?confirm=true">Back</a><br><br><br>';
                } else if ($_GET["component"] == "optic") {
                    $backup_command = "sudo -u " . $config["exec_user"] . " rm -rf '/dev/shm/optic_backup'; sudo -u " . $config["exec_user"] . " cp -rf '" . $optic_directory . "' '/dev/shm/optic_backup'";
                    exec($backup_command, $output, $return);

                    if ($return == 0) { // Check to make sure the backup was successful before updating Optic
                        echo "<p>The old Optic install has been temporarily backed up to '/dev/shm/optic_backup/'.</p>";
                        $update_command = "sudo -u cvieira git -C '" . $optic_directory . "' reset --hard HEAD; git -C '" . $optic_directory . "' pull";
                        exec($update_command, $output, $return);

                        echo "<p>Update process output: <span style='opacity:0.6;'>";
                        foreach ($output as $line) {
                            echo $line;
                        }
                        echo "</span></p>";
                    } else {
                        echo "<p>Optic could not be backed up before updating. The update has been cancelled to avoid data loss.</p>";
                    }
                    echo '<a class="button" role="button" href="update.php?confirm=true">Back</a><br><br><br>';
                } else {
                    echo '
                    <a class="button" role="button" href="update.php?confirm=true&component=predator">Update Predator</a><br><br><br>
                    <a class="button" role="button" href="update.php?confirm=true&component=optic">Update Optic</a>
                    ';
                }
            } else {
                echo "<p>This tool allows developers and advanced users to overwrite the local installation of the selected component with the latest development version. This process will likely reset the configuration, which will require you to reconfigure everything from scratch. Under normal circumstances you should use the command line to manually update to the latest stable release to avoid unexpected behavior.</p>";
                echo "<p>If you understand the caveats of using this tool, you can acknowledge this warning and access the rest of this page.</p>";
                echo '<a class="button" role="button" href="update.php?confirm=true">Acknowledge</a>';
            }
            ?>
        </main>
    </body>
</html>
