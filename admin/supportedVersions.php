<?php

function getSupportedVersions()
{
    $supportedVersions = [];

    $dom = new DomDocument();
    @$dom->loadHTMLFile('https://www.php.net/supported-versions.php');
    $xpath = new DomXPath($dom);

    // get the table with the Currently Supported Versions
    $nodes = $xpath->query("//h3[contains(text(), 'Currently Supported Versions')]/../table[1]/tbody");
    if ($nodes->length != 1) {
        return $supportedVersions;
    }

    $table = $dom->saveHTML($nodes->item(0));

    preg_match_all('/<tr class="(.*)">.*<td>.*<a href=".*">(.*)<\/a>.*<\/td>/Us', $table, $matches);

    foreach ($matches[2] as $k => $branch) {
        $supportedVersions[] = ['branch' => $branch, 'status' => $matches[1][$k]];
    }

    return $supportedVersions;
}