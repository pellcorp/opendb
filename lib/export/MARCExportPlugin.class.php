<?php
/*
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell
    Copyright (C) 2013 by Nathaniel Clark <Nathaniel.Clark@misrule.us>

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

/*
 * Return MARCXML Holdings Records
 * https://www.loc.gov/standards/marcxml/
 * http://www.loc.gov/marc/umh/index.html
 * http://www.loc.gov/marc/holdings/echdlist.html
 * Also Uses Bibliographic Records (as required by standard)
 * http://www.loc.gov/marc/umb/
 * http://www.loc.gov/marc/bibliographic/ecbdlist.html
 *
 * Mappings
 * Item -> Bibliographic Record
 * Instance -> Holding Record
 */
class MARCExportPlugin {
    var $_data;
    var $_file_title;

    /*
     * The content type, when saved as file.
     */
    function get_file_content_type() {
        return 'application/marcxml+xml';
    }

    /*
     * The filename extension, when saved as file.
     */
    function get_file_extension() {
        return 'mrcx';
    }

    function get_display_name() {
        return 'MARC21 XML Format';
    }

    function get_plugin_type() {
        return 'item';
    }

    // List of articles to skip in titles (must end with space for
    // seperate word articles)
    var $ARTICLES = array ('a ',  'an ', 'the ', "d'", 'ye ', 'le ', 'la ');

    // An incomplete list of MARC 21 languages
    // http://www.loc.gov/marc/languages/language_name.html
    var $LANGMAP = array( "arabic"    => "ara",
                          "chinese"   => "chi",
                          "english"   => "eng",
                          "eng"       => "eng",
                          "french"    => "fre",
                          "german"    => "ger",
                          "greek"     => "gre",
                          "hebrew"    => "heb",
                          "italian"   => "ita",
                          "japanese"  => "jpn",
                          "latin"     => "lat",
                          "portugese" => "por",
                          "russian"   => "rus",
                          "spanish"   => "spa",
                          "none"      => "zxx",
                          "unknown"   => "unk",
                          "multiple"  => "mul",
    );

    function _tolang($newvalue, $oldvalue) {
        $value = strtolower($newvalue);
        $pos = strpos($value, '_');
        if ($pos !== false) {
            $value = substr($value, 0, $pos);
        }
        if (array_key_exists($value, $this->LANGMAP)) {
            if ($oldvalue == '' ||
                $oldvalue == '###' ||
                $oldvalue == $this->LANGMAP['unknown'] ||
                $oldvalue == $this->LANGMAP['none'])
                return $this->LANGMAP[$value];
            else
                return "mul";
        } else {
            if ($oldvalue == '')
                $newvalue = $this->LANGMAP["unknown"];
            else
                $newvalue = $oldvalue;
            error_log("Unknown language: `$value' returning $newvalue");
            return $newvalue;
        }
    }

    function _toyear($value) {
        return substr(array_pop(explode('/', $value)), 0, 4);
    }

    /** Target audience
      # - Unknown or not specified
      a - Preschool		0-5yo
      b - Primary		6-8yo
      c - Pre-adolescent	9-13yo
      d - Adolescent		14-17
      e - Adult		18+
      f - Specialized		- Very limited audiance (e.g. beginner software engineering)
      g - General		General Audiance
      j - Juvenile		0-15yo
      | - No attempt to code
    */
    function _toTA($value, $old)
    {
        // This is a combination of MPAA and ESRB ratings
        switch(strtoupper($value)) {
            // ESRB Ratings
        case 'EC':	return 'j'; // ESRB Early Childhood
        case 'K-A':	return 'g'; // ESRB Kids to Adults
        case 'E':	return 'g'; // ESRB E
        case 'E10':	return 'c'; // ESRB E10+
        case 'T':	return 'd'; // ESRB Teen
        case 'M':	return 'e'; // ESBB Mature
        case 'AO':	return 'e'; // ESRB Adults Only
            // MPAA Ratings
        case 'G':	return 'g';
        case 'PG':	return 'c';
        case 'PG-13':	return 'd';
        case 'R':	return 'e';
        case 'M':	return 'e';
        case 'X':	return 'e';
        case 'MA':	return 'e';
        case 'NC-17':	return 'e';
            // Other
        case 'NP':	return '#'; // Rating Pending
        case 'NR':	return '#'; // Not Rated
        }
    }

