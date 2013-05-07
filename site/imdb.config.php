<?php
define('PILOT_IMDBFALLBACK', FALSE);

class mdb_config {
	var $imdbsite;
	protected $pilot_imdbfill;
	var $usecache;
	var $storecache;
	var $imdb_utf8recode;
	var $debug;
	var $maxresults;
	var $searchvariant;

	/** Constructor and only method of this base class.
	 *  There's no need to call this yourself - you should just place your
	 *  configuration data here.
	 * @constructor mdb_config
	 */
	function __construct() {
		/** IMDB server to use.
		 *  choices are www.imdb.&lt;lang&gt; with &lt;lang&gt; being one of
		 *  de|es|fr|it|pt, uk.imdb.com, and akas.imdb.com - the localized ones are
		 *  only qualified to find the movies IMDB ID (with the imdbsearch class;
		 *  akas.imdb.com will be the best place to search as it has all AKAs) -- but
		 *  parsing (with the imdb class) for most of the details will fail for
		 *  most of the details.
		 * @attribute string imdbsite
		 */
		$this->imdbsite = "akas.imdb.com";
		/** MoviePilot server to use.
		 *  choices are &lt;lang&gt;.moviepilot.com - where &lt;lang&gt; is one
		 *  of es|fr|pl|uk - , and www.moviepilot.de for German. More may follow
		 *  sometimes in the future. So it is really intended for chosing the
		 *  language of the desired content.
		 * @attribute string pilotsite
		 */
		$this->pilot_imdbfill = NO_ACCESS;

		/** Use a cached page to retrieve the information if available?
		 * @attribute boolean usecache
		 */
		$this->usecache = false;

		/** Store the pages retrieved for later use?
		 * @attribute boolean storecache
		 */
		$this->storecache = false;

		/** Try to recode all non-UTF-8 content to UTF-8?
		 *  As the name suggests, this only should concern IMDB classes.
		 * @attribute boolean imdb_utf8recode
		 */
		$this->imdb_utf8recode = FALSE;

		/** Enable debug mode?
		 * @attribute boolean debug
		 */
		$this->debug = 0;

		#--------------------------------------------------=[ TWEAKING OPTIONS ]=--
		/** Limit for the result set of searches.
		 *  Use 0 for no limit, or the number of maximum entries you wish. Default
		 *  (when commented out) is 20.
		 * @attribute integer maxresults
		 */
		$this->maxresults = 20;

		/** Moviename search variant. There are different ways of searching for a
		 *  movie name, with slightly differing result sets. Set the variant you
		 *  prefer, either "sevec", "moonface", or "izzy". The latter one is the
		 *  default if you comment out this setting or use an empty string.
		 * @attribute string searchvariant
		 */
		$this->searchvariant = "";

		/** Set the default user agent (if none is detected)
		 * @attribute string user_agent
		 */
		$this->default_agent = 'Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3';
		/** Enforce the use of a special user agent
		 * @attribute string force_agent
		 */
		$this->force_agent = '';

		/** Trigger the HTTP referer
		 *  This is required in some places. However, if you think you need to disable
		 *  this behaviour, do it here.
		 * @attribute boolean trigger_referer
		 */
		$this->trigger_referer = FALSE;
	}
}
?>
