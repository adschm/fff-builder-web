<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8" />
<title>fff-builder Table</title>
<link rel="stylesheet" href="/style.css" />
<link rel="stylesheet" href="/table.css" />
<meta name="robots" content="noindex, nofollow" />
</head>
<body>
<div id="mainframe">
<p class="head">Overview</p>

<table class="fwtable">
<tr>
<td class="tableheadleft">&nbsp;</td>
<td class="tablehead">Log</td>
<td class="tablehead">Images</td>
<td class="tablehead">Packages</td>
</tr>

<?php
error_reporting(E_ALL);

function empty_dir($dir) {
	$it = new FilesystemIterator($dir);
	return !$it->valid();
}

function tristate_dir($dir,$url,$label) {
	if(is_dir($dir)) {
		if(!empty_dir($dir)) {
			echo "<td><a href=\"".$url."\">".$label."</a></td>\n";
		} else {
			echo "<td class=\"tdempty\">empty</td>\n";
		}
	} else {
		echo "<td class=\"tdbuilding\">building</td>\n";
	}
}

$basepath = "/data/fwbin";
$dir = new DirectoryIterator($basepath);
$builddirs = array();
foreach($dir as $fullbuild) {
	if($fullbuild->isDot()) {continue;}
	if(!$fullbuild->isDir()) {continue;}

	$builddirs[$fullbuild->getFilename()] = array(
		"name"=>$fullbuild->getFilename(),
		"path"=>$fullbuild->getPathname()
		);
}
krsort($builddirs);

foreach($builddirs as $buildarray) {
	// e.g. /data/fwbin/2020-00-00_00-00_aaaaaabbbbbb
	$buildpath = $buildarray["path"];
	// e.g. /fw/2020-00-00_00-00_aaaaaabbbbbb
	$buildurl = "/fw/".$buildarray["name"];

	echo "<tr><td class=\"emptycol\" colspan=\"4\">&nbsp;</td></tr>\n";
	echo "<tr>\n";
	echo "<td class=\"fullbuildhead\">".$buildarray["name"]."</td>\n";
	echo "<td><a href=\"".$buildurl."/logs/prepare.out\">prepare log</a></td>\n";
	tristate_dir($buildpath."/targets",$buildurl."/targets/","all images");
	tristate_dir($buildpath."/packages",$buildurl."/packages/","all packages");
	echo "</tr>\n";

	$itertargets = new DirectoryIterator($buildpath."/logs");
	$arraytargets = array();
	foreach($itertargets as $logfile) {
		if($logfile->isDot() or substr($logfile->getFilename(),0,5) != "build") {
			continue;
		}
		// e.g. node-ath79-generic
		$targetname = substr($logfile->getBasename('.'.$logfile->getExtension()),6);
		$arraytargets[$targetname] = array(
			"name"=>$targetname,
			"logfile"=>$logfile->getFilename()
			);
	}
	ksort($arraytargets);
	foreach($arraytargets as $target) {
		echo "<tr>\n";
		echo "<td>".$target["name"]."</td>\n";
		echo "<td><a href=\"".$buildurl."/logs/".$target["logfile"]."\">build log</a></td>\n";
		$targetdir = $buildpath."/targets/".$target["name"];
		tristate_dir($targetdir,$buildurl."/targets/".$target["name"]."/","images");
		$packagedir = $buildpath."/packages/".$target["name"];
		tristate_dir($packagedir,$buildurl."/packages/".$target["name"]."/","packages");
		echo "</tr>\n";
	}
}
?>
</table>
</div>
</body>
</html>

