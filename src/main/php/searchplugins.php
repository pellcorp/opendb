<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2001-2012 by Jason Pell

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

    Reference for creating the plugin:
        http://developer.mozilla.org/en/docs/Creating_OpenSearch_plugins_for_Firefox
        http://software.hixie.ch/utilities/cgi/data/data
 */

// This must be first - includes config.php
require_once("include/begin.inc.php");

include_once("lib/database.php");
include_once("lib/auth.php");
include_once("lib/logging.php");
include_once("lib/http.php");

$siteUrl = get_site_url();
$siteTitle = get_opendb_title();
if ($_GET['type'] == "title") {
	$shortName = "$siteTitle Title";
	$description = "OpenDb title search on " . $siteUrl;
	$searchUrl = $siteUrl . "listings.php?search_list=y&amp;linked_items=include&amp;title_match=partial&amp;title={searchTerms}";
	$searchtags = "opendb title search";
} else if ($_GET['type'] == "upc") {
	$shortName = "$siteTitle UPC";
	$description = "OpenDb UPC search on " . $siteUrl;
	$searchUrl = $siteUrl . "listings.php?search_list=y&amp;attribute_type=UPC_ID&amp;attr_match=partial&amp;attribute_val={searchTerms}";
	$searchtags = "opendb upc search";
}
header("Content-Type: text/xml");
?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>