    /* Media Size */

    /* "Configuration of Playback Channels" 007v/08
     * k - Mixed
     * m - Monaural
     * n - Not applicable
     * q - Quadraphonic, multichannel, or surround
     * s - Stereophonic
     * u - Unknown
     * z - Other
     * | - No attempt to code
     */
    const AUDIO_MONO = 'm';
    const AUDIO_STEREO = 's';
    const AUDIO_HIFI = 'q';
    const AUDIO_NONE = 'n';

    function _toaudiovalue($name)
    {
        switch (strtolower($name)) {
        case 'mono':	return $_this->AUDIO_MONO;
        case 'stereo':	return $_this->AUDIO_STEREO;
        case 'none':	return $_this->AUDIO_NONE;
        default: return $_this->AUDIO_HIFI;
        }
    }

    function _toAudio($value, $old)
    {
        $new = $this->_toaudiovalue($name);
        if ($new == $_this->AUDIO_HIFI || $old == $_this->AUDIO_HIFI)
            return $_this->AUDIO_HIFI;
        if ($new == $_this->AUDIO_STEREO || $old == $_this->AUDIO_STEREO)
            return $_this->AUDIO_STEREO;
        if ($new == $_this->AUDIO_MONO || $old == $_this->AUDIO_MONO)
            return $_this->AUDIO_MONO;
        return $_this->AUDIO_NONE;
    }

    /* 007c/01 - Specific material designation
       a - Tape cartridge
       b - Chip cartridge
       c - Computer optical disc cartridge
       d - Computer disc, type unspecified
       e - Computer disc cartridge, type unspecified
       f - Tape cassette
       h - Tape reel
       j - Magnetic disk
       k - Computer card
       m - Magneto-optical disc
       o - Optical disc
       r - Remote
       u - Unspecified
       z - Other
       | - No attempt to code
    */
    function _toMediaType($value, $old)
    {
        switch(strtoupper($value)) {
        case 'CD':
        case 'BD': // Blu-Ray
        case 'DVD':
        case 'GCGD':	// GameCube Game Disc
        case 'GD-ROM':	// Dreamcast
        case 'UMD':	// Sony UMD
            $type = 'o';
            break;
        case 'CARTRIDGE':
            $type = 'b';
            break;
        default:
            error_log("Unknown Media Type: `$value'");
            $type = 'u';
            break;
        }

        return $type;
    }

    /* 007c/04 - Dimensions
       a - 3 1/2 in.
       e - 12 in.
       g - 4 3/4 in. or 12 cm.
       i - 1 1/8 x 2 3/8 in.
       j - 3 7/8 x 2 1/2 in.
       n - Not applicable
       o - 5 1/4 in.
       u - Unknown
       v - 8 in.
       z - Other
       | - No attempt to code
    */
    function _toMediaSize($value, $old)
    {
        switch(strtoupper($value)) {
        case 'CD':
        case 'BD': // Blu-Ray
        case 'DVD':
        case 'GD-ROM':	// Dreamcast
            $size = 'g'; // assume full size disc 120mm
            break;
        case 'UMD':	// Sony UMD - 64mm
            $size = 'j';
            break;
        case 'GCGD':	// GameCube Game Disc - 80mm
            $size = 'z';
            break;
        case 'CARTRIDGE':
            $size = 'u';
            break;
        default:
            error_log("Unknown Media Type: `$value'");
            $size = 'u';
            break;
        }

        return $size;
    }

    /* Format of translations of OpenDB data to MARC 21
     *
     * OpenDBVALUE => [ array of locations ]
     * locationA = [ TAG, Indicators, subfield to fill, (optional) format ]
     * Indicators = 'AB' - two characters, or one of the following special:
     *   ''  -  add subfield to existing tag
     *   '+' - to concat field to an existing field
     *   'T' - Replace indicators with Title Rules (MARC tag 245 $a)
     * locationB = [ 'TAG/AA-BB', (optional) format ]
     * format = Similar in style to standard printf format with special characters of the following:
     *   %s - text of tag (this is the default if format is omitted)
     *   %Y - four digit year
     *   %L - Abreviated language (3 letter aberviation, mul for multilingual)
     *   %D - Today as YYMMDD
     *   %3 - three digit int (zero padded)
     * The following are all single characters
     *   %A - Audio type (MARC 007v/08)
     *   %R - "Target Audiance" (Rating) (MARC 008/22 Books/VisualMedia/ComputerFiles)
     *   %T - Media Type (MARC 007c/01)
     *   %S - Media Size (MARC 007c/04)
     */

