# Documentation

This document exlains how to install, setup, and use Optic.


## Introduction

### Terminology

Optic and Predator form a somewhat complex link, and it's important to understand a few terms before continuing.

- The **instance** refers to the instance of Predator that is being controlled by Optic.
- The **controller** refers to Optic, controlling a Predator instance.

- The **interface directory** is a directory used by Predator to feed information that will be read by Optic. Think of this directory as the bridge Predator uses to active share information as it operates.
- The **instance directory** is the main Predator directory, containing all of the scripts and assets used by the back-end.
- The **controller directory** is the main Optic directory, containing all of the scripts and assets used by the front-end controller interface.

### Security

Optic is primarily intended to be installed on a system dedicated to the usage of Predator. As such, the following instructions often involve granting permissions without regard for the security of other applications. If you plan to install Optic on a system running multiple services, use caution when granting very relaxed permissions.

### Methodology

Optic must be installed on the device hosting Predator, and it interacts with the instance in a few different ways.

- **Fetching** information is done using the interface directory, as defined previously. Predator places information regarding its operation into this directory for other programs to read.
- **Controlling** the instance is done directly, using a shell script. When the user commands Optic to start or stop Predator, a shell script is executed accordingly.
- **Configuring** the instance is done by directly modifying its configuration file.


## Installing

### Dependencies

There are a few dependencies that need to be installed for Optic to function.

1. Install Apache, or another web-server host.
    - Example: `sudo apt install apache2`
2. Install and enable PHP for your web-server.
    - Example: `sudo apt install php8.1; sudo a2enmod php8.1`
3. Restart your web-server host.
    - Example: `sudo apache2ctl restart`
4. Install Predator
    - Downloads and documentation can be found at <https://v0lttech.com/predator.php>

### Installation

After the dependencies are installed, copy the Optic directory from the source you received it from, to the root of your web-server directory.

For example: `cp ~/Downloads/Optic /var/www/html/optic`


## Set Up

### Permissions

For Optic to function properly, Apache and PHP must be granted administrative rights. Without these, the controller won't be able to start and stop processes.

1. Open the sudo configuration file with the command `visudo`
2. Add the line `www-data ALL=(ALL) NOPASSWD: ALL`
3. Save the document and exit.
4. You should also make sure that the controller directory, interface directory, and instant directory are all writable to PHP.


### Connecting

After the basic set-up process is complete, you should be able to view the Optic interface in a web browser.

1. Open a web browser of your choice.
2. Enter the URL for your Optic installation.
    - Example: `http://192.168.0.76/optic/`
3. After the login page appears, enter the default password, `predator`.
4. Once you've logged in, you should see the main interface.

It should be noted that you're likely to see several errors at this point, given that Optic hasn't been fully configured yet.


### Configuring

Once you've verified that Optic is working as expected, you should configure it.

1. Click the "Settings" button on the main Optic dashboard.
2. Adjust settings as necessary or desired.

The "Interface Settings" section contains settings relating to the graphical Optic interface itself.

- The "Password" setting specifies the password used to protect the web interface.
    - This password is not encrypted, nor does it protect the security of the physical device running Optic.
- The "Auto Refresh" setting determines how the main dashboard will automatically refresh with information from Predator.
    - The "Server" option will cause refreshes to be triggered at a regular interval by an automatic refresh tag attached to relevant pages on the server side.
    - The "Client" option will cause refreshes to be triggered at a regular interval by a client-side refresh script.
        - This option depends on JavaScript being supported and enabled by your browser.
    - The "Off" option disables the auto-refresh altogether.
        - This option may cause unexpected behavior, since Optic is likely to show outdated information unless manually refreshed regularly.
- The "Heartbeat Threshold" setting determines how many seconds the Predator instance needs to stop responding for before Optic considers it to be inactive.
    - On slower devices, this value should be raised to prevent long processing times from causing Optic to mistakenly believe the instance isn't running.
    - On faster devices, this value can be lowered to make the control interface more responsive.
    - It's better to err on the side of too high, since values that are too low can lead to unexpected behavior, like multiple instances running at once.
- The "Theme" setting determines the aesthetic theme that the web interface uses.
    - This setting is strictly visual, and doesn't influence functionality in any significant way.

The "Connection Settings" section contains settings relating to the connection between Optic and the Predator instance.

- The "Instance Directory" setting should be used to specify the absolute directory path of the Predator instance directory.


## Usage

At this point, Optic should be fully configured, and there shouldn't be any errors on the main dashboard.

### Controlling

After the initial setup and configuration, controlling the linked Predator instance is extremely simple.

1. On the main Optic dashboard, click the "Start" button to start Predator.
    - If Predator is already running, the "Start" button will be replaced with "Restart".
2. If auto-refresh is enabled, you should see the status update within a few seconds.
    - If auto-refresh is disabled, you should manually refresh the page after a few seconds instead.
    - Do not click the "Start" button multiple times rapidly, as this will repeatedly attempt to terminate and restart Predator.
3. Once Predator is running, you'll be able to see diagnostics regarding its status, including any recent errors.
    - At this point, the web interface can be closed without stopping the instance.
4. To save the current and previous dashcam segments, press the "Lock" button.
5. To stop the instance, simply press the "Stop" button.
    - This button will stop all Python processes, even if multiple instances of Predator were inadvertently launched.


## Interface

This section briefly describes the elements of the main Optic dashboard interface.

### Disk Usage

Just below the page header are two lines that indicate disk usage. These lines take the following format:

[saved dashcam video] / [total working directory size]
[total disk free] / [total disk size]

### Controls

Below the disk usage section are the control buttons. These buttons are explained in the "Controlling" section above.

### Status

The status frame is located at the bottom of the page. This frame indicates whether or not Predator is running, and will show any errors or warnings issued by the instance. Recent errors and warnings are displayed in red and yellow font respectively. Older alerts are displayed in gray.


## Audio

If Predator encounters an error, Optic can play an alert sound. However, for this feature to work, client side refresh must be enabled in the Optic settings. Additionally, both JavaScript and auto-play need to be permitted in your browser settings.
