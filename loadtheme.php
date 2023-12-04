<?php


// This script echos out the stylesheet containing the configured theme.
// This script must be called in the <head></head> tag, and must be called after the "./config.php" import line.

echo "<link rel='stylesheet' href='./styles/themes/" . $config["theme"] . ".css'>";

?>
