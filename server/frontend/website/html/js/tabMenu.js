var data = null;

var motesInfoXMLDoc = null;

var quota = null;
var usedQuota = null;

var timeAtServer;
var updateTimeInterval = null;

var numOfWaitingJobs = 0;
var numOfRunningJobs = 0;
var numOfTerminatedJobs = 0;

var client = null;

var lastRefresh = null;

function openTab(evt, tabName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
    
    if(tabName == "userAccounts"){
        document.getElementById("userIDtxt").value="";
        document.getElementById("userAccountForm").style.display="none";
        document.getElementById("userIDLabelFill").innerHTML="";
        document.getElementById("tableUsersInfo").style.display="none";
        document.getElementById("updateNotify").innerHTML = "";
    }
    
    //my jobs tab
    if(tabName == "myJobs"){
        refreshJobs();
    } else{        
        if(updateTimeInterval != null) {
            clearInterval(updateTimeInterval);
            updateTimeInterval = null;
        }
    }
    
    //motes tab
    if(tabName == "Motes") {
       loadMotes();
    }
}

//refresh jobs
function refreshJobs(){
    document.getElementById("jobsTableDiv").style.display = "block";
    
    var addJobDiv = document.getElementById("addJobDiv");
    addJobDiv.style.display = "none";
    
    document.getElementById("associationJobDiv").style.display = "none";
    
    document.getElementById("scheduleJobDiv").style.display = "none";

    var tableHeader = document.getElementById("jobsTable").getElementsByTagName('thead')[0];
    tableHeader.innerHTML = "";

    var tableBody = document.getElementById("jobsTable").getElementsByTagName('tbody')[0];
    tableBody.innerHTML = "";

    var waitingTableBody = document.getElementById("waitingJobsTable").getElementsByTagName('tbody')[0];
    waitingTableBody.innerHTML = "";

    var runningTableBody = document.getElementById("runningJobsTable").getElementsByTagName('tbody')[0];
    runningTableBody.innerHTML = "";

    var terminatedTableBody = document.getElementById("terminatedJobsTable").getElementsByTagName('tbody')[0];
    terminatedTableBody.innerHTML = "";

    document.getElementById("waiting-table-scroll").style.display = "none";
    document.getElementById("running-table-scroll").style.display = "none";
    document.getElementById("showMQTT").style.display = "none";
    document.getElementById("terminated-table-scroll").style.display = "none";

    
    var userInfo = new FormData();
    userInfo.append('userID', userID);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/getJobs.php', true);
    xhr.send(userInfo);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var jobsXML = xhr.responseXML;
            quota = jobsXML.getElementsByTagName("quota")[0].childNodes[0].nodeValue;
            usedQuota = jobsXML.getElementsByTagName("usedQuota")[0].childNodes[0].nodeValue;

            parts = jobsXML.getElementsByTagName("timeNowAtServer")[0].childNodes[0].nodeValue.split(".");
            timeAtServer = new Date(Date.UTC(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]));

            var timezone = +8;
            var offset = (timeAtServer.getTimezoneOffset() + (timezone * 60)) * 60 * 1000;
            // Use the timestamp and offset as necessary to calculate min/sec etc, i.e. for countdowns.
            var timestamp = timeAtServer.getTime() + offset,
                seconds = Math.floor(timestamp / 1000) % 60,
                minutes = Math.floor(timestamp / 1000 / 60) % 60,
                hours   = Math.floor(timestamp / 1000 / 60 / 60);

            // Or update the timestamp to reflect the timezone offset.
            timeAtServer.setTime(timeAtServer.getTime() + offset);
            // Then Output dates and times using the normal methods.
            var date = timeAtServer.getDate(),
                hour = timeAtServer.getHours();

            timeNowAtServer = timeAtServer;

            
            // timeAtServer = new Date(jobsXML.getElementsByTagName("timeNowAtServer")[0].childNodes[0].nodeValue * 1000);
            // console.log(jobsXML.getElementsByTagName("timeNowAtServer")[0].childNodes[0].nodeValue.replace(/-/g, "/"));
            // console.log(timeAtServer.toString());


            
            var quotaLabel = document.getElementById("quotaReportLabel");
            quotaLabel.innerHTML = "You have a quota (dd.hh:mm) of " + showQuota(quota) + "<br>(" + showQuota(usedQuota) + " of pending jobs and " + showQuota((quota - usedQuota)) + " available)<br><br>Time now: " + timeAtServer.toString().split(' ', 5).join(' ') + " (SGT)";
            
            if(updateTimeInterval == null)
                updateTimeInterval = setInterval(updateTimeMain, 1000);
            
            if(jobsXML.getElementsByTagName("jobs").length == 0){
                //no jobs
                document.getElementById("jobsReportLabel").innerHTML = "Dear " + userName + ", you don't have any jobs!";
                
                document.getElementById("jobsNumLabel").innerHTML = "";
                document.getElementById("waitingJobsNumLabel").innerHTML = "";
                document.getElementById("runningJobsNumLabel").innerHTML = "";
                document.getElementById("terminatedJobsNumLabel").innerHTML = "";
            } else {
                document.getElementById("jobsReportLabel").innerHTML = "";
                
                document.getElementById("jobsNumLabel").innerHTML = "You have " + jobsXML.getElementsByTagName("jobs")[0].childNodes.length + " jobs in total";
                
                numOfWaitingJobs = 0;
                numOfRunningJobs = 0;
                numOfTerminatedJobs = 0;

                //if there are jobs, view them
                //header
                var trHeader = document.createElement("tr");

                var thJob = document.createElement("th");
                var thFile = document.createElement("th");
                //var thMote = document.createElement("th");
                var thExpand = document.createElement("th");
                var thEdit = document.createElement("th");
                var thSchedule = document.createElement("th");
                var thDelete = document.createElement("th");

                var txtJob = document.createTextNode("Job");
                var txtFile = document.createTextNode("File(s)");
                //var txtMote = document.createTextNode("Mote Type(s)");

                thExpand.classList.add("table_edit_links");
                thDelete.classList.add("table_edit_links");
                thEdit.classList.add("table_edit_links");
                thSchedule.classList.add("table_edit_links");

                thJob.appendChild(txtJob);
                thFile.appendChild(txtFile);
                //thMote.appendChild(txtMote);

                trHeader.appendChild(thJob);
                trHeader.appendChild(thFile);
                //trHeader.appendChild(thMote);
                trHeader.appendChild(thExpand);
                trHeader.appendChild(thEdit);
                trHeader.appendChild(thSchedule);
                trHeader.appendChild(thDelete);

                tableHeader.appendChild(trHeader);
                //body
                
                for(i = jobsXML.getElementsByTagName("jobs")[0].childNodes.length - 1; i >= 0 ; i--){
                    var trJob = document.createElement("tr");
                    
                    var jobID = jobsXML.getElementsByTagName("jobID")[i].childNodes[0].nodeValue;
                    
                    var jobName = jobsXML.getElementsByTagName("jobName")[i].childNodes[0].nodeValue;
                    
                    trJob.id = jobID + "@jobRow";

                    var tdJobName = document.createElement("td");
                    var tdFileName = document.createElement("td");
                    //var tdMoteType = document.createElement("td");

                    var tdExpand = document.createElement("td");
                    var tdDelete = document.createElement("td");
                    var tdEdit = document.createElement("td");
                    var tdSchedule = document.createElement("td");

                    var tdExpandDiv = document.createElement("div");
                    var tdDeleteDiv = document.createElement("div");
                    var tdEditDiv = document.createElement("div");
                    var tdScheduleDiv = document.createElement("div");

                    jQuery(tdExpandDiv).attr('title', "Show results");
                    jQuery(tdEditDiv).attr('title', "Edit job");
                    jQuery(tdDeleteDiv).attr('title', "Delete job");
                    jQuery(tdScheduleDiv).attr('title', "Schedule job");

                    tdExpandDiv.classList.add("table-expandable-arrow");
                    tdExpandDiv.id= jobID + "@tdExpandDiv@trJobResults";

                    tdEditDiv.classList.add("table-edit");
                    tdEditDiv.id= jobID + "@tdEditDiv";

                    tdDeleteDiv.classList.add("table-delete");
                    tdDeleteDiv.id= jobID + "@tdDeleteDiv";

                    tdScheduleDiv.classList.add("table-schedule");
                    tdScheduleDiv.id= jobID + "@tdScheduleDiv";

                    tdExpandDiv.onclick = expandClick;
                    tdEditDiv.onclick = editClick;
                    tdDeleteDiv.onclick = deleteClick;
                    tdScheduleDiv.onclick = scheduleJob;

                    var txtJobName = document.createTextNode(jobName);

                    var filesList = document.createElement("dl");
                    //var moteTypesList = document.createElement("ul");
                    //moteTypesList.id = jobID + "@moteTypesList";

                    var job = jobsXML.getElementsByTagName("job")[i];
                    var lastMoteTypeID = -1;

                    if(job.getElementsByTagName("files").length != 0){
                        var moteTypeArr = [];
                        moteTypeArr[0] = job.getElementsByTagName("moteTypeID")[0].childNodes[0].nodeValue;
                        for (var j = 1; j < job.getElementsByTagName("file").length; j++){
                            for(var k = 0; k < moteTypeArr.length; k++) {
                                if(moteTypeArr[k] == job.getElementsByTagName("moteTypeID")[j].childNodes[0].nodeValue)
                                    break;
                            }
                            if(k == moteTypeArr.length)
                                moteTypeArr[moteTypeArr.length] = job.getElementsByTagName("moteTypeID")[j].childNodes[0].nodeValue;
                        }
                        
                        for(var k = 0; k < moteTypeArr.length; k++) {
                            var moteTypeAdded = 0;
                            for (var j = 0; j < job.getElementsByTagName("file").length; j++){

                                /*var listitemMoteType = document.createElement("li");
                                listitemMoteType.id = job.getElementsByTagName("moteTypeID")[j].childNodes[0].nodeValue;
                                if(job.getElementsByTagName("moteTypeID")[j].childNodes[0].nodeValue != lastMoteTypeID) {
                                    lastMoteTypeID = job.getElementsByTagName("moteTypeID")[j].childNodes[0].nodeValue;
                                    var listitemDataMoteType = document.createTextNode(job.getElementsByTagName("moteTypeName")[j].childNodes[0].nodeValue);
                                    listitemMoteType.appendChild(listitemDataMoteType);
                                } else {
                                    var listitemDataMoteType = document.createTextNode("to be deleted");
                                    listitemMoteType.appendChild(listitemDataMoteType);
                                }

                                moteTypesList.appendChild(listitemMoteType);*/
                                if(moteTypeArr[k] == job.getElementsByTagName("moteTypeID")[j].childNodes[0].nodeValue) {
                                    if(moteTypeAdded == 0) {
                                        moteTypeAdded = 1;

                                        var listitemMoteType = document.createElement("dt");
                                        var listitemDataMoteType = document.createTextNode("Mote Type " + job.getElementsByTagName("moteTypeName")[j].childNodes[0].nodeValue + ":");
                                        listitemMoteType.appendChild(listitemDataMoteType);

                                        listitemMoteType.style.color = "black";

                                        filesList.appendChild(listitemMoteType);
                                    }

                                    var listitemFile = document.createElement("li");
                                    var listitemDataFile = document.createTextNode(job.getElementsByTagName("fileName")[j].childNodes[0].nodeValue);
                                    listitemFile.appendChild(listitemDataFile);

                                    filesList.appendChild(listitemFile);
                                }
                            }
                        }
                    }


                    // check if dcube
                    job_dcube = job.getElementsByTagName("dcube")[0].childNodes[0].nodeValue;
                    if(job_dcube != 0){
                        var listitemMoteType = document.createElement("dt");
                        var listitemDataMoteType = document.createTextNode("DCube:");
                        listitemMoteType.appendChild(listitemDataMoteType);
                        listitemMoteType.style.color = "black";
                        filesList.appendChild(listitemMoteType);

                        var listitemFile = document.createElement("li");
                        var listitemDataFile = document.createTextNode(job.getElementsByTagName("dcube_filename")[0].childNodes[0].nodeValue);
                        listitemFile.appendChild(listitemDataFile);

                        filesList.appendChild(listitemFile);
                    }


                    tdJobName.appendChild(txtJobName);
                    tdJobName.id = jobID + "@jobName";
                    tdFileName.appendChild(filesList);
                    //tdMoteType.appendChild(moteTypesList);
                    
                    tdJobName.style.width = "30%";
                    //tdFileName.style.textAlign = "left";

                    tdExpand.appendChild(tdExpandDiv);
                    tdDelete.appendChild(tdDeleteDiv);
                    tdEdit.appendChild(tdEditDiv);
                    tdSchedule.appendChild(tdScheduleDiv);

                    trJob.appendChild(tdJobName);
                    trJob.appendChild(tdFileName);
                    //trJob.appendChild(tdMoteType);
                    trJob.appendChild(tdExpand);
                    trJob.appendChild(tdEdit);
                    trJob.appendChild(tdSchedule);
                    trJob.appendChild(tdDelete);
                    
                    tableBody.appendChild(trJob);

                    var results = 0;
                    if(job.getElementsByTagName("result").length != 0){
                        var trJobResults = document.createElement("tr");
                        trJobResults.id = jobID + "@trJobResults";

                        var td = document.createElement("td");
                        td.setAttribute("colspan", "2");

                        var list = document.createElement("ol");
                        list.style.paddingTop = "5px";
                        list.style.paddingLeft = "10px";
                        list.style.color = "black";
                        list.id = jobID + "@resultsList";

                        for (j = 0; j < job.getElementsByTagName("result").length; j++){
                            var resultID = job.getElementsByTagName("resultID")[j].childNodes[0].nodeValue;
                            
                            var runtimeID = job.getElementsByTagName("runtimeID")[j].childNodes[0].nodeValue;
                            
                            var status = job.getElementsByTagName("status")[j].childNodes[0].nodeValue;
                            
                            var s = new Date(job.getElementsByTagName("start")[j].childNodes[0].nodeValue.replace(/-/g, "/"));

                            var e = new Date(job.getElementsByTagName("end")[j].childNodes[0].nodeValue.replace(/-/g, "/"));

                            var duration = (e.getTime() - s.getTime()) / (60*1000);
                            var durationSTR = countDown(e.getTime() - s.getTime());
                            
                            if(status == 2) {
                                
                                results++;
                            
                                var listitem = document.createElement("li");
                                listitem.style.paddingTop = "10px";
                                listitem.id = job.getElementsByTagName("resultID")[j].childNodes[0].nodeValue + "@listItemResult";

                                var listitemData1 = document.createTextNode("Run time: " + s.toString().split(' ', 5).join(' ') + " (SGT)");

                                var listitemData2 = document.createTextNode("Duration (dd.hh:mm): " + durationSTR);

                                var listitemDelete = document.createElement("a");
                                var txtlistitemDelete = document.createTextNode("Remove ");
                                listitemDelete.appendChild(txtlistitemDelete);

                                listitemDelete.style.cursor = "pointer";
                                listitemDelete.style.color = "blue";
                                listitemDelete.id = job.getElementsByTagName("resultID")[j].childNodes[0].nodeValue + "@removeResult@" + jobID;
                                listitemDelete.onclick = removeResult;

                                var listitemDownload = document.createElement("a");
                                var txtlistitemDownload = document.createTextNode(" Download");
                                listitemDownload.appendChild(txtlistitemDownload);

                                listitemDownload.style.cursor = "pointer";
                                listitemDownload.style.color = "blue";
                                listitemDownload.id = job.getElementsByTagName("resultID")[j].childNodes[0].nodeValue + "@" + jobName + "@" + (j+1);
                                listitemDownload.onclick = downloadResult;

                                listitem.appendChild(listitemData1);
                                listitem.appendChild(document.createElement("br"));
                                listitem.appendChild(listitemData2);
                                listitem.appendChild(document.createElement("br"));
                                listitem.appendChild(listitemDelete);
                                listitem.appendChild(listitemDownload);
                                list.appendChild(listitem);
                            }
                            
                            if(status == -1 || status == 0) {
                                var trJob = document.createElement("tr");
                                var tdJobName = document.createElement("td");
                                tdJobName.id = e;
                                var tdStartTime = document.createElement("td");
                                tdStartTime.id = s;
                                var tdDuration = document.createElement("td");
                                tdDuration.id = resultID + "@duration";

                                var tdDelete = document.createElement("td");
                                var tdDeleteDiv = document.createElement("div");
                                jQuery(tdDeleteDiv).attr('title', "Cancel schedule");

                                tdDeleteDiv.classList.add("table-delete");
                                tdDeleteDiv.onclick = cancelSchedule;

                                var txtJobName = document.createTextNode(jobName);
                                var txtDuration = document.createTextNode(durationSTR);
                                
                                tdJobName.appendChild(txtJobName);
                                tdJobName.style.width = "30%";
                                tdDuration.appendChild(txtDuration);
                                tdDelete.appendChild(tdDeleteDiv);

                                trJob.appendChild(tdJobName);
                            }
                            
                            var nw = vis.moment(timeAtServer).set('s', 0).toDate();
                            disable_cancel_dcube_job = 0;
                            if(status == -1) {
                                numOfWaitingJobs++;

                                if(nw.getTime() <= s.getTime()){
                                    var diff = (s.getTime() - nw.getTime()) / (60*1000);
                                    if (diff >= 1){
                                        var txtStartTime = document.createTextNode(countDown(s.getTime() - nw.getTime()));
                                    }
                                    else{
                                        if(job_dcube != 0){
                                            // disable cancel
                                            disable_cancel_dcube_job = 1;
                                        }
                                        var txtStartTime = document.createTextNode("Executing...");
                                    }
                                } else {
                                    if(job_dcube != 0){
                                        // disable cancel
                                        disable_cancel_dcube_job = 1;
                                    }
                                    var txtStartTime = document.createTextNode("Executing...");
                                }
                                
                                tdStartTime.appendChild(txtStartTime);

                                trJob.appendChild(tdStartTime);
                                trJob.appendChild(tdDuration);
                                trJob.appendChild(tdDelete);

                                //add waiting job
                                if(disable_cancel_dcube_job == 1)
                                    tdDeleteDiv.id = jobID + "@not";
                                else
                                    tdDeleteDiv.id = jobID + "@waitingJobRow@" + resultID + "@" + runtimeID + "@" + duration;
                                trJob.id = resultID + "@waitingJobRow";
                                waitingTableBody.appendChild(trJob);

                                //disable edit for this job
                                tdEditDiv.id = jobID + "@not";
                            }
                            
                            disable_cancel_dcube_job = 0;
                            if(status == 0) {
                                if(job_dcube != 0){
                                    // disable cancel
                                    disable_cancel_dcube_job = 1;
                                }
                                numOfRunningJobs++;
                                
                                if(nw.getTime() < e.getTime()){
                                    var diff = (e.getTime() - nw.getTime()) / (60*1000);
                                    if (diff >= 1)
                                        var txtStartTime = document.createTextNode(countDown(e.getTime() - nw.getTime()));
                                    else
                                        var txtStartTime = document.createTextNode("Less than 1 minute");
                                } else
                                    var txtStartTime = document.createTextNode("Less than 1 minute");

                                tdStartTime.appendChild(txtStartTime);

                                trJob.appendChild(tdStartTime);
                                trJob.appendChild(tdDuration);
                                trJob.appendChild(tdDelete);

                                //add running jobs
                                tdDeleteDiv.onclick = cancelSchedule;
                                if(disable_cancel_dcube_job == 1)
                                    tdDeleteDiv.id= jobID + "@not";
                                else
                                    tdDeleteDiv.id= jobID + "@runningJobRow@" + resultID + "@" + runtimeID + "@" + duration;
                                
                                trJob.id = resultID + "@runningJobRow";

                                runningTableBody.appendChild(trJob);
                            }

                            if(status == 3) {
                                numOfTerminatedJobs++;
                                //add terminated jobs
                                var trJob = document.createElement("tr");
                                var tdJobName = document.createElement("td");
                                var tdStartTime = document.createElement("td");
                                var tdDownload = document.createElement("td");

                                var tdDelete = document.createElement("td");
                                var tdDeleteDiv = document.createElement("div");
                                jQuery(tdDeleteDiv).attr('title', "Remove result");

                                tdDeleteDiv.classList.add("table-delete");
                                tdDeleteDiv.onclick = cancelSchedule;

                                var txtJobName = document.createTextNode(jobName);
                                var txtStartTime = document.createTextNode(s.toString().split(' ', 5).join(' ') + " (SGT)");

                                var listitemDownload = document.createElement("a");
                                var txtlistitemDownload = document.createTextNode(" Download");
                                listitemDownload.appendChild(txtlistitemDownload);

                                listitemDownload.style.cursor = "pointer";
                                listitemDownload.style.color = "blue";
                                listitemDownload.id = resultID + "@" + jobName + "@" + (j+1);
                                listitemDownload.onclick = downloadResult;
                                
                                tdJobName.appendChild(txtJobName);
                                tdStartTime.appendChild(txtStartTime);
                                tdDownload.appendChild(listitemDownload);
                    
                                tdJobName.style.width = "30%";

                                tdDelete.appendChild(tdDeleteDiv);

                                trJob.appendChild(tdJobName);
                                trJob.appendChild(tdStartTime);
                                trJob.appendChild(tdDownload);
                                trJob.appendChild(tdDelete);

                                tdDeleteDiv.onclick = removeResultTerminated;
                                tdDeleteDiv.id= jobID + "@terminatedJobRow@" + resultID;
                                
                                trJob.id = resultID + "@terminatedJobRow";

                                terminatedTableBody.appendChild(trJob);
                            }
                        }
                    }
                    
                    if(results > 0){
                        td.appendChild(document.createTextNode("Results:"));
                        td.appendChild(list);

                        trJobResults.appendChild(td);
                        tableBody.appendChild(trJobResults);

                        jQuery(trJobResults).hide();
                        jQuery(tdExpand).css("visibility", "visible");
                    }
                    else{
                        jQuery(tdExpand).css("visibility", "hidden");
                    }
                    
                    document.getElementById("waitingJobsNumLabel").innerHTML = "You have " + numOfWaitingJobs + " upcoming jobs";
                        
                    if(numOfWaitingJobs > 0)
                       document.getElementById("waiting-table-scroll").style.display = "block";
                    else
                        document.getElementById("waiting-table-scroll").style.display = "none";

                    document.getElementById("runningJobsNumLabel").innerHTML = "You have " + numOfRunningJobs + " running jobs";

                    if(numOfRunningJobs > 0) {
                        document.getElementById("running-table-scroll").style.display = "block";
                        document.getElementById("showMQTT").style.display = "block";
                    }
                    else {
                        document.getElementById("running-table-scroll").style.display = "none";
                        document.getElementById("showMQTT").style.display = "none";
                    }

                    document.getElementById("terminatedJobsNumLabel").innerHTML = "You have " + numOfTerminatedJobs + " failed jobs";

                    if(numOfTerminatedJobs > 0)
                       document.getElementById("terminated-table-scroll").style.display = "block";
                    else
                        document.getElementById("terminated-table-scroll").style.display = "none";
                }
            }
        }
    };
}

