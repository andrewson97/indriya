//edit
function editClick(){
    var i = this.id.split("@");
    if(i[1] == "not") {
        alert("Sorry, cannot edit an upcoming job!");
    } else {        
        jobIDUpdate = i[0];

        var jobInfo = new FormData();
        jobInfo.append('userID', userID);
        jobInfo.append('jobID', jobIDUpdate);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/getJob.php', true);
        xhr.send(jobInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var jobXML = xhr.responseXML;
                //build up data form and start editing job
                data = new FormData();
                data.append("userID", userID);
                data.append('jobID', jobIDUpdate);
                data.append('jobName', jobXML.getElementsByTagName("jobName")[0].childNodes[0].nodeValue);
                data.append('dcube', jobXML.getElementsByTagName("dcube")[0].childNodes[0].nodeValue);
                
                var j = 0;
                var file = jobXML.getElementsByTagName("file")[0];
                data.append("numberOfMoteTypes", 1);
                var moteTypeID = file.getElementsByTagName("moteTypeID")[0].childNodes[0].nodeValue;
                data.append("moteTypeID" + j, moteTypeID);
                data.append("moteTypeName" + j, file.getElementsByTagName("moteTypeName")[0].childNodes[0].nodeValue);
                
                data.append("oldFileID" + moteTypeID + "[]", file.getElementsByTagName("fileID")[0].childNodes[0].nodeValue);
                data.append("oldFileName" + moteTypeID + "[]", file.getElementsByTagName("fileName")[0].childNodes[0].nodeValue);
                
                for(var i = 0; i < file.getElementsByTagName("moteID").length; i++) {
                    data.append("oldMotes" + file.getElementsByTagName("fileID")[0].childNodes[0].nodeValue + "[]", file.getElementsByTagName("moteID")[i].childNodes[0].nodeValue);
                }
                
                for(var i = 1; i < jobXML.getElementsByTagName("file").length; i++) {
                    file = jobXML.getElementsByTagName("file")[i];
                    var moteTypeID = file.getElementsByTagName("moteTypeID")[0].childNodes[0].nodeValue;
                    if(data.get("moteTypeID" + j) != file.getElementsByTagName("moteTypeID")[0].childNodes[0].nodeValue) {
                        //same mote type, increase number of files and add file
                        j++;
                        var num = data.get("numberOfMoteTypes");
                        num++;
                        data.set("numberOfMoteTypes", num);
                        
                        data.append("moteTypeID" + j, file.getElementsByTagName("moteTypeID")[0].childNodes[0].nodeValue);
                        data.append("moteTypeName" + j, file.getElementsByTagName("moteTypeName")[0].childNodes[0].nodeValue);
                    }
                    //increase number of mote types, add new mote type, set number of files and add first file
                    data.append("oldFileID" + moteTypeID + "[]", file.getElementsByTagName("fileID")[0].childNodes[0].nodeValue);
                    data.append("oldFileName" + moteTypeID + "[]", file.getElementsByTagName("fileName")[0].childNodes[0].nodeValue);

                    for(var k = 0; k < file.getElementsByTagName("moteID").length; k++) {
                        data.append("oldMotes" + file.getElementsByTagName("fileID")[0].childNodes[0].nodeValue + "[]", file.getElementsByTagName("moteID")[k].childNodes[0].nodeValue);
                    }
                }

                if(data.get("dcube") != 0){
                    dcube_file = jobXML.getElementsByTagName("dcube_file")[0];
                    data.append("dcube_oldFileID", dcube_file.getElementsByTagName("dcube_fileID")[0].childNodes[0].nodeValue);
                    data.append("dcube_oldFileName", dcube_file.getElementsByTagName("dcube_fileName")[0].childNodes[0].nodeValue);
                }

                
                //view job name, mote type, olf files to the user
                if(data.get("numberOfMoteTypes") == 1)
                    addMoteTypes(data.get("moteTypeID0"));
                else
                    addMoteTypes(-1);
                
               /*for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                   var motTypeID = data.get("moteTypeID" + i);
                   console.log(data.get("moteTypeID" + i));
                   console.log(data.get("moteTypeName" + i));
                   var oldFileID = data.getAll("oldFileID" + motTypeID + "[]");
                   var oldFileName = data.getAll("oldFileName" + motTypeID + "[]");
                   for(var j = 0; j < oldFileID.length; j++) {
                       console.log(oldFileID[j]);
                       console.log(oldFileName[j]);
                       var oldMotes = data.getAll("oldMotes" + oldFileID[j] + "[]");
                       for(var k = 0; k < oldMotes.length; k++)
                           console.log(oldMotes[k]);
                   }
               }*/
            }
        };
    }
}

