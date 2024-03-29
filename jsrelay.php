<?php
// This page serves as a relay to allow JavaScript scripts to read server-side information.

include "./config.php";
include "./utils.php";

$force_login_redirect = true;
include "./authentication.php";

$info["is_alive"] = is_alive($config);
$info["latest_error"] = latest_error($config);
$info["disk_usage"] = disk_usage($config);
$info["state"] = predator_state($config);

echo json_encode($info, JSON_UNESCAPED_SLASHES);

?>
