#!/bin/sh

STATUS_ROOT=`pwd`

# First we get strings that are not into php
xgettext --keyword=_ -keyword=N_ --default-domain=squirrelmail \
    --no-location -C independent_strings.txt --output-dir=${STATUS_ROOT}

# Now we look for strings into code.
# First stable.
cd ../squirrelmail.stable
${STATUS_ROOT}/getcurpot.sh
cd ../squirrelmail.devel
${STATUS_ROOT}/getcurpot.sh
cd ${STATUS_ROOT}
