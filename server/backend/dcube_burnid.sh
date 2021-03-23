let "ID = 100";
let "i = 1";
basic='testbed-rpi-';
user='pi@';
location=':/home/pi/';
burnid='burn-nodeid.sky.';
dev_basic='/dev/serial/by-id/';

echo "release dcube nodes"
for i in `seq 1 8`;
do
	dcube=$basic$i;
	ssh=$user$dcube;
	ssh -o StrictHostKeyChecking=no $ssh "/home/pi/scripts/dcube_target_reset.sh 1" &
done

let "i = 1";

echo "copy and burn id"
while read dev;
do
	dev=$dev_basic$dev;
	dcube=$basic$i;
	ssh=$user$dcube;
	ssh_bin_location=$ssh$location;
	burnid_num=$burnid$ID;
	echo "id: "$ID;
	echo "i: "$i;
	echo "dev: "$dev;
	echo "scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/$burnid_num $ssh_bin_location"
	#scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/$burnid_num $ssh_bin_location
	echo "scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py $ssh_bin_location"
	#scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py $ssh_bin_location
	echo "ssh -o StrictHostKeyChecking=no $ssh '/home/pi/burn_telosb_test.py telosb $dev /home/pi/burn-nodeid.sky.$ID' &";
	#ssh -o StrictHostKeyChecking=no $ssh "/home/pi/burn_telosb_test.py telosb $dev /home/pi/burn-nodeid.sky.$ID" &
	let "ID++";
	let "i++";
	echo "";
done < dcube.serial

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.100 pi@testbed-rpi-1:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-1:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-1 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5NVJ6-if00-port0 /home/pi/burn-nodeid.sky.100" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.101 pi@testbed-rpi-2:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-2:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-2 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5NORI-if00-port0 /home/pi/burn-nodeid.sky.101" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.102 pi@testbed-rpi-3:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-3:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-3 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5RPIV-if00-port0 /home/pi/burn-nodeid.sky.102" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.103 pi@testbed-rpi-4:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-4:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-4 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5RPRT-if00-port0 /home/pi/burn-nodeid.sky.103" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.104 pi@testbed-rpi-5:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-5:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-5 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5RPZL-if00-port0 /home/pi/burn-nodeid.sky.104" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.105 pi@testbed-rpi-6:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-6:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-6 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5NVIF-if00-port0 /home/pi/burn-nodeid.sky.105" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.106 pi@testbed-rpi-7:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-7:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-7 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR5RQ04-if00-port0 /home/pi/burn-nodeid.sky.106" &

scp -o StrictHostKeyChecking=no ~/contiki-2.7/examples/hello-world/burn-nodeid.sky.107 pi@testbed-rpi-8:/home/pi/
scp -o StrictHostKeyChecking=no /home/cirlab/indriya_upgrade/burn_telosb_test.py pi@testbed-rpi-8:/home/pi/
ssh -o StrictHostKeyChecking=no pi@testbed-rpi-8 "/home/pi/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBTNSO2S-if00-port0 /home/pi/burn-nodeid.sky.107"
