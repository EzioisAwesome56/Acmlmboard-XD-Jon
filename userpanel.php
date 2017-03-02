<?php
$userMenu = new PipeMenu();

if($loguserid)
{
	$userMenu->add(new PipeMenuHtmlEntry(userLink($loguser)));

	if(isAllowed("editProfile"))
		$userMenu->add(new PipeMenuLinkEntry(__("Edit profile"), "editprofile", "", "", "pencil"));

	$bucket = "bottomMenu"; include("./lib/pluginloader.php");

	if(!isset($_POST['id']) && isset($_GET['id']))
		$_POST['id'] = (int)$_GET['id'];

	if (isset($user_panel))
        echo $user_panel;

	$userMenu->add(new PipeMenuLinkEntry(__("Log out"), "", "", "", "signout", "document.forms[0].submit(); return false;"));

}
else
{
	$userMenu->add(new PipeMenuLinkEntry(__("Log in"), "login", "", "", "signin"));
}
$bucket = "regLink"; include("./lib/pluginloader.php");
$layout_userpanel = $userMenu;
?>
