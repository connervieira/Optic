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
        <title><?php echo $config["product_name"]; ?> - Settings</title>
        <link rel="stylesheet" href="./styles/main.css">
        <?php include "./loadtheme.php"; ?>
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
    </head>
    <body>
        <div class="navbar">
            <a class="button" role="button" href="./settings.php">Back</a>
            <a class="button" role="button" href="./settingsinstance.php">Basic</a>
        </div>
        <h1><?php echo $config["product_name"]; ?></h1>
        <h2>Instance Settings</h2>
        <p>This tool allows you to edit the instance configuration file directly. This tool supports only basic error checking, and should only be used by experienced users.</p>
        <main>
            <?php
            pro();
            if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
                verify_permissions($config);


                 // Load the instance configuration file from the disk.
                $instance_configuration_file = $config["instance_directory"] . "/config.json";
                $raw_instance_configuration = file_get_contents($instance_configuration_file);


                if ($_POST["updatedconfig"] !== null) {
                    if (json_decode($_POST["updatedconfig"])) {
                        file_put_contents($instance_configuration_file, $_POST["updatedconfig"]);
                        echo "<p class='success'>Successfully updated the configuration.</p>";
                    } else {
                        echo "<p class='error'>The configuration submitted is not valid JSON.</p>";
                    }
                }
            } else {
                $raw_instance_configuration = "{}";
            }

            ?>
            <form method="post">
                <textarea id="updatedconfig" name="updatedconfig" style="width:100%;height:500px;"><?php
                    if ($_POST["updatedconfig"] !== null) {
                        echo $_POST["updatedconfig"];
                    } else {
                        echo $raw_instance_configuration;
                    }
                    ?></textarea>
                <br><br><input type="submit" class="button" value="Submit">
            </form>
        </main>
    </body>
</html>
