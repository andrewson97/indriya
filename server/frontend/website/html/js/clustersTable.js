function edit_cluster_row(no) {
    document.getElementById("edit_cluster_button"+no).style.display="none";
    document.getElementById("save_cluster_button"+no).style.display="block";
    
    var name=document.getElementById("name_cluster_row"+no);
    var name_data=name.innerHTML;
    
    var floorLevel=document.getElementById("name_floorLevel_row"+no);
    var floorLevel_data=floorLevel.innerHTML;
    
    name.innerHTML="<input type='text' class='text1' style='width: 180px' id='name_cluster_text"+no+"' value='"+name_data+"'>";
    floorLevel.innerHTML="<input type='text' class='text1' style='width: 180px' id='name_floorLevel_text"+no+"' value='"+floorLevel_data+"'>";
}

function save_cluster_row(no) {
    var name_val=document.getElementById("name_cluster_text"+no).value;
    var floorLevel_val=document.getElementById("name_floorLevel_text"+no).value;
    
    if(name_val == "" || !name_val.replace(/\s/g, '').length){
        alert("Kindly, type in the cluster name in the text field.");
    } else if(floorLevel_val == "" || !floorLevel_val.replace(/\s/g, '').length) {
        alert("Kindly, type in the floor level in the text field.");
    } else {
        var clusterInfo = new FormData();
        clusterInfo.append('userID', userID);
        clusterInfo.append('clusterID', no);
        clusterInfo.append('clusterName', name_val);
        clusterInfo.append('floorLevel', floorLevel_val);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/updateCluster.php', true);
        xhr.send(clusterInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var flag = this.responseText;
                if(flag) {
                    loadMotes();
                } else {
                    alert("Error occurred, please try again later!");
                }
            }
        };
    }
}

function delete_cluster_row(no) {
    //delete cluster from DB
    var result = confirm("Deleting a cluster will affect other records in the database. Are you sure?");
    if (result) {
        var clusterInfo = new FormData();
        clusterInfo.append('userID', userID);
        clusterInfo.append('clusterID', no);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/deleteCluster.php', true);
        xhr.send(clusterInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var flag = this.responseText;
                if(flag == 1){
                    loadMotes();
                } else{
                    alert("Error occurred! Please try again later.");
                }
            }
        };
    }
}

function add_cluster_row() {
    var new_name=document.getElementById("new_cluster_name").value;
    var new_floorLevel=document.getElementById("new_floorLevel_name").value;
    
    var str = new_name + "@" + new_floorLevel;
    
    if(new_name == "" || !new_name.replace(/\s/g, '').length){
        alert("Kindly, type in the cluster name in the text field.");
    } else if (new_floorLevel == "" || !new_floorLevel.replace(/\s/g, '').length) {
        alert("Kindly, type in the floor level in the text field.");
    } else {

        var clusterInfo = new FormData();
        clusterInfo.append('userID', userID);
        clusterInfo.append('clusterName', new_name);
        clusterInfo.append('floorLevel', new_floorLevel);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/addCluster.php', true);
        xhr.send(clusterInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var clusterID = this.responseText;
                if(clusterID != -1){
                    var table=document.getElementById("clusters_data_table");
                    var row = table.insertRow(1).outerHTML="<tr id='cluster_row"+clusterID+"'><td id='name_cluster_row"+clusterID+"'>"+new_name+"</td><td id='name_floorLevel_row"+clusterID+"'>"+new_floorLevel+"</td><td><input class='button' type='button' id='edit_cluster_button"+clusterID+"' value='Edit' class='edit' onclick='edit_cluster_row("+clusterID+")' style='float: left'> <input class='button' type='button' id='save_cluster_button"+clusterID+"' value='Save' class='save' onclick='save_cluster_row("+clusterID+")' style='display: none''> <input class='button' type='button' value='Delete' class='delete' onclick='delete_cluster_row("+clusterID+")' style='float: left'></td></tr>";
                    
                    addclusters_load();
                } else{
                    alert("Error occurred! Please try again later.");
                }

                document.getElementById("new_cluster_name").value="";
                document.getElementById("new_floorLevel_name").value="";
            }
        };
    }
}