//expand
function expandClick(){
    var i = this.id.split("@");
    
    var element = document.getElementById(i[0] + '@' + i[2]);
    jQuery(element).toggle('slow');
    jQuery(this).toggleClass("up");
    
    if(i[2] == "trJobResults"){
        if(jQuery(this).attr('title') == "Show results")
            jQuery(this).attr('title', "Hide results");
        else
            jQuery(this).attr('title', "Show results");
    } else{
        if(jQuery(this).attr('title') == "Show details")
            jQuery(this).attr('title', "Hide details");
        else
            jQuery(this).attr('title', "Show details");
        
        var numOfExp = 0;
        var numOfCol = 0;
        var table = document.getElementById("usersTable");
        for(i = 0; i < ((table.rows.length - 2) / 2); i++){
            var exp = document.getElementById(i + "@tdExpandDiv@trMoteResults");
            if(jQuery(exp).attr('title') == "Hide details")
                numOfExp++;
            else
                numOfCol++;
        }
        
        if(numOfExp == ((table.rows.length - 2) / 2)) {
            var expAll = document.getElementById("expandAllUsersTable");
            jQuery(expAll).toggleClass("up");
            jQuery(expAll).attr('title', "Hide all details");
        }
        if(numOfCol == ((table.rows.length - 2) / 2)) {
            var expAll = document.getElementById("expandAllUsersTable");
            jQuery(expAll).toggleClass("up");
            jQuery(expAll).attr('title', "Show all details");
        }
    }
}