    function _format_field($format, $value, $oldvalue)
    {
        $output = '';

        $istok = 0;
        foreach (str_split($format) as $lem) {
            if ($istok == 0) {
                if ($lem == '%') {
                    $istok = 1;
                } else {
                    $output .= $lem;
                }
            } else {
                switch ($lem) {
                case 's': $output .= $value; break;
                case 'L': $output .= $this->_tolang($value, $oldvalue); break;
                case 'Y': $output .= $this->_toyear($value); break;
                case 'A': $output .= $this->_toAudio($value, $oldvalue); break;
                case 'R': $output .= $this->_toTA($value, $oldvalue); break;
                case 'T': $output .= $this->_toMediaType($value, $oldvalue); break;
                case 'S': $output .= $this->_toMediaSize($value, $oldvalue); break;
                case 'D': $output .= date("ymd"); break;
                case '3':
                    $n = (int)$value;
                    if ($value < 1000)
                        $output .= sprintf("%03d", $value);
                    else
                        $output .= "000";
                    break;
                default:
                    error_log("Unknown string token: `$lem' SKIPPING");
                    break;
                }
                $istok = 0;
            }
        }

        return $output;
    }

    function _saveConcat($location, $value)
    {
        $this->saved[] = array($location, $value);
    }

    function _processSaved()
    {
        foreach($this->saved as $ar)
            $this->_process_location($ar[0], $ar[1], TRUE);
    }

    // Fill in MARC data into this->data
    function _process_location($location, $value, $force=FALSE) {
        // BUISNESS LOGIC
        if (count($location) > 2) { // variable length location
            $tag = $location[0];
            $indicator = $location[1];
            $field = $location[2];
            $format = (count($location) > 3) ?$location[3] :'%s';

            // check for special indicators
            if ($indicator == '+') {
                $indicator = '';
                $concat = 1;
            } else
                $concat = 0;

            $text = $this->_format_field($format, $value, '');

            // Handle integrating indicator
            if ($indicator == 'T') {
                $loc = '0';
                foreach ($this->ARTICLES as $the) {
                    $len = strlen($the);
                    if (strncasecmp($text, $the, $len) == 0) {
                        $loc = $len;
                        break;
                    }
                }
                $indicator = '0'.$loc;
            }

            if (array_key_exists($tag, $this->data)) {
                if ($concat) {
                    for($i = 0; $i < count($this->data[$tag]); ++$i) {
                        if (array_key_exists($field, $this->data[$tag][$i])) {
                            $this->data[$tag][$i][$field][0] .= $text;
                            return;
                        }
                    }
                    if (!$force) {
                        $this->_saveConcat($location, $value);
                        return;
                    }
                }

                if ($indicator == '') {
                    $this->data[$tag][count($this->data[$tag])-1][$field][] = $text;

                } else {
                    // Handle Special title indicators
                    if ($this->data[$tag][count($this->data[$tag])-1]['indicator'] == '') {
                        $this->data[$tag][count($this->data[$tag])-1][$field][] = $text;
                        $this->data[$tag][count($this->data[$tag])-1]['indicator'] = $indicator;

                    } else {
                        $this->data[$tag][] = array( 'indicator' => $indicator, $field => array( $text ) );
                    }
                }

            } else if ($concat || $force) {
                $this->_saveConcat($location, $value);

            } else {
                $this->data[$tag] = array( array( 'indicator' => $indicator, $field => array( $text ) ) );
            }

        } else { // fixed lenth location
            list($tag, $range) = explode('/', $location[0]);
            $format = $location[1];
            $a = explode('-', $range);
            $start = $a[0];
            $end = array_pop($a);

            if (array_key_exists($tag, $this->data))
                $oldtext = $this->data[$tag][0]['a'][0];
            else {
                // Precreate structure
                $this->data[$tag] = array( array( 'a' => array() ) );
                $oldtext = '';
            }
            $old = str_split($oldtext);

            // Concat a string to the substring so that value passed
            // to format field is a string and not false
            $newtext = $this->_format_field($format, $value, substr($oldtext, $start, ($end-$start+1))."");
            $new = str_split($newtext);

            $i = 0;
            while ($start+$i <= $end) {
                $old[$start+$i] = $new[$i];
                ++$i;
            }
            $this->data[$tag][0]['a'][0] = implode($old);
        }
    }

