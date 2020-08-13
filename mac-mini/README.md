# Mac Minis - Clusters
# setup steps for mac minis

****************************************
network configuration:
	edit /etc/network/interfaces in each device from network_conf/mac_mini_name_file

****************************************
contiki:
	copy contiki-3.0 to /home/cirlab/
****************************************
python-msp430-tools:
	copy python-msp430-tools to /home/cirlab/
	cd python-msp430-tools/
	sudo python setup.py install
****************************************
ser2nets:
	copy ser2nets to /home/cirlab/
	cd ser2nets/
	chmod a+x configure
	./configure 
	sudo apt-get install build-essential
	./configure 
	make
	sudo make install
	sudo ln /usr/local/bin/ser2nets ~/ser2nets/ser2nets
	sudo ln ~/ser2nets/ser2nets /usr/local/bin/ser2nets
	ser2nets -c ser2net_mac_mini_name.conf #ser2net conf file will be copied from the indriya server when run commands.sh 
test ser2nets:
	nc localhost 4110
****************************************
rc.local:
	edit /etc/rc.local to include rc.local file in this directory
****************************************
uniflash:
	copy Uniflash_3.4.1.00012_linux
	cd Uniflash_3.4.1.00012_linux/
	./uniflash_setup_3.4.1.00012.bin # install to /home/cirlab/ti/uniflash
	copy reset_sensortags.sh to /home/cirlab/ti/uniflash/ccs_base/common/uscif/xds110
	add "cirlab ALL = (root) NOPASSWD: /home/cirlab/ti/uniflash/uniflash.sh" to /etc/sudoers
****************************************
binary files:
	mkdir /home/cirlab/elf
****************************************
get sensortags serials for configuration files:
	cd /home/cirlab/ti/uniflash/ccs_base/common/uscif/xds110
	sudo ./xdsdfu -e
****************************************
sensortag configuration files:
	mkdir /home/cirlab/sensortag_configs
	copy and edit flash.ccxml to /home/cirlab/sensortag_configs with serials of connected sensortags
****************************************
indriya_upgrade:
	copy indriya_upgrade to /home/cirlab/
	cd /usr/local/bin/
	sudo ln -s /home/cirlab/indriya_upgrade/mac_mini/uniflash_custom_rewrite.sh 
	./uniflash_custom_rewrite.sh /home/cirlab/sensortag_configs/L433.ccxml /home/cirlab/elf/25003.elf # test uniflash
	edit indriya_upgrade/nodes_virt_id_phy_id.json to include mapping between port, sensortag_id, serial_id, flash_file
****************************************
flash_sensortag_linux_64:
	copy flash_sensortag_linux_64 to /home/cirlab
****************************************