//expand all
function expandAllClick(){
    var table = document.getElementById("usersTable");
    
    for(i = 0; i < ((table.rows.length - 2) / 2); i++){
        var exp = document.getElementById(i + "@tdExpandDiv@trMoteResults");
        var element = document.getElementById(i + "@trMoteResults");
        jQuery(element).toggle('slow');
        jQuery(exp).toggleClass("up");
        
        if(jQuery(exp).attr('title') == "Show details")
            jQuery(exp).attr('title', "Hide details");
        else
            jQuery(exp).attr('title', "Show details");
    }
    
    jQuery(this).toggleClass("up");
    
    if(jQuery(this).attr('title') == "Show all details")
        jQuery(this).attr('title', "Hide all details");
    else
        jQuery(this).attr('title', "Show all details");
}

//delete
function deleteClick(){
    var result = confirm("Want to delete this job?");
    if (result) {
        document.getElementById("cancelScheduleModal").style.display = "block";
        var i = this.id.split("@");

        var jobInfo = new FormData();
        jobInfo.append('userID', userID);
        jobInfo.append('jobID', i[0]);
    
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/deleteJob.php', true);
        xhr.send(jobInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("cancelScheduleModal").style.display = "none";
                var flag = this.responseText;
                if(flag == 1){
                    /*document.getElementById(i[0] + "@jobRow").outerHTML = "";
                    var tableBody = document.getElementById("jobsTable").getElementsByTagName('tbody')[0];
                    if(tableBody.innerHTML == ""){
                        var addJobDiv = document.getElementById("addJobDiv");
                        addJobDiv.style.display = "none";

                        var tableHeader = document.getElementById("jobsTable").getElementsByTagName('thead')[0];
                        tableHeader.innerHTML = "";
                        
                        document.getElementById("jobsReportLabel").innerHTML = "Dear " + userName + ", you don't have any jobs!";
                    }*/
                    
                    refreshJobs();
                } else{
                    alert("Error occurred! Please try again later.");
                }
            }
        };
    }
}

