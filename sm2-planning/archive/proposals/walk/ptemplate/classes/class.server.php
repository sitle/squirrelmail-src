<?php

class Server
{
    var $Name = 'Generic Server';
    var $Folders = array();
    
    function Server()
    {
        $f = new Folder();
	$f->Name = 'Inbox';
	for ($i = 0; $i < 10; $i ++)
	{
	    $m = new Message();
	    $m->To = "TO $i TO";
	    $m->From = "FROM $i FROM";
	    $m->ID = $i;
	    $m->Subject = "SUBJECT $i SUBJECT";
	    $f->Messages[] = $m;
	}
	$this->Folders[] = &$f;
    }
}

?>
