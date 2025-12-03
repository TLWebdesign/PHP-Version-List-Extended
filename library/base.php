<?php

if (!function_exists('shell_exec')) {
    echo '<div class="alert alert-danger"><strong>PHP Error:</strong> function shell_exec is not available or disabled.<br>Plugin can not be used until this function is available.</div>';
    return;
}
require_once dirname(__DIR__) . '/library/httpsocket.php';

$supportedVersionsDataFile = dirname(__DIR__) . '/supportedVersions';

// refresh the supportedVersions if the file not exists or the data is older than 24 hours
if (!file_exists($supportedVersionsDataFile) || (time() - filemtime($supportedVersionsDataFile) > 24 * 3600)) {
    require_once dirname(__DIR__) . '/library/supportedVersions.php';

    $data = fopen($supportedVersionsDataFile, "w+");
    fwrite($data, serialize(getSupportedVersions()));
    fclose($data);
}

$supportedVersions = unserialize(file_get_contents($supportedVersionsDataFile));

function getDaUserInfo($username)
{
    $info    = ['usertype' => null, 'creator' => null];
    $baseDir = '/usr/local/directadmin/data/users/';
    $file    = $baseDir . $username . '/user.conf';

    if (!is_file($file)) {
        return $info;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        if ($key === 'usertype') {
            $info['usertype'] = $value;
        } elseif ($key === 'creator') {
            $info['creator'] = $value;
        }
    }

    return $info;
}

function filterListForCurrentUser(&$list)
{
    $daUser = isset($_SERVER['USER']) ? $_SERVER['USER'] : '';

    if ($daUser === '') {
        return;
    }

    $info = getDaUserInfo($daUser);
    $type = isset($info['usertype']) ? $info['usertype'] : null;

    // Normal user: don't show anything
    if ($type === 'user') {
        return;
    }

    // Reseller: see their own account + all users they are creator of
    if ($type === 'reseller') {
        $allowed = [$daUser];

        foreach (glob('/usr/local/directadmin/data/users/*', GLOB_ONLYDIR) as $dir) {
            $u = basename($dir);

            if ($u === $daUser) {
                continue;
            }

            $meta = getDaUserInfo($u);
            if (isset($meta['creator']) && $meta['creator'] === $daUser) {
                $allowed[] = $u;
            }
        }

        foreach (array_keys($list) as $u) {
            if (!in_array($u, $allowed, true)) {
                unset($list[$u]);
            }
        }

        return;
    }

    // Admin or unknown: no filtering
}

function colorize($version)
{
    global $supportedVersions;
    $colorized = "";

    if (empty($version)) {
        return $colorized;
    }

    $allBranches = [];
    foreach ($supportedVersions as $supportedVersion) {
        $allBranches[] = $supportedVersion['branch'];

        if (preg_match('/^' . $supportedVersion['branch'] . '/', $version)) {
            $color = ($supportedVersion['status'] === 'security') ? '#f93' : 'black';
            $color = ($supportedVersion['status'] === 'stable') ? '#9c9' : $color;

            $colorized = '<span style="font-weight: bold; color: ' . $color . '">' . $version . '</span>';
            break;
        }
    }

    if (empty($colorized)) {
        if ((float)$version < (float)min($allBranches)) {
            $colorized = '<span style="font-weight: bold; color: #f33">' . $version . '</span>';
        } else {
            $colorized = $version;
        }
    }

    return $colorized;
}

