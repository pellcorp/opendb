#!/bin/bash

git clone --depth=1 git@github.com:pellcorp/opendb.git opendb_$$
cd opendb_$$
rm -rf .git
tar -zcvf /tmp/opendb_$$.tar.gz *
echo Your tarball is /tmp/opendb_$$.tar.gz 
cd ..
rm -rf opendb_$$
echo done!