//remove result
function removeResult(){
    var result = confirm("Want to delete this result?");
    if (result) {
        var i = this.id.split("@");

        var resultInfo = new FormData();
        resultInfo.append('userID', userID);
        resultInfo.append('resultID', i[0]);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/deleteResult.php', true);
        xhr.send(resultInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var flag = this.responseText;
                if(flag == 1){
                    document.getElementById(i[0] + "@listItemResult").outerHTML = "";
                    
                    var ul = document.getElementById(i[2] + "@resultsList");
                    if(ul.getElementsByTagName("li").length == 0) {
                        document.getElementById(i[2] + "@trJobResults").outerHTML = "";
                        var exp = document.getElementById(i[2] + "@tdExpandDiv@trJobResults");
                        jQuery(exp).css("visibility", "hidden");
                    }
                } else{
                    alert("Error occurred! Please try again later.");
                }
            }
        };
    }
}

function removeResultTerminated() {
    var result = confirm("Want to delete this result?");
    if (result) {
        var i = this.id.split("@");

        var resultInfo = new FormData();
        resultInfo.append('userID', userID);
        resultInfo.append('resultID', i[2]);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/deleteResult.php', true);
        xhr.send(resultInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var flag = this.responseText;
                if(flag == 1){
                    //delete from table
                    document.getElementById(i[2] + "@" + i[1]).outerHTML = "";

                    //update tables and number
                    var tableBody = document.getElementById("terminatedJobsTable").getElementsByTagName('tbody')[0];
                    if(tableBody.innerHTML == "")
                        document.getElementById("terminated-table-scroll").style.display = "none";

                    
                    numOfTerminatedJobs--;
                    document.getElementById("terminatedJobsNumLabel").innerHTML = "You have " + numOfTerminatedJobs + " failed jobs";
                } else{
                    alert("Error occurred! Please try again later.");
                }
            }
        };
    }
}

