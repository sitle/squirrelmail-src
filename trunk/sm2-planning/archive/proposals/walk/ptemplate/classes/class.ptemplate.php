<?PHP

/*
    PHP Template Class
*/

class PTemplate {
    var $FILELIST = array();           // Holds the array of filehandles
                                       // FILELIST[HANDLE] == "fileName"
    var $ROOT = '';                    // Holds path-to-templates
    var $ERROR = '';                   // Holds the last error message


//      ************************************************************


    function PTemplate ($pathToTemplates = '')
    {
        if ($pathToTemplates != '')
        {
            $this->set_root($pathToTemplates);
        }
    }


//      ************************************************************
//      All templates will be loaded from this "root" directory
//      Can be changed in mid-process by re-calling with a new value.


    function set_root ($root)
    {
        $root = strtr($root, '\\', '/');
        $trailer = substr($root, -1);

        if ($trailer != '/')
        {
            $root .= '/';
        }

        if (is_dir($root))
        {
            $this->ROOT = $root;
        }
        else
        {
            $this->error("Specified ROOT dir [$root] is not a directory -- " .
                'Using [' . $this->ROOT . ']');
        }
    }


//      ************************************************************
//      A quick check of the template file before reading it.
//      This is -not- a reliable check, mostly due to inconsistencies
//      in the way PHP determines if a file is readable.


    function is_safe ($filename)
    {
        if (! file_exists($filename))
        {
            $this->error("[$filename] does not exist");
            return false;
        }

        return true;
    }



//      ************************************************************
//      Starts PHP crunching on the file.


    function parse($FileRef)
    {
        if (empty($this->ROOT))
        {
            $this->error('Cannot open template. Root not valid.', 1);
        }

        if (empty($this->FILELIST[$FileRef]))
        {
            $this->error("File name [$FileRef] has not been defined.");
            return;
        }
        
        $FilePath = $this->ROOT . $this->FILELIST[$FileRef];
        
        if (! $this->is_safe($FilePath))
        {
            $this->error("Unable to find $FilePath.");
            return;
        }
        
	// Run the file here.
	include $FilePath;
    }
    
    
//      ************************************************************


    function define ($fileList)
    {
        foreach ($fileList as $FileTag => $FileName)
        {
            $this->FILELIST[$FileTag] = $FileName;
        }
        return true;
    }
    
    // Assumes root is a directory containing template files.
    // $TemplateSet is the name of a directory inside the root directory
    // All .html & .htm files in this directory are defined.
    // All .php files in this directory are executed
    function define_templates($TemplateSet)
    {
        $TempRoot = $this->ROOT;
	
	$this->set_root($this->ROOT . $TemplateSet);
	
	if ($TempRoot == $this->ROOT)
	{
	    $this->error("Problem defining template directory.");
	    return;
	}
	
	$TemplateSet = str_replace($TempRoot, "", $this->ROOT);
	$this->set_root($TempRoot);
	
        $dirhandle = opendir($this->ROOT . $TemplateSet);
        while (($file = readdir($dirhandle)) !== false)
        {
	    if (eregi('^(.+)\\.htm(l)?$', $file, $matches))
	        $this->define(Array($matches[1] => $TemplateSet . $file));
	    elseif (eregi('\\.php$', $file))
	        include $this->ROOT . $TemplateSet . $file;
        }
        closedir($dirhandle);
    }


//      ************************************************************


    function error ($errorMsg, $die = 0)
    {
        $this->ERROR = $errorMsg;
        echo "ERROR: $errorMsg<BR>\n";
        if ($die)
            exit;
        return;
    }


//      ************************************************************

}

?>
