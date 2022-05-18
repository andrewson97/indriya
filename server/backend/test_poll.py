#!/usr/bin/python3

import requests
# from flask import Flask, request
import json
import polling

def is_correct_response(response):
	print(response.status_code)
	if(response.status_code == 200):
		json_response = json.loads(response.text)
		print(json_response)
		return  json_response['finished'] == True
	else:
		return False


def is_dcube_id_correct(dcube_id):
	print("polling")
	return dcube_id != -1

def get_dcube_id(result_id):
	dcube_id = -1
	if dcube_jobs.get(result_id) != None:
		if dcube_jobs[result_id].get('dcube_id') != None:
			dcube_id = dcube_jobs[result_id]['dcube_id']
	return dcube_id

dcube_jobs={'20011': {'time_to': '1550486100', 'binary_file': '20001.'}, '20009': {'time_to': '1550485800', 'binary_file': '20001.'}, '20014': {'time_to': '1550495699', 'binary_file': 'calib.ihex', 'dcube_id': 60}, '20013': {'time_to': '1550492100', 'binary_file': 'calib.ihex', 'dcube_id': 59}, '20015': {'time_to': '1550498099', 'binary_file': 'calib.ihex', 'dcube_id': 61}, '20010': {'time_to': '1550486100', 'binary_file': '20001.'}, '20016': {'time_to': '1550501999', 'binary_file': 'calib.ihex', 'dcube_id': 62}}

print("polling")
# polling.poll(
# 	lambda: requests.get('http://ocean.comp.nus.edu.sg/api/queue/65?key=0oQ8FPEzRbXkjONLGEOHAq7OPFB8RvlymRASZYve7jED7HljaPgma0IceIcTJmnw'),
# 	check_success=is_correct_response,
# 	step=1,
# 	timeout=10)

polling.poll(
	lambda: get_dcube_id('20014'),
	check_success=is_dcube_id_correct,
	step=1,
	timeout=10)
print("done polling")