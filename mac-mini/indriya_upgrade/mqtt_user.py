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
	email_command = 'sendEmail -f indriyaplus@gmail.com -t ' + user + ' -u "indriya++ mqtt credential"  -m "Hello,\nYour MQTT account will be activated in less than 30mins. Your credentials are:\nusername: ' + user + '\npassword: ' + password + '\n\nPlease add the attached CA certificate to your browser to access real-time features.\n\nYou may\n(1) subscribe using:\nmosquitto_sub -h ocean.comp.nus.edu.sg -t \'#\' -u username -P password\n\n,and (2) publish using:\nmosquitto_pub -h ocean.comp.nus.edu.sg -t \'username/push/nodeid\' -m \'data that the node pulls goes here\' -u username -P password\n\nfor secure mqtt, use: -p 8884 --cafile m2mqtt_ca.crt\n or -p 8883 if you have a client certificate\n\n\nReal Time Data for Real Time Decisions :)" -s smtp.gmail.com:587 -o tls=yes -xu indriyaplus@gmail.com -xp indriyaplusplus -cc "pappavoo@comp.nus.edu.sg" -a m2mqtt_ca.crt'
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

