# indriya
Indriya is a Public Wireless Sensor Network Testbed. This repository includes all the steps and resources required to setup the testbed.

## Devices
The testbed includes a public server which conencts over the network to several clusters (mac minis). Each cluster device connects to the motes via USB cables and hubs.

## Setup Components
### Server
The main public server has two main components namely; frontend ([indriya/server/frontend](https://github.com/ebramkw/indriya/tree/master/server/frontend)) and backend ([indriya/server/backend](https://github.com/ebramkw/indriya/tree/master/server/backend)).

The frontend contains:
- apache server and website files (html, css, javascript, and php).
- mysql database server and database structure.

The backend contains:
- scripts for scheduling, configuration, ...
- publish/subscribe server (MQTT).
- database server (influxDB).

### cluster
Each cluster does the following:
- burn to motes (TelosB and CC2650)
- copy serial logs from motes to the network on specified ports
