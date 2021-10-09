<?php
/*
 Open Media Collectors Database
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

/*
 * @param $img is DOMElement
 * return URL or ""
 */
function amazon_image2url($img) {
    $i = $img->getAttribute('src');
    if (starts_with($i, "data:")) {
        $i = $img->getAttribute("data-a-dynamic-image");
        if ($i)
            $i = preg_replace('/.*?(http[^&"]+).*/', "$1", $i);
    }

    if ($i) {
        // remove image extras _xxx_.
        return preg_replace('!(\/[^.]+\.)_[^.]+_\.!', "$1", $i);
    }
    return "";
}


function _def2array($outer, $item, $def) {
    $a = [];
    foreach($outer->getElementsByTagName($item) as $li) {
        $text = "";
        $b = false;
        foreach($li->childNodes as $c)
            if ($b) {
                if (trim($c->textContent))
                    $text .= $c->textContent;
            } else if ($c->nodeType == XML_ELEMENT_NODE && $c->tagName == $def && trim($c->textContent))
                $b = $c;
        if ($b)
            $a[trim($b->textContent, " \t\n\r\0\x0B:")] = trim($text);
    }
    return $a;
}

// DOMElement of <ul><li><b>KEY:</b> value</li>...<ul>
function _boldul2array($ul) {
    return _def2array($ul, 'li', 'b');
}

function amazon_details($xmlDoc) {
    // UL of definition list items
    $e = $xmlDoc->getElementById('productDetailsTable');
    if (!$e)
        $e = $xmlDoc->getElementById('detail-bullets');
    if ($e) {
        $e = $e->getElementsByTagName('ul')->item(0);
        if ($e)
            return _boldul2array($e);
    }

    // Table of th td pairs
    $a = [];
    $e = $xmlDoc->getElementById('productDetails_detailBullets_sections1');
    if ($e) {
        foreach($e->getElementsByTagName('tr') as $tr) {
            $th = trim($tr->getElementsByTagName('th')->item(0)->textContent);
            $a[$th] = trim($tr->getElementsByTagName('td')->item(0)->textContent);
        }
    }

    return $a;
}

function amazon_rank2genre($str) {
    preg_match_all("/^\s*in\s+(.*?)\n/m", $str, $regs);
    $a = [];
    foreach($regs[1] as $reg)
        $a = array_unique(array_merge($a, preg_split("/\s*>\s*/", $reg)));
    return $a;
}
?>
