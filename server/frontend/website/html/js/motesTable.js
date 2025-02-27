function edit_mote_row(no) {
    document.getElementById("edit_mote_button"+no).style.display="none";
    document.getElementById("save_mote_button"+no).style.display="block";
    
    var physical_id=document.getElementById("physical_id_row"+no);
    var physical_id_data=physical_id.innerHTML;
    physical_id.innerHTML="<input type='text' class='text1' style='width: 80px' id='physical_id_text"+no+"' value='"+physical_id_data+"'>";
    
    var virtual_id=document.getElementById("virtual_id_row"+no);
    var virtual_id_data=virtual_id.innerHTML;
    virtual_id.innerHTML="<input type='text' class='text1' style='width: 80px' id='virtual_id_text"+no+"' value='"+virtual_id_data+"'>";
    
    var gateway_ip=document.getElementById("gateway_ip_row"+no);
    var gateway_ip_data=gateway_ip.innerHTML;
    gateway_ip.innerHTML="<input type='text' class='text1' style='width: 80px' id='gateway_ip_text"+no+"' value='"+gateway_ip_data+"'>";
    
    var gateway_ttyid=document.getElementById("gateway_ttyid_row"+no);
    var gateway_ttyid_data=gateway_ttyid.innerHTML;
    gateway_ttyid.innerHTML="<input type='text' class='text1' style='width: 80px' id='gateway_ttyid_text"+no+"' value='"+gateway_ttyid_data+"'>";
    
    var gateway_port=document.getElementById("gateway_port_row"+no);
    var gateway_port_data=gateway_port.innerHTML;
    gateway_port.innerHTML="<input type='text' class='text1' style='width: 80px' id='gateway_port_text"+no+"' value='"+gateway_port_data+"'>";
    
    var coordinates=document.getElementById("coordinates_row"+no);
    var coordinates_data=coordinates.innerHTML;
    coordinates.innerHTML="<input type='text' class='text1' style='width: 80px' id='coordinates_text"+no+"' value='"+coordinates_data+"'>";
    
    var moteTypeListFull = document.getElementById("moteTypesList_load");
    
    var moteTypesList_row=document.getElementById("moteTypeName_row"+no);
    var moteTypesList_row_data=moteTypesList_row.innerHTML;
    
    var str = "<select class='text1' id='moteTypesList_row_text"+no+"'>";
    for(var i = 0; i < moteTypeListFull.length; i++) {
        if(moteTypesList_row_data == moteTypeListFull.options[i].text)
            str += "<option value = '" + moteTypeListFull.options[i].value + "' selected='selected'>" + moteTypeListFull.options[i].text + "</option>";
        else
            str += "<option value = '" + moteTypeListFull.options[i].value + "'>" + moteTypeListFull.options[i].text + "</option>";
    }
    str += "</select>";
    moteTypesList_row.innerHTML = str;
    
    var clustersListFull = document.getElementById("clustersList_load");
    
    var clustersList_row=document.getElementById("clusterName_row"+no);
    var clustersList_row_data = clustersList_row.innerHTML;
    var str = "<select class='text1' id='clustersList_row_text"+no+"'>";
    for(var i = 0; i < clustersListFull.length; i++) {
        if(clustersList_row_data == clustersListFull.options[i].text)
            str += "<option value = '" + clustersListFull.options[i].value + "' selected='selected'>" + clustersListFull.options[i].text + "</option>";
        else
            str += "<option value = '" + clustersListFull.options[i].value + "'>" + clustersListFull.options[i].text + "</option>";
    }
    str += "</select>";
    clustersList_row.innerHTML = str;
}

