basic='testbed-rpi-';
user='pi@';

echo "reset dcube nodes"
for i in `seq 1 8`;
do
	dcube=$basic$i;
	ssh=$user$dcube;
	ssh -o StrictHostKeyChecking=no $ssh "/home/pi/scripts/dcube_target_reset.sh 0" &
done
