# Changelog

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
