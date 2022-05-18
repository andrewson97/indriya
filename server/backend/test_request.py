import subprocess
# import requests
import json
# r = requests.post('http://ocean.comp.nus.edu.sg/api/queue/create_job?key=0oQ8FPEzRbXkjONLGEOHAq7OPFB8RvlymRASZYve7jED7HljaPgma0IceIcTJmnw', data = {'name':'testing'})
# print(r.json())
name="testing"
binary_file="calib.ihex"
duration="100"

command='curl -H "Content-Type: application/json" -X POST -d \'{"name":"' + name + '", "duration":' + duration + ' ,"file": "\'$(cat ' + binary_file + '|base64 -w0)\'"}\' "http://ocean.comp.nus.edu.sg/api/queue/create_job?key=0oQ8FPEzRbXkjONLGEOHAq7OPFB8RvlymRASZYve7jED7HljaPgma0IceIcTJmnw"'
print(command)
p = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE ) # stdout=subprocess.PIPE, shell=True)
(output, err) = p.communicate()
output = output.decode("utf-8")
print(output)
dcube_output=json.loads(output)
# print(dcube_output)
print(dcube_output['id'])


# curl -H "Content-Type: application/json" -X POST -d '{"result_id":"12889", "time_to":"1550249032" , "binary_file": "calib.ihex"}' "http://indriya.comp.nus.edu.sg:5000/new_dcube_job"