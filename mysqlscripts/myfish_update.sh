#!/bin/bash

SVN_USER="qc"
SVN_PASS="qc^^)@*"

SVN_REPO="http://10.198.48.127/svn/projects/qc/zingfish/QC1"
SVN_SOURCE="/home/svn_source/zingfish"
WEB_SOURCE="/var/www/myfish"

rm -rf $SVN_SOURCE/*
svn checkout $SVN_REPO --username $SVN_USER --password $SVN_PASS $SVN_SOURCE/
find $SVN_SOURCE/ -name ".svn" | xargs rm -Rf
rsync -avz --delete $SVN_SOURCE/ $WEB_SOURCE/

chmod -R 777 $WEB_SOURCE
rm -R /data/eaccelerator/cache/*
/etc/init.d/httpd restart
php /var/www/myfish/web/CreateJsonFileInLinux.php