function isNumberKey(e) {
    var charCode = (e.charCode) ? e.which : e.keyCode;
    if((charCode >= 65 && charCode <= 90) || (charCode >= 97 && charCode <= 122) || (charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8 || charCode == 190 || charCode == 45 || charCode == 95)
       return true;
   return false;
}

function isNumberKeyUserID(e) {
    var charCode = (e.charCode) ? e.which : e.keyCode;
    if((charCode >= 65 && charCode <= 90) || (charCode >= 97 && charCode <= 122) || (charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8 || charCode == 190 || charCode == 64)
       return true;
   return false;
}

function isNumberDurationKey(e) {
    var charCode = (e.charCode) ? e.which : e.keyCode;
    if((charCode >= 48 && charCode <= 57))
       return true;
   return false;
}

function downloadResult() 
{
    var ifrm = document.getElementById('frame1');
    ifrm.src = "php/downloadResult.php?r="+this.id;
}

function cancelSchedule() {
    var i = this.id.split("@");
    if(i[1] == "not"){
        alert("Sorry, cannot cancel a running job with dcube!");
    } else{
        document.getElementById("cancelScheduleModal").style.display = "block";
    
        var jobScheduleInfo = new FormData();
        jobScheduleInfo.append('userID', userID);
        jobScheduleInfo.append('jobID', i[0]);
        jobScheduleInfo.append('resultID', i[2]);
        jobScheduleInfo.append('runtimeID', i[3]);
        
        //use str to delete from DB
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/cancelSchedule.php', true);
        xhr.send(jobScheduleInfo);
        xhr.onload = function () {
            if (xhr.status === 200) {
                if(this.responseText == 0){
                    alert("Error occurred! Please try again later.");
                } else{
                    //get if waiting or running, update number and tables
                    var duration = parseInt(i[4]);

                    //delete from table
                    document.getElementById(i[2] + "@" + i[1]).outerHTML = "";

                    //update tables and number
                    var tableBody = document.getElementById("waitingJobsTable").getElementsByTagName('tbody')[0];
                    if(tableBody.innerHTML == "")
                        document.getElementById("waiting-table-scroll").style.display = "none";

                    tableBody = document.getElementById("runningJobsTable").getElementsByTagName('tbody')[0];
                    if(tableBody.innerHTML == ""){
                        document.getElementById("running-table-scroll").style.display = "none";
                        document.getElementById("showMQTT").style.display = "none";
                    }

                    //console.log(duration);
                    usedQuota = usedQuota - duration;
                    var quotaLabel = document.getElementById("quotaReportLabel");
                    quotaLabel.innerHTML = "You have a quota (dd.hh:mm) of " + showQuota(quota) + "<br>(" + showQuota(usedQuota) + " of pending jobs and " + showQuota((quota - usedQuota)) + " available)<br><br>Time now: " + timeAtServer.toString().split(' ', 5).join(' ') + " (SGT)";

                    if(i[1] == "waitingJobRow"){
                        numOfWaitingJobs--;

                        document.getElementById("waitingJobsNumLabel").innerHTML = "You have " + numOfWaitingJobs + " upcoming jobs";
                    } else if(i[1] == "runningJobRow") {
                        numOfRunningJobs--;

                        document.getElementById("runningJobsNumLabel").innerHTML = "You have " + numOfRunningJobs + " running jobs";
                    }
                }
                document.getElementById("cancelScheduleModal").style.display = "none";
            }
        };
    }
}

function updateTimeMain() {
    var s = vis.moment(timeAtServer).get('s');
    timeAtServer = vis.moment(timeAtServer).set('s', s + 1).toDate();

    timeNowAtServer = timeAtServer;
    
    var quotaLabel = document.getElementById("quotaReportLabel");
    quotaLabel.innerHTML = "You have a quota (dd.hh:mm) of " + showQuota(quota) + "<br>(" + showQuota(usedQuota) + " of pending jobs and " + showQuota((quota - usedQuota)) + " available)<br><br>Time now: " + timeAtServer.toString().split(' ', 5).join(' ') + " (SGT)";

    var shouldRefreshW = 0;
    var shouldRefreshR = 0;

    var tableBody = document.getElementById("waitingJobsTable").getElementsByTagName('tbody')[0];
    var diff = 0;
    var s;
    var nw = vis.moment(timeAtServer).set('s', 0).toDate();
    for(var i = 0; i < tableBody.rows.length; i++) {
        s = new Date(tableBody.rows[i].cells[1].id);
        if(nw.getTime() <= s.getTime()){
            diff = (s.getTime() - nw.getTime()) / (60*1000);
            if (diff >= 1)
                tableBody.rows[i].cells[1].innerHTML = countDown(s.getTime() - nw.getTime());
            else
                tableBody.rows[i].cells[1].innerHTML = "Executing...";
        } else
            shouldRefreshW++;
    }

    tableBody = document.getElementById("runningJobsTable").getElementsByTagName('tbody')[0];
    diff = 0;
    var e;
    for(i = 0; i < tableBody.rows.length; i++) {
        s = new Date(tableBody.rows[i].cells[1].id);
        e = new Date(tableBody.rows[i].cells[0].id);
        if(nw.getTime() < e.getTime()){
            diff = (e.getTime() - nw.getTime()) / (60*1000);
            if (diff >= 1)
                tableBody.rows[i].cells[1].innerHTML = countDown(e.getTime() - nw.getTime());
            else
                tableBody.rows[i].cells[1].innerHTML = "Less than 1 minute";
        } else
            shouldRefreshR++;
    }

    if(shouldRefreshR > 0 && document.getElementById("jobsTableDiv").style.display == "block"){
        refreshJobs();
    } else if(shouldRefreshW > 0 && document.getElementById("jobsTableDiv").style.display == "block"){
        if(lastRefresh == null){
            lastRefresh = timeAtServer;
            refreshJobs();
        } else{
            var difference = (timeAtServer.getTime() - lastRefresh.getTime()) / (60*1000);
            if(difference >= 1) {
                lastRefresh = timeAtServer;
                refreshJobs();
            }
        }
    }
}

function connectMQTT() {
    var cont = document.getElementById("mqttContainer");
    cont.innerHTML = "";

    document.getElementById("publish_msg").value = "";
    document.getElementById("publish_moteID").value = "";

    // Create a client instance: Broker, Port, Websocket Path, Client ID 1884, 8885
    client = new Paho.MQTT.Client("indriya.comp.nus.edu.sg", Number(25), "", "");
    // Connect the client, with a Username and Password
    client.connect({
	    onSuccess: onConnect, 
	    userName : userID,
	    password : mqtt_passw,
        useSSL: true
    });
    
}

// Called when the connection is made
function onConnect(){
    //open modal
    document.getElementById("publish").style.display = "block";
    var modal = document.getElementById('mqttModal');
    modal.style.display = "block";

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            client.disconnect();
            modal.style.display = "none";
        }
    }

    client.subscribe("#");
    
    //append message to modal container
    var cont = document.getElementById("mqttContainer");
    client.onMessageArrived = function (message) {
        cont.append(message.payloadString);
    }
}

