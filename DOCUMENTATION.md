# Documentation

This document explains how to install, setup, and use Optic and Optic Pro.


## Support

If you run into problems during the installation process, you can contact support using the information found at <https://v0lttech.com/contact.php>. [Optic Pro](https://v0lttech.com/opticpro.php) customers get complementary support from start to finish through the installation, configuration, setup, and usage process.


## Introduction

### Terminology

Optic and Predator form a somewhat complex link, and it's important to understand a few terms before continuing.

- The **instance** refers to the instance of Predator that is being controlled by Optic.
- The **controller** refers to Optic, controlling a Predator instance.

- The **interface directory** is the directory used by Predator to feed information that will be read by Optic. Think of this directory as the bridge Predator uses to active share information as it operates. All information in this directory is volatile.
- The **working directory** is the  directory used by Predator to store files as it works. This is where Predator stores logs, video files, images, and other information. This directory is not volatile, and should be stored somewhere secure.
- The **instance directory** is the main Predator directory, containing all of the scripts and assets used by the back-end.
- The **controller directory** is the main Optic directory, containing all of the scripts and assets used by the front-end controller interface.

### Security

Optic is primarily intended to be installed on a system dedicated to the usage of Predator. As such, the following instructions often involve granting permissions without regard for the security of other applications. If you plan to install Optic on a system running multiple services, use caution when granting very relaxed permissions.

Additionally, Optic is not designed to be exposed to the internet. While the interface does support authentication, someone with direct network access to Optic may be able to do things you wouldn't want them to do. As with all other services, you should take care to avoid unnecessary risk. If you plan to expose Optic to the internet regardless, here are some things to consider:
- The 'config.txt' file is a plain text file that contains important configuration values including the interface password, and full directory paths. You should configure your web-server to deny access to this file over the network. The Optic directory contains a '.htaccess' file that restricts access to this file when using Apache, but this will only work if overrides are enabled in your Apache configuration.
- The 'downloadnormal.php' and 'downloadsaved.php' pages allow [Optic Pro](https://v0lttech.com/opticpro.php) users to copy a desired video file to a directory accessible over the network so it can be downloaded. While the ability to select a file to copy requires that a user be authenticated, viewing a file that has already been copied does not. As such, you should be aware that someone with direct network access to Optic can download recently viewed video files if they know the exact file name.


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
4. Install Predator (Version 10.0)
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
4. You should also make sure that the controller directory, interface directory, and instance directory are all writable to PHP.


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
        - If your browser doesn't use JavaScript, you can select this option to allow Optic to continue to function.
    - The "Client" option will cause refreshes to be triggered at a regular interval by a client-side refresh script.
        - For the smoothest experience, you should select this option.
        - This option depends on JavaScript being supported and enabled by your browser.
    - The "Off" option disables the auto-refresh altogether.
        - This option may cause unexpected behavior, since Optic is likely to show outdated information unless manually refreshed regularly.
- The "Photosensitive Mode" toggle allows the user to disable visual effects that could be harmful to individuals with photo-sensitivity problems.
    - When photo-sensitivity mode is enabled, rapid flashing effects are disabled.
    - When photosensitive mode is disabled, rapid flashing effects are enabled.
- The "Heartbeat Threshold" setting determines how many seconds the Predator instance needs to stop responding for before Optic considers it to be inactive.
    - On slower devices, this value should be raised to prevent long processing times from causing Optic to mistakenly believe the instance isn't running.
    - On faster devices, this value can be lowered to make the control interface more responsive.
    - It's better to err on the side of too high, since values that are too low can lead to unexpected behavior, like multiple instances running at once.
- The "Theme" setting determines the aesthetic theme that the web interface uses.
    - This setting is strictly visual, and doesn't influence functionality in any significant way.

The "Connection Settings" section contains settings relating to the connection between Optic and the Predator instance.

- The "Instance Directory" setting should be used to specify the absolute directory path of the Predator instance directory.


### Unlocking

If you've purchased Optic Pro, now is a good time to make sure it has been unlocked. If you downloaded Optic Pro directly, then it should be automatically unlocked. However, if you're trying to unlock and existing Optic instance, or the automatic unlock failed, you should run the `unlock.php` script by navigating to it in your browser. For example: `http://192.168.0.76/optic/unlock.php`. You should see a message stating that Optic was successfully unlocked.


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

### State

Predator's current state is indicated using colored icons just above the controls, and just below the disk usage display. This feature is only available when the auto-refresh mode is set to "client" in the Optic configuration. These images show the following information about Predator's current state:

- GPS
    - Green: The GPS is online, and has a 3D fix.
    - Yellow: The GPS is online, and has a 2D fix.
    - Red: The GPS is online, but does not have a fix.
    - Gray: The GPS is offline.
- Camera:
    - Green: Dash-cam recording is active.
    - Blue: Predator is in parked mode, and is dormant.
    - Yellow: Predator is in parked mode, and is actively recording an event.
    - Gray: Dash-cam recording is offline.

### Controls

Below the disk usage section are the control buttons. These buttons are explained in the "Controlling" section above.

### Status

The status frame is located at the bottom of the page. This frame indicates whether or not Predator is running, and will show any errors or warnings issued by the instance. Recent errors and warnings are displayed in red and yellow font respectively. Older alerts are displayed in gray.


## Pages

### Controller Settings Page

The controller settings page allows you to configure Optic. This page can be reached from the main interface by clicking the "Settings" button in the top left, then the "Controller Settings" button in the center of the page.

### Instance Settings Page

The instance settings page allows you to configure Predator from the Optic web interface. This page can be reached from the main interface by clicking the "Settings" button in the top left, then the "Instance Settings" button in the center of the page.

### Advanced Instance Settings Page

The advanced instance settings page allows you to modify the Predator configuration file directly from Optic web interface. This page can be reached from the main interface by clicking the "Settings" button in the top left, then the "Instance Settings" button in the center of the page, then the "Advanced" button in the top left.

### SystemD Service Page

The SystemD page allows you to manage Predator's dashcam recording functionality as a SystemD service so that it starts automatically when the system boots. This page can be reached from the main interface by clicking the "Settings" button in the top left, then the "Management" button on the top right, then the "Service" button.

### Normal Storage Management Page

The normal storage management page allows you to manage unsaved dashcam video segments recorded by Predator. This page can be reached from the main interface by clicking the "Storage" button in the top right.

### Saved Storage Management Page

The saved storage management page allows you to manage video segments recorded by Predator that have been saved. This page can be reached from the main interface by clicking the "Storage" button in the top right, then the "Saved" in the top right.


## Audio

If Predator encounters an error, Optic can play an alert sound. However, for this feature to work, client side refresh must be enabled in the Optic settings. Additionally, both JavaScript and auto-play need to be permitted in your browser settings.
