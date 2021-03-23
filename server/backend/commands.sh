#!/bin/bash
###################
# LOOP BY NODEID
###################


# for i in `seq 1 99`;
#        do
# 		echo "Will execute: make TARGET=sky clean"
# 		make TARGET=sky clean
# 		echo "Will execute: cd ~/contiki-2.7/examples/hello-world/ && make TARGET=sky nodeid=$i burn-nodeid.sky"
# 		cd ~/contiki-2.7/examples/hello-world/ && make TARGET=sky nodeid=$i burn-nodeid.sky
# 		echo "Will execute: mv burn-nodeid.sky burn-nodeid.sky.$i"
# 		mv burn-nodeid.sky burn-nodeid.sky.$i

# 	done
    
x=1
while [ $x -le 10 ]
do
    x=$(( $x + 1 ))
    
    serial='.serial'
    stag_serial='.stag.serial'
    stag_serial_lr='.stag.serial.lr'
    username='username@'
    ser2net='ser2net_'
    conf='.conf'
    location=':/home/username/indriya_upgrade/'
    bin_location='telosb_bin/'
    bak='.bak'

    port=40000

    echo "*** LOOP BY NODE ID ***"

    #for i in `seq 100 199`;
    #        do
    #		echo "Will execute: cd ~/contiki-2.7/examples/hello-world/ && make TARGET=sky nodeid=$i burn-nodeid.sky"
    #		cd ~/contiki-2.7/examples/hello-world/ && make TARGET=sky nodeid=$i burn-nodeid.sky
    #		echo "Will execute: mv burn-nodeid.sky burn-nodeid.sky.$i"
    #		mv burn-nodeid.sky burn-nodeid.sky.$i
    #		echo "Will execute: make TARGET=sky clean"
    #		make TARGET=sky clean
    # 	done
    # for i in `seq 1 99`;
    #        do
    # 		echo "Will execute: cd ~/contiki-2.7/examples/hello-world/ && make TARGET=sky nodeid=$i burn-nodeid.sky"
    # 		cd ~/contiki-2.7/examples/hello-world/ && make TARGET=sky nodeid=$i burn-nodeid.sky
    # 		echo "Will execute: mv burn-nodeid.sky burn-nodeid.sky.$i"
    # 		mv burn-nodeid.sky burn-nodeid.sky.$i
    # 		echo "Will execute: make TARGET=sky clean"
    # 		make TARGET=sky clean
    # 	done

    #echo ""
    #echo "*** SCP BINARIES ***"

    #echo "Will execute: scp ~/contiki-2.7/examples/hello-world/*sky* username@mac-mini-com1-l1-mac:/home/username/indriya_upgrade/telosb_bin/"
    #scp ~/contiki-2.7/examples/hello-world/*sky* username@mac-mini-com1-b-mac:/home/username/indriya_upgrade/telosb_bin/
    #echo "Will execute: scp ~/contiki-2.7/examples/hello-world/*sky* username@mac-mini-com1-l1-el:/home/username/indriya_upgrade/telosb_bin/"
    #scp ~/contiki-2.7/examples/hello-world/*sky* username@mac-mini-com1-b-el:/home/username/indriya_upgrade/telosb_bin/



    ##################
    # SECOND LOOP READ TELOSB DEVICE FROM FILE
    ##################
    # INCREMENT #ID#, start from let's say 100 
    # NEXT RECORD FROM mac-mini-com1-l1-el.serial

    ###################################
    ### serial file can be generated with ssh username@mac-mini-com1-l1-mac 'ls /dev/serial/by-id'
    ###
    ###################################
    #echo ""
    #echo "*** SECOND LOOP READ TELOSB DEVICE FROM FILE ***"

    # +------------+--------------+-------------+
    # | moteTypeID | moteTypeName | runningTime |
    # +------------+--------------+-------------+
    # |          7 | cc2650       |       0 |
    # |          8 | telosb       |        0 |
    # |          9 | cc1350       |           0 |
    # +------------+--------------+-------------+

    # +-----------+-------------+------------+
    # | clusterID | clusterName | floorLevel |
    # +-----------+-------------+------------+
    # |         1 | NAME         | COM1#2     |
    # |         2 | NAME     | COM2#B     |
    # |         3 | NAME         | COM1#2     |
    # |         4 | NAME         | COM1#1     |
    # |         5 | NAME          | COM1#1     |
    # |         6 | NAME          | COM1#B     |
    # |         7 | NAME         | COM1#B     |
    # +-----------+-------------+------------+


    # bak json file
    echo "bak json file"
    cp ocean.json ocean.json.bak

    # clear json file
    echo "clear json file"
    echo "" > ocean.json

    # clear motes from DB
    echo "DELETE from motes;" | mysql -uroot -p ...;

    let "ID = 1";
    while read mac_mini;
            read mac_mini_db_id;
            do echo "mac-mini: "$mac_mini;
            ssh=$username$mac_mini
            conf_file_name=$ser2net$mac_mini$conf
            bak_conf_file_name=$conf_file_name$bak
            ssh_location=$ssh$location
            ssh_bin_location=$ssh_location$bin_location

            # bak conf file
            echo "bak conf file"
            echo "cp $conf_file_name $bak_conf_file_name";
            cp $conf_file_name $bak_conf_file_name

            # clear conf file
            echo "clear conf file"
            echo "" > $conf_file_name
            
            echo "scp ~/contiki-2.7/examples/hello-world/*sky* $ssh_bin_location"
            scp ~/contiki-2.7/examples/hello-world/*sky* $ssh_bin_location
            
            file_name=$mac_mini$serial;
            echo "serial: "$file_name;
            while read dev;
                    do echo "id: "$ID;
                    echo "dev: "$dev;
                    echo "ssh $ssh '/home/username/indriya_upgrade/burn_telosb_test.py telosb $dev /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.$ID' &";
                    ssh $ssh "/home/username/indriya_upgrade/burn_telosb_test.py telosb $dev /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.$ID" &
                    echo "\"$ID\":{\"gateway\":\"$mac_mini\",\"port\":$port,\"serial_id\":\"$dev\",\"flash_file\":\"\"}, ==> ocean.json"
                    echo "\"$ID\":{\"gateway\":\"$mac_mini\",\"port\":$port,\"serial_id\":\"$dev\",\"flash_file\":\"\"}," >> ocean.json
                    echo "$port:raw:0:$dev:115200,8DATABITS,NONE,1STOPBIT ==> $conf_file_name"
                    echo "$port:raw:0:$dev:115200,8DATABITS,NONE,1STOPBIT" >> $conf_file_name
                    # echo "DELETE from motes WHERE virtual_id = $ID; ==> DB"
                    # echo "INSERT INTO motes (virtual_id, moteTypes_moteTypeID, clusters_clusterID) values ($ID, 8, $mac_mini_db_id); ==> DB"
                    echo "INSERT INTO motes (virtual_id, moteTypes_moteTypeID, clusters_clusterID) values ($ID, 8, $mac_mini_db_id);" | mysql -uroot -pPASSWORD DATABASE_NAME;
                    let "ID++";
                    let "port++";
            done < $file_name
            echo ""

            file_name=$mac_mini$stag_serial
        echo "stag.serial: "$file_name;
        while read stag_id;
            do echo "id:  "$stag_id;
                    read dev;
                    echo "dev: "$dev;
                    usb=${dev##*_}
                    usb=`echo $usb |sed 's/.\{5\}$//'`
                    echo $usb;

                    echo "\"$stag_id\":{\"gateway\":\"$mac_mini\",\"port\":$port,\"serial_id\":\"$dev\",\"flash_file\":\"/home/username/sensortag_configs/$usb.ccxml\"}, ==> ocean.json"
            echo "\"$stag_id\":{\"gateway\":\"$mac_mini\",\"port\":$port,\"serial_id\":\"$dev\",\"flash_file\":\"/home/username/sensortag_configs/$usb.ccxml\"}," >> ocean.json
            echo "$port:raw:0:$dev:115200,8DATABITS,NONE,1STOPBIT ==> $conf_file_name"
            echo "$port:raw:0:$dev:115200,8DATABITS,NONE,1STOPBIT" >> $conf_file_name
            # echo "DELETE from motes WHERE virtual_id = $stag_id; ==> DB";
            # echo "INSERT INTO motes (virtual_id, moteTypes_moteTypeID, clusters_clusterID) values ($stag_id, 7, $mac_mini_db_id); ==> DB"
            echo "INSERT INTO motes (virtual_id, moteTypes_moteTypeID, clusters_clusterID) values ($stag_id, 7, $mac_mini_db_id);" | mysql -uroot -pPASSWORD DATABASE_NAME;
            let "port++";
        done < $file_name
        echo ""

        file_name=$mac_mini$stag_serial_lr
        echo "stag.serial,lr: "$file_name;
        while read stag_id;
            do echo "id:  "$stag_id;
                    read dev;
                    echo "dev: "$dev;
                    usb=${dev##*_}
                    usb=`echo $usb |sed 's/.\{5\}$//'`
                    echo $usb;

                    echo "\"$stag_id\":{\"gateway\":\"$mac_mini\",\"port\":$port,\"serial_id\":\"$dev\",\"flash_file\":\"/home/username/sensortag_configs/$usb.ccxml\"}, ==> ocean.json"
            echo "\"$stag_id\":{\"gateway\":\"$mac_mini\",\"port\":$port,\"serial_id\":\"$dev\",\"flash_file\":\"/home/username/sensortag_configs/$usb.ccxml\"}," >> ocean.json
            echo "$port:raw:0:$dev:115200,8DATABITS,NONE,1STOPBIT ==> $conf_file_name"
            echo "$port:raw:0:$dev:115200,8DATABITS,NONE,1STOPBIT" >> $conf_file_name
            # echo "DELETE from motes WHERE virtual_id = $stag_id; ==> DB";
            # echo "INSERT INTO motes (virtual_id, moteTypes_moteTypeID, clusters_clusterID) values ($stag_id, 9, $mac_mini_db_id); ==> DB"
            echo "INSERT INTO motes (virtual_id, moteTypes_moteTypeID, clusters_clusterID) values ($stag_id, 9, $mac_mini_db_id);" | mysql -uroot -pPASSWORD DATABASE_NAME;
            let "port++";
        done < $file_name
        echo ""

        echo "scp $conf_file_name $ssh_location"
        scp $conf_file_name $ssh_location
        echo ""
            echo ""
    done < mac-mini

    echo "run $x times"
    sleep 30
done

    #ssh username@mac-mini-com1-b-el '/home/username/indriya_upgrade/burn_telosb_test.py telosb #RECORD_FOR_SERIAL_DEVICE_FROM_FILE# /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.#ID#' &
    # example 
    # ssh username@mac-mini-com1-b-el '/home/username/indriya_upgrade/burn_telosb_test.py telosb /dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBR1PSTE-if00-port0 /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.101' &
    ######

    #################
    # LOOP FOR NEXT FILE
    #################
    # INCREMENT #ID##, start from let's say 100 
    # NEXT RECORD FROM mac-mini-com1-b-mac.serial
    #ssh username@mac-mini-com1-b-mac '/home/username/indriya_upgrade/burn_telosb_test.py telosb #RECORD_FOR_SERIAL_DEVICE_FROM_FILE# /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.#ID#' &

    #echo ""
    #echo "*** LOOP FOR NEXT FILE ***"

    #while read line_from_file;
    #	do echo "Will execute: ssh username@mac-mini-com1-b-mac '/home/username/indriya_upgrade/burn_telosb_test.py telosb $line_from_file /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.$ID' &";
    #    ssh username@mac-mini-com1-b-mac "/home/username/indriya_upgrade/burn_telosb_test.py telosb $line_from_file /home/username/indriya_upgrade/telosb_bin/burn-nodeid.sky.$ID" &
    #    echo "\"$ID\":{\"gateway\":\"mac-mini-com1-b-mac\",\"port\":$port,\"serial_id\":\"$line_from_file\",\"flash_file\":\"\"},"  >> ocean.json
    #	echo "$port:raw:0:$line_from_file:115200,8DATABITS,NONE,1STOPBIT" >> ser2net_mac-mini-com1-b-mac.conf
    #	let "ID++";
    #	let "port++";
    #done < mac-mini-com1-b-mac.serial
    #echo "Will execute: scp ser2net_mac-mini-com1-b-mac.conf username@mac-mini-com1-b-mac:/home/username/indriya_upgrade/"
    #scp ser2net_mac-mini-com1-b-mac.conf username@mac-mini-com1-b-mac:/home/username/indriya_upgrade/


    # 4030:raw:0:/dev/serial/by-id/usb-Texas_Instruments_XDS110__02.02.05.01__with_CMSIS-DAP_L1016-if00:115200,8DATABITS,NONE,1STOPBIT
    # "222":{"gateway":"mac-mini-com1-l2-vcr","port":4122,"serial_id":"/dev/serial/by-id/usb-XBOW_Crossbow_Telos_Rev.B_XBTNMGW8-if00-port0","flash_file":""} 

#dcube
#./dcube_burnid.sh
#./dcube_after_burnid.sh
