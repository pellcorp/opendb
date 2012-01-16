#!/bin/bash
svn export http://svn.izzysoft.de/repos/imdbphp/trunk/ trunk
mv trunk/browseremulator.class.php ./browseremulator.class.php
mv trunk/imdb_charts.class.php ./imdb_charts.class.php
mv trunk/imdb.class.php ./imdb.class.php
mv trunk/imdb_movielist.class.php ./imdb_movielist.class.php
mv trunk/imdb_nowplaying.class.php ./imdb_nowplaying.class.php
mv trunk/imdb_person.class.php ./imdb_person.class.php
mv trunk/imdbsearch.class.php ./imdbsearch.class.php
mv trunk/imdb_trailers.class.php ./imdb_trailers.class.php
mv trunk/mdb_base.class.php ./mdb_base.class.php
mv trunk/mdb_request.class.php ./mdb_request.class.php
mv trunk/movie_base.class.php ./movie_base.class.php
mv trunk/movieposterdb.class.php ./movieposterdb.class.php
mv trunk/person_base.class.php ./person_base.class.php
mv trunk/pilot.class.php ./pilot.class.php
mv trunk/pilot_person.class.php ./pilot_person.class.php
mv trunk/pilotsearch.class.php ./pilotsearch.class.php
rm -rf trunk
chown apache:users *
