#!/bin/bash
svn export http://svn.izzysoft.de/repos/imdbphp/trunk/ trunk
mv trunk/imdb.class.php ./imdb.class.php
mv trunk/imdbsearch.class.php ./imdbsearch.class.php
mv trunk/mdb_base.class.php ./mdb_base.class.php
mv trunk/mdb_config.class.php ./mdb_config.class.php
mv trunk/movie_base.class.php ./movie_base.class.php
rm -rf trunk