# 💧 Aqua Pump

### Smart Water Pumping & Dispensing Management System

Aqua Pump is an IoT-enabled water pumping and dispensing management
system. It combines an ESP32-based automatic dispensing unit with a web
application for monitoring pump activity, viewing telemetry, reviewing
history, and receiving system alerts.

> **Live website:** <http://aquapump.lxi.be/>\
> **Secure website:** <https://aquapump.lxi.be/>

------------------------------------------------------------------------

## Table of Contents

1.  [Project Overview](#1-project-overview)
2.  [Problem Statement](#2-problem-statement)
3.  [Project Objectives](#3-project-objectives)
4.  [Current MVP Scope](#4-current-mvp-scope)
5.  [Core Features](#5-core-features)
6.  [System Architecture](#6-system-architecture)
7.  [Technology Stack](#7-technology-stack)
8.  [Hardware Configuration](#8-hardware-configuration)
9.  [Repository Structure](#9-repository-structure)
10. [Database Design](#10-database-design)
11. [Installation and Setup](#11-installation-and-setup)
12. [Deployment](#12-deployment)
13. [Telemetry and API](#13-telemetry-and-api)
14. [Testing](#14-testing)
15. [Scrum and Team Organization](#15-scrum-and-team-organization)
16. [Known Limitations and Next
    Steps](#16-known-limitations-and-next-steps)
17. [Contributing](#17-contributing)
18. [Academic Information](#18-academic-information)

------------------------------------------------------------------------

## 1. Project Overview

Aqua Pump is a Software Engineering and Internet of Things (IoT) project
focused on improving the monitoring and management of water pumping and
dispensing operations.

The current MVP consists of:

-   An **ESP32** that reads sensors and controls the pump automatically.
-   A **PHP backend** that receives and stores device telemetry.
-   A **MySQL/MariaDB database** that stores users, pump information,
    telemetry, history, and alerts.
-   A **web dashboard** that displays system status and operational
    information.

The ESP32 is responsible for automatic pump operation. The web dashboard
monitors the system and displays information received from the device;
it is not the authority for the automatic pumping logic.

## 2. Problem Statement

Water users and pump operators may face unreliable water availability,
inefficient manual dispensing, limited visibility into pump activity,
poor operational record keeping, and difficulty identifying faults.

Aqua Pump addresses part of this problem by combining automatic pump
operation with a web-based monitoring and record-keeping platform.

## 3. Project Objectives

The project aims to:

1.  Automate the defined water dispensing process using an ESP32.
2.  Monitor container detection and water distance measurements.
3.  Send device telemetry to a web backend.
4.  Display pump and device status through a web dashboard.
5.  Record pump state changes and operating duration.
6.  Present relevant system alerts and fault information.
7.  Test the integrated hardware and software against defined
    requirements.

## 4. Current MVP Scope

### Included in the current implementation

-   ESP32-based automatic pump operation.
-   IR-based container detection.
-   Ultrasonic distance measurement.
-   Relay-controlled DC pump.
-   Wi-Fi telemetry transmission to the PHP backend.
-   PHP/MySQLi backend endpoints.
-   User authentication and web dashboard.
-   Pump status, device connection status, history, and alerts.
-   Pump runtime tracking based on recorded start/stop events.

### Not yet fully implemented or verified

-   Calibrated water-volume measurement through the flow sensor.
-   Complete production-grade device authentication and security
    hardening.
-   Final physical verification of relay polarity and all hardware
    safety conditions.
-   Full end-to-end acceptance testing under real operating conditions.

Features outside the current MVP should be treated as future
improvements until implemented and tested.

## 5. Core Features

### Web application

-   User login and access to the dashboard.
-   Pump status and device connection monitoring.
-   Container and ultrasonic sensor information when telemetry is
    available.
-   Pump activity history and runtime information.
-   System alerts and fault information.
-   Automatic refresh of monitoring information.

### ESP32 firmware

-   Reads the IR container-detection sensor.
-   Reads the ultrasonic distance sensor.
-   Controls the pump through a relay.
-   Applies automatic start/stop conditions.
-   Enforces a maximum pump runtime.
-   Sends telemetry to the backend over Wi-Fi.

## 6. System Architecture

``` text
          ┌──────────────────────────┐
          │       WEB BROWSER        │
          │  Dashboard / History     │
          │  Control Panel / Alerts  │
          └────────────┬─────────────┘
                       │ HTTPS
                       ▼
          ┌──────────────────────────┐
          │       PHP BACKEND        │
          │ Authentication           │
          │ Telemetry API             │
          │ Status / History / Alerts │
          └────────────┬─────────────┘
                       │ MySQLi
                       ▼
          ┌──────────────────────────┐
          │     MySQL / MariaDB      │
          │ Users, Pumps, Telemetry  │
          │ History, Alerts          │
          └──────────────────────────┘

                       ▲
                       │ Wi-Fi / HTTP(S)
                       │ JSON telemetry
          ┌────────────┴─────────────┐
          │           ESP32          │
          │ IR + Ultrasonic Sensors  │
          │ Relay + DC Pump          │
          └──────────────────────────┘
```

**Important:** Automatic pump decisions are made by the ESP32 firmware.
The backend stores and serves monitoring information.

## 7. Technology Stack

  Layer                     Technology
  ------------------------- ------------------------------
  Frontend                  HTML5, CSS3, JavaScript
  Backend                   PHP
  Database                  MySQL / MariaDB
  Database access           MySQLi
  Microcontroller           ESP32
  Firmware development      Arduino IDE / PlatformIO
  Device communication      Wi-Fi, HTTP and JSON
  Web server                Apache (hosting environment)
  Hosting                   LXI.be
  Version control           Git and GitHub
  Database administration   phpMyAdmin

## 8. Hardware Configuration

The current firmware configuration uses the following ESP32 connections:

  Component           ESP32 GPIO Purpose
  ----------------- ------------ -------------------------------
  Relay module           GPIO 26 Pump switching
  Ultrasonic TRIG         GPIO 5 Distance measurement trigger
  Ultrasonic ECHO        GPIO 18 Distance measurement response
  IR sensor              GPIO 27 Container detection

### Current firmware parameters

  Parameter                              Value
  -------------------------- -----------------
  Pump start distance                    15 cm
  Pump stop distance                      5 cm
  Maximum pump runtime              60 seconds
  Telemetry interval                 2 seconds
  Wi-Fi connection timeout          15 seconds
  Device ID                    `DISPENSER_001`
  Operating mode                        `AUTO`

> Verify the relay module's actual active level and the electrical
> ratings of the pump and power supply before connecting or operating
> the load. Test relay switching with the pump disconnected first.

The flow sensor is not yet integrated into the main firmware and
calibrated as part of the current MVP. Therefore, the dashboard must not
present estimated values as measured water consumption.

## 9. Repository Structure

The repository is organized into the following main areas:

``` text
aqua-pump/
│
├── README.md
├── .gitignore
│
├── backend/
│   └── PHP backend files and API endpoints
│
├── database/
│   └── SQL database export and schema files
│
├── frontend/
│   └── Web interface, pages, styles and scripts
│
├── hardware/
│   └── ESP32 firmware and hardware-related files
│
└── docs/
    └── Project, design and testing documentation
```

The exact files and subdirectories may vary as the project evolves.
Refer to the repository itself for the current file inventory.

## 10. Database Design

The current database contains the following main tables:

  Table                Purpose
  -------------------- --------------------------------------------
  `users`              Application user accounts
  `pumps`              Pump/device status and related information
  `device_telemetry`   Incoming device telemetry records
  `history`            Pump state-change and runtime history
  `alerts`             System alerts and fault information

The SQL export is maintained in the repository's `database/` directory.

### Important data note

Pump runtime is calculated from recorded start and stop events. Water
volume remains unmeasured until the flow sensor is integrated,
calibrated, and its readings are reliably stored.

## 11. Installation and Setup

### Requirements

For local development, install:

-   PHP with the MySQLi extension.
-   MySQL or MariaDB.
-   Apache or another PHP-compatible web server.
-   A modern web browser.
-   Arduino IDE or PlatformIO for ESP32 firmware development.
-   Git.

### Clone the repository

``` bash
git clone https://github.com/Leim-User/aqua-pump-sen.git
cd aqua-pump-sen
```

### Configure the database

1.  Create a MySQL/MariaDB database.
2.  Create a database user and grant it access to that database.
3.  Import the SQL file from the `database/` directory using phpMyAdmin
    or the MySQL command line.
4.  Update the application's database configuration with the correct
    host, database name, username, and password.
5.  Do not commit real database credentials to GitHub.

Example command-line import:

``` bash
mysql -u YOUR_DB_USER -p YOUR_DB_NAME < database/aqua_pump.sql
```

Replace the example filename with the actual SQL export filename in the
repository.

### Configure the PHP application

Update the database configuration file used by the backend with the
hosting or local environment's database details. Keep secrets outside
public source control.

Do not use `root` as the database user on the hosted server. Use the
database username created in the hosting control panel.

## 12. Deployment

The web application is hosted on **LXI.be**.

-   **Website:** <http://aquapump.lxi.be/>
-   **HTTPS website:** <https://aquapump.lxi.be/>
-   **Dashboard:** <https://aquapump.lxi.be/frontend/index.php>

The hosted application requires its PHP files, database configuration,
and imported database schema to be correctly deployed.

After deployment, verify:

1.  The website opens.
2.  Login works.
3.  Dashboard data loads from the database.
4.  History and alerts pages load.
5.  The ESP32 can reach the telemetry endpoint.
6.  The device status updates when fresh telemetry is received.

## 13. Telemetry and API

The ESP32 sends JSON telemetry to the PHP backend over Wi-Fi.

The current telemetry payload contains these fields:

  Field               Description
  ------------------- ----------------------------------
  `device_id`         Device identifier
  `mode`              Operating mode, currently `AUTO`
  `bucket_detected`   Container detection state
  `distance_cm`       Ultrasonic distance reading
  `pump_status`       Current pump state
  `fault`             Fault information, when present

Example payload:

``` json
{
  "device_id": "DISPENSER_001",
  "mode": "AUTO",
  "bucket_detected": true,
  "distance_cm": 8.5,
  "pump_status": "OFF",
  "fault": null
}
```

This is an illustrative payload. Use the firmware's actual data types
and endpoint configuration when testing.

The backend is responsible for validating incoming data and recording
telemetry and relevant pump state transitions. The dashboard reads the
stored information for display.

## 14. Testing

Testing should cover both software and hardware.

### Software tests

-   PHP syntax validation.
-   Database connection and query tests.
-   Login and authentication tests.
-   Telemetry endpoint validation.
-   Invalid or incomplete JSON payloads.
-   Pump status synchronization.
-   History creation on state transitions.
-   Alert creation and recovery behavior.
-   Dashboard, history, and alerts page loading.

### Hardware tests

-   IR sensor detection.
-   Ultrasonic distance readings.
-   Relay active-level verification.
-   Pump start and stop conditions.
-   Container removal behavior.
-   Maximum runtime timeout.
-   Wi-Fi connection and reconnection.
-   Telemetry delivery to the hosted backend.

### End-to-end tests

Verify the complete flow:

``` text
Sensor input
    ↓
ESP32 automatic decision
    ↓
Pump / relay action
    ↓
ESP32 telemetry
    ↓
PHP API validation
    ↓
Database persistence
    ↓
Dashboard / History / Alerts
```

Record expected results, actual results, test date, and any defects. A
successful PHP syntax check alone does not prove that the complete
system works.

## 15. Scrum and Team Organization

The project follows an iterative Agile approach using Scrum practices:

-   Product Backlog refinement.
-   Sprint Planning.
-   Development.
-   Testing and review.
-   Sprint Review.
-   Sprint Retrospective.

### Team

  -----------------------------------------------------------------------
  Member                              Role / focus
  ----------------------------------- -----------------------------------
  Lüscher Lema                        Scrum Master / Team Lead; software
                                      coordination, backend, database and
                                      integration

  Lum Mulah                           Development Team Member; hardware
                                      and technical implementation
  -----------------------------------------------------------------------

Responsibilities may be shared as needed. Both members contribute to
integration, testing, documentation, and delivery.

## 16. Known Limitations and Next Steps

The following items require further implementation or verification:

-   Integrate and calibrate the flow sensor before displaying measured
    water volume.
-   Add and verify per-device authentication for telemetry requests.
-   Ensure production communication uses HTTPS.
-   Verify relay polarity and electrical safety physically.
-   Test the complete ESP32-to-hosted-database workflow.
-   Improve error handling for offline devices and stale telemetry.
-   Keep database credentials, Wi-Fi passwords, and API keys out of
    public repositories.
-   Complete system acceptance testing and document the results.

## 17. Contributing

Team members should:

1.  Select an agreed task from the project backlog.
2.  Create or switch to an appropriate Git branch.
3.  Implement the change.
4.  Test the implementation.
5.  Commit with a descriptive message.
6.  Push the branch to GitHub.
7.  Open a Pull Request where appropriate.
8.  Review and address feedback before merging.

Suggested commit prefixes:

``` text
feat: add a new feature
fix: correct an issue
docs: update documentation
test: add or update tests
refactor: improve code structure
```

Never commit passwords, API keys, private configuration files, or other
secrets.

## 18. Academic Information

**Project:** Aqua Pump --- Smart Water Pumping & Dispensing Management
System\
**Course:** Software Engineering\
**Methodology:** Agile Software Development --- Scrum\
**Institution:** ICT University\
**Academic year:** 2026

------------------------------------------------------------------------

## Project Status

**Status:** In Development --- online deployment is available.

The web application is hosted online. Hardware connectivity, flow
measurement, security hardening, and complete end-to-end validation
remain work items.

## Final Goal

Aqua Pump aims to demonstrate the engineering of an integrated system:
from identifying a real-world water management problem, through
requirements analysis and system design, to hardware/software
implementation, testing, deployment, and continuous improvement.
