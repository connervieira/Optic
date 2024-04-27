# Change-log

This document contains a list of all the changes for each version of Optic.


## Version 1.0 

### Initial Release

December 4th, 2023

- Completed core functionality.
    - Implemented basic controls.
        - Added the ability to start Predator.
        - Added the ability to stop Predator.
        - Added the ability to lock dashcam video segments.


## Version 1.1

December 5th, 2023

- Added basic dashcam file management.
    - Dashcam videos can now be viewed, downloaded, and erased from the Optic interface.


## Version 1.2

December 9th, 2023

- Increased the precision of the disk usage display.
- The root settings page now shows the 'Controller' and 'Instance' buttons on separate lines.
- Improved support for managing recordings when multiple capture devices are used simultaneously.
- Updated permission verification check.
    - Not fatal errors have been turned into warnings that don't prevent the rest of the page from loading.


## Version 1.3

December 15th, 2023

- The following instance configuration values can now be modified from the Optic web interface.
    - `dashcam>saving>unsaved_history_length`
    - `dashcam>capture>opencv>stamps>main>message_1`
    - `dashcam>capture>opencv>stamps>main>message_2`
- Added hover text to several configuration values in the instance configuration web interface.
- Added advanced instance configuration page, which allows for the instance configuration file to be edited directly.
- Added the ability to manage Predator's dashcam functionality is a SystemD service.
    - Optic Pro can create, delete, enable, disable, start, and stop Predator as a SystemD service, as well as view log files.


## Version 1.4

January 15th, 2024

- Updated dashcam video downloading.
    - Downloaded dashcam videos are now named after their timestamp, rather than just being called "transfer".
    - Multiple dashcam videos can now be downloaded concurrently.
    - Fixed an issue where the "Saved Storage" page would download dashcam videos from the unsaved directory. This would lead to download failures if the original dashcam video file had been deleted.
- Updated `.htaccess` to disallow listing all files in the Optic directory.


## Version 2.0

March 5th, 2024

- Updated instance configuration page.
    - Updated the configuration interface to reflect Predator V10's configuration layout.
    - Added more resolution options.
    - Added more GPS video overlay stamp configuration options.
    - Added parking mode configuration options.
    - Added the ability to add, remove, and modify capture devices.
    - Added the ability to configure both the working directory and interface directory.
    - Added in-depth segment saving configuration options.
    - Added GPS toggle.
- Updated the storage page to be compatible with Predator V10.0.
    - Optic is now compatible with the new Predator configuration layout.
    - Optic now recognizes separate audio files and displays them next to their associated video file.
    - Segments that are shorter than the expected segment length are now considered to be the end of a video section.
        - This causes Predator restarts to be shown as the start of a new section of video.
- Optic no longer displays a warning when the interface directory is missing, since Predator should automatically create it when it first starts.
- Updated the way Optic handles permissions
    - Optic now attempts to set the correct permissions on the Predator configuration file before running the permissions check.
    - The permissions check on the `start.sh` script now only runs if the file exists.
        - This prevents Optic from returning errors if the start script hasn't yet been created.
    - Optic now checks to see if Predator's interface directory is disabled in its configuration.
- It is now possible to set an empty interface password in the configuration to disable authentication.
- Changed the colors of the "Lock" button on the main dashboard to better differentiate when Predator is not active.
- Updated Optic's management utilities.
    - The SystemD service page is now reached from a dedicated "management" utilities page.
    - Added an "advanced mode" configuration value to enable and disable potentially destructive administration tools.
    - Added update tool to force update Predator and Optic from the web interface.
- Added Predator state indicator icons to the dashboard.
- Added working directory and interface directory file viewer.


## Version 2.0.1

March 17th, 2024

- Fixed an issue where continuously recorded segments with multiple channels would be displayed as separate video sections.
- Updated SystemD service registration.
    - Fixed SystemD service by running with the correct working directory.
    - The Predator dash-cam SystemD service file created by Optic now starts Predator in headless mode.
- Fixed typos on the erase pages.


## Version 2.1

*Release date to be determined*

- Updated instance configuration page.
    - Added support for per-device frame-rate configuration.
    - Added support for per-device resolution configuration.
    - Added support for individually enabling or disabling devices.
- Reduced the margin of error for detecting continuous video sections due to improved accuracy in Predator's video segmentation.
- Updated the management page.
    - Added a tool for forcefully updating file permissions.
    - Re-ordered the advanced tools.
    - Added a notice explaining how to enabled advanced tools.
- Added video timestamp offset configuration option.
