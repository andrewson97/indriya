import subprocess

def run_cmd(command, success_identifier, success=True):
        p = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE ) # stdout=subprocess.PIPE, shell=True)
        (output, err) = p.communicate()
        output = output.decode("utf-8")
        err = err.decode("utf-8")
        print("OUTPUT")
        print(output)
        print("ERR")
        print(err)
        if(err==""):
                print("ERROR IS BLANK")

        if(success and (output.find(success_identifier) > -1 or err.find(success_identifier) > -1)):
                # print("SUCCESS!!")
                # logger.info("SUCCESS:" + command)
                return True
        elif(not success and (not(output.find(success_identifier) > -1) or not(err.find(success_identifier) > -1))): # here we have an identifier for failure...
                # print("SUCCESS!!")
                # logger.info("SUCCESS:" + command)
                return True
        else:
                # print("FAILURE!!")
                logger.warning("FAILURE:" + command + "\n\n" + output + "\n" + err)
                return False


#print(run_cmd('uniflash_custom_rewrite.sh /home/cirlab/sensortag_configs/L3000695.ccxml /home/cirlab/elf/welcome.elf', "Error", False))
print(run_cmd('rsync -av --ignore-existing /var/www/file/1425.elf cirlab@lenovo-gw-1.d1:/home/cirlab/elf/','error',False))

