#!/bin/bash

if [ $# -lt 3 ]; then
	echo "Usage $0 <PreviousRelease> <CurrentRelease> <CurrentReleaseZipVersion>"
	echo "Example $0 1_5_0_A6 1_5_0_A7 1.5.0a7"
	exit;
fi

mkdir $$
cd $$

echo Exporting release ...
svn export https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/tags/RELEASE_$2

echo Creating Zip File ... OpenDb-$3.zip
cd RELEASE_$2
zip -r OpenDb-$3.zip *
mv OpenDb-$3.zip ..
cd ..
rm -r RELEASE_$2

echo Uploading to sourceforge
lftp upload.sourceforge.net <<EOF
cd incoming
put OpenDb-$3.zip
quit
EOF

echo Generating Changelog...
firstRevision=`svn info https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/tags/RELEASE_$1 | grep "Last Changed Rev:" | egrep -o "([0-9]+)"`
secondRevision=`svn info https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/tags/RELEASE_$2 | grep "Last Changed Rev:" | egrep -o "([0-9]+)"`
echo Generating log for $firstRevision ... $secondRevision;
svn2cl.sh --revision $firstRevision:$secondRevision https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/trunk

cd ..
rm -r $$

