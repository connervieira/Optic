<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";

$instance_config = load_instance_config($config);

$file_to_transfer = $instance_config["general"]["working_directory"] . "/" . $_GET["video"];
$file_extension = pathinfo($file_to_transfer, PATHINFO_EXTENSION);

shell_exec("cp '" . $file_to_transfer . "' './transfer." . $file_extension . "'"); // Copy the file to a location accessible to the webserver.
header("Location: ./transfer." . $file_extension); // Redirect to the copied file.
?>
