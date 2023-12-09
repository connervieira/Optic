#!/bin/bash


########## CONFIGURATION START ##########

user_home=(/home/$(ls /home/ | head -n 1)); # Auto-detect the home directory of the first user on the system. If there are multiple users on the system that have home directories in the '/home' folder, then this might need to be manually set (for example, to '/home/pi').

predator_root="$user_home/Software/Predator/Instance"; # This is the path to the root Predator instance directory (the one containing `main.py`, `config.json`, and other support files).
predator_root="$user_home/Software/Programming/Python/Predator/"; # This is the path to the root Predator instance directory (the one containing `main.py`, `config.json`, and other support files).
optic_root="/var/www/html/optic" # This is the path to the root Optic directory (the one containing `index.php` and all other pages/support files).

########## CONFIGURATION END ##########

# Under normal circumstances, none of the lines below should need to be modified.





if [ "$EUID" -ne 0 ]; then # Check to see if the current user is root.
    echo "Please run this script as root."; sudo su; # Escalate the user to root.
fi
if [ "$EUID" -ne 0 ]; then # Check to again to see if the current user is root.
    echo "This script needs to be run as root. Please authenticate as root using the \`sudo su\` command."; # Inform the user that this script needs to be run as root.
    exit 1; # Exit the script.
fi


echo "This script expects that the Optic directory can be found at '$optic_root', and that the root Predator instance directory can be found at '$predator_root'. If these files can't be found in these locations, please edit the configuration section at the top of this script to reflect their accurate locations.";
echo "Continuing in 10 seconds"; sleep 7; echo "3"; sleep 1; echo "2"; sleep 1; echo "1"; sleep 1; # Wait for 10 seconds before continuing.

if [ ! -d $predator_root ]; then # Check to make sure the Predator directory exists.
    echo "Error: The root Predator instance directory does not exist at $predator_root."; # Inform the user of the problem.
    exit 1; # Exit the script.
fi
if [ ! -d $optic_root ]; then # Check to make sure the Optic directory exists.
    echo "Error: The Optic directory does not exist at $optic_root."; # Inform the user of the problem.
    exit 1; # Exit the script.
fi


echo "Installing system updates"; apt update; apt upgrade -y; # Update the system
echo "Installing dependencies"; apt install -y apache2 php; a2enmod php*; # Install dependencies.
echo "Restarting Apache2"; apache2ctl restart;
echo "Granting sudo permissions to 'www-data'"; echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers; # Grant the 'www-data' user sudo permissions.

chmod 777 $optic_root; # Make the Optic directory writable to all programs.
chmod 777 $optic_root/*; # Make the Optic directory contents writable to all programs.

chmod 777 $predator_root; # Make the Predator directory writable to all programs.
chmod 777 $predator_root/*; # Make the Predator directory contents writable to all programs.

hostname_output=$(hostname -I); # Get all local IP addresses.
IFS=' ' read -ra ip_array <<< "$hostname_output"; # Convert the local IP addresses to an array.

echo "Installation complete. Please continue configuring Optic from the web interface, which can likely be found at http://"${ip_array[0]}"/optic on the local network.";