<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?php echo htmlspecialchars($shortName); ?></ShortName>
<Description><?php echo $description; ?></Description>
<Tags><?php echo $searchtags; ?></Tags>
<Image height="16" width="16" type="image/png">data:image/png,%89PNG%0D%0A%1A%0A%00%00%00%0DIHDR%00%00%00%10%00%00%00%10%08%03%00%00%00(-%0FS%00%00%00%01sRGB%00%AE%CE%1C%E9%00%00%02%D0PLTE%00T%B3%00V%B5%00X%B1%00X%B7%00W%BE%03Y%B2%16R%B3%00%5D%B5%00%5E%B6%00_%B7%00%5D%C2%09Y%C0%0B%5B%B4%00%60%B8%00a%B9%0E%5D%A9%00b%BA%00d%B6%01e%B7%12Z%CF%15%5D%B7%15%5B%CA%06f%B8%00g%C5%0Ag%B9%00l%BD%23b%96%00m%BF%1C_%C0%0Eh%BA%00m%CC%1Fb%B6%12i%BB1a%97%17k%BD9c%8D%3Ed%82%1Ck%C5%1Cn%BA%40f%84%0Eu%C1Be%8A%1Eo%BB%00z%D2%20p%BC%00%7D%CFDj%88%15w%C3%24r%BENi~Wjt(t%C1%1Dy%CC%2Cx%BE!%7C%C8fkm_nt%3Dw%A6%2Fz%C11%7B%C22%7C%C3xl%5B4%7D%C4%7Cp_it%808%80%C7%3C%85%C6J%83%B3%40%88%C9I%84%C7C%8A%CBD%8B%CCO%8B%C8P%8C%C9Q%8D%CAJ%92%CDN%95%D1O%96%D2P%97%D3X%94%D1X%97%CDe%95%C1%D7y!%5B%99%CF%DEx%1B%5D%9B%D1%5E%9C%D3%60%9E%D5%FFu%00%FFv%00%FFz%00%FF%7B%00j%A0%D1%5E%A4%E0%FF%7C%00%FE%7F%00l%A2%D3%FF%80%00%FF%81%00%FF%82%00o%A5%D7%FF%81%10%FF%82%12p%A6%D8%FF%86%00%FF%87%00q%A7%D9%FF%88%01%FE%86%12s%A9%DB%FF%87%14%FF%88%15y%A9%D5%FF%8D%04%FE%8C%16%FF%8D%18%7D%AC%D9~%AD%DA%FF%8E%25%80%AF%DC%FE%92%25%81%B0%DD%FF%93'%FF%95%1E%FF%94(%FE%97%1D%87%B1%D8%FF%95)%88%B2%DA%84%B3%E0%89%B3%DB%FE%983%FF%994%8C%B5%DD%8D%B6%DE%FF%9C%3E%8F%B8%E0%FE%9F%3F%E3%A5i%96%BA%DD%92%BC%E4%97%BB%DE%FF%A2I%C0%B2%A0%98%BC%DF%FE%A6J%A1%BD%D4%FF%A7K%FF%A7R%FF%A8S%FF%A9T%9D%C2%E4%FF%AA%5C%9E%C3%E5%FE%AD%5C%FF%AE%5D%FF%AF%5E%FF%B1f%A8%C8%E5%FE%B4g%A9%C9%E6%A6%CA%ED%FF%B6o%E1%BF%9F%AC%CC%EA%FF%B8w%AD%CD%EB%B3%CE%E6%FF%BDy%FD%BD%80%FF%BEz%B5%D0%E8%FF%BF%82%FF%C3%7D%FD%C3%83%FD%C4%8B%B9%D5%ED%FF%C6%8D%FF%C7%88%BB%D7%EF%FF%C9%95%FF%CC%89%FE%CC%90%C2%DA%EC%FE%CD%97%E0%D5%C8%FE%CE%9E%FF%D0%A0%C7%DF%F1%FF%D2%A1%FF%D2%A7%C8%E0%F2%FF%D3%A9%FD%D6%A9%FD%D8%B2%D0%E4%F0%FF%D9%B3%FF%DA%B4%FE%DB%BA%D8%E4%F3%FF%DC%BB%FF%DE%BD%DA%E7%F5%FF%DF%BE%FD%E1%BE%FF%DF%C4%DC%E8%F6%DD%E9%F7%FD%E3%C6%DE%EA%F8%D6%EE%FF%E2%E9%FF%FF%E6%C9%FE%E6%CF%E4%ED%F5%FF%E8%D1%FF%EB%C6%FF%E9%D2%E7%EF%F8%FF%EA%D9%E9%F1%FA%FF%EC%DA%FF%ED%DB%FC%F1%E4%EF%F5%F7%FE%F2%E5%F0%F6%F8%FF%F3%E6%FF%F4%E7%FE%F6%EE%F4%F9%FC%FF%F7%EF%F5%FA%FD%FF%F8%F1%FF%F9%F2%FF%F9%F8%F8%FE%FF%FF%FC%FA%FC%FF%FB%FE%FF%FC84%E9%CB%00%00%00%01bKGD%00%88%05%1DH%00%00%00%09pHYs%00%00%0B%13%00%00%0B%13%01%00%9A%9C%18%00%00%00%07tIME%07%D8%07%10%15%3B(%C3j%994%00%00%00%1DtEXtComment%00Created%20with%20The%20GIMP%EFd%25n%00%00%00%CCIDAT%18%D3cx%8F%06%180%05%F6%17%BC_%F2%A8%F4%C5%FB%5B%25%EF%7B%EF4%BEg%D8%2C%B6%87'%40b%1ES%9D%C4%1B%3Eyo%B5J%86%80%807%1C.%25o%E49%04%8D%ADyO%85%070%3C%E2%D0%E0w%16Q5%B9%23%A2k%C6%7D%C97%80%E1%ED%EB7q%EA%F6q%99G%AED%15f%C4%BD%01%D9r%2C%A2n%E1%89%B8%C4%95q%EF%DFG%9F%00%09%ACO%7D%FF%FA%7D%5CD_%E2%EB3%D1%CFA%02%CDy%EF%DF%BF%8D%8B%CB%8B%8B%8B%7B%0CvX%1EP%E06%90%B7%EAuF%22X%20n%05%10G%AC%89%B8%FD%BE3%1A%24%F04%3A51%22%F5%D5%D5%E8%E6%D4%B8%B9%60%81%5D%2B%B7%DC~%FF%FA%EA%96%B5%FBpx%0E%0D%00%00%DF%F6%ADc%9B%9AT%9A%00%00%00%00IEND%AEB%60%82</Image>
<Url type="text/html" method="GET" template="<?php echo $searchUrl; ?>"/>
<InputEncoding>UTF-8</InputEncoding>
<AdultContent>false</AdultContent>
</OpenSearchDescription>
<?php
// Cleanup after begin.inc.php
require_once("include/end.inc.php");
?>