#!/bin/bash
echo '========================='
echo '== Initializing the DB =='
echo '========================='

echo
echo '============================='
echo '| 1. Starting MySQL:         '
echo '============================='
#start MySQL
/usr/bin/mysqld_safe --user=mysql &

maxAttempts=10
mysqlStarted=1
attempts=0

set +e
until [ $mysqlStarted == 0 ]; do
    ((attempts++))
    echo "Trying to see if MySQL has been started, attempt: $attempts"

    #find out if it has started
    mysqladmin -h 127.0.0.1 ping --password="root"
    mysqlStarted=`echo $?`

    if [ $attempts == $maxAttempts ]; then
        echo "MySQL failed to start after $maxAttempts tries. Exiting now. The contents of the log file:";
        exit 1;
    fi;

    #wait for a while
    sleep 2
done
set -e

mysqladmin -h 127.0.0.1 ping --password="root"
if [ $? != 0 ]; then
    exit 1;
fi

echo
echo '============================='
echo '| 2. Create database:        '
echo '============================='
mysql -h 127.0.0.1 -u root -proot -e "CREATE DATABASE circleci;"
if [ $? != 0 ]; then
    exit 1;
fi