function save_mote_row(no) {
    var physical_id_val=document.getElementById("physical_id_text"+no).value;
    var virtual_id_val=document.getElementById("virtual_id_text"+no).value;
    var gateway_ip_val=document.getElementById("gateway_ip_text"+no).value;
    var gateway_ttyid_val=document.getElementById("gateway_ttyid_text"+no).value;
    var gateway_port_val=document.getElementById("gateway_port_text"+no).value;
    var coordinates_val=document.getElementById("coordinates_text"+no).value;
    
    if(physical_id_val == "" || virtual_id_val == "" || gateway_ip_val == "" || gateway_ttyid_val == "" || gateway_port_val == "" || coordinates_val == "" || !physical_id_val.replace(/\s/g, '').length || !virtual_id_val.replace(/\s/g, '').length || !gateway_ip_val.replace(/\s/g, '').length || !gateway_ttyid_val.replace(/\s/g, '').length || !gateway_port_val.replace(/\s/g, '').length || !coordinates_val.replace(/\s/g, '').length){
        alert("Kindly, type in the mote information in the text field.");
    } else {
        var moteTypeList=document.getElementById("moteTypesList_row_text"+no);
        var moteType_val = moteTypeList.options[moteTypeList.selectedIndex].text;
        var moteTypeID_val = moteTypeList.options[moteTypeList.selectedIndex].value;
        
        var clustersList=document.getElementById("clustersList_row_text"+no);
        var cluster_val = clustersList.options[clustersList.selectedIndex].text;
        var cluster_valID_val = clustersList.options[clustersList.selectedIndex].value;
        
        var moteInfo = new FormData();
        moteInfo.append('userID', userID);
        moteInfo.append('moteID', no);
        moteInfo.append('physical_id', physical_id_val);
        moteInfo.append('virtual_id', virtual_id_val);
        moteInfo.append('gateway_ip', gateway_ip_val);
        moteInfo.append('gateway_ttyid', gateway_ttyid_val);
        moteInfo.append('gateway_port', gateway_port_val);
        moteInfo.append('coordinates', coordinates_val);
        moteInfo.append('moteTypeID', moteTypeID_val);
        moteInfo.append('clusterID', cluster_valID_val);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/updateMote.php', true);
        xhr.send(moteInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var flag = this.responseText;
                if(flag) {
                    document.getElementById("physical_id_row"+no).innerHTML=physical_id_val;
                    document.getElementById("virtual_id_row"+no).innerHTML=virtual_id_val;
                    document.getElementById("gateway_ip_row"+no).innerHTML=gateway_ip_val;
                    document.getElementById("gateway_ttyid_row"+no).innerHTML=gateway_ttyid_val;
                    document.getElementById("gateway_port_row"+no).innerHTML=gateway_port_val;
                    document.getElementById("coordinates_row"+no).innerHTML=coordinates_val;
                    document.getElementById("moteTypeName_row"+no).innerHTML=moteType_val;
                    document.getElementById("clusterName_row"+no).innerHTML=cluster_val;

                    document.getElementById("edit_mote_button"+no).style.display="block";
                    document.getElementById("save_mote_button"+no).style.display="none";
                    
                    addMoteTypes_load();
                } else {
                    alert("Error occurred, please try again later!");
                }
            }
        };
    }
}

function delete_mote_row(no) {
    //delete mote type from DB
    var result = confirm("Deleting a mote will affect other records. Are you sure?");
    if (result) {
        var moteInfo = new FormData();
        moteInfo.append('userID', userID);
        moteInfo.append('moteID', no);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/deleteMote.php', true);
        xhr.send(moteInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var flag = this.responseText;
                if(flag == 1){
                    document.getElementById("mote_row"+no+"").outerHTML="";
                } else{
                    alert("Error occurred! Please try again later.");
                }
            }
        };
    }
}

