-- script to create mysql database for indriya --

create database indriyaDB;

use indriyaDB;


create table clusters(
    clusterID int(11) not null auto_increment,
    clusterName varchar(89) not null,
    floorLevel varchar(89) not null,
    primary key (clusterID)
    );

create table moteTypes(
    moteTypeID int(11) not null auto_increment,
    moteTypeName varchar(89) not null,
    runningTime bigint(20) not null,
    primary key (moteTypeID)
);

create table users (
    userID varchar(89) not null,
    quota int(11) not null,
    admin tinyint(1) not null,
    allMotes tinyint(1) not null,
    runningTime bigint(20) not null,
    totalSubmissions bigint(20) not null default 0, 
    mqtt_passw varchar(50),
    create_date timestamp not null default CURRENT_TIMESTAMP,
    details text not null,
    primary key (userID)
);

create table jobs(
    jobID int(11) not null auto_increment,
    jobName varchar(89) not null,
    users_userID varchar(89) not null,
    dcube int(11) not null default 0,
    primary key (jobID),
    foreign key (users_userID) references users(userID)
);

create table runtimes(
    runtimeID int(11) not null auto_increment,
    start timestamp not null default CURRENT_TIMESTAMP,
    end timestamp not null default CURRENT_TIMESTAMP,
    jobs_jobID int(11) not null,
    primary key (runtimeID),
    foreign key (jobs_jobID) references jobs(jobID) 
);

create table results(
    resultID int(11) not null auto_increment,
    status int(11) not null,
    jobs_jobID int(11) not null,
    runtimes_runtimeID int(11) not null,
    primary key (resultID),
    foreign key (jobs_jobID) references jobs(jobID),
    foreign key (runtimes_runtimeID) references runtimes(runtimeID)
);

create table files(
    fileID int(11) not null auto_increment,
    fileName varchar(89) not null,
    jobs_jobID int(11) not null,
    moteTypes_moteTypeID int(11) not null,
    dcube int(11) not null default 0,
    primary key (fileID),
    foreign key (jobs_jobID) references jobs(jobID),
    foreign key (moteTypes_moteTypeID) references moteTypes(moteTypeID)
);


create table motes(
    moteID int(11) not null auto_increment,
    physical_id varchar(89) not null default 0,
    virtual_id varchar(89) not null,
    gateway_ip varchar(89) not null default 0,
    gateway_ttyid varchar(89) not null default 0,
    gateway_port varchar(89) not null default 0,
    coordinates varchar(89) not null default 0,
    moteTypes_moteTypeID int(11) not null,
    clusters_clusterID int(11) not null,
    status tinyint(1) not null default -1 ,
    primary key (moteID),
    foreign key (moteTypes_moteTypeID) references moteTypes(moteTypeID),
    foreign key (clusters_clusterID) references clusters(clusterID)
);

create table file_mote(
    files_fileID int(11) not null,
    motes_moteID int(11) not null,
    foreign key (files_fileID)references files(fileID),
    foreign key (motes_moteID) references motes(moteID)
);




