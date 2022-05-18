serial='.serial'
stag_serial='.stag.serial'
stag_serial_lr='.stag.serial.lr'
cirlab='cirlab@'
ser2net='ser2net_'
conf='.conf'
location=':/home/cirlab/indriya_upgrade/'
bin_location='telosb_bin/'
bak='.bak'
while read mac_mini;
    read mac_mini_db_id;
    do echo "mac-mini: "$mac_mini;
    ssh=$cirlab$mac_mini
    ssh_location=$ssh$location
    echo "scp ~/indriya_upgrade/nodes_virt_id_phy_id.json $ssh_location"
    scp ~/indriya_upgrade/nodes_virt_id_phy_id.json $ssh_location
done < mac-mini