function phpVersionCompare($a, $b)
{
    $a = strip_tags($a);
    $b = strip_tags($b);

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

// fetch PHP versions via an API call (no rights to access the custombuild options.conf file)
$sock = new HTTPSocket;
$sock->connect("ssl://" . $_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT']);
$sock->set_login($_SERVER['USER']);
$sock->query('/CMD_API_SYSTEM_INFO');
$systemInfo = $sock->fetch_parsed_body();

// Colorize all PHP version entries dynamically (php, php2, php3, ..., php10)
$keys        = array_keys($systemInfo);
$versionKeys = preg_grep('/^php(\d*)$/', $keys);

foreach ($versionKeys as $keyValue) {
    if (!empty($systemInfo[$keyValue])) {
        $systemInfo[$keyValue] = colorize($systemInfo[$keyValue]);
    }
}

/**
 * Translate the stored phpX_select value to the actual PHP version string.
 * $value should be a string that is passed (e.g. "1", "2", ... "10", "0").
 */
function translateValueToVersion($phpEnabled, $value)
{
    global $systemInfo;

    if ($phpEnabled !== 'ON') {
        return;
    }

    // Explicit 0 or empty means PHP is off for this selector
    if ($value === '0' || $value === null || $value === '') {
        return 'Off';
    }

    $installId = (int) $value;
    if ($installId < 1) {
        return;
    }

    // In DirectAdmin, "1" maps to "php", "2" to "php2", etc.
    $key = ($installId === 1) ? 'php' : 'php' . $installId;

    if (isset($systemInfo[$key]) && $systemInfo[$key] !== '') {
        return $systemInfo[$key];
    }

    // Fallback if the system reports a selector value we do not know
    return 'Unknown';
}

// read all the directadmin users data config files. /dev/null added to have at least 2 files to search on as with only
// 1 match (domain) it won't show you the path.
// ignore files which ends with .hotlink.conf
$phpConfigData = shell_exec("grep -E '^(php=|php1_select=|subdomain=)' `find /usr/local/directadmin/data/users/*/domains -type f | grep -v '\\.hotlink\\.conf$'` /dev/null");
preg_match_all("/users\\/(?P<user>[^\\/]+)\\/domains\\/(?P<file>[^:]+)\:(?P<data>.*)\n/U", $phpConfigData, $matches);

$list   = [];
$params = []; // array used to know later which phpx_select parameters there are

// First pass: parse main domain .conf files to get base domain PHP settings
foreach ($matches[0] as $k => $v) {
    $user     = $matches['user'][$k];
    $fileFull = $matches['file'][$k];
    $dataLine = trim($matches['data'][$k]);

    // Only interested in main domain config files here
    if (substr($fileFull, -5) !== '.conf') {
        continue;
    }

    if (strpos($dataLine, '=') === false) {
        continue;
    }

    list($param, $val) = explode('=', $dataLine, 2);

    // Only care about php flag and php1_select
    if ($param !== 'php' && $param !== 'php1_select') {
        continue;
    }

    $baseDomain = substr($fileFull, 0, -5); // strip ".conf"

    $params[]                         = $param;
    $list[$user][$baseDomain][$param] = $val;
}

// Second pass: expand subdomains using `<domain>.subdomains` and `<domain>.subdomains.docroot.override`
foreach ($list as $user => $domains) {
    $domainsDir   = '/usr/local/directadmin/data/users/' . $user . '/domains';
    $baseDomains  = array_keys($domains);

    foreach ($baseDomains as $baseDomain) {
        $subListFile  = $domainsDir . '/' . $baseDomain . '.subdomains';
        $overrideFile = $domainsDir . '/' . $baseDomain . '.subdomains.docroot.override';

        if (!is_file($subListFile)) {
            continue;
        }

        // Read subdomain names (one per line)
        $subNames = file($subListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Build overrides map from docroot.override
        $subOverrides = [];
        if (is_file($overrideFile)) {
            $overrideLines = file($overrideFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($overrideLines as $line) {
                if ($line === '' || strpos($line, '=') === false) {
                    continue;
                }

                list($label, $query) = explode('=', $line, 2);

                $decoded = urldecode($query);
                $parsed  = [];
                parse_str($decoded, $parsed);

                if (isset($parsed['php1_select']) && $parsed['php1_select'] !== '') {
                    $subOverrides[$label] = $parsed['php1_select'];
                }
            }
        }

        // Determine base PHP settings for this domain
        $baseData    = $list[$user][$baseDomain] ?? [];
        $mainPhpFlag = isset($baseData['php']) ? $baseData['php'] : 'ON';
        $mainPhpSel  = isset($baseData['php1_select']) ? $baseData['php1_select'] : '1';

        foreach ($subNames as $subName) {
            $subName = trim($subName);
            if ($subName === '') {
                continue;
            }

            // Build the FQDN for the subdomain
            $subDomainFqdn = $subName . '.' . $baseDomain;

            // If there is an explicit php1_select override, use it; otherwise inherit from main domain
            $subPhpSel = isset($subOverrides[$subName]) ? $subOverrides[$subName] : $mainPhpSel;

            $list[$user][$subDomainFqdn]['php']         = $mainPhpFlag;
            $list[$user][$subDomainFqdn]['php1_select'] = $subPhpSel;
        }
    }
}

// not all config files have a variable php1_select, this is causing an incorrect count in the summary table
// my feeling is that when you create an account and you do not touch the PHP version settings that the php1_select variable
// will not be in the file and DirectAdmin is for them taking the first PHP version version.
// also a second correction will be done if the php variable has the value OFF, unset all other variables from the data array.
foreach ($list as $user => $domains) {
    foreach ($domains as $domain => $data) {
        // For some subdomain configs there may be no explicit "php" flag; assume ON by default
        $phpFlag = isset($data['php']) ? $data['php'] : 'ON';

        if ($phpFlag === 'ON' && !array_key_exists('php1_select', $data)) {
            $data['php1_select'] = '1';
            $data['php']         = 'ON';
            $list[$user][$domain] = $data;
        } else {
            if ($phpFlag === 'OFF') {
                // When PHP is disabled, ignore all other settings for that (sub)domain
                $list[$user][$domain] = ['php' => 'OFF'];
            } else {
                // Ensure the php flag is present for later logic
                $data['php']          = $phpFlag;
                $list[$user][$domain] = $data;
            }
        }
    }
}

// Filter the list based on the current DirectAdmin user (admin/reseller/user)
filterListForCurrentUser($list);

/*
// make the values unique
$params = array_unique($params);
var_dump($params);
*/

$keys        = array_keys($systemInfo);
$versionKeys = preg_grep('/^php(\d*)$/', $keys);

// build an array with key the php install id (1, .., N) and as value the php version
$phpVersions = [];
foreach ($versionKeys as $keyValue) {
    if ($keyValue === 'php') {
        $num = 1;
    } else {
        $suffix = substr($keyValue, 3); // after "php"
        $num    = (int) $suffix;
        if ($num < 2) {
            $num = 1;
        }
    }

    $phpVersions[$num] = $systemInfo[$keyValue];
}
uasort($phpVersions, 'phpVersionCompare');

// create a simple array with key the php install id (1..N) and as value the count
$stats         = [];
$phpInstallIds = array_keys($phpVersions);

foreach ($phpInstallIds as $installId) {
    $stats[$installId] = 0;
}

// loop to build up the stats, only considering php1_select (DA no longer supports a second selector)
foreach ($list as $user => $domains) {
    foreach ($domains as $domain => $settings) {
        if (!isset($settings['php1_select'])) {
            continue;
        }

        $val = (int) $settings['php1_select'];
        if ($val === 0) {
            continue;
        }

        if (!isset($stats[$val])) {
            $stats[$val] = 0;
        }

        $stats[$val]++;
    }
}

$totalUsage = array_sum($stats);
?>
<h3 style="padding-left: 10px">PHP version summary</h3>
<div class="table-responsive px-3">
    <table class="table table-striped table-hover" cellspacing="1" cellpadding="3" style="width: auto !important;">
        <thead>
        <tr>
            <th scope="col"></th>
            <th scope="col">Usage</th>
        </tr>
        </thead>

        <tbody>
        <?php

        foreach ($phpVersions as $key => $version) {
            if (empty($version)) {
                continue;
            }

            $count = $stats[$key] ?? 0;

            echo "
            <tr>
                <th scope=\"row\">PHP {$version}</th>
                <td class=\"text-end\">{$count}</td>
            </tr>\n";
        }

        echo "
            <tr>
                <th scope=\"row\">Total</th>
                <td class=\"text-end\">{$totalUsage}</td>
            </tr>\n";
        ?>
        </tbody>
    </table>
</div>

<h3 style="padding-left: 10px">PHP version overview per domain</h3>
<div class="table-responsive px-3">
    <table class="table table-striped table-hover" cellspacing="1" cellpadding="3">
        <thead>
        <tr>
            <th scope="col">Nr</th>
            <th scope="col">User</th>
            <th scope="col">Domain</th>
            <th scope="col">PHP</th>
            <th scope="col">PHP Version</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $rowNumber = 1;
        foreach ($list as $user => $domains) {
            foreach ($domains as $domain => $settings) {
                $phpFlag    = isset($settings['php']) ? $settings['php'] : 'ON';
                $phpEnabled = ($phpFlag === 'ON') ? '<span style="color: green">ON</span>' : '<span style="color: red; font-weight: bold">OFF</span>';

                $firstPhpId = isset($settings['php1_select']) ? $settings['php1_select'] : '1';
                $firstPhp   = translateValueToVersion($phpFlag, $firstPhpId);

                echo "<tr><td>{$rowNumber}</td> <td>{$user}</td> <td>{$domain}</td> <td>{$phpEnabled}</td> <td>{$firstPhp}</td></tr>\n";
                $rowNumber++;
            }
        }
        ?>
        </tbody>
    </table>
</div>