#!/bin/sh

SMROOT="/home/philippe/cvsroot/locales/"
CURRENT=`pwd`

for i in *
    do
    if [ -f $i/index.php ]
    then	    
        # This may be an interesting folder
	echo Fetching $CURRENT/$i
	xgettext --keyword=_ -keyword=N_ --default-domain=squirrelmail --no-location -j -C ${CURRENT}/$i/*.php --output-dir=$SMROOT
	cd $i
	${SMROOT}getcurpot.sh
	cd $CURRENT
    #else
        # echo "xgettext --keyword=_ -keyword=N_ --default-domain=squirrelmail --no-location -j -C ${CURRENT}/$i/*.php --output-dir=$SMROOT"
    fi
done