function deleteSelectedFile() {
    //delete selected file form form data (file id, name, motes)
    //delete selected file from select
    
    var moteTypeID = this.id.split("@")[0];
    
    var OldFilesList = document.getElementById(moteTypeID + "@OldFilesList");
    if(OldFilesList.selectedIndex == -1) {
        alert("Please, select file(s) to delete!");
    } else {
        for(var k = 0; k < OldFilesList.options.length; k++) {
            if(OldFilesList.options[k].selected) {
                var selectedFileID = OldFilesList.options[k].value;
                var oldFileID = data.getAll("oldFileID" + moteTypeID + "[]");
                var oldFileName = data.getAll("oldFileName" + moteTypeID + "[]");
                for(var j = 0; j < oldFileID.length; j++) {
                    if(selectedFileID == oldFileID[j]) {
                        data.delete("oldMotes" + oldFileID[j] + "[]");

                        oldFileID.splice(j, 1);
                        oldFileName.splice(j, 1);
                        break;
                    }
                }

                data.delete("oldFileID" + moteTypeID + "[]");
                data.delete("oldFileName" + moteTypeID + "[]");

                for(var j = 0; j < oldFileID.length; j++) {
                    data.append("oldFileID" + moteTypeID + "[]", oldFileID[j]);
                    data.append("oldFileName" + moteTypeID + "[]", oldFileName[j]);
                }
                        
            }
        }
        
        // Remember selected items.
        var is_selected = [];
        for (var i = 0; i < OldFilesList.options.length; ++i)
        {
            is_selected[i] = OldFilesList.options[i].selected;
        }

        // Remove selected items.
        i = OldFilesList.options.length;
        while (i--)
        {
            if (is_selected[i])
            {
                OldFilesList.remove(i);
            }
        }
    }
}

function deleteSelectedDcubeFile() {
    //delete selected file form form data (file id, name, motes)
    //delete selected file from select
    
    var moteTypeID = this.id.split("@")[0];
    
    var OldFilesList = document.getElementById("OldFilesList@dcube");
    if(OldFilesList.selectedIndex == -1) {
        alert("Please, select file to delete!");
    } else {
        var selectedFileID = OldFilesList.options[OldFilesList.selectedIndex].value;

        var dcube_oldFileID = data.get("dcube_oldFileID");
        var dcube_oldFileName = data.get("dcube_oldFileName");
        data.delete("dcube_oldFileID");
        data.delete("dcube_oldFileName");
        
        // Remember selected items.
        var is_selected = [];
        for (var i = 0; i < OldFilesList.options.length; ++i)
        {
            is_selected[i] = OldFilesList.options[i].selected;
        }

        // Remove selected items.
        i = OldFilesList.options.length;
        while (i--)
        {
            if (is_selected[i])
            {
                OldFilesList.remove(i);
            }
        }
    }
}

