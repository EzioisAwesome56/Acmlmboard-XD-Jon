<?php
if($loguserid)
{
	print "<li>".UserLink($loguser)."</li>";
    
	if(IsAllowed("editProfile"))
		print actionLinkTagItem(__("Edit profile"), "editprofile");
	if(IsAllowed("viewPM"))
		print actionLinkTagItem(__("Private messages"), "private");
	if(IsAllowed("editMoods"))
		print actionLinkTagItem(__("Mood avatars"), "editavatars");

	$bucket = "bottomMenu"; include("./lib/pluginloader.php");

	if(!isset($_POST['id']) && isset($_GET['id']))
		$_POST['id'] = (int)$_GET['id'];
        
	if (isset($user_panel))
	{
        echo $user_panel;
    }

	print "<li><a href=\"#\" onclick=\"document.forms[0].submit();\">" .  __("Log out") . "</a></li>";
    
}
else
{
	print actionLinkTagItem(__("Register"), "register");
	print actionLinkTagItem(__("Log in"), "login");
}

					
?>