    function _marcLine($tag, $data)
    {
        $output = "    ";
        if (strtolower($tag) == 'leader') {
            $output .= "<leader>".strtr($data['a'][0], "#", " ")."</leader>";

        } elseif ((int)$tag < 10) {
            $output .= '<controlfield tag="'.$tag.'">'.strtr($data['a'][0], "#", " ")."</controlfield>";
            
        } else {
            if (array_key_exists('indicator', $data))
                $ind = array_slice(str_split(strtr($data['indicator'].'  ', '#', ' ')), 0, 2);
            else
                $ind = [' ', ' '];
            unset($data['indicator']);
            $output .= '<datafield tag="'.$tag.'" ind1="'.$ind[0].'" ind2="'.$ind[1].'">'."\n";

            ksort($data);
            
            $first = TRUE;
            $ar = array();
            foreach($data as $field => $entries) {
                foreach($entries as $text) {
                    $output .= '      <subfield code="'.$field.'">'.strtr($text, '#', ' ')."</subfield>\n";
                }
            }
            $output .= "    </datafield>";
        }
        return $output."\n";
    }

    // Export MARC 21 Record
    function _marcRecord()
    {
        $output = $this->_marcLine("Leader", $this->data["Leader"][0]);
        unset($this->data['Leader']);

        // sort rest of keys
        ksort($this->data);
        foreach ($this->data as $tag => $data) {
            foreach($data as $datum) {
                $output .= $this->_marcLine($tag, $datum);
            }
        }
        return $output;
    }

    /****************************************************************
     * MARC Translation Structures
     */

    // Call once per item with item type
    var $_TYPE2MARC  = array( "BD"    => array( array( 'Leader/00-23', '00000ngm#a2200000#a#4500' ),
                                                array( '007/00-08', 'vd#csaizq' ),
                                                #                    0         1         2         3
                                                #                    0123456789012345678901234567890123456789
                                                array( '008/00-39', '######nuuuuuuuuxx#---############m|mul#d' ) ),
                              "BOOK"  => array( array( 'Leader/00-23', '00000nam#a2200000#a#4500' ),
                                                array( '007/00-01', 'ta' ),
                                                array( '008/00-39', '######nuuuuuuuuxx############000#uu####d' ) ),
                              "COMIC" => array( array( 'Leader/00-23', '00000nas#a2200000#a#4500' ),
                                                array( '007/00-01', 'ta' ),
                                                array( '008/00-39', '######nuuuuuuuuxx#a#####6####000#uu####d' ) ),
                              "DVD"   => array( array( 'Leader/00-23', '00000ngm#a2200000#a#4500' ),
                                                array( '007/00-08', 'vd#cvaizq' ),
                                                array( '008/00-39', '######nuuuuuuuuxx#---############vu####d' ) ),
                              "GAME"  => array( array( 'Leader/00-23', '00000ngm#a2200000#a#4500' ),
                                                array( '007/00-13', 'co#cga---uunun' ),
                                                array( '008/00-39', '######nuuuuuuuuxx######q#g#############d' ) ),
                              "VHS"   => array( array( 'Leader/00-23', '00000ngm#a2200000#a#4500' ),
                                                array( '007/00-08', 'vf#cbahos' ),
                                                array( '008/00-39', '######nuuuuuuuuxx#---############vu####d' ),
                                                array( '346', '', 'a' ),
                              ),
    );

    // Call once per item with item title
    var $_TITLE2MARC = array( "BD"    => array( array( '245', 'T',  'a' ) ),
                              "BOOK"  => array( array( '245', 'T',  'a' ) ),
                              "COMIC" => array( array( '246', '14', 'a' ) ),
                              "DVD"   => array( array( '245', 'T',  'a' ) ),
                              "GAME"  => array( array( '245', 'T',  'a' ) ),
                              "VHS"   => array( array( '245', 'T',  'a' ) ),
    );

