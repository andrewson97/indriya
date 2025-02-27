var refreshInterval2;

var timeNowAtServer2;

var modalStr;

var timeLabelStr;

// When the user clicks the button, open the modal 
function showResourcesState() {
    
    var associationMoteTypesList = document.getElementById("associationMoteTypesList");
    
    if(associationMoteTypesList.length > 1)
        var moteTypeID = "all";
    else
        var moteTypeID = associationMoteTypesList.options[associationMoteTypesList.selectedIndex].value;
    
    var containerStr = "resourcesStateVisualization";
    modalStr = "resourcesStateModal";
    timeLabelStr = "timeNowLabelResourcesState";
    imgStr = "loadingResourcesState";
    var infoDiv = "resourcesStateInfoModal";

    refreshScheduleJob2(infoDiv, modalStr, imgStr, containerStr, timeLabelStr, moteTypeID, 0);
}

function refreshScheduleJob2(infoDiv, recModalStr, imgStr, containerStr, timeLabel, moteTypeID, allTime) {
    modalStr = recModalStr;
    timeLabelStr = timeLabel;
    //get all motetypes usage from db and show it
    var moteTypeInfo = new FormData();
    moteTypeInfo.append('userID', userID);
    moteTypeInfo.append('moteTypeID', moteTypeID);
    moteTypeInfo.append('allTime', allTime);

    document.getElementById(imgStr).style.display = "block";
    document.getElementById(containerStr).style.display = "none";
    document.getElementById(infoDiv).style.display = "none";
    document.getElementById(modalStr).style.display = "block";

    if(refreshInterval2 == null)
        refreshInterval2 = setInterval(updateTime2, 1000);

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        var modal = document.getElementById(modalStr);
        if (event.target == modal) {
            clearInterval(refreshInterval2);
            refreshInterval2 = null;
            
            modal.style.display = "none";
        }
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/getResourcesState.php', true);
    xhr.send(moteTypeInfo);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var xmlDoc = xhr.responseXML;
            
            // timeNowAtServer2 = new Date(xmlDoc.getElementsByTagName("timeNowAtServer")[0].childNodes[0].nodeValue.replace(/-/g, "/"));
            parts = xmlDoc.getElementsByTagName("timeNowAtServer")[0].childNodes[0].nodeValue.split(".");
            timeNowAtServer2 = new Date(Date.UTC(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]));

            var timezone = +8;
            var offset = (timeNowAtServer2.getTimezoneOffset() + (timezone * 60)) * 60 * 1000;
            // Use the timestamp and offset as necessary to calculate min/sec etc, i.e. for countdowns.
            var timestamp = timeNowAtServer2.getTime() + offset,
                seconds = Math.floor(timestamp / 1000) % 60,
                minutes = Math.floor(timestamp / 1000 / 60) % 60,
                hours   = Math.floor(timestamp / 1000 / 60 / 60);

            // Or update the timestamp to reflect the timezone offset.
            timeNowAtServer2.setTime(timeNowAtServer2.getTime() + offset);
            // Then Output dates and times using the normal methods.
            var date = timeNowAtServer2.getDate(),
                hour = timeNowAtServer2.getHours();

            timeNowAtServer = timeNowAtServer2;
            
            document.getElementById(timeLabelStr).innerHTML = "Time now: " + timeNowAtServer2.toString().split(' ', 5).join(' ') + " (SGT)";
            
            var container = document.getElementById(containerStr);
            container.innerHTML = "";
            
            var minDate = timeNowAtServer2;
            if(allTime == 0) {
                var maxDate = new Date(minDate.getTime() + 2*24*60*60*1000);
                // Configuration for the Timeline
                var options = {
                    // moment: function (date) {
                    //     return vis.moment(date).utcOffset('+08:00');
                    // },
                    
                    min: minDate,
                    max: maxDate,
                    start: minDate,
                    end: maxDate,
                    
                    //max zoom out - 6 hrs
                    zoomMax: 1000 * 60 * 60 * 6,
                    //max zoom in - 1 hr
                    zoomMin: 1000 * 60 * 60 * 1,
                    
                    //timeAxis: {scale: 'minute', step: 30},
                    
                    stack: false,
                    
                    width: '100%',
                    
                    height: '250px',
                    
                    showTooltips: true,
                    
                    moveable: true,
                    zoomable: true,

                    verticalScroll: true,
                    zoomKey: 'ctrlKey',
                    maxHeight: "100%",
                };
            } else {
                var firstDay = new Date(minDate.getFullYear(), minDate.getMonth(), 1);
                var lastDay = new Date(minDate.getFullYear(), minDate.getMonth() + 1, 1);
                if(userAdmin){
                    var options = {
                        editable: {
                            remove: true,
                        },
                        onRemove: jobRemove,

                        min: firstDay,
                        max: lastDay,
                        start: firstDay,
                        end: lastDay,

                        //max zoom out - 2 days
                        zoomMax: 1000 * 60 * 60 * 24 * 2,
                        //max zoom in - 1 hr
                        zoomMin: 1000 * 60 * 60 * 1,
                        
                        //timeAxis: {scale: 'minute', step: 60},
                        
                        stack: false,
                        
                        width: '100%',
                        
                        height: '250px',
                        
                        showTooltips: true,
                        
                        moveable: true,
                        zoomable: true,

                        verticalScroll: true,
                        zoomKey: 'ctrlKey',
                        maxHeight: "100%",

                        moment: function (date) {
                            return vis.moment(date).utcOffset('+08:00');
                        }
                    };
                } else{
                    var options = {
                        min: firstDay,
                        max: lastDay,
                        start: firstDay,
                        end: lastDay,

                        //max zoom out - 2 days
                        zoomMax: 1000 * 60 * 60 * 24 * 2,
                        //max zoom in - 1 hr
                        zoomMin: 1000 * 60 * 60 * 1,
                        
                        //timeAxis: {scale: 'minute', step: 60},
                        
                        stack: false,
                        
                        width: '100%',
                        
                        height: '250px',
                        
                        showTooltips: true,
                        
                        moveable: true,
                        zoomable: true,

                        verticalScroll: true,
                        zoomKey: 'ctrlKey',
                        maxHeight: "100%",

                        moment: function (date) {
                            return vis.moment(date).utcOffset('+08:00');
                        }
                    };
                }
            }
            
            //fill in groups from XML file
            for(var i = 0; i < xmlDoc.getElementsByTagName("moteTypes")[0].childNodes.length; i++) {
                
                var moteTypeID = xmlDoc.getElementsByTagName("moteTypeID")[i].childNodes[0].nodeValue;

                var moteTypeName = xmlDoc.getElementsByTagName("moteTypeName")[i].childNodes[0].nodeValue;
                
                var Groups = new vis.DataSet([]);
                var items2 = new vis.DataSet([]);
                
                //fill in clusters Group from XML file
                for(var j = 0; j < xmlDoc.getElementsByTagName("clusters")[0].childNodes.length; j++) {
                    var clusterID = xmlDoc.getElementsByTagName("clusterID")[j].childNodes[0].nodeValue;

                    var clusterName = xmlDoc.getElementsByTagName("clusterName")[j].childNodes[0].nodeValue;
                    
                    var motesIDsArr = [];
                    var c = 0;
                    for(var k = 0; k < xmlDoc.getElementsByTagName("motes")[0].childNodes.length; k++) {
                        var moteID = xmlDoc.getElementsByTagName("moteID")[k].childNodes[0].nodeValue;
                        
                        var virtual_id = xmlDoc.getElementsByTagName("virtual_id")[k].childNodes[0].nodeValue;
                        
                        var status = xmlDoc.getElementsByTagName("status")[k].childNodes[0].nodeValue;
                        
                        var clusters_clusterID = xmlDoc.getElementsByTagName("clusters_clusterID")[k].childNodes[0].nodeValue;
                        
                        var moteTypes_moteTypeID = xmlDoc.getElementsByTagName("moteTypes_moteTypeID")[k].childNodes[0].nodeValue;
                        
                        if(status == 1 && clusterID == clusters_clusterID && moteTypeID == moteTypes_moteTypeID) {
                            Groups.add({id: 'mote' + moteID, content: 'Mote ' + virtual_id});
                            
                            motesIDsArr[c] = 'mote' + moteID;
                            c++;
                        }
                    }

                    if(motesIDsArr != null && motesIDsArr.length != 0) {
                        Groups.add({id: 'cluster' + clusterID, content: clusterName, nestedGroups: motesIDsArr, showNested: true});
                    }
                }
                
                var counter = 1;
                //fill in items, busy slots
                for(var j = 0; j < xmlDoc.getElementsByTagName("busy")[0].childNodes.length; j++) {
                    var file = xmlDoc.getElementsByTagName("file")[j];
                    var addClustersArr = [];
                    var c = 0;
                    for(var k = 0; k < file.getElementsByTagName("moteID").length; k++) {
                        var moteID = file.getElementsByTagName("moteID")[k].childNodes[0].nodeValue;
                        var clusterID = file.getElementsByTagName("clusters_clusterID")[k].childNodes[0].nodeValue;
                        if(moteTypeID == file.getElementsByTagName("moteTypeID")[k].childNodes[0].nodeValue) {
                            for(var l = 0; l < file.getElementsByTagName("start").length; l++) {
                                var s = file.getElementsByTagName("start")[l].childNodes[0].nodeValue;
                                var e = file.getElementsByTagName("end")[l].childNodes[0].nodeValue;

                                if(userAdmin && (new Date(e)) > minDate){
                                    var jobID = file.getElementsByTagName("jobs_jobID")[l].childNodes[0].nodeValue;
                                    var resultID = file.getElementsByTagName("result_resultID")[l].childNodes[0].nodeValue;
                                    var runtimeID = file.getElementsByTagName("runtimeID")[l].childNodes[0].nodeValue;

                                    if(vis.moment(s).toDate() > vis.moment(timeNowAtServer2).toDate())
                                        items2.add({id: counter, group: 'mote' + moteID, content: jobID + '@' + resultID + '@' + runtimeID, start: s, end: e, className: 'red', editable: true, selectable: true, type: 'range', title: 'Busy'});
                                    else
                                        items2.add({id: counter, group: 'mote' + moteID, content: jobID + '@' + resultID + '@' + runtimeID, start: s, end: e, className: 'red', editable: false, selectable: false, type: 'range', title: 'Busy'});
                                }
                                else
                                    items2.add({id: counter, group: 'mote' + moteID, content: '', start: s, end: e, className: 'red', editable: false, selectable: false, type: 'range', title: 'Busy'});
                                counter++;

                                if(addClustersArr.indexOf(clusterID) == -1) {
                                    items2.add({id: counter, group: 'cluster' + clusterID, content: '', start: s, end: e, className: 'red', editable: false, selectable: false, type: 'range', title: 'Busy'});
                                    counter++;
                                }
                            }
                            if(addClustersArr.indexOf(clusterID) == -1) {
                                addClustersArr[c] = clusterID;
                                c++;
                            }
                        }
                    }
                }
                
                var cont = document.createElement("div");
                
                var moteTypeLabel = document.createElement("label");
                moteTypeLabel.classList.add("text1");
                moteTypeLabel.style.color = "#37637f";
                moteTypeLabel.style.float = "left";
                moteTypeLabel.appendChild(document.createTextNode(moteTypeName));
                container.appendChild(moteTypeLabel);
                
                container.appendChild(cont);
                
                var timeline2 = new vis.Timeline(cont, items2, Groups, options);
                
                moveToStart(timeline2);
            }

            document.getElementById(imgStr).style.display = "none";
            document.getElementById(containerStr).style.display = "block";
            document.getElementById(infoDiv).style.display = "block";
        }
    };
}

