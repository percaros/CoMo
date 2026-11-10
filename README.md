# CoMo: IoT Classroom Energy Management System

## Overview

CoMo (Consumo Moderado) is an end-to-end IoT system that monitors and reduces energy consumption in university classrooms. A classroom is powered only while a valid reservation is active: the teacher inserts an RFID card, the system checks the reservation in the database, and relay boards switch on the air conditioning, lights, PC and projector. When the reservation ends, everything is shut down automatically.

The system was installed in four real classrooms and achieved an **8.08% reduction in energy consumption**.

## Team

CoMo was developed as a team thesis project in Electronics Engineering at Pontificia Universidad Javeriana Cali (Colombia).

- Santiago Colorado Ochoa
- Diego Ardila

Original repository: https://github.com/Dalejandro02/CoMo

## System Architecture

<img width="1889" height="860" alt="Architecture" src="https://github.com/user-attachments/assets/0e4bff83-e582-4938-805d-9f1548365880" />

The solution is made of independent modules that communicate over WiFi using HTTP.

### RFID module (`RFID_module/`)

- ESP8266 board with an MFRC522 RFID reader (SPI), an SSD1306 OLED display (I2C), a buzzer and a card-presence switch.
- Reads the card UID and checks it against the classroom reservations served by the backend.
- Grants access from 5 minutes before the reservation starts until it ends, and extends the session automatically when the same user has a consecutive reservation.
- If the card is removed early, it warns the user with the display and the buzzer and waits for the card to be reinserted before shutting the classroom down.
- Logs every event (entry, removal, unauthorized card) to the database.

### Relay control module (`Relay_control/`)

- ESP8266 board with a static IP address and a small HTTP server that drives four relay outputs.
- Receives the activation command from the RFID module or from the manual control panel.
- **Fail-safe behaviour:** if the WiFi connection is lost, the relays stay on, so a network fault never leaves a classroom without power. When the connection returns, the board restores its last commanded state.

### Current sensor module (`Current_sensor/`)

- ESP8266 board with an ACS712 Hall-effect current sensor read through the analog input.
- Measures the sensor offset, takes 1,000 samples and computes the RMS current.
- Sends a reading to the backend every 20 seconds.

### PC and projector shutdown (`PC_shutdown/`)

- Python script running on the classroom PC, connected to the ESP8266 over a serial port.
- When the session ends, it switches the projector off through its serial control port and then shuts the PC down.
- The RFID module waits before cutting power, so the PC can shut down cleanly.

### Backend (`Backend/`)

- PHP and MySQL.
- `db_access.php`: serves the reservations as paginated JSON, together with the server date and time.
- `insertar_uid.php`: stores the RFID event log.
- `data_<device>_<classroom>.php`: store the current readings for each device (air conditioning, lights, PC, projector) in each classroom.
- `panel_control.php`: web panel to switch each device on or off manually.

### Data and analytics

- Energy consumption data is stored in MySQL and visualised in Power BI dashboards.

## How It Works

1. The teacher inserts the RFID card in the reader.
2. The RFID module requests the reservations from the backend and validates the card, the classroom, the date and the time.
3. If the reservation is valid, the RFID module sends the activation command to the relay boards.
4. The current sensor modules send RMS current readings to the backend during the session.
5. When the reservation ends, or the card is not reinserted, the projector and the PC are shut down and the relays are switched off.

## Hardware

- ESP8266 development boards (Wemos D1 Mini)
- MFRC522 RFID reader
- SSD1306 OLED display, 128 x 64
- ACS712 current sensor module
- Relay modules
- 120 VAC to 5 VDC converter modules
- Buzzer

## Hardware Design Files

The design files are in `Hardware/`. They were made with EasyEDA.

- `Hardware/Schematic/CoMo_schematic_EasyEDA.json`: schematic source with three sheets (RFID, AC_Current and RELAY). Every symbol is stored inside the file, so it opens without any extra component library. Open it in EasyEDA (File, Open, EasyEDA Source) or import it in KiCad 8 or later (File, Import, Non-KiCad Schematic).
- `Hardware/Gerber_RFID_board/`: Gerber and drill files of the RFID module PCB, a two-layer board of 110 x 40 mm. They can be sent to a PCB manufacturer as they are, or inspected with any Gerber viewer.

## Results

- **8.08% reduction in energy consumption**
- Installed and tested in real university classrooms
- Automatic control of the classroom based on real usage

## Repository Structure

```
├── Backend/          Backend and database logic (PHP, MySQL)
├── Current_sensor/   Firmware for current measurement
├── Hardware/         Schematic source and PCB Gerber files
├── PC_shutdown/      PC and projector shutdown script
├── RFID_module/      Firmware for RFID authentication
└── Relay_control/    Firmware for the relay boards
```

## Configuration

Credentials are not included in this repository. Before running the system, replace these placeholders with your own values:

| Placeholder | Where | Meaning |
| --- | --- | --- |
| `YOUR_WIFI_SSID` | the three `.ino` files | Name of the WiFi network |
| `YOUR_WIFI_PASSWORD` | the three `.ino` files | Password of the WiFi network |
| `YOUR_DB_USER` | files in `Backend/` | MySQL user |
| `YOUR_DB_PASSWORD` | files in `Backend/` | MySQL password |

The IP addresses of the backend server and of the relay boards are set in the firmware and in `Backend/panel_control.php`, and must match your own network.

## Technologies Used

- Embedded systems: ESP8266 (Arduino framework, C++)
- Communication: WiFi, HTTP, SPI, I2C, serial
- Backend: PHP, MySQL
- Scripting: Python
- Data analysis: Power BI