    // Call once per item with file title (aka name of library)
    var $_FILE2MARC = array( array( '852', '', 'a' ),
                             array( '008/00-05', '%D' ) );

    var $_PARAM2MARC = array( "ACTORS"     => array( array( '511', '1#', 'a' ),
                                                     array( '700', '1#', 'a' ) ),
                              "ADDR_LINE"  => array(), // ignore
                              "AGE_RATING" => array( array( '008/22', '%R' ),
                                                     array( '521', '8#', 'a', 'MPAA Rating: %s' ) ),
                              "ALT_ID"     => array( array( '500', '##', 'a', 'Alternate ID: %s.' ) ),
                              "ALT_TITLE"  => array( array( '246', '1#', 'a' ) ),
                              "AMAZONASIN" => array( array( '500', '##', 'a', 'ASIN: %s.' ) ),
                              "AMAZUKASIN" => array( array( '500', '##', 'a', 'ASIN: %s (uk).' ) ),
                              "ANAMORPHIC" => array( array( '538', '##', 'a', 'Anamorphic: %s' ) ),
                              "ARTIST"     => array( array( '100', '1#', 'a' ) ),
                              "AUDIO_LANG" => array( array( '008/35-37', '%L' ),
                                                     array( '041', '',   'a', '%L' ) ),
                              "AUDIO_XTRA" => array( array( '500', '##', 'a', 'Audio Extra: %s.' ) ),
                              "AUTHOR"     => array( array( '100', '1#', 'a' ) ),
                              "BD_AUDIO"   => array( array( '007/08', '%A' ),
                                                     array( '538', '##', 'a', 'Blu-Ray Audio: %s' ) ),
                              "BD_CODEC"   => array( array( '538', '##', 'a', 'Blu-Ray Codec: %s' ) ),
                              "BD_PROFILE" => array( array( '538', '##', 'a', 'Blu-Ray Profile: %s' ) ),
                              "BD_REGION"  => array( array( '538', '##', 'a', 'Blu-Ray Region: %s' ) ),
                              "BD_SIZE"    => array( array( '538', '##', 'a', 'Blu-Ray Size: %s' ) ), // 25GB or 50GB
                              "BD_TYPE"    => array( array( '538', '##', 'a', 'Blu-Ray Type: %s' ) ), // should always be PRESSED
                              "BINDING"    => array( array( '020', '+',  'a', ' (%s)' ) ),
                              "BOOKGENRE"  => array( array( '655', '#4', 'a' ) ),
                              "CDDBGENRE"  => array( array( '655', '#4', 'a' ) ),
                              "CDTIME"     => array( array( '008/18-20' ), ),
                              "CDTRACK"    => array( array( '500', '##', 'a', 'CD Track Title: %s.' ) ),
                              "CITY"       => array(), // ignore
                              "COMMENTS"   => array( array( '500', '##', 'a' ) ),
                              "COMPOSER"   => array( array( '511', '0#', 'a', '%s, cmp' ) ),
                              "COM_INUM"   => array( array( '245', '+',  'n', ' : No. %s' ) ),
                              "COM_ITIT"   => array( array( '245', '',   'p' ) ),
                              "COM_SERIES" => array( array( '245', 'T', 'a' ) ),
                              "CONDUCTER"  => array( array( '511', '0#', 'a', '%s, cnd' ) ),
                              "CONTROLLER" => array( array( '538', '##', 'a', 'Extra Game Controllers: %s' ) ),
                              "COUNTRY"    => array(), // ignore
                              "COVERPRICE" => array( array( '020', '', 'c' ) ),
                              "DESIGNER"   => array( array( '511', '0#', 'a', '%s, dsr' ) ),
                              "DIRECTOR"   => array( array( '511', '0#', 'a', '%s, drt' ),
                                                     array( '700', '1#', 'a' ) ),
                              "DVD_AUDIO"  => array( array( '007/08', '%A' ),
                                                     array( '538', '##', 'a', 'Audio Format: %s' ) ),
                              "DVD_EXTRAS" => array( array( '500', '##', 'a', 'DVD Extras: %s' ) ),
                              "DVD_REGION" => array( array( '538', '##', 'a', 'DVD Region: %s' ) ),
                              "DVD_TYPE"   => array( array( '538', '##', 'a', 'DVD Type: %s' ) ),
                              "EPISODES"   => array( array( '500', '##', 'a', 'Episodes: %s' ) ),
                              "EXPRODUCER" => array( array( '511', '0#', 'a', 'Ex-Producer: %s' ) ),
                              "FEATURES"   => array( array( '300', '##', 'e' ), ),
                              "FREEDB_ID"  => array( array( '500', '##', 'a', 'FreeDB ID: %s' ) ),
                              "GAMEDVLPR"  => array( array( '110', '2#', 'a' ) ),
                              "GAMEFLOW"   => array( array( '538', '##', 'a', 'Game Flow: %s' ) ),
                              "GAMEGENRE"  => array( array( '655', '#4', 'a' ) ),
                              "GAMEPBDATE" => array( array( '260', '',   'c' ),
                                                     array( '008/06-14', 's%Y####' ) ),
                              "GAMEPBLSHR" => array( array( '260', '',   'b' ),
                                                     array( '710', '2#', 'a' ) ),
                              "GAMEPERSP"  => array( array( '500','##',  'a', 'Perspecive: %s' ) ),
                              "GAMERATING" => array( array( '008/22', '%R' ),
                                                     array( '521', '8#', 'a', 'ESRB Rating: %s' ) ),
                              "GAMEREGION" => array( array( '538', '##', 'a', 'Region Encoding: %s' ) ),
                              "GAMEREQS"   => array( array( '538', '##', 'a', 'Game Requires: %s' ) ),
                              "GAMESYSTEM" => array( array( '250', '##', 'a', '%s (ed.)' ) ),
                              "GAME_ADDON" => array(), //ignore
                              "GAME_AUDIO" => array( array( '007/08', '%A' ),
                                                     array( '538', '##', 'a', 'Audio Format: %s' ) ),
                              "GAME_MEDIA" => array( array( '007/01', '%T' ),
                                                     array( '007/04', '%S' ) ),
                              "GAME_PLOT"  => array( array( '520', '##', 'a' ) ),
                              "IBLIST_ID"  => array( array( '500','##',  'a', 'IBListID: %s' ) ),
                              "IMAGEURL"   => array( array( '856', '4#', 'u' ),
                                                     array( '856', '',   'z', 'front cover image' ) ),
                              "IMAGEURLB"  => array( array( '856', '4#', 'u' ),
                                                     array( '856', '',   'z', 'back cover image' ) ),
                              "IMDBRATING" => array(), // ignore
                              "IMDB_ID"    => array( array( '500', '##', 'a', 'IMDB: %s' ) ),
                              "INSTRUMENT" => array( array( '511', '0#', 'a', 'Instramentalist: %s' ) ),
                              "ISBN"       => array( array( '020', '##', 'a' ) ),
                              "ISBN13"     => array( array( '020', '##', 'a' ) ),
                              "LYRICIST"   => array( array( '511', '0#', 'a', 'Lyricist: %s' ) ),
                              "MGPLTFRMID" => array( array( '500', '##', 'a', 'MobyGames Platform ID: %s' ) ),
                              "MOBYGAMEID" => array( array( '500', '##', 'a', 'MobyGameID: %s.' ) ),
                              "MOVIEGENRE" => array( array( '655', '#4', 'a' ) ),
                              "MOVIE_PLOT" => array( array( '520', '##', 'a' ) ),
                              "MUSICGENRE" => array( array( '655', '#4', 'a' ) ),
                              "NO_MEDIA"   => array( array( '300', '##', 'a', '%s discs ;' ) ),
                              "NO_PAGES"   => array( array( '300', '##', 'a', '%s p. ;' ) ),
                              "NO_PLAYERS" => array( array( '538', '##', 'a' ) ),
                              "PHONE_NO"   => array(), //ignore
                              "POSTCODE"   => array(), //ignore
                              "PRODUCER"   => array( array( '511', '0#', 'a', 'Producer: %s' ) ),
                              "PROGRAMMER" => array( array( '511', '0#', 'a', '%s, prg' ) ),
                              "PUBLISHER"  => array( array( '260', '',   'b' ) ),
                              "PUB_YEAR"   => array( array( '260', '',   'c' ),
                                                     array( '008/06-14', 's%Y####' ) ),
                              "RATIO"      => array( array( '538', '##', 'a', 'Aspect Ratio: %s' ) ),
                              "RUN_TIME"   => array( array( '008/18-20', '%3' ), ),
                              "SERIES"     => array( array( '246', '1#', 'a' ),
                                                     array( '830', '#0', 'a' ),
                                                     array( '490', '1#', 'a' ) ),
                              "STATE"      => array(), // ignore
                              "STUDIO"     => array( array( '260', '',   'b' ),
                                                     array( '710', '2#', 'a' ) ),
                              "SUBS_XTRA"  => array( array( '546', '##', 'Subtitle Extras: %s' ) ),
                              "SUBTITLES"  => array( array( '546', '##', 'Subtitle available in %s' ),
                                                     array( '041', '',   'j', '%L' ) ),
                              "SYNOPSIS"   => array( array( '520', '##', 'a' ) ),
                              "TEXT_LANG"  => array( array( '008/35-37', '%L' ),
                                                     array( '041', '',   'a', '%L' ) ),
                              "TV_RES"     => array( array( '346', '',   'b' ),
                                                     array( '538', '##', 'a', 'Resolution: %s' ) ),
                              "UPC_ID"     => array( array( '024', '1#', 'a' ) ),
                              "VHS_TYPE"   => array( array( '538', '##', 'a' ) ),
                              "VIDQUALITY" => array(), // IGNORE - VHS video quality
                              "VID_FORMAT" => array( array( '346', '',   'b' ) ), // NTSC or PAL
                              "VOCALIST"   => array( array( '511', '0#', 'a', 'Vocalist: %s' ) ),
                              "VOLUME"     => array( array( '245', '',   'n', 'Vol. %s' ),
                                                     array( '830', '',   'n' ), ),
                              "WRITER"     => array( array( '511', '0#', 'a', 'Writer: %s' ) ),
                              "YEAR"       => array( array( '260', '',   'c', '%Y' ),
                                                     array( '008/06-14', 's%Y####' ) ),

                              // custom
                              "LOC_DESC"   => array( array( '852', '', 'c' ) ),
                              "LOC_TYPE"   => array( array( '852', '', 'b' ) ),
                              "LOC_CLASS"  => array( array( '050', '', 'a' ) ),
    );