function add_mote_row() {
    var physical_id=document.getElementById("new_mote_physical_id").value;
    var virtual_id=document.getElementById("new_mote_virtual_id").value;
    var gateway_ip=document.getElementById("new_mote_gateway_ip").value;
    var gateway_ttyid=document.getElementById("new_mote_gateway_ttyid").value;
    var gateway_port=document.getElementById("new_mote_gateway_port").value;
    var coordinates=document.getElementById("new_mote_coordinates").value;
    
    if(physical_id == "" || virtual_id == "" || gateway_ip == "" || gateway_ttyid == "" || gateway_port == "" || coordinates == ""  || !physical_id.replace(/\s/g, '').length || !virtual_id.replace(/\s/g, '').length || !gateway_ip.replace(/\s/g, '').length || !gateway_ttyid.replace(/\s/g, '').length || !gateway_port.replace(/\s/g, '').length || !coordinates.replace(/\s/g, '').length) {
        alert("Kindly, fill in all the mote fields to add a mote.");
    } else {
        var moteTypesList_load=document.getElementById("moteTypesList_load");
        var clustersList_load=document.getElementById("clustersList_load");
        if(moteTypesList_load.length == 0){
            alert("kindly, add a mote type first");
        } else if(clustersList_load.length == 0){
            alert("kindly, add a cluster first");
        } else {
            var moteTypeID = moteTypesList_load.options[moteTypesList_load.selectedIndex].value;
            var moteTypeName = moteTypesList_load.options[moteTypesList_load.selectedIndex].text;

            var clusterID = clustersList_load.options[clustersList_load.selectedIndex].value;
            var clusterName = clustersList_load.options[clustersList_load.selectedIndex].text;

            var moteInfo = new FormData();
            moteInfo.append('userID', userID);
            moteInfo.append('physical_id', physical_id);
            moteInfo.append('virtual_id', virtual_id);
            moteInfo.append('gateway_ip', gateway_ip);
            moteInfo.append('gateway_ttyid', gateway_ttyid);
            moteInfo.append('gateway_port', gateway_port);
            moteInfo.append('coordinates', coordinates);
            moteInfo.append('moteTypeID', moteTypeID);
            moteInfo.append('clusterID', clusterID);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/addMote.php', true);
            xhr.send(moteInfo);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var moteID = this.responseText;
                    if(moteID != -1){
                        var table=document.getElementById("motes_data_table");
                        var row = table.insertRow(1).outerHTML="<tr id='mote_row"+moteID+"'><td id='physical_id_row"+moteID+"'>"+physical_id+"</td><td id='virtual_id_row"+moteID+"'>"+virtual_id+"</td><td id='gateway_ip_row"+moteID+"'>"+gateway_ip+"</td><td id='gateway_ttyid_row"+moteID+"'>"+gateway_ttyid+"</td><td id='gateway_port_row"+moteID+"'>"+gateway_port+"</td><td id='coordinates_row"+moteID+"'>"+coordinates+"</td><td id='moteTypeName_row"+moteID+"'>"+moteTypeName+"</td><td id='clusterName_row"+moteID+"'>"+clusterName+"</td><td>undefined</td><td><input class='button' type='button' id='edit_mote_button"+moteID+"' value='Edit' class='edit' onclick='edit_mote_row("+moteID+")' style='float: left'> <input class='button' type='button' id='save_mote_button"+moteID+"' value='Save' class='save' onclick='save_mote_row("+moteID+")' style='display: none''> <input class='button' type='button' value='Delete' class='delete' onclick='delete_mote_row("+moteID+")' style='float: left'></td></tr>";
                    } else{
                        alert("Error occurred! Please try again later.");
                    }

                    document.getElementById("new_mote_physical_id").value="";
                    document.getElementById("new_mote_virtual_id").value="";
                    document.getElementById("new_mote_gateway_ip").value="";
                    document.getElementById("new_mote_gateway_ttyid").value="";
                    document.getElementById("new_mote_gateway_port").value="";
                    document.getElementById("new_mote_coordinates").value="";
                    document.getElementById("moteTypesList_load").selectedIndex=0;
                    document.getElementById("clustersList_load").selectedIndex=0;
                }
            };
        }
    }
}