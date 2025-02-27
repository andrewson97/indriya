function showStatistics() {
    //view all users and jobs and running time
    document.getElementById("userAccountForm").style.display="none";
    document.getElementById("userIDLabelFill").innerHTML = "";
    document.getElementById("userIDtxt").value = "";

    var usersTableHeader = document.getElementById("usersTable").getElementsByTagName('thead')[0];
    usersTableHeader.innerHTML = "";        

    var usersTableBody = document.getElementById("usersTable").getElementsByTagName('tbody')[0];
    usersTableBody.innerHTML = "";

    var usersTableFooter = document.getElementById("usersTable").getElementsByTagName('tfoot')[0];
    usersTableFooter.innerHTML = "";

    var motesTableHeader = document.getElementById("motesTable").getElementsByTagName('thead')[0];
    motesTableHeader.innerHTML = "";        

    var motesTableBody = document.getElementById("motesTable").getElementsByTagName('tbody')[0];
    motesTableBody.innerHTML = "";

    var motesTableFooter = document.getElementById("motesTable").getElementsByTagName('tfoot')[0];
    motesTableFooter.innerHTML = "";

    var usersInfo = new FormData();
    usersInfo.append('userID', userID);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/getUsersInfo.php', true);
    xhr.send(usersInfo);
    xhr.onload = function () {
        if (xhr.status === 200) {
            xmlDoc = xhr.responseXML;
            if(xmlDoc == null){
                //no users, never!
                alert("Dear " + userName + ", there is not users in the database!");
            }
            else {

                //show users info.
                //header
                var trHeader = document.createElement("tr");

                var thUser = document.createElement("th");
                var thJob = document.createElement("th");
                var thTime = document.createElement("th");
                var thAllMotes = document.createElement("th");
                var thExpand = document.createElement("th");

                var txtUser = document.createTextNode("User(s)");
                var txtJob = document.createTextNode("Number of Submissions");
                var txtTime = document.createTextNode("Total Run Time (dd.hh:mm)");
                var txtAllMotes = document.createTextNode("All Mote Types Privilege");

                thExpand.classList.add("table_edit_links");
                
                var thExpandDiv = document.createElement("div");
                thExpandDiv.classList.add("table-expandable-arrow");
                thExpandDiv.id = "expandAllUsersTable";
                thExpandDiv.onclick = expandAllClick;
                
                jQuery(thExpandDiv).attr('title', "Show all details");
                

                thUser.appendChild(txtUser);
                thJob.appendChild(txtJob);
                thTime.appendChild(txtTime);
                thAllMotes.appendChild(txtAllMotes);
                thExpand.appendChild(thExpandDiv);

                trHeader.appendChild(thUser);
                trHeader.appendChild(thJob);
                trHeader.appendChild(thTime);
                trHeader.appendChild(thAllMotes);
                trHeader.appendChild(thExpand);
                
                /*
                var trHeader1 = document.createElement("tr");
                var thNote = document.createElement("th");
                thNote.setAttribute("colspan", "5");
                var noteLabel = document.createElement("label");
                noteLabel.style.fontSize = "10pt";
                
                noteLabel.appendChild(document.createTextNode("Note: number of jobs is a real time value however, total run time is a history value!"));
                thNote.appendChild(noteLabel);
                trHeader1.appendChild(thNote);
                
                usersTableHeader.appendChild(trHeader1);
                */
                usersTableHeader.appendChild(trHeader);

                //body
                var totalNumberJobs = 0;
                var totalRunTime = 0;
                var numOfUsersPrivilege = 0;
                // console.log(xmlDoc);
                for(i = 0; i < xmlDoc.getElementsByTagName("users")[0].childNodes.length - 1; i++){
                    var trUser = document.createElement("tr");

                    var tdUserName = document.createElement("td");
                    var tdJobNumbers = document.createElement("td");
                    var tdRunTime = document.createElement("td");
                    var tdAllMotes = document.createElement("td");
                    var tdExpand = document.createElement("td");

                    var tdExpandDiv = document.createElement("div");
                    
                    jQuery(tdExpandDiv).attr('title', "Show details");

                    tdExpandDiv.classList.add("table-expandable-arrow");
                    tdExpandDiv.id= i + "@tdExpandDiv@trMoteResults";

                    tdExpandDiv.onclick = expandClick;

                    var txtUserName = document.createTextNode(xmlDoc.getElementsByTagName("userID")[i].childNodes[0].nodeValue);
                    
                    var txtRunTime = document.createTextNode(showQuota(xmlDoc.getElementsByTagName("runningTime")[i].childNodes[0].nodeValue));

                    var txtJobNumbers = document.createTextNode(xmlDoc.getElementsByTagName("totalSubmissions")[i].childNodes[0].nodeValue);

                    totalNumberJobs += +xmlDoc.getElementsByTagName("totalSubmissions")[i].childNodes[0].nodeValue;

                    totalRunTime += +xmlDoc.getElementsByTagName("runningTime")[i].childNodes[0].nodeValue;

                    numOfUsersPrivilege += +xmlDoc.getElementsByTagName("allMotes")[i].childNodes[0].nodeValue;

                    /*var user = xmlDoc.getElementsByTagName("user")[i];
                    if(user.getElementsByTagName("jobs").length != 0){
                        var txtJobNumbers = document.createTextNode(user.getElementsByTagName("job").length);

                        totalNumberJobs += user.getElementsByTagName("job").length;
                    } else {
                        var txtJobNumbers = document.createTextNode("0");
                    }
                    */
                    
                    if(xmlDoc.getElementsByTagName("allMotes")[i].childNodes[0].nodeValue == 1) {
                        var txtAllMotes = document.createTextNode("Yes");
                    } else {
                        var txtAllMotes = document.createTextNode("No");
                    }

                    var refToUser = document.createElement("a");
                    refToUser.onclick = linkToUser;
                    refToUser.appendChild(txtUserName);

                    tdUserName.appendChild(refToUser);
                    tdJobNumbers.appendChild(txtJobNumbers);
                    tdRunTime.appendChild(txtRunTime);
                    tdAllMotes.appendChild(txtAllMotes);

                    tdExpand.appendChild(tdExpandDiv);

                    trUser.appendChild(tdUserName);
                    trUser.appendChild(tdJobNumbers);
                    trUser.appendChild(tdRunTime);
                    trUser.appendChild(tdAllMotes);
                    trUser.appendChild(tdExpand);
                    usersTableBody.appendChild(trUser);

                    var trMoteResults = document.createElement("tr");
                    trMoteResults.id = i + "@trMoteResults";

                    var td = document.createElement("td");
                    td.setAttribute("colspan", "4");

                    var listJobs = document.createElement("ul");
                    listJobs.id = "ranks";


                    var listitem = document.createElement("li");
                    var listitemData = document.createTextNode("Quota: " + xmlDoc.getElementsByTagName("quota")[i].childNodes[0].nodeValue + " minutes");
                    listitem.appendChild(listitemData);
                    listJobs.appendChild(listitem);
                    
                    var listitem = document.createElement("li");
                    var listitemData = document.createTextNode("Date created: " + xmlDoc.getElementsByTagName("create_date")[i].childNodes[0].nodeValue);
                    listitem.appendChild(listitemData);
                    listJobs.appendChild(listitem);
                    
                    var listitem = document.createElement("li");
                    var listitemData = document.createTextNode("Details: " + xmlDoc.getElementsByTagName("details")[i].childNodes[0].nodeValue);
                    listitem.classList.add("wordwrap");
                    listitem.appendChild(listitemData);
                    listJobs.appendChild(listitem);
                    
                    td.appendChild(document.createTextNode("User details"));
                    td.appendChild(listJobs);                    

                    trMoteResults.appendChild(td);
                    usersTableBody.appendChild(trMoteResults);

                    jQuery(trMoteResults).hide();
                    tdExpand.style.display = "block";

                    /*
                    if(user.getElementsByTagName("jobs").length != 0){
                        var trMoteResults = document.createElement("tr");
                        trMoteResults.id = i + "@trMoteResults";

                        var td = document.createElement("td");
                        td.setAttribute("colspan", "4");

                        var listJobs = document.createElement("ul");
                        listJobs.id = "ranks";

                        for (j = 0; j < user.getElementsByTagName("job").length; j++){
                            var listitem = document.createElement("li");
                            var listitemData = document.createTextNode("Job " + (j + 1) + " run on mote type(s): ");

                            listitem.appendChild(listitemData);
                            listJobs.appendChild(listitem);
                            
                            var job = user.getElementsByTagName("job")[j];
                            
                            var listMotes = document.createElement("ul");
                            listMotes.id = "ranks";
                            
                            var moteTypeAdded = -1;
                            for(k = 0; k < job.getElementsByTagName("moteTypeName").length; k++) {
                                if(moteTypeAdded != job.getElementsByTagName("moteTypeID")[k].childNodes[0].nodeValue) {
                                    moteTypeAdded = job.getElementsByTagName("moteTypeID")[k].childNodes[0].nodeValue;
                                    
                                    var listitem = document.createElement("li");
                                    var listitemData = document.createTextNode(job.getElementsByTagName("moteTypeName")[k].childNodes[0].nodeValue);

                                    listitem.appendChild(listitemData);
                                    listMotes.appendChild(listitem);
                                }
                            }
                            listJobs.appendChild(listMotes);
                        }
                        
                        numOfUsersPrivilege += +xmlDoc.getElementsByTagName("allMotes")[i].childNodes[0].nodeValue;
                        
                        td.appendChild(document.createTextNode("Mote Type Usage"));
                        td.appendChild(listJobs);

                        trMoteResults.appendChild(td);
                        usersTableBody.appendChild(trMoteResults);

                        jQuery(trMoteResults).hide();
                        tdExpand.style.display = "block";
                    }
                    else{
                        tdExpand.style.display = "none";
                    }
                    */
                }

                //footer
                var trFooter = document.createElement("tr");

                var thUser = document.createElement("th");
                var thJob = document.createElement("th");
                var thTime = document.createElement("th");
                var thAllMotes = document.createElement("th");
                var thExpand = document.createElement("th");

                var txtUser = document.createTextNode("Total: " + (xmlDoc.getElementsByTagName("users")[0].childNodes.length - 1) + " users");
                var txtJob = document.createTextNode(totalNumberJobs + " submissions");
                var txtTime = document.createTextNode(showQuota(totalRunTime));
                var txtAllMotes = document.createTextNode(numOfUsersPrivilege + " privileged users");

                thExpand.classList.add("table_edit_links");
                thExpand.style.display = "none";

                thUser.appendChild(txtUser);
                thJob.appendChild(txtJob);
                thTime.appendChild(txtTime);
                thAllMotes.appendChild(txtAllMotes);

                trFooter.appendChild(thUser);
                trFooter.appendChild(thJob);
                trFooter.appendChild(thTime);
                trFooter.appendChild(thAllMotes);
                trFooter.appendChild(thExpand);

                usersTableFooter.appendChild(trFooter);

                //show motes info.

                //header
                var trHeader = document.createElement("tr");

                var thMotes = document.createElement("th");
                var thTime = document.createElement("th");

                var txtMotes = document.createTextNode("Mote Type");
                var txtTime = document.createTextNode("Total Run Time (dd.hh:mm)");

                thMotes.appendChild(txtMotes);
                thTime.appendChild(txtTime);

                trHeader.appendChild(thMotes);
                trHeader.appendChild(thTime);
                
                var trHeader1 = document.createElement("tr");
                var thNote = document.createElement("th");
                thNote.setAttribute("colspan", "2");
                var noteLabel = document.createElement("label");
                noteLabel.style.fontSize = "10pt";
                
                noteLabel.appendChild(document.createTextNode("Note: total run time is a history value!"));
                thNote.appendChild(noteLabel);
                trHeader1.appendChild(thNote);
                
                motesTableBody.appendChild(trHeader1);

                motesTableBody.appendChild(trHeader);

                //body
                var totalMotesRunTime = 0;
                for(i = 0; i < xmlDoc.getElementsByTagName("moteTypesTotal")[0].childNodes.length; i++){
                    var trMote = document.createElement("tr");

                    var tdMoteName = document.createElement("td");
                    var tdRunTime = document.createElement("td");

                    var mote = xmlDoc.getElementsByTagName("moteTypeTotal")[i];

                    var txtMoteName = document.createTextNode(mote.getElementsByTagName("moteTypeName")[0].childNodes[0].nodeValue);

                    var txtRunTime = document.createTextNode(showQuota(mote.getElementsByTagName("moteTypeTime")[0].childNodes[0].nodeValue));

                    totalMotesRunTime += +mote.getElementsByTagName("moteTypeTime")[0].childNodes[0].nodeValue;

                    tdMoteName.appendChild(txtMoteName);
                    tdRunTime.appendChild(txtRunTime);

                    trMote.appendChild(tdMoteName);
                    trMote.appendChild(tdRunTime);
                    motesTableBody.appendChild(trMote);
                }

                //footer
                var trFooter = document.createElement("tr");

                var thMotes = document.createElement("th");
                var thTime = document.createElement("th");

                var txtMotes = document.createTextNode("Total: " + xmlDoc.getElementsByTagName("moteTypesTotal")[0].childNodes.length + " mote types");
                var txtTime = document.createTextNode(showQuota(totalMotesRunTime));

                thMotes.appendChild(txtMotes);
                thTime.appendChild(txtTime);

                trFooter.appendChild(thMotes);
                trFooter.appendChild(thTime);

                motesTableFooter.appendChild(trFooter);
            }
        }
    };

    document.getElementById("tableUsersInfo").style.display="block";
}

function showAllTimeUsage() {
    var moteTypeID = "all";
    var modal = "resourcesAllTimeUsageModal";
    var containerStr = "resourcesStateAllTimeUsageVisualization";
    var timeLabelStr = "timeNowLabelResourcesAllTimeUsage";
    var imgStr = "loadingResourcesAllTimeUsage";
    var infoDiv = "resourcesAllTimeUsageInfoModal";
    
    refreshScheduleJob2(infoDiv, modal, imgStr, containerStr, timeLabelStr, moteTypeID, 1);
}

function linkToUser() {
    showUser(this.text);
}

function clearAllUpcomingJobs(){
    var result = confirm("Are you sure? This will clear all the upcoming scheduled jobs!");
    if (result) {
        var clearAllUpcomingJobsInfo = new FormData();
        clearAllUpcomingJobsInfo.append('userID', userID);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/clearAllUpcomingJobs.php', true);
        xhr.send(clearAllUpcomingJobsInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                if(this.responseText == 0) {
                    alert("Error occurred! Please try again later.");
                } else {
                    alert("All upcoming scheduled jobs are canceled successfully.");
                }
            }
        };
    }
}