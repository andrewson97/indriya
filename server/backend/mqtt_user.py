#!/usr/bin/python3
import random
import fasteners
import subprocess
from _thread import start_new_thread
from time import sleep

import logging
logging.config.fileConfig('logging.conf')
logger = logging.getLogger('indriya_main')

def run_cmd(command, success_identifier=""):
        p = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE ) # stdout=subproc$
        (output, err) = p.communicate()
        output = output.decode("utf-8")
        err = err.decode("utf-8")
        print(output,err)
        if(output.find(success_identifier) > -1 or err.find(success_identifier) > -1):
                print("SUCCESS!!")
                #logger.info("SUCCESS:" + command)
                return True
        else:
                print("FAILURE!!")
                #logger.warning("FAILURE:" + command)
                return False

def generate_password():
	characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
	password = ''
	for i in range(16):
		password += random.choice(characters)
	return password

def send_email(user,password):
	email_command = 'sendEmail -f indriya2.testbed@gmail.com -t ' + user + ' -u "Indriya2 account activation"  -m "\nWelcome to Indriya2 Tesbed!!!\n\nPlease reply to this email with the following details duly filled in order to activate your account:\nFullname:\nName of supervisor:\nEmail of supervisor:\nName of University/Institution:\nCountry:\nPurpose of using the testbed:\n\nYou may get started with the following tutorial:\n https://indriya.comp.nus.edu.sg/Indriya2_tutorial.pdf\n\nPlease note that your MQTT account will be activated in less than 30mins. Your credentials are:\nusername: ' + user + '\npassword: ' + password + '\n\nYou may\n(1) subscribe using:\nmosquitto_sub -h indriya.comp.nus.edu.sg -t \'#\' -u username -P password -p 8080 --cafile cacert.pem\n\n(2) publish using:\nmosquitto_pub -h indriya.comp.nus.edu.sg -t \'username/push/nodeid\' -m  \'data that is pushed to the node goes here\' -u username -P password -p 8080 --cafile cacert.pem\nwhere username, password and nodeid are to be replaced with your username, password and the node id respectively.\n" -s smtp.gmail.com:587 -o tls=yes -xu indriya2.testbed@gmail.com -xp qayqubuwblxszfro -cc "indriya2.testbed@gmail.com" -a cacert.pem'
	print(email_command)
	email_sent = run_cmd(email_command,"successfully!")
	if not email_sent:
		for i in range(2):
			sleep(5)
			email_sent = run_cmd(email_command,"successfully!")
			if email_sent:
				break
	msg = "email sent to " + user if(email_sent) else "email not sent to " + user + " " + password
	logger.warn(msg)



def add_new_mqtt_user(user):
	success = 0
	if user != "":
		password = generate_password()
		mosquitto_lock = fasteners.InterProcessLock('/tmp/tmp_mosquitto_lock_file')
		while 1:
				mosquitto_lock_acquired = mosquitto_lock.acquire(blocking=False)
				#try:
				if(mosquitto_lock_acquired):
					# update mosquitto_passwd, mosquitto_acl
					mosquitto_passwd_file = "/home/cirlab/indriya_upgrade/mosquitto_passwd"
					mosquitto_acl_file = "/home/cirlab/indriya_upgrade/mosquitto_acl"
					with open(mosquitto_acl_file, 'a') as f:
						f.write('\n')
						f.write('user ' + user + '\n')
						f.write('topic readwrite ' + user + '/#\n')
						f.close()
					mos_pass_cmd = "mosquitto_passwd -b " + mosquitto_passwd_file + " " + user + " " + password
					run_cmd(mos_pass_cmd)
					success = 1
					#print("releasing")
					mosquitto_lock.release()
					#print("released")
					break
				else:
					sleep(1)
				#finally:
				#mosquitto_lock.release()
	if(success):
		start_new_thread(send_email,(user, password,))
		return password
	else:
		return None
	#pass


