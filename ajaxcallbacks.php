<?php

$ajaxPage = true;
include("lib/common.php");
header("Cache-Control: no-cache");

$action = $_GET['a'];
$id = (int)$_GET['id'];
$hideTricks = " <a href=\"javascript:void(0)\" onclick=\"hideTricks(".$id.")\">".__("Back")."</a>";
if($action == "q")	//Quote
{
	$qQuote = "	select
					p.id, p.deleted, pt.text,
					f.minpower,
					u.name poster
				from posts p
					left join {posts_text} pt on pt.pid = p.id and pt.revision = p.currentrevision
					left join {threads} t on t.id=p.thread
					left join {forums} f on f.id=t.forum
					left join {users} u on u.id=p.user
				where p.id={0}";
	$rQuote = Query($qQuote, $id);

	if(!NumRows($rQuote))
		die(__("Unknown post ID."));

	$quote = Fetch($rQuote);

	if($quote['minpower'] > $loguser['powerlevel'])
		die("No.");

	if ($quote['deleted'])
		$quote['text'] = __("Post is deleted");

	$reply = "[quote=\"".$quote['poster']."\" id=\"".$quote['id']."\"]".$quote['text']."[/quote]";
	$reply = str_replace("/me", "[b]* ".htmlspecialchars($quote['poster'])."[/b]", $reply);
	die($reply);
}
else if ($action == 'rp') // retrieve post
{
	$rPost = Query("	
			SELECT 
				p.id, p.date, p.num, p.deleted, p.deletedby, p.reason, p.options, p.mood, p.ip, 
				pt.text, pt.revision, pt.user AS revuser, pt.date AS revdate,
				u.(_userfields), u.(rankset,title,picture,posts,postheader,signature,signsep,lastposttime,lastactivity,regdate,globalblock),
				ru.(_userfields),
				du.(_userfields),
				f.id fid
			FROM 
				{posts} p 
				LEFT JOIN {posts_text} pt ON pt.pid = p.id AND pt.revision = p.currentrevision 
				LEFT JOIN {users} u ON u.id = p.user
				LEFT JOIN {users} ru ON ru.id=pt.user
				LEFT JOIN {users} du ON du.id=p.deletedby
				LEFT JOIN {threads} t ON t.id=p.thread
				LEFT JOIN {forums} f ON f.id=t.forum
			WHERE p.id={0}", $id);

	
	if (!NumRows($rPost))
		die(__("Unknown post ID."));
	$post = Fetch($rPost);

	if (!CanMod($loguserid, $post['fid']))
		die(__("No."));

	die(MakePost($post, isset($_GET['o']) ? POST_DELETED_SNOOP : POST_NORMAL, array('tid'=>$post['thread'], 'fid'=>$post['fid'])));
}
else if($action == "ou")	//Online Users
{
	die(OnlineUsers((int)$_GET['f'], false));
}
else if($action == "tf")	//Theme File
{
	$theme = $_GET['t'];

	$themeFile = "themes/$theme/style.css";
	if(!file_exists($themeFile))
		$themeFile = "themes/$theme/style.php";


function checkForImage(&$image, $external, $file)
{
	global $dataDir, $dataUrl;
	
	if($image) return;
	
	if($external)
	{		
		if(file_exists($dataDir.$file))
			$image = $dataUrl.$file;
	}
	else
	{
		if(file_exists($file))
			$image = $file;
	}
}

	checkForImage($layout_logopic, true, "logos/logo_$theme.png");
	checkForImage($layout_logopic, true, "logos/logo_$theme.jpg");
	checkForImage($layout_logopic, true, "logos/logo_$theme.gif");
	checkForImage($layout_logopic, true, "logos/logo.png");
	checkForImage($layout_logopic, true, "logos/logo.jpg");
	checkForImage($layout_logopic, true, "logos/logo.gif");
	checkForImage($layout_logopic, false, "themes/$theme/logo.png");
	checkForImage($layout_logopic, false, "themes/$theme/logo.jpg");
	checkForImage($layout_logopic, false, "themes/$theme/logo.gif");
	checkForImage($layout_logopic, false, "themes/$theme/logo.png");
	checkForImage($layout_logopic, false, "img/logo.png");

	die($themeFile."|".$layout_logopic);
}
elseif($action == "srl")	//Show Revision List
{
	$qPost = "select currentrevision, thread from {posts} where id={0}";
	$rPost = Query($qPost, $id);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die(format(__("Unknown post ID #{0}."), $id)." ".$hideTricks);

	$qThread = "select forum from {threads} where id={0}";
	$rThread = Query($qThread, $post['thread']);
	$thread = Fetch($rThread);
	$qForum = "select minpower from {forums} where id={0}";
	$rForum = Query($qForum, $thread['forum']);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
		die(__("No.")." ".$hideTricks);


	$qRevs = "SELECT 
				revision, date AS revdate,
				ru.(_userfields)
			FROM 
				{posts_text}
				LEFT JOIN {users} ru ON ru.id = user
			WHERE pid={0} 
			ORDER BY revision ASC";
	$revs = Query($qRevs, $id);
	
	
	$reply = __("Show revision:")."<br />";
	while($revision = Fetch($revs))
	{
		$reply .= " <a href=\"javascript:void(0)\" onclick=\"showRevision(".$id.",".$revision["revision"].")\">".format(__("rev. {0}"), $revision["revision"])."</a>";

		if ($revision['ru_id'])
		{
			$ru_link = UserLink(getDataPrefix($revision, "ru_"));
			$revdetail = " ".format(__("by {0} on {1}"), $ru_link, formatdate($revision['revdate']));
		}
		else
			$revdetail = '';
		$reply .= $revdetail;
		$reply .= "<br />";
	}
				
	$hideTricks = " <a href=\"javascript:void(0)\" onclick=\"showRevision(".$id.",".$post["currentrevision"]."); hideTricks(".$id.")\">".__("Back")."</a>";
	$reply .= $hideTricks;
	die($reply);
}
elseif($action == "sr")	//Show Revision
{

	$rPost = Query("	
			SELECT 
				p.id, p.date, p.num, p.deleted, p.deletedby, p.reason, p.options, p.mood, p.ip, 
				pt.text, pt.revision, pt.user AS revuser, pt.date AS revdate,
				u.(_userfields), u.(rankset,title,picture,posts,postheader,signature,signsep,lastposttime,lastactivity,regdate,globalblock),
				ru.(_userfields),
				du.(_userfields)
			FROM 
				{posts} p 
				LEFT JOIN {posts_text} pt ON pt.pid = p.id AND pt.revision = {1} 
				LEFT JOIN {users} u ON u.id = p.user
				LEFT JOIN {users} ru ON ru.id=pt.user
				LEFT JOIN {users} du ON du.id=p.deletedby
			WHERE p.id={0}", $id, (int)$_GET['rev']);
	
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die(format(__("Unknown post ID #{0} or revision missing."), $id));

	$qThread = "select forum from {threads} where id={0}";
	$rThread = Query($qThread, $post['thread']);
	$thread = Fetch($rThread);
	$qForum = "select minpower from {forums} where id={0}";
	$rForum = Query($qForum, $thread['forum']);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
		die(__("No."));

//	die(var_dump($post));
	die(makePostText($post));
}
elseif($action == "em")	//Email
{
	$blah = FetchResult("select email from {users} where id={0} and showemail=1", $id);
	die(htmlspecialchars($blah));
}
elseif($action == "vc")	//View Counter
{
	$blah = FetchResult("select views from {misc}");
	die(number_format($blah));
}

die(__("Unknown action."));
?>
