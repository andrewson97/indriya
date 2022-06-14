import subprocess, shlex

import os


def set_dep():
    cmd = ["sudo apt-get update -y","sudo apt install curl",
    "sudo curl -sL https://repos.influxdata.com/influxdb.key | sudo apt-key add -",
    "sudo echo 'deb https://repos.influxdata.com/ubuntu bionic stable' | sudo tee /etc/apt/sources.list.d/influxdb.list",
    "sudo apt install influxdb","sudo apt update",
    "sudo add-apt-repository ppa:mosquitto-dev/mosquitto-ppa",
    "sudo apt install mosquitto mosquitto-clients",
    "sudo systemctl enable --now influxdb",
    "pip install influxdb","pip install paho-mqtt python-etcd",
    "pip install pyserial","pip install termcolor",
    "pip install polling","pip install flask","pip install filelock",
    ]
    for cmds in cmd :
        os.system(cmds)
        # args = shlex.split(cmds)
        # subprocess.Popen(args)

set_dep()
