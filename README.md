# Optic

A graphical dashcam interface for [Predator](https://v0lttech.com/predator.php).

![Optic logo](https://v0lttech.com/assets/img/opticprologo.svg)


## Note

Optic is dependent on [Predator](https://v0lttech.com/predator.php) to function, and does not provide any useful functionality alone. Before installing Optic, Predator needs to be installed and configured.


## Installation

Downloads for Optic and Optic Pro can be found at <https://v0lttech.com/optic.php> and <https://v0lttech.com/opticpro.php> respectively.

To learn more about installing, configuring, and using Optic, see the [DOCUMENTATION.md](DOCUMENTATION.md) file.


## Description

Optic is a graphical interface for controlling Predator's dashcam functionality. Optic is designed to run on a low-powered computer, alongside Predator, and provides a simple interface for controlling dashcam recording over a network. Users can connect to Optic from a phone, tablet, or other network-enabled device with a web browser. Full installations can even include a dedicated control interface to remove the need for an external device.

While the initial installation and setup process requires some technical knowledge, Optic is designed to be extremely easy to use after the configuration process is completed. As such, users who want to take advantage of Predator's dashcam functionality can have their installer setup Predator and Optic on their behalf.


## Optic vs Optic Pro

Optic is a fully functional dashcam control interface for Predator. However, certain features are reserved for [Optic Pro](https://v0lttech.com/opticpro.php), a paid version of the same software. Optic and Optic Pro are nearly identical, except for some code that restricts access to certain features. In fact, it is entirely possible for a determined user to unlock the publicly available Optic version into the Pro version by making specific tweaks the source code. Optic users are well within their right to unlock Optic Pro manually by studying and modifying the source code, although this process is not supported.

In addition to all the features offered by Optic, these are the features available exclusively to [Optic Pro](https://v0lttech.com/opticpro.php).
- Customer support through the complete installation, configuration, and setup process.
- The ability to view all normal and saved dashcam video segments from the web interface.
- The ability to download any desired dashcam video segment from the web interface.
- The ability to erase dashcam video to free disk space.
- The ability to configure a custom message in the Predator dashcam overlay from the web interface, in place of the "PREDATOR" mark.
- The ability to manage Predator's dashcam recording functionality as a SystemD service, so that it starts automatically when the system boots.


## Screenshots

![Main interface in light mode](/assets/image/screenshots/main/light.png)
![Storage interface in dark mode](/assets/image/screenshots/storage/dark.png)



## Features

### Simple

After the initial setup is complete, Optic is intended to be simple and easy to use, just like a commercially available dashcam would be. As such, any driver comfortable using a traditional dashcam should have no problem using Optic.

### Reliable

Optic is designed to be reliable, in the sense that it can detect and notify the user when problems are encountered. Optic can display errors and warnings directly from Predator, and has its own diagnostic capabilities built in.

### Compatible

Optic works through nearly any web browser, and doesn't even require JavaScript for its core functionality. Whether you're using a modern fully-featured browser, or a minimalist lightweight one, Optic should work great.

### Lightweight

Optic is designed to be extremely lightweight, both in terms of network resources and processing power. While low latency is very beneficial, Optic doesn't need high bandwidth to function well.

### Adaptive

The Optic interface is designed to make the most of the available screen space, regardless of whether it is used from a large desktop monitor or a small smartphone display.

### Customizable

Optic allows the user to customize both the Optic interface itself, as well as certain Predator configuration values to match their needs, all from the web interface.

### Reversible

While Optic provides a graphical interface for Predator's dashcam functionality, it does not lock out or otherwise inhibit the ability to use Predator from the command line, as you normally would. In fact, it's even possible to start Predator from the command line for debugging purposes, then control it from the Optic web interface.
