#!/bin/bash

cd /home/ldb/dev/php/userside_bgb

DATE=`date '+%Y-%m-%d %H:%M:%S'`

if [ -n "$(git status --porcelain)" ]; then
    echo "Index all"
    git add . 

    echo "Autocommit "$DATE
    git commit -m "Autocommit ${DATE}"

    echo "Push"
    git push
fi

