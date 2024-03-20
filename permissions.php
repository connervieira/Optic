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
        <title><?php echo $config["product_name"]; ?> - Permissions</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./management.php">Back</a>
        </div>
        <h1><?php echo htmlspecialchars($config["product_name"]); ?></h1>
        <h2>Permissions</h2>
        <p>This utility allows you to forcefully update the permissions for Predator and <?php echo htmlspecialchars($config["product_name"]); ?>.</p>
        <br>
        <main>
            <a class="button" role="button" href="?dir=instance">Instance</a>
            <a class="button" role="button" href="?dir=controller">Controller</a>
            <a class="button" role="button" href="?dir=home">Home</a>
            <hr class="separator">
            <div>
                <?php
                $selected_directory = $_GET["dir"];
                if (isset($selected_directory) == true) { // Check to see if the user has selected a directory.
                    if ($selected_directory == "instance") { $directory_path = $config["instance_directory"];
                    } else if ($selected_directory == "controller") { $directory_path = "./";
                    } else if ($selected_directory == "home") { $directory_path = shell_exec("echo ~" . $config["exec_user"]);
                    } else { echo "<p class='warning'>Unknown directory selected.</p>"; $directory_path = ""; exit(); }
                    $directory_path = trim($directory_path);


                    if (is_dir($directory_path) == false) {
                        echo "<p class='warning'>The selected directory does not appear to exist. However, this may be because PHP does not have permission to read it. You can still attempt to update it's permissions below if you're sure it exists.</p>";
                    }
                    if (time() - $_GET["confirmation"] < 0) {
                        echo "<p>The confirmation timestamp is in the future. If you clicked an external link to get here, it is possible someone is attempting to manipulate you into manipulating permissions. No videos were deleted.</p>";
                    } else if (time() - $_GET["confirmation"] < 30) {
                        shell_exec("sudo chmod -R 777 " . $directory_path);
                        echo "<p>Permissions have been updated for '" . $directory_path . "'.</p>";
                    } else {
                        echo "<p>Are you sure you would like make '" . $directory_path ."' accessable to all processes on this system?</p>";
                        echo "<a class='button' href='?dir=" . $selected_directory ."&confirmation=" . time() . "'>Confirm</a>";
                    }
                } else {
                    echo "<p><i>Select a directory to update.</i></p>";
                }
                ?>
            </div>
        </main>
    </body>
</html>