//modal close, client.disconnect();
function spanMQTTClick() {
    client.disconnect();
    document.getElementById("mqttModal").style.display = "none";
}


// Publish a Message
function publishToMQTT(){
    var publishMsg = document.getElementById("publish_msg").value;
    var publishMoteID = document.getElementById("publish_moteID").value;
    if(publishMsg == "" || publishMsg == null || !publishMsg.replace(/\s/g, '').length || publishMoteID == "" || publishMoteID == null || !publishMoteID.replace(/\s/g, '').length) {
        alert("Kindly, enter the message!");
    } else {
        var message = new Paho.MQTT.Message(publishMsg);
        message.destinationName = userID + "/pull/" + publishMoteID;
        message.qos = 0;

        client.send(message);
    }
}

function spanMQTTPublishClick(){
    document.getElementById("publish").style.display = "none";
}

function countDown(seconds){
    seconds = seconds / 1000;
    var days        = Math.floor(seconds/24/60/60);
    var hoursLeft   = Math.floor((seconds) - (days*86400));
    var hours       = Math.floor(hoursLeft/3600);
    var minutesLeft = Math.floor((hoursLeft) - (hours*3600));
    var minutes     = Math.floor(minutesLeft/60);

    days = ("0" + days).slice(-2);
    hours = ("0" + hours).slice(-2);
    minutes = ("0" + minutes).slice(-2);

    return days + "." + hours + ":" + minutes;
}

function showQuota(minutes){
    seconds = minutes * 60;
    var days        = Math.floor(seconds/24/60/60);
    var hoursLeft   = Math.floor((seconds) - (days*86400));
    var hours       = Math.floor(hoursLeft/3600);
    var minutesLeft = Math.floor((hoursLeft) - (hours*3600));
    var minutes     = Math.floor(minutesLeft/60);

    days = ("0" + days).slice(-2);
    hours = ("0" + hours).slice(-2);
    minutes = ("0" + minutes).slice(-2);

    return days + "." + hours + ":" + minutes;
}

function showAlumnusModal(){
    window.onclick = function(event) {
        var modal = document.getElementById("alumnusModal");
        if (event.target == modal) {            
            modal.style.display = "none";
        }
    }
    document.getElementById("alumnusModal").style.display = "block";
}

function spanAlumnusClick(){
    document.getElementById("alumnusModal").style.display = "none";
}
