<?php
	#get db pass
	include "php/databaseHandler.php";
	$indriya_db_pass = getpsw();

	$uploadPath = "/var/www/files";

	#get user id
	$request_user = str_replace("'", '"', $_POST['user']);
	$request_user_Arr = json_decode($request_user, true);
	$user_id = $request_user_Arr['user_id'];

	#check if user exists in db
	$con = mysqli_connect('localhost','root',$indriya_db_pass);
	if (!$con) {
	    $response = array(
			'status' => 'ERROR',
			"message" => "Database error, please contact the admin"
		);
	} else {
		mysqli_select_db($con,"indriyaDB");
		$sql="SELECT * FROM users WHERE userID = '".$user_id."'";
		$result = mysqli_query($con,$sql);

		if (!mysqli_num_rows($result)) {
			$response = array(
				'status' => 'ERROR',
				"message" => "User ".user_id." doesn't exist"
			);
		} else {
			//get user's password from database
			$row = mysqli_fetch_array($result);
			$passw = $row['mqtt_passw'];
			$quota = $row['quota'];
			$oldRunTime = $row['runningTime'];									    
			#user has no passw!
			if($passw == NULL) {
				$response = array(
					'status' => 'ERROR',
					"message" => "User " . $user_id . " password is not set!"
				);
			} else {
				$enc_job_config = $_POST['enc_job_config'];
				$job_config = mcrypt_decrypt(MCRYPT_BLOWFISH, $passw, base64_decode($enc_job_config), MCRYPT_MODE_ECB);
				$job_config = trim($job_config, "@");


				$request = str_replace("'", '"', $job_config);
				$requestArr = json_decode($request, true);

				if($requestArr == null) {
					//bad json request ERROR
					$response = array(
						'status' => 'ERROR',
						"message" => "Bad Request"
					);
				} else {
					//check on user match
					if($user_id != $requestArr['user_id']) {
						$response = array(
								'status' => 'ERROR',
								"message" => "User deosn't match!"
							);
					} else {
						//get received hash and remove it
						$rec_hash = $requestArr['hash'];
						unset($requestArr['hash']);
						
						//calculate hash and compare
						$algo = 'ripemd160';
						$hash = hash($algo, json_encode($requestArr));
						if($rec_hash != $hash) {
							$response = array(
								'status' => 'ERROR',
								"message" => "Hash deosn't match!"
							);
						} else {
							//check on token
							$token = $requestArr['token'];
							$timestamp = date('Y-m-d H:i:s', $token);
							$nw = date('Y-m-d H:i:s');
							$received_date_time = strtotime($timestamp);
							$now_time = strtotime($nw);
							//get time difference in seconds
							$time_diff = round(abs($now_time - $received_date_time), 2);
							if($time_diff >= 60) {
								//90 seconds difference is not allowed
								$response = array(
									'status' => 'ERROR',
									"message" => "Delayed request! Request sent time: " . $timestamp . ", Request received time: " . $nw
								);
							} else {
								//check on files associated with motes if uploaded
								$filesArr = $requestArr['file_config'];
								$files_not_uploaded_arr = array();
								for($i = 0; $i < sizeof($filesArr); $i++){
									//get file name
									$file_name = $filesArr[$i]['binary_file'];
									if($_FILES[$file_name] == null){
										$files_not_uploaded_arr[] = $file_name;
									}
								}

								//if not uploaded, send file is not uploaded ERROR
								if(sizeof($files_not_uploaded_arr) != 0) {
									$response = array(
										'status' => 'ERROR',
										"message" => "File(s) not attached!",
										"File_list" => $files_not_uploaded_arr
									);
								} else {
									//get all motes for the selected job
									$jobMotes = array();
									$motes_not_exist = array();
									for($i = 0; $i < sizeof($filesArr); $i++){
										$mote_list = $filesArr[$i]['mote_list'];
										for($j = 0; $j < sizeof($mote_list); $j++){
											$sql = "select * from motes inner join moteTypes on motes.virtual_id = '".$mote_list[$j]."' and moteTypes.moteTypeName = '".$filesArr[$i]['type']."'";
											$result = mysqli_query($con,$sql);
											$row = mysqli_fetch_array($result);

											//check if mote exist
											if($row['moteID'] == NULL){
												$motes_not_exist[] = $mote_list[$j];
											}
											else{
												//check on mote status
												///////// change to 1 ///////////
												if($row['status'] == -1)
													$jobMotes[] = $row['moteID'];
												else
													$motes_not_exist[] = $mote_list[$j];
											}
										}
									}

									//empty list of motes
									if(sizeof($jobMotes) == 0){
										$response = array(
											'status' => 'ERROR',
											"message" => "No motes to schedule job",
											"not_exist_mote_list" => $motes_not_exist
										);
									} else {
										//update status and runtime for this user
										$sql="SELECT * FROM jobs WHERE users_userID = '".$user_id."'";
										$result = mysqli_query($con,$sql);

										$totalRunTime = 0;
										if (mysqli_num_rows($result)) {
											while($row = mysqli_fetch_array($result)) {
												//add results of each job
									            $sql="SELECT * FROM results WHERE jobs_jobID = '".$row['jobID']."'";
									            $jobResults = mysqli_query($con,$sql);
									            if (mysqli_num_rows($jobResults)){
									                while($resultRow = mysqli_fetch_array($jobResults)){
									                    $status = $resultRow['status'];
									                    
									                    //get start, end from runtimes
									                    $sql="select * from runtimes where runtimeID = '".$resultRow['runtimes_runtimeID']."'";
									                    $resultU = mysqli_query($con,$sql);
									                    $runtimeRow = mysqli_fetch_array($resultU);
									                    
									                    //if status = -1, check on start time ==>Waiting
									                    if($status == -1) {
									                        if($runtimeRow['start'] <= date('Y-m-d H:i:s')) {
									                            $sql="UPDATE results SET status = '0' WHERE resultID = '".$resultRow['resultID']."'";
									                            $resultU = mysqli_query($con,$sql);
									                            
									                            $status = 0;
									                        }
									                    }
									                    
									                    //if status = 0, check on end time ==>Running
									                    if($status == 0) {
									                        if($runtimeRow['end'] <= date('Y-m-d H:i:s')) {
									                            //job is ended, status = 1
									                            $sql="UPDATE results SET status = '1' WHERE resultID = '".$resultRow['resultID']."'";
									                            $resultU = mysqli_query($con,$sql);
									                            
									                            $status = 1;
									                            
									                            //increament run time for this user
									                            $s = strtotime($runtimeRow['start']);
									                            $e = strtotime($runtimeRow['end']);
									                            $totalRunTime +=  round(abs($e - $s) / 60,2);
									                            
									                            //increament run time for mote type
									                            //get mote types for the job running this result
									                            $sql="select * from moteTypes inner join files on files.moteTypes_moteTypeID = moteTypes.moteTypeID inner join jobs on jobs.jobID = files.jobs_jobID and jobs.jobID = '".$row['jobID']."'";
									                            $resultMoteType = mysqli_query($con,$sql);
									                            while($moteTypeRow = mysqli_fetch_array($resultMoteType)){
									                                //get mote type usage
									                                $oldRunTime = $moteTypeRow['runningTime'];
									                                
									                                $total = $oldRunTime + round(abs($e - $s) / 60,2);
									                                
									                                $sql="UPDATE moteTypes SET runningTime = '".$total."' WHERE moteTypeID = '".$moteTypeRow['moteTypeID']."'";
									                                $resultU = mysqli_query($con,$sql);
									                            }
									                        }
									                    }
									                }
									            }
											}
										}

										if($totalRunTime > 0) {
										    $totalRunTime += $oldRunTime;
										    
										    //update running time for user
										    $sql="UPDATE users SET runningTime = '".$totalRunTime."' WHERE userID = '".$user_id."'";
										    $resultU = mysqli_query($con,$sql);
										}

										//get user's used quota
										$sql="SELECT * FROM runtimes inner join results on results.runtimes_runtimeID = runtimes.runtimeID inner join jobs on jobs.jobID = results.jobs_jobID and jobs.users_userID = '".$user_id."'";
										$usedQuota = 0;
										$resultR = mysqli_query($con,$sql);
										if (mysqli_num_rows($resultR)) {
										    while($rowR = mysqli_fetch_array($resultR)) {
										    	//check if status < 1
										        if($rowR['status'] < 1){
										            $s = strtotime($rowR['start']);
										            $e = strtotime($rowR['end']);
										            $usedQuota +=  round(abs($e - $s) / 60,2);
										        }
										    }
										}

										//get new job duration
										//get start and end for this job
										$s = $requestArr['time']['from'];
										$e = $requestArr['time']['to'];

										$temp = date('Y-m-d H:i:s', $s);
										$min = date('i', $s);
										$sec = date('s', $s);
										$c = (int)($min / 5);
										$temp = date('Y-m-d H:i:s',strtotime(-$min.' minutes '.-$sec.' seconds', strtotime($temp)));
										$s = strtotime(+($c * 5).' minutes', strtotime($temp));

										$temp = date('Y-m-d H:i:s', $e);
										$min = date('i', $e);
										$sec = date('s', $e);
										$c = (int)($min / 5);
										$temp = date('Y-m-d H:i:s',strtotime(-$min.' minutes '.-$sec.' seconds', strtotime($temp)));
										$e = strtotime(+($c * 5).' minutes', strtotime($temp));

										//check if used quota exceeds assigned quota
										$usedQuota_job =  $usedQuota + round(abs($e - $s) / 60,2);
										if($usedQuota_job > (int) $quota) {
												$response = array(
												'status' => 'ERROR',
												"message" => "Cannot schedule this job because it exceeds your quota!",
												"quota" => $quota,
												"used_quota" => $usedQuota.'',
												"job_duration" => round(abs($e - $s) / 60,2) .''
											);
										} else {
											//check on time slot
											//if busy by other users, send busy time slot ERROR
											$overlapping = 0;
											$sql="SELECT * FROM jobs";
											$result = mysqli_query($con,$sql);
											while($row = mysqli_fetch_array($result)) {
												if($row['users_userID'] != $user_id) {
													//get all motes for each job
												    $sql="select file_mote.motes_moteID from file_mote inner join files on files.fileID = file_mote.files_fileID inner join jobs on jobs.jobID = files.jobs_jobID and jobs.jobID = '".$row['jobID']."'";
												    $resultMotes = mysqli_query($con,$sql);
												    while($motesRow = mysqli_fetch_array($resultMotes)){
												        if(array_search($motesRow['motes_moteID'], $jobMotes) !== false) {
												            //get all run time for this job and go to next job
												            $sql="select * from runtimes where jobs_jobID = '".$row['jobID']."'";
												            $resultTime = mysqli_query($con,$sql);
												            if (mysqli_num_rows($resultTime)) {
												                $overlapping = 0;
												                while($timeRow = mysqli_fetch_array($resultTime)) {
												                    if((date('Y-m-d H:i:s', $s) < $timeRow['end']) && (date('Y-m-d H:i:s', $e) > $timeRow['start']))
												                        $overlapping++;
												                }
												            }
												            break;
												        }
												    }
												    if($overlapping != 0)
												        break;
												}
											}

											if($overlapping != 0) {
												$response = array(
													'status' => 'ERROR',
													"message" => "Busy time slot"
												);
											} else {
												//insert in the database, upload files, and call schedule api
												//insert in the database
												//insert job
												$jobName = 'job';
												$sql="INSERT INTO jobs (jobName, users_userID) VALUES('".$jobName."', '".$user_id."')";
									            $result = mysqli_query($con,$sql);
									            $jobID = $con->insert_id;

												//insert files
												$unsupportedFiles = array();
												for($i = 0; $i < sizeof($filesArr); $i++){
													//get file name
													$file_name = $filesArr[$i]['binary_file'];

													//get mote type id
													$sql = "SELECT * FROM moteTypes where moteTypeName = '".$filesArr[$i]['type']."'";
													$result = mysqli_query($con,$sql);
													$row = mysqli_fetch_array($result);
													$MoteTypeID = $row['moteTypeID'];

													//insert file in db
													$sql="INSERT INTO files (fileName, jobs_jobID, moteTypes_moteTypeID) VALUES('".$_FILES[$file_name]['name']."', '".$jobID."', '".$MoteTypeID."')";
								                    $result = mysqli_query($con,$sql);
								                    $fileID = $con->insert_id;

								                    //insert file-motes relation for the last inserted file
								                    $moteArr = array();
								                    $mote_list = $filesArr[$i]['mote_list'];
													for($j = 0; $j < sizeof($mote_list); $j++){
														$sql = "select * from motes inner join moteTypes on motes.virtual_id = '".$mote_list[$j]."' and moteTypes.moteTypeName = '".$filesArr[$i]['type']."'";
														$result = mysqli_query($con,$sql);
														$row = mysqli_fetch_array($result);

														//check if mote exist
														if($row['moteID'] != NULL && $row['status'] == -1){
															$moteArr[] = $row['moteID'];
														}
													}
								                    foreach($moteArr as $key => $moteID) {
								                        $sql="INSERT INTO file_mote (files_fileID, motes_moteID) VALUES('".$fileID."', '".$moteID."')";
								                        $result = mysqli_query($con,$sql);
								                    }

													//upload all files
													//get file extenstion
													$info = new SplFileInfo($_FILES[$file_name]['name']);
													$result = move_uploaded_file($_FILES[$file_name]['tmp_name'], $uploadPath.'/'.$fileID.'.'.$info->getExtension());

													//check on files format
													$url = 'http://localhost:5000/check_binary';
													$obj = array(
									                    "binary_file" => $fileID. '.'.$info->getExtension(),
									                    "type" => $filesArr[$i]['type']
									                );
									                $opts = array('http' =>
									                    array(
									                        'method'  => 'POST',
									                        'header'  => 'Content-type: application/json',
									                        'content' => json_encode($obj)
									                    )
									                );
									                $context  = stream_context_create($opts);
									                $result = file_get_contents($url, false, $context);
									                $result = str_replace("'", '"', $result);
									                $response = json_decode($result, true);

									                if($response['result'] == 0) {
									                	if($unsupportedFiles[$filesArr[$i]['type']] == NULL)
									                        $unsupportedFiles[$filesArr[$i]['type']] = $_FILES[$file_name]['name'];
									                    else
									                        $unsupportedFiles[$filesArr[$i]['type']] = $unsupportedFiles[$filesArr[$i]['type']] . ', ' . $_FILES[$file_name]['name'];
									                }
												}

												//check if there are unsupported files
												if(sizeof($unsupportedFiles) != 0){
													//delete job and files
										            $sql="SELECT * FROM files WHERE jobs_jobID = '".$jobID."'";
										            $jobFiles = mysqli_query($con,$sql);
										            if (mysqli_num_rows($jobFiles)){
										                while($filesRow = mysqli_fetch_array($jobFiles)){            
										                    $files = glob($uploadPath.'/'.$filesRow['fileID'].'.*');
										                    $file = $files[0];
										                    if (is_file($file)){
										                        if(is_writable($file))
										                        {
										                            $ok = unlink($file);
										                        }
										                    }
										                }
										            }
										            $sql="DELETE FROM jobs WHERE jobID = '".$jobID."'";
										            $result = mysqli_query($con,$sql);

										            $response = array(
														'status' => 'ERROR',
														"message" => "Unsupported file format!",
														"unsupported_file_list" => $unsupportedFiles
													);
												} else {
													//add time to DB
												    $sql="INSERT INTO runtimes (start, end, jobs_jobID) VALUES('".date('Y-m-d H:i:s', $s)."', '".date('Y-m-d H:i:s', $e)."', '".$jobID."')";
												    $result = mysqli_query($con,$sql);
												    $runtimeID = $con->insert_id;

												    //add result to DB
												    $sql="INSERT INTO results (status, jobs_jobID, runtimes_runtimeID) VALUES('-1', '".$jobID."', '".$runtimeID."')";
												    $result = mysqli_query($con,$sql);
												    $resultID = $con->insert_id;

												    //send to gatway
												    #$url = 'http://localhost:8080/php/test.php';
												    $url = 'http://localhost:5000/new_job';
												    
												    //build up job_config array
												    $job_config_arr = array();
												    //fileID, motetype, motes
												    $sql="select * from files where jobs_jobID = '".$jobID."'";
												    $result = mysqli_query($con,$sql);
												    while($fileRow = mysqli_fetch_array($result)){
												        //file array
												        $file_arr = array();
												        
												        $fileName = $fileRow['fileName'];
												        $info = new SplFileInfo($fileName);
												        
												        //file
												        $file_arr["binary_file"] = $fileRow['fileID'].'.'.$info->getExtension();

												        //mote type
												        $sql="select * from moteTypes where moteTypeID = '".$fileRow['moteTypes_moteTypeID']."'";
												        $resultMoteType = mysqli_query($con,$sql);
												        $moteTypeRow = mysqli_fetch_array($resultMoteType);

												        $file_arr["type"] = $moteTypeRow['moteTypeName'];
												        
												        $mote_arr = array();
												        $sql="select * from motes inner join file_mote on motes.moteID = file_mote.motes_moteID and file_mote.files_fileID = '".$fileRow['fileID']."'";
												        $resultMotes = mysqli_query($con,$sql);
												        while($moteRow = mysqli_fetch_array($resultMotes)) {
												            $mote_arr[] = $moteRow['virtual_id'];
												        }
												        
												        $file_arr['mote_list'] = $mote_arr;
												        
												        $job_config_arr[] = $file_arr;
												    }
												    
												    
												    $obj = array(
												        "user" => $user_id,
												        "result_id" => $resultID.'',
												        "job_config" => $job_config_arr,
												        "time" => array(
												            "from" => $s.'',
												            "to" => $e.''
												        ),
												    );
												    
												    $opts = array('http' =>
												      array(
												          'method'  => 'POST',
												          'header'  => 'Content-type: application/json',
												          'content' => json_encode($obj)
												      )
												     );
												    
												    $context  = stream_context_create($opts);
												    $result = file_get_contents($url, false, $context);
												    
												    $result = str_replace("'", '"', $result);
												    
												    $response = json_decode($result, true);

												    //error, delete schedule
												    if($response['result'] == 0) {
												        mysqli_select_db($con,"indriyaDB");
												        $sql="DELETE FROM runtimes WHERE runtimeID = '".$runtimeID."'";
												        $result = mysqli_query($con,$sql);

												        //return error response from schedule api
												        $response = array(
															'status' => 'ERROR',
															"message" => "Cannot schedule job!",
															"not_exist_mote_list" => $motes_not_exist
														);
												    } else {
												    	//return success from schedule api
														$response = array(
															'status' => 'SUCCESS',
															"message" => "Job scheduled successfully",
															"result_id" => $resultID,
															"not_exist_mote_list" => $motes_not_exist
														);
												    }
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		mysqli_close($con);
	}

	echo json_encode($response);

	$response["user"] = $user_id;
	$response["time"] = date('Y-m-d H:i:s');
	$log_file = "/var/log/indriya/scheduleAPI.log";
	$handle = fopen($log_file, 'a');
	fwrite($handle, json_encode($response));
	fwrite($handle, "\n");
	fclose($handle);
?>
