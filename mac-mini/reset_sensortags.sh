#!/bin/bash
cmd="sudo ./xdsdfu -e"
eval $cmd
for (( counter = 0; counter <= 20; counter++ ))
do
	echo "sudo ./xdsdfu  -m -i $counter"
	cmd="sudo ./xdsdfu  -m -i $counter"
	eval $cmd
	sleep 2
#done
#sleep 10
#for (( counter = 0; counter <= 20; counter++ ))
#do
	echo "sudo ./xdsdfu -f firmware.bin -r -i $counter"
	cmd="sudo ./xdsdfu  -f firmware.bin -r  -i $counter"
	$cmd
	sleep 5
done
