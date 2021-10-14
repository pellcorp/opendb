<?php
/*
    The Movie Database Site Plugin
    Copyright (C) 2013 by Rodney Beck <denney@mantrasoftware.net>

    Created for Open Media Collectors Database
    Copyright (C) 2001,2013 by Jason Pell

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 */
include_once("./lib/SitePlugin.class.php");

class tmdb extends SitePlugin {
    private $baseURL = 'http://api.themoviedb.org/3/';
    private $imgBase = '';
    private $imgResultsSize = '';
    private $apikey = '';

	function __construct($site_type) {
		parent::__construct($site_type);

        $this->apikey = $this->_site_plugin_conf_r['tmdb_apikey'];

        $jsonData = json_decode($this->fetchURI($this->baseURL . 'configuration?api_key=' . $this->apikey), true);

        if (!is_null($jsonData)) {
            $this->imgBase = $jsonData['images']['base_url'];
            $this->imgResultsSize = $jsonData['images']['poster_sizes'][0];
            if (is_numeric($this->_site_plugin_conf_r['cover_width'])) $this->_site_plugin_conf_r['cover_width'] = 'w'.$this->_site_plugin_conf_r['cover_width'];
        }
    }

    function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
        if ($this->apikey == '') return false;

        if (strlen($search_vars_r['tmdb_id']) > 0) {
            $this->addListingRow(null, null, null, array('tmdb_id' => $search_vars_r['tmdb_id']));

            return true;
        } else {
            $release = strlen($search_vars_r['year']) > 0 ? '&year=' . rawurlencode(strtolower($search_vars_r['year'])) : '';
            $jsonData = json_decode($this->fetchURI($this->baseURL . 'search/movie?api_key=' . $this->apikey . $release . '&query=' . rawurlencode(strtolower($search_vars_r['title']))), true);

            if (!is_null($jsonData)) {
                foreach ($jsonData['results'] as $result) {
                    $this->addListingRow($result['original_title'], $this->imgBase . $this->imgResultsSize . $result['poster_path'], $result['release_date'], array('tmdb_id' => $result['id']));
                }

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * title            = Movie title.
     * orig_title       = Original movie title.
     * tagline          = Short movie tagline.
     * plot             = Movie overview.
     * runtime          = Movie runtime.
     * cover            = Movie poster image URL.
     * imdb_id          = Internet Movie Database ID.
     * pub_year         = Movie release year.
     * budget           = Movie budget.
     * revenue          = Movie revenue.
     * collection       = Movie series name.
     *
     * genre            = Array of movie genres.
     * prod_companies   = Array of production companies.
     * actors           = Array of movie actors.
     * directors        = Array of movie directors.
     * producers        = Array of movie producers.
     * music            = Array of music composers.
     * writers          = Array of movie writers.
     */
    function queryItem($search_attributes_r, $s_item_type) {
        if ($this->apikey == '') return false;

        $jsonData = json_decode($this->fetchURI($this->baseURL . 'movie/' . $search_attributes_r['tmdb_id'] . '?api_key=' . $this->apikey . '&append_to_response=casts', true), true);

        if (!is_null($jsonData)) {
            $this->addItemAttribute('title', $jsonData['title']);
            $this->addItemAttribute('orig_title', $jsonData['original_title']);
            $this->addItemAttribute('tagline', $jsonData['tagline']);
            $this->addItemAttribute('plot', $jsonData['overview']);
            $this->addItemAttribute('runtime', $jsonData['runtime']);
            $this->addItemAttribute('cover', $this->imgBase . $this->_site_plugin_conf_r['cover_width'] . $jsonData['poster_path']);
            $this->addItemAttribute('imdb_id', $jsonData['imdb_id']);
            $this->addItemAttribute('year', substr($jsonData['release_date'], 0, 4));
            $this->addItemAttribute('budget', $jsonData['budget']);
            $this->addItemAttribute('revenue', $jsonData['revenue']);
            $this->addItemAttribute('collection', $jsonData['belongs_to_collection']['name']);

            $genres = array();
            foreach ($jsonData['genres'] as $genre) {
                $genres[] = $genre['name'];
            }
            $this->addItemAttribute('genre', $genres);

            $prod_companies = array();
            foreach ($jsonData['production_companies'] as $prod_company) {
                $prod_companies[] = $prod_company['name'];
            }
            $this->addItemAttribute('prod_companies', $prod_companies);

            $actors = array();
            foreach ($jsonData['casts']['cast'] as $actor) {
                $actors[] = $actor['name'];
            }
            $this->addItemAttribute('actors', $actors);

            $directors = array();
            $producers = array();
            $music = array();
            $writers = array();
            $others = array();
            foreach ($jsonData['casts']['crew'] as $crew) {
                switch ($crew['job']) {
                    case 'Director':
                        $directors[] = $crew['name'];
                        break;
                    case 'Producer':
                    case 'Executive Producer':
                        $producers[] = $crew['name'];
                        break;
                    case 'Music':
                    case 'Musical':
                    case 'Original Music Composer':
                        $music[] = $crew['name'];
                        break;
                    case 'Writer':
                    case 'Screenplay':
                    case 'Novel':
                        $writers[] = $crew['name'];
                        break;
                    default:
                        $others[] = $crew['job'];
                        break;
                }
            }
            $this->addItemAttribute('directors', $directors);
            $this->addItemAttribute('producers', $producers);
            $this->addItemAttribute('music', $music);
            $this->addItemAttribute('writers', $writers);
            $this->addItemAttribute('others', $others);

            return true;
        } else {
            return false;
        }
    }
}
