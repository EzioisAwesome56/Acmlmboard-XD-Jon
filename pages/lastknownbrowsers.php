<?php

$title = "Last known browsers";

$isMod = $loguser['powerlevel'] > 0;
$sort = "id asc";
$ual = "";
if(isset($_GET['byua']))
{
	$sort = "lastknownbrowser asc";
	$ual .= "byua&amp;";
}
AssertForbidden("viewLKB");

MakeCrumbs(array(__("Admin") => actionLink("admin"), __("Last Known Browsers") => actionLink("lastknownbrowsers")), "");

$numUsers = FetchResult("select count(*) from {users} where powerlevel < 5");

$ppp = $loguser['postsperpage'];
if($ppp<1) $ppp=50;

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

$peeps = Query("select u.(_userfields), lastip, lastknownbrowser from {users} u where powerlevel < 5 order by {0} limit {1}, {2}", $sort, $from, $ppp);

$numonpage = NumRows($peeps);
for($i = $ppp; $i < $numUsers; $i+=$ppp)
{
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " ".actionLinkTag(($i/$ppp)+1, "lastknownbrowsers", "", $ual."from=".$i);
}
if($pagelinks)
{
	if($from == 0)
		$pagelinks = "1".$pagelinks;
	else
		$pagelinks = actionLinkTag(1, "lastknownbrowsers", "", $ual).$pagelinks;
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);
}


if($isMod)
	$format = "
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td>
				{2}
			</td>
			<td>
				{4}
			</td>
			<td>
				{5}
			</td>
		</tr>
	";
else
	$format = "
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
			<td>
				{2}
			</td>
			<td>
				{3}
			</td>
			<td>
				{5}
			</td>
		</tr>
	";

$items = "";
while($user = Fetch($peeps))
{
	$bucket = "userMangler"; include("./lib/pluginloader.php");
	$lip = $user['lastip'];
	$lkb = $user['lastknownbrowser'];
	$lkb = str_replace("-->", "", str_replace("<!--", " &mdash;", $lkb));

	$cellClass = ($cellClass+1) % 2;
	$items .= format($format, $cellClass, $user['id'], UserLink(getDataPrefix($user, "u_")), IP2C($lip), formatIP($lip), $lkb);
}

if($isMod)
	write("
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th>
				".__("ID")."
			</th>
			<th>
				".__("Name")."
			</th>
			<th>
				".__("IP")."
			</th>
			<th>
				".__("Last known browser")."
			</th>
		</tr>
		{0}
	</table>
", $items);
else
	write("
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th>
				".__("ID")."
			</th>
			<th>
				".__("Name")."
			</th>
			<th>
				&nbsp;
			</th>
			<th>
				".__("Last known browser")."
			</th>
		</tr>
		{0}
	</table>
", $items);

?>