function jobRemove(item, callback) {
    // remove job
    var i = item.content.split("@");

    document.getElementById("cancelScheduleModal").style.display = "block";
    
    var jobScheduleInfo = new FormData();
    jobScheduleInfo.append('userID', userID);
    jobScheduleInfo.append('jobID', i[0]);
    jobScheduleInfo.append('resultID', i[1]);
    jobScheduleInfo.append('runtimeID', i[2]);
    
    //use str to delete from DB
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/cancelSchedule.php', true);
    xhr.send(jobScheduleInfo);
    xhr.onload = function () {
        if (xhr.status === 200) {
            if(this.responseText == 0){
                alert("Error occurred! Please try again later.");
            } else{
                // click button
                clearInterval(refreshInterval2);
                refreshInterval2 = null;
                document.getElementById(modalStr).style.display = "none";
                alert("Job canceled successfully");
            }
            document.getElementById("cancelScheduleModal").style.display = "none";
        }
    };
}

// When the user clicks on <span> (x), close the modal
function spanClick() {
    clearInterval(refreshInterval2);
    refreshInterval2 = null;
    
    document.getElementById(modalStr).style.display = "none";
}

function updateTime2() {
    var s = vis.moment(timeNowAtServer2).get('s');
    timeNowAtServer2 = vis.moment(timeNowAtServer2).set('s', s + 1).toDate();

    timeNowAtServer = timeNowAtServer2;
    
    document.getElementById(timeLabelStr).innerHTML = "Time now: " + timeNowAtServer2.toString().split(' ', 5).join(' ') + " (SGT)";
}

function moveToStart(obj) {
    setTimeout( function() {obj.moveTo(timeNowAtServer2);}, 1000);
}