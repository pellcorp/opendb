#!/bin/bash

if [ $# -lt 2 ]; then
	echo "Usage `basename $0` <PreviousReleaseTag> <CurrentReleaseTag>"
	echo "Example `basename $0` 1_5_0_A6 1_5_0_A7"
	echo "Do not include the RELEASE_ prefix!!!"
	exit;
fi

RELEASE_OPENDB_EXPORT_DIR=/tmp/OpenDb_Export_$$
mkdir $RELEASE_OPENDB_EXPORT_DIR
cd $RELEASE_OPENDB_EXPORT_DIR

echo Exporting release ...
svn export https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/tags/RELEASE_$2

CurrentReleaseZip="OpenDb-`echo $2 | tr _ . | tr [:upper:] [:lower:]`.zip"
echo Creating Zip File ... $RELEASE_OPENDB_EXPORT_DIR/$CurrentReleaseZip
cd $RELEASE_OPENDB_EXPORT_DIR/RELEASE_$2
zip -r $CurrentReleaseZip *
mv $CurrentReleaseZip $RELEASE_OPENDB_EXPORT_DIR
cd $RELEASE_OPENDB_EXPORT_DIR
rm -r $RELEASE_OPENDB_EXPORT_DIR/RELEASE_$2

CurrentReleaseChangelog="ReleaseNotes-`echo $2 | tr _ . | tr [:upper:] [:lower:]`.txt"
echo Generating Release Notes... $CurrentReleaseChangelog
firstRevision=`svn info https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/tags/RELEASE_$1 | grep "Last Changed Rev:" | egrep -o "([0-9]+)"`
secondRevision=`svn info https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/tags/RELEASE_$2 | grep "Last Changed Rev:" | egrep -o "([0-9]+)"`
echo Generating log for $firstRevision ... $secondRevision;
svn2cl.sh --output=$CurrentReleaseChangelog --revision $firstRevision:$secondRevision https://opendb.svn.sourceforge.net/svnroot/opendb/opendb/trunk

echo "The zip file and ChangeLog can be found in the $RELEASE_OPENDB_EXPORT_DIR directory"
