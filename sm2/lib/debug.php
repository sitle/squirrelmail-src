<?php

include('classes.php');
sm_include('mailboxtree');


include('config.php');
sm_include('Messages');
sm_include('imap_backend');
session_start();

global $msg;
if (!isset($_SESSION["msg"])) {
    $msg =& new messages('imap',array('username' => $username,
                                 'host'=> $host,
                                 'password'=>$password,
                                 'port' => $port,
                                 'type'=>'imap'));
    $_SESSION["msg"] =& $msg;
} else {
    $msg =& $_SESSION["msg"];
}
$aTreeProps = array();
echo '<pre>';
if ($msg->Connect()) {
        $msg->login($username,$password,$host);
} else {
        echo "LOGIN FAILED";
}

if (isset($_POST['refresh']) && $_POST['refresh']) {
    if (isset($msg->mailboxtree_LIST)) {
        unset($msg->mailboxtree_LIST);
    }
}

if (isset($_POST['showlabel']) && $_POST['showlabel']) {
    $aTreeProps['showlabel'] = true;
    $_POST['showlabel'] = true;
} else {
    $_POST['showlabel'] = false;
    $aTreeProps['showlabel'] = false;
}

if (isset($_POST['expandtree']) && $_POST['expandtree']) {
    $aTreeProps['expand'] = true;
    $_POST['expandtree'] = true;
} else {
    $_POST['expandtree'] = false;
    $aTreeProps['expand'] = false;
}


$tree =& $msg->getMailBoxTree('LIST',$aTreeProps,array());

if (isset($_GET['expand'])) {
    $tree->expand($_GET['expand']);
}
if (isset($_GET['collapse'])) {
    $tree->collapse($_GET['collapse']);
}
    $_SESSION['tree]'] = $tree;
?>

<form name="mailbox" method="post" action="debug.php">

<?php
$tree->renderEngine = 'renderline';
$tree->render();
?>
<br>
Expand Tree<input type="checkbox" name="expandtree" <?php if ($_POST['expandtree']) echo "checked"; ?>"><BR>
Show Namespace labels <input type="checkbox" name="showlabel" <?php if ($_POST['showlabel']) echo "checked"; ?>"><BR>
Refresh tree <input type="checkbox" name="refresh"><BR>
<input type="submit" name="submit" VALUE="Mailbox tree test">

<?php

function renderline($oNode,$iPerm,$depth,$haschildren,$expanded) {

    $type = get_class($oNode);
    $prefix = str_repeat('&nbsp;',$depth*3);
    $prefix .= '<input type="checkbox" value="'.$oNode->id.'">';
    switch ($type) {
        case 'label':
            echo $prefix . "<b>".$oNode->label."</b><br>";
            break;
        case 'mailbox':
            $uri = '';
            if ($haschildren) {
                if ($expanded) {
                    $uri = '<a href="debug.php?collapse='.$oNode->id.'">- </a>';
                } else {
                    $uri = '<a href="debug.php?expand='.$oNode->id.'">+ </a>';
                }
            }
            echo $prefix .$uri.  "<tt>".$oNode->label."</tt><br>";
            break;
        default: break;
    }
}
echo "<table>";
foreach ($tree->nodes as $node) {
    echo '<tr><td>' . $node[0]->id . "</td><td>".$node[1] . "</td><td>".$node[2].'</td></tr>';
}
echo "</table>";
?>