    /*
     * The file header, when saved as file.
     */
    function file_header($title) {
        $this->_file_title = $title;
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<collection xmlns="http://www.loc.gov/MARC21/slim">'."\n";
    }

    /*
     * The file footer, when saved as file.
     */
    function file_footer() {
        return "</collection>";
    }

    function start_item($item_id, $s_item_type, $opendbtitle) {
        $this->data = array();
        $this->saved = array();
        $this->type = $s_item_type;
        $title = $opendbtitle;
        foreach ($this->ARTICLES as $article) {
            $needle = ", ".rtrim($article);
            $len = strlen($needle);
            $end = substr($opendbtitle, -$len);
            if (strcasecmp($end, $needle) == 0) {
                $title = substr($end, 2).' '.substr($opendbtitle, 0, -$len);
                break;
            }
        }

        foreach ($this->_TYPE2MARC[$s_item_type] as $loc) {
            $this->_process_location($loc, $s_item_type);
        }
        foreach ($this->_TITLE2MARC[$s_item_type] as $loc) {
            $this->_process_location($loc, $title);
        }
        foreach ($this->_FILE2MARC as $loc) {
            $this->_process_location($loc, $this->_file_title);
        }
        return "  <record>\n";
    }

    function start_item_instance($item_id, $instance_no, $owner_id, $borrow_duration, $s_status_type, $status_comment, $update_on) {
        return "";
    }

    function end_item_instance() {
        return "";
    }

    function end_item() {
        // print MARC record
        $this->_processSaved();
        return $this->_marcRecord()."  </record>\n";
    }

    /* Run On every attribute for items and item instances */
    function item_attribute($s_attribute_type, $order_no, $attribute_val) {
        if (!array_key_exists($s_attribute_type, $this->_PARAM2MARC)) {
            // echo "MISSING PROPERTY: $s_attribute_type\n";
            $this->_process_location($this->_PARAM2MARC["COMMENTS"][0], $s_attribute_type.': '.$attribute_val);
            return "";
        }

        foreach ($this->_PARAM2MARC[$s_attribute_type] as $loc) {
            $this->_process_location($loc, $attribute_val);
        }
        return "";
    }
}
