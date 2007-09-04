<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2006 by Jason Pell

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

// This must be first - includes config.php
require_once("./include/begin.inc.php");

include_once("./functions/database.php");
include_once("./functions/auth.php");
include_once("./functions/logging.php");
include_once("./functions/http.php");

$siteUrl = get_site_url();

$shortName = get_opendb_title();
$description = "OpenDb title search on ".$siteUrl;
$searchUrl = $siteUrl."listings.php?search_list=y&amp;linked_items=include&amp;title_match=partial&amp;title={searchTerms}";

header("Content-Type: text/xml");
?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>

<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?php echo $shortName; ?></ShortName>
<Description><?php echo $description; ?></Description>
<Tags>opendb title search</Tags>
<Image height="16" width="16" type="image/png">data:image/gif;base64,R0lGODlhHgAWAOfwAABUswBWtQBYsQBYtwBXvgNZshZSswBdtQBetgBftwBdwglZwAtbtABguABhuQ5dqQBiugBktgFltxJazxVdtxVbygZmuABnxQpnuQBsvSNilgBtvxxfwA5ougBtzB9ithJpuzFhlxdrvTljjT5kghxrxRxuukBmhA51wUJlih5vuwB60iBwvAB9z0RqiBV3wyRyvk5pfldqdCh0wR15zCx4viF8yGZrbV9udD13pi96wTF7wjJ8w3hsWzR9xHxwX2l0gDiAxzyFxkqDs0CIyUmEx0OKy0SLzE%2BLyFCMyVGNykqSzU6V0U%2BW0lCX01iU0ViXzWWVwdd5IVuZz954G12b0V6c02Ce1f91AP92AP96AP97AGqg0V6k4P98AP5%2FAGyi0%2F%2BAAP%2BBAP%2BCAG%2Bl1%2F%2BBEP%2BCEnCm2P%2BGAP%2BHAHGn2f%2BIAf6GEnOp2%2F%2BHFP%2BIFXmp1f%2BNBP6MFv%2BNGH2s2X6t2v%2BOJYCv3P6SJYGw3f%2BTJ%2F%2BVHv%2BUKP6XHYex2P%2BVKYiy2oSz4Imz2%2F6YM%2F%2BZNIy13Y223v%2BcPo%2B44P6fP%2BOlaZa63ZK85Je73v%2BiScCyoJi83%2F6mSqG91P%2BnS%2F%2BnUv%2BoU%2F%2BpVJ3C5P%2BqXJ7D5f6tXP%2BuXf%2BvXv%2BxZqjI5f60Z6nJ5qbK7f%2B2b%2BG%2Fn6zM6v%2B4d63N67PO5v%2B9ef29gP%2B%2BerXQ6P%2B%2Fgv%2FDff3Dg%2F3Ei7nV7f%2FGjf%2FHiLvX7%2F%2FJlf%2FMif7MkMLa7P7Nl%2BDVyP7Onv%2FQoMff8f%2FSof%2FSp8jg8v%2FTqf3Wqf3YstDk8P%2FZs%2F%2FatP7butjk8%2F%2Fcu%2F%2Fevdrn9f%2Ffvv3hvv%2FfxNzo9t3p9%2F3jxt7q%2BNbu%2F%2BLp%2F%2F%2Fmyf7mz%2BTt9f%2Fo0f%2Frxv%2Fp0ufv%2BP%2Fq2enx%2Bv%2Fs2v%2Ft2%2Fzx5O%2F19%2F7y5fD2%2BP%2Fz5v%2F05%2F727vT5%2FP%2F37%2FX6%2Ff%2F48f%2F58v%2F5%2BPj%2B%2F%2F%2F8%2Bvz%2F%2B%2F7%2F%2FP%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2FyH%2BFUNyZWF0ZWQgd2l0aCBUaGUgR0lNUAAsAAAAAB4AFgAACP4A37375QfOKYGkIImbVScTOoHaFtFZ9U5co1ncCgWKJtDPBwgYGiRBV6SBjw4MPiB5B8uEgwgfzhxDwKIGAgYqns3CYMEULxQM%2FEBpgMGTJwwC%2FKDAoIydEAeFPiBY4smEgDxgKCh5t05Qgx1EBMDhygVBhg8djhixuiSCCWvvpgjgAuUDFHbsFiHQQSQAHbxqpH6IoGNGjR1gGphQ1u4KAiiXEMwA9w4JgC5MBAgxZ62GACgRQAxrx67dtAIilL3jUsCKOCEIMKAg8GDUkAAUPJRQQIMbHAocWqyw8WiBB2nsmlCA8o4bHR02gFAJQ4KAjBsaZORq906SixEpev5IORFD0ThDSAyRXveOXR8vXk5M%2BDHGy5c0xNhRG6MFTRw3aMBHCDsCFfjOOPXFkYMCUdTihhdb2KGOLmV44cg667xS4RjIsMNegcaMgcUeQoXyDjIPhoFLKvCl0t47c2yRRocGvqNhGY5w9WEiXmBRiSNahNEMe8y4EeE5NbZDCYSoECgQO5h84QUfD46hii6ilIGFG8TUKNAgXrgBjIHtVALfHA96oQV8XhwyjpMGjvOgG9sYyM4gW5SBRoVuDDJHGF6U0YmXJ85Z4zZzwGcHfKpgqEuAYehS4zq0QJhIjZrAhwcheQbTHjtLbqGJl5t4EQYqBuICHxasEIIFGml1CsTjFoPaeYgWZcQSTjjEYFKhF5SMM0d%2F5awzjiiAjmFMjeXEGGgYYYgYKCXqVANhGYPw8augXkLzK3xbjNGnLgSmAigWWJhahh2SellOMLqwkgoqtASzDXdcVaMLLbW0gosv1HBVY0AAOw%3D%3D</Image>
<Url type="text/html" method="GET" template="<?php echo $searchUrl; ?>"/>
<InputEncoding>UTF-8</InputEncoding>
<AdultContent>false</AdultContent>
</OpenSearchDescription>

<?php
// Cleanup after begin.inc.php
require_once("./include/end.inc.php");
?>