function nextUpdateJobDB() {
    //check for job name
    var jobNametxt = document.getElementById("jobNametxt");
    var moteTypesList = document.getElementById("moteTypesList");
    
    if(jobNametxt.value == "" || jobNametxt.value == null) {
        alert("Please, enter the job name!");
        return false;
    } else {
        if(moteTypesList.options[moteTypesList.selectedIndex].value == -1) {
            var filesForAll = moteTypesList.length - 2;
            for(i = 1; i < moteTypesList.length - 1; i++) {
                var motTypeID = moteTypesList.options[i].value;

                if(moteTypesList.options[i].innerHTML == "telosb"){
                    var dcube_select = document.getElementById("dcube_select");
                
                    if(dcube_select.options[dcube_select.selectedIndex].value == "dcube_yes"){
                        // check that only one file is included in dcube
                        var dcube_fileInput = document.getElementById("inputFile@dcube");

                        if(data.get("dcube_oldFileID") == null)
                            var dcube_oldFileID_length = 0;
                        else
                            var dcube_oldFileID_length = 1;
                        if((dcube_fileInput.files.length + dcube_oldFileID_length) != 1) {
                            alert("Please, select one file for DCube nodes!");
                            return false;
                        }
                        // included, change dcube to 2 and add file
                        data.set("dcube", 2);

                        for(j = 0; j < dcube_fileInput.files.length; j++) {
                            data.append('dcube_fileName', dcube_fileInput.files.item(j).name);

                            var file = dcube_fileInput.files[j];
                            data.append('dcube_file', file, file.name);
                        }
                        var moteTypeID = moteTypesList.options[i].value;
                        data.append('dcube_moteTypeID', moteTypeID);
                    }else if(dcube_select.options[dcube_select.selectedIndex].value == "dcube_no"){
                        // not included, change dcube to 0
                        data.set("dcube", 0);
                    }
                }

                var fileInput = document.getElementById(motTypeID + "@inputFile");
                var oldFileID = data.getAll("oldFileID" + motTypeID + "[]");
                if((fileInput.files.length + oldFileID.length) == 0) {
                    //no files (old or new) for this mote type.
                    alert("Please, select file(s) for " + moteTypesList.options[i].innerHTML + " mote type!");
                    filesForAll--;
                    return false;
                } else {
                    var size_is_ok = 1;
                    var length_is_ok = 1;
                    for(j = 0; j < fileInput.files.length; j++) {
                        if(fileInput.files[j].size > max_file_size){
                            size_is_ok = 0;
                            break;
                        }

                        if(fileInput.files[j].name.length > max_file_name_length){
                            length_is_ok = 0;
                            break;
                        }
                    }
                    if(size_is_ok == 0) {
                        alert("Error, max file size is " + max_file_size/1024 + " KB");
                        return false;
                    }
                    if(length_is_ok == 0) {
                        alert("Error, max file name length is " + max_file_name_length + " characters");
                        return false;
                    }
                }
            }
            if(filesForAll != 0) {
                data.set('jobName', jobNametxt.value);
                
                for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                    data.delete("moteTypeID" + i);
                    data.delete("moteTypeName" + i);
                    
                    for(var j = 0; j < data.get("numberOfFiles" + i); j++){
                        data.delete('fileName' + i + "" + j);
                        data.delete('file' + i + "" + j);
                        data.delete("motes" + i + "" + j + "[]");
                    }
                    data.delete('numberOfFiles' + i);
                }
                data.set('numberOfMoteTypes', (moteTypesList.length - 2));
                for(var i = 1; i < moteTypesList.length - 1; i++) {
                    var motTypeID = moteTypesList.options[i].value;
                    data.append('moteTypeID' + (i - 1), motTypeID);
                    data.append('moteTypeName' + (i - 1), moteTypesList.options[i].text);
                    var fileInput = document.getElementById(motTypeID + "@inputFile");
                    //add new files to data
                    data.append('numberOfFiles' + (i - 1), fileInput.files.length);
                    for(j = 0; j < fileInput.files.length; j++) {
                        data.append('fileName' + (i - 1) + "" + j, fileInput.files.item(j).name);

                        var file = fileInput.files[j];
                        data.append('file' + (i - 1) + "" + j, file, file.name);
                    }
                }
            }
        } else {
            // telosb, check if dcube included or not
            if(moteTypesList.options[moteTypesList.selectedIndex].innerHTML == "telosb"){
                var dcube_select = document.getElementById("dcube_select");
                
                if(dcube_select.options[dcube_select.selectedIndex].value == "dcube_yes"){
                    // check that only one file is included in dcube
                    var dcube_fileInput = document.getElementById("inputFile@dcube");

                    if(data.get("dcube_oldFileID") == null)
                        var dcube_oldFileID_length = 0;
                    else
                        var dcube_oldFileID_length = 1;
                    if((dcube_fileInput.files.length + dcube_oldFileID_length) != 1) {
                        alert("Please, select one file for DCube nodes!");
                        return false;
                    }
                    // included, change dcube to 2 and add file
                    data.set("dcube", 2);

                    for(j = 0; j < dcube_fileInput.files.length; j++) {
                        data.append('dcube_fileName', dcube_fileInput.files.item(j).name);

                        var file = dcube_fileInput.files[j];
                        data.append('dcube_file', file, file.name);
                    }
                    var moteTypeID = moteTypesList.options[moteTypesList.selectedIndex].value;
                    data.append('dcube_moteTypeID', moteTypeID);


                    var fileInput = document.getElementById(moteTypeID + "@inputFile");
                    var oldFileID = data.getAll("oldFileID" + moteTypeID + "[]");
                    if((fileInput.files.length + oldFileID.length) == 0) {
                        //no files (old or new) for this mote type.
                        alert("Please, select file(s) for " + moteTypesList.options[moteTypesList.selectedIndex].innerHTML + " mote type!");
                        return false;
                    } else {
                        var size_is_ok = 1;
                        var length_is_ok = 1;   
                        for(j = 0; j < fileInput.files.length; j++) {
                            if(fileInput.files[j].size > max_file_size){
                                size_is_ok = 0;
                                break;
                            }

                            if(fileInput.files[j].name.length > max_file_name_length){
                                length_is_ok = 0;
                                break;
                            }
                        }

                        if(size_is_ok == 1 && length_is_ok == 1) {
                            data.set('jobName', jobNametxt.value);
                        
                            for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                                //delete files for non selected mote type
                                if(data.get("moteTypeID" + i) != moteTypeID) {
                                    var tempMoteTypeID = data.get("moteTypeID" + i);
                                    var oldFileID = data.getAll("oldFileID" + tempMoteTypeID + "[]");
                                    for(var j = 0; j < oldFileID.length; j++) {
                                        data.delete("oldMotes" + oldFileID[j] + "[]");
                                    }
                                    data.delete("oldFileID" + tempMoteTypeID + "[]");
                                    data.delete("oldFileName" + tempMoteTypeID + "[]");
                                }
                                
                                //delete new files if any
                                for(var j = 0; j < data.get("numberOfFiles" + i); j++){
                                    data.delete('fileName' + i + "" + j);
                                    data.delete('file' + i + "" + j);
                                    data.delete("motes" + i + "" + j + "[]");
                                }
                                data.delete('numberOfFiles' + i);
                            }
                            //delete old id, name to add again in sequence later
                            for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                                data.delete("moteTypeID" + i);
                                data.delete("moteTypeName" + i);
                            }
                            
                            data.set('numberOfMoteTypes', 1);
                            data.append('moteTypeID0', moteTypeID);
                            data.append('moteTypeName0', moteTypesList.options[moteTypesList.selectedIndex].text);
                            data.append('numberOfFiles0', fileInput.files.length);
                            for(j = 0; j < fileInput.files.length; j++) {
                                data.append('fileName0' + j, fileInput.files.item(j).name);

                                var file = fileInput.files[j];
                                data.append('file0' + j, file, file.name);
                            }
                        } else {
                            if(size_is_ok == 0) {
                                alert("Error, max file size is " + max_file_size/1024 + " KB");
                                return false;
                            }
                            if(length_is_ok == 0) {
                                alert("Error, max file name length is " + max_file_name_length + " characters");
                                return false;
                            }
                        }
                    }
                } else if(dcube_select.options[dcube_select.selectedIndex].value == "dcube_no"){
                    // not included, change dcube to 0
                    data.set("dcube", 0);

                    var moteTypeID = moteTypesList.options[moteTypesList.selectedIndex].value;
                    var fileInput = document.getElementById(moteTypeID + "@inputFile");
                    var oldFileID = data.getAll("oldFileID" + moteTypeID + "[]");
                    if((fileInput.files.length + oldFileID.length) == 0) {
                        //no files (old or new) for this mote type.
                        alert("Please, select file(s) for " + moteTypesList.options[moteTypesList.selectedIndex].innerHTML + " mote type!");
                        return false;
                    } else {
                        var size_is_ok = 1;
                        var length_is_ok = 1;   
                        for(j = 0; j < fileInput.files.length; j++) {
                            if(fileInput.files[j].size > max_file_size){
                                size_is_ok = 0;
                                break;
                            }

                            if(fileInput.files[j].name.length > max_file_name_length){
                                length_is_ok = 0;
                                break;
                            }
                        }

                        if(size_is_ok == 1 && length_is_ok == 1) {
                            data.set('jobName', jobNametxt.value);
                        
                            for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                                //delete files for non selected mote type
                                if(data.get("moteTypeID" + i) != moteTypeID) {
                                    var tempMoteTypeID = data.get("moteTypeID" + i);
                                    var oldFileID = data.getAll("oldFileID" + tempMoteTypeID + "[]");
                                    for(var j = 0; j < oldFileID.length; j++) {
                                        data.delete("oldMotes" + oldFileID[j] + "[]");
                                    }
                                    data.delete("oldFileID" + tempMoteTypeID + "[]");
                                    data.delete("oldFileName" + tempMoteTypeID + "[]");
                                }
                                
                                //delete new files if any
                                for(var j = 0; j < data.get("numberOfFiles" + i); j++){
                                    data.delete('fileName' + i + "" + j);
                                    data.delete('file' + i + "" + j);
                                    data.delete("motes" + i + "" + j + "[]");
                                }
                                data.delete('numberOfFiles' + i);
                            }
                            //delete old id, name to add again in sequence later
                            for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                                data.delete("moteTypeID" + i);
                                data.delete("moteTypeName" + i);
                            }
                            
                            data.set('numberOfMoteTypes', 1);
                            data.append('moteTypeID0', moteTypeID);
                            data.append('moteTypeName0', moteTypesList.options[moteTypesList.selectedIndex].text);
                            data.append('numberOfFiles0', fileInput.files.length);
                            for(j = 0; j < fileInput.files.length; j++) {
                                data.append('fileName0' + j, fileInput.files.item(j).name);

                                var file = fileInput.files[j];
                                data.append('file0' + j, file, file.name);
                            }
                        } else {
                            if(size_is_ok == 0) {
                                alert("Error, max file size is " + max_file_size/1024 + " KB");
                                return false;
                            }
                            if(length_is_ok == 0) {
                                alert("Error, max file name length is " + max_file_name_length + " characters");
                                return false;
                            }
                        }
                    }
                }
            } else{
                // not telosb, change dcube to 0
                data.set("dcube", 0);

                var moteTypeID = moteTypesList.options[moteTypesList.selectedIndex].value;
                var fileInput = document.getElementById(moteTypeID + "@inputFile");
                var oldFileID = data.getAll("oldFileID" + moteTypeID + "[]");
                if((fileInput.files.length + oldFileID.length) == 0) {
                    //no files (old or new) for this mote type.
                    alert("Please, select file(s) for " + moteTypesList.options[moteTypesList.selectedIndex].innerHTML + " mote type!");
                    return false;
                } else {
                    var size_is_ok = 1;
                    var length_is_ok = 1;   
                    for(j = 0; j < fileInput.files.length; j++) {
                        if(fileInput.files[j].size > max_file_size){
                            size_is_ok = 0;
                            break;
                        }

                        if(fileInput.files[j].name.length > max_file_name_length){
                            length_is_ok = 0;
                            break;
                        }
                    }

                    if(size_is_ok == 1 && length_is_ok == 1) {
                        data.set('jobName', jobNametxt.value);
                    
                        for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                            //delete files for non selected mote type
                            if(data.get("moteTypeID" + i) != moteTypeID) {
                                var tempMoteTypeID = data.get("moteTypeID" + i);
                                var oldFileID = data.getAll("oldFileID" + tempMoteTypeID + "[]");
                                for(var j = 0; j < oldFileID.length; j++) {
                                    data.delete("oldMotes" + oldFileID[j] + "[]");
                                }
                                data.delete("oldFileID" + tempMoteTypeID + "[]");
                                data.delete("oldFileName" + tempMoteTypeID + "[]");
                            }
                            
                            //delete new files if any
                            for(var j = 0; j < data.get("numberOfFiles" + i); j++){
                                data.delete('fileName' + i + "" + j);
                                data.delete('file' + i + "" + j);
                                data.delete("motes" + i + "" + j + "[]");
                            }
                            data.delete('numberOfFiles' + i);
                        }
                        //delete old id, name to add again in sequence later
                        for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
                            data.delete("moteTypeID" + i);
                            data.delete("moteTypeName" + i);
                        }
                        
                        data.set('numberOfMoteTypes', 1);
                        data.append('moteTypeID0', moteTypeID);
                        data.append('moteTypeName0', moteTypesList.options[moteTypesList.selectedIndex].text);
                        data.append('numberOfFiles0', fileInput.files.length);
                        for(j = 0; j < fileInput.files.length; j++) {
                            data.append('fileName0' + j, fileInput.files.item(j).name);

                            var file = fileInput.files[j];
                            data.append('file0' + j, file, file.name);
                        }
                    } else {
                        if(size_is_ok == 0) {
                            alert("Error, max file size is " + max_file_size/1024 + " KB");
                            return false;
                        }
                        if(length_is_ok == 0) {
                            alert("Error, max file name length is " + max_file_name_length + " characters");
                            return false;
                        }
                    }
                }
            }
        }
    }
    
    /*console.log("numberOfMoteTypes " + data.get("numberOfMoteTypes"));
    for(var i = 0; i < data.get("numberOfMoteTypes"); i++) {
        var motTypeID = data.get("moteTypeID" + i);
        console.log("moteTypeID " + data.get("moteTypeID" + i));
        console.log("moteTypeName " + data.get("moteTypeName" + i));
        var oldFileID = data.getAll("oldFileID" + motTypeID + "[]");
        var oldFileName = data.getAll("oldFileName" + motTypeID + "[]");
        for(var j = 0; j < oldFileID.length; j++) {
            console.log("oldFileID " + oldFileID[j]);
            console.log("oldFileName " + oldFileName[j]);
            var oldMotes = data.getAll("oldMotes" + oldFileID[j] + "[]");
            for(var k = 0; k < oldMotes.length; k++)
                console.log("oldMotes " + oldMotes[k]);
        }
        console.log("numberOfFiles " + data.get("numberOfFiles" + i));
        for(var j = 0; j < data.get("numberOfFiles" + i); j++) {
            console.log("fileName " + data.get("fileName" + i + "" + j));
        }
    }*/
    
    document.getElementById("associationAddJobButtons").style.display = "none";
    document.getElementById("associationUpdateJobButtons").style.display = "block";
    
    var jobName = data.get('jobName');
    document.getElementById("associationJobLegend").innerHTML = "Edit Associations for \"" + jobName + "\" Job";
    document.getElementById("associationJobNameLabel").innerHTML = "Job name: " + jobName;

    fillassociationMoteTypesList();

    var addJobDiv = document.getElementById("addJobDiv");
    jQuery(addJobDiv).toggle('slow');
    var associationJobDiv = document.getElementById("associationJobDiv");
    jQuery(associationJobDiv).toggle('slow');
}

