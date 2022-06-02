import subprocess, shlex

import os


def install_essentials():
    cmd = ["sudo apt update", "sudo apt install apache2",
    "sudo ufw enable", "sudo ufw allow 'Apache'",
    "sudo apt-get install php7.2", "sudo apt install mysql-server",
    "sudo systemctl start mysql.service","sudo apt install git",
    "sudo apt install python3-pip",
    "pip3 install GitPython",]
    for cmds in cmd :
        args = shlex.split(cmds)
        subprocess.Popen(args).communicate("password")

def get_path():
    cmd = shlex.split("whoami")
    user = subprocess.run(cmd, stdout=subprocess.PIPE)
    username = str(user.stdout.decode())
    username = username.strip('\n')
    path_a = "/home/" + username + "/ackcio"
    return path_a

def download_essentials():
    path_a = get_path()
    print(path_a)
    import git
    git.Git(path_a).clone("https://github.com/andrewson97/virtualhost.git")
    git.Git(path_a).clone("https://github.com/andrewson97/indriya.git")



def apache_setup1():
    path_a = get_path()
    cmd =["chmod +x " + path_a + "/virtualhost/virtualhost.sh",
    "sudo cp " + path_a + "/virtualhost/virtualhost.sh /usr/local/bin/virtualhost"]
    for cmds in cmd :
        args = shlex.split(cmds)
        subprocess.Popen(args).communicate("password")

def apache_setup2():
    action = input("create or delete :")
    domain = input("domain name:")
    host_dir = input ("host directory:")
    #arg = shlex.split("sudo virtualhost " + action + " " + domain + " " + host_dir)
    subprocess.run(["sudo", "virtualhost" , action , domain , host_dir])
    if action == "create":
        print ("\nvirtual host sucsusfully setup\n coppying needed files to the directory")
        place_files(host_dir)
        cmd1 = "sudo chown -R $USER:$USER /var/www"
        cmd2 = "sudo chmod -R 755 /var/www"
        cmd3 = "sudo chmod 777 /var/www/" + host_dir + "/conf.d/indriya_db_pass"
        os.system(cmd1)
        os.system(cmd2)
        os.system(cmd3)
    return str(host_dir)

def place_files(host_dir):
    path_a = get_path()
    cmd = "sudo cp -RT " + path_a + "/indriya-master/server/frontend/website " + path_a + "/www/" + host_dir
    arg = shlex.split(cmd)
    subprocess.run(arg)
    """cmd1 = "sudo cp -RT" + path_a + "/indriya-master/server/frontend/website " + path_a + "/www/" + host_dir
    x = os.system(cmd1)
    if x == 0 : print ("files coppied sucsussfully")"""


def sql_setup():
    cmds = ["sudo systemctl start mysql.service",
    "sudo mysql_secure_installation",]
    for cmd in cmds:
        arg = shlex.split(cmd)
        subprocess.run(arg)
    sqlpwd = input("\nplease enter the root password of mysql:")
    return sqlpwd
  

def write_essentials(sqlpwd,host_dir):
    path_a = get_path()
    path = path_a + "/www/" + host_dir + "/conf.d/indriya_db_pass"
    #d_path = path_a + "/www/conf.d/indriya_db_pass"
    f = open(path, "w")
    f.write(sqlpwd)
    f.close()
    #fd = open(d_path)
    #fd.write(sqlpwd)
    #fd.close()

def create_db():
    path_a = get_path()
    cmd = "sudo mysql -u root -p < " + path_a + "/indriya-master/server/frontend/database/indriyaDB.sql"
    #print(cmd)
    #arg = shlex.split(cmd)
    #print(arg)
    #subprocess.Popen(arg)
    x = os.system(cmd)
    if x == 0 : print ("DB created Sucusfully")


install_essentials()
download_essentials()
apache_setup1()
host_dir = apache_setup2()
#sqlpwd = sql_setup()
#host_dir = "indriya"
sqlpwd = "admin"
write_essentials(sqlpwd,host_dir)
create_db()
