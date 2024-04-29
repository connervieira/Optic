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
        <title><?php echo $config["product_name"]; ?> - Files</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar" role="navigation">
            <a class="button" role="button" href="./management.php">Back</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Files</h2>
        <p>This utility allows you to view the files in both the working directory and interface directory used by the connected Predator instance.</p>
        <br>
        <main>
            <a class="button" role="button" href="?dir=working">Working</a>
            <a class="button" role="button" href="?dir=interface">Interface</a>
            <hr class="separator">
            <div>
                <?php
                $allowed_extensions = array("csv", "txt", "json"); // These are the extensions that can be shown by this utility.

                $selected_directory = $_GET["dir"];
                $selected_file = preg_replace("/[^A-Za-z0-9 _.\-]/", '', $_GET["file"]);
                if (isset($selected_directory) == true) { // Check to see if the user has selected a directory.
                    $instance_config = load_instance_config($config);

                    if ($selected_directory == "working") { $directory_path = $instance_config["general"]["working_directory"];
                    } else if ($selected_directory == "interface") { $directory_path = $instance_config["general"]["interface_directory"];
                    } else { echo "<p class='warning'>Unknown directory selected.</p>"; $directory_path = ""; }

                    if (isset($selected_file) == true) { // Check to see if the user has selected a file.
                        $full_file_path = $directory_path . "/" . $selected_file;
                        if (file_exists($full_file_path)) { // Check to see if the selected file exists.
                            $file_contents = file_get_contents($full_file_path);
                            if (pathinfo($full_file_path, PATHINFO_EXTENSION) == "json") { // Check to see if this is a JSON file.
                                if ($_GET["format"] == "true") {
                                    $file_contents = json_encode(json_decode($file_contents), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                    echo '<a class="button" role="button" href="?dir=' . $selected_directory . '&file=' . $selected_file . '">Raw</a><br><br>';
                                } else {
                                    echo '<a class="button" role="button" href="?dir=' . $selected_directory . '&file=' . $selected_file . '&format=true">Format</a><br><br>';
                                }
                            }
                            echo "<pre style='text-align:left;white-space: pre-wrap;'>";
                            echo $file_contents;
                            echo "</pre>";
                        } else {
                            echo "<p class='warning'>The selected file does not exist.</p>";
                        }
                    } else {
                        if (is_dir($directory_path)) { // Check to see if the directory to scan actually exists.
                            $directory_contents = scandir($directory_path);
                        } else {
                            echo "<p class='warning'>The selected directory does not exist.</p>";
                            $directory_contents = array();
                        }

                        $files_to_show = array(); // This is a placeholder that will be populated with the files to display in the next step.
                        foreach ($directory_contents as $file) { // Iterate over each file in the directory.
                            if (in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extensions)) { // Check to see if this file's extension is in the list of allowed extensions.
                                echo "<p><a href='?dir=" . $selected_directory . "&file=" . $file . "'>" . $file . "</a></p>";
                            }
                        }
                    }
                } else {
                    echo "<p><i>Select a directory to view.</i></p>";
                }
                ?>
            </div>
        </main>
    </body>
</html>