function updateJobDB() {
    //check for association (at least one file per mote type)
    var moteAdded = [];
    for (var i = 0; i < data.get('numberOfMoteTypes'); i++){
        moteAdded[i] = 0;
        //old files
        var motTypeID = data.get("moteTypeID" + i);
        var oldFileID = data.getAll("oldFileID" + motTypeID + "[]");
        for(var j = 0; j < oldFileID.length; j++) {
            if(data.has("oldMotes" + oldFileID[j] + "[]")) {
                moteAdded[i]++;
            }
        }
        
        //new files
        for(var j = 0; j < data.get('numberOfFiles' + i); j++) {
            if(data.has("motes" + i + '' + j + "[]")) {
                moteAdded[i]++;
            }
        }
    }
    
    var ok = 1;
    for(var i = 0; i < moteAdded.length; i++) {
        var motTypeID = data.get("moteTypeID" + i);
        var oldFileID = data.getAll("oldFileID" + motTypeID + "[]");
        
        var numOfFiles = parseInt(data.get('numberOfFiles' + i)) + parseInt(oldFileID.length);
        
        if(moteAdded[i] < numOfFiles) {
            ok = 0;
            alert("Kindly, assiciate all uploaded files with motes for mote type " + data.get('moteTypeName' + i));
            break;
        }
    }
    
    //send data form to php in xmlhttp to save to DB and show jobs table
    if(ok) {
        //check if schedule! add to data form duration and start timestamp
        /*if(document.getElementById("scheduleLater").checked == true) {
            data.append("scheduleLater", 1);
        } else {
            data.append("scheduleLater", 0);
            data.append("duration", document.getElementById("durationJob").value);
            if(document.getElementById("scheduleSoon").checked == true)
                updateSchedule();
            data.append("startTimestamp", document.getElementById("scheduledDateTime").value);
        }*/
        document.getElementById("errorAddJob").style.display = "none";
        document.getElementById("errorAddJobUnsupportedFile").style.display = "none";
        document.getElementById("unsupportedFiles").innerHTML = "";
        document.getElementById("successAddJob").style.display = "none";
        
        document.getElementById("uploadingAddJob").style.display = "block";
        document.getElementById("addJobModal").style.display = "block";
        
        var xhr = new XMLHttpRequest();
        
        xhr.open('POST', 'php/updateJob.php', true);
        xhr.send(data);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var myObj = JSON.parse(this.responseText);
               
                document.getElementById("uploadingAddJob").style.display = "none";
                
                if(myObj.status == "SUCCESS") {
                    addedJobID = myObj.jobID;
                    var btn = document.getElementById("btnScheduleNowJobAddJob");
                    document.getElementById("successAddJob").style.display = "block";
                } else {
                    if(myObj.message == "unsuported file format"){
                        var container = document.getElementById("unsupportedFiles");
                        container.append(myObj.files);
                        document.getElementById("errorAddJobUnsupportedFile").style.display = "block";
                    }
                    else
                        document.getElementById("errorAddJob").style.display = "block";
                }
            }
        };
    }
}