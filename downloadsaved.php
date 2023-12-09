<?php
include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";

pro();
if ($config["\160\x72\157\x64\165\143\x74\137\156\141\x6d\145"] == "\117\x70\164\151\143\40\x50\x72\x6f") {
    $instance_config = load_instance_config($config);

    $file_to_transfer = $instance_config["general"]["working_directory"] . "/" . $instance_config["dashcam"]["saving"]["directory"] . "/" . $_GET["video"];
    $file_extension = pathinfo($file_to_transfer, PATHINFO_EXTENSION);

    shell_exec("cp '" . $file_to_transfer . "' './transfer." . $file_extension . "'"); // Copy the file to a location accessible to the webserver.
    header("Location: ./transfer." . $file_extension); // Redirect to the copied file.
}
?>
