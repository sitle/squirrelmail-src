<?

/*
	CVS Revision:  1.1.0
	Autor:     
	Modificat: Claudiu Costin, 12 iulie 2000
*/

class FastTemplate {
        // contine lista de handle-ri de fisiere, in forma FILELIST[HANDLE] == "numefisier"
        var $FILELIST   = array();
        // contine lista de blocuri dinamice si handle-rii de fisiere pentru blocuri
        var $DYNAMIC    = array();
        // contine lista de variabile de handle-ri de tipul PARSEVARS[HANDLE] == "valoare"
        var $PARSEVARS  = array();
        // voi incarca un template numai cind este utilizat, iar atunci -> LOADED[FILEHANDLE] == 1
        var $LOADED     = array();
        // contine numele hadle-urilor asignate de un apel la parse()
        var $HANDLE     = array();
	// contine calea catre directorul de template-uri
        var $ROOT       = "";
        // set la TRUE daca se executa sub arhitectura WIN32 
        var $WIN32      = false;
        // contine ultimul mesaj de eroare                                                	
        var $ERROR      = "";
        // contine ultimul handler folosit de parse()
        var $LAST       = "";
        // precizeaza daca se verifica strict template-ul si existenta variabilelor
	var $STRICT     = true;

        // ************************************************************

	function FastTemplate ($pathToTemplates = "")
	{
		global $php_errormsg;

		if(!empty($pathToTemplates))
		{
			$this->set_root($pathToTemplates);
		}

	} // sfirsit (new) FastTemplate ()


        //************************************************************
        // toate template-urile vor fi luate din acest director "radacina
        // poate fi schmibat pe parcurs daca este reapelata cu
        // o noua valoare

	function set_root ($root)
	{
		$trailer = substr($root,-1);

		if(!$this->WIN32)
		{
			if( (ord($trailer)) != 47 )
			{
				$root = "$root". chr(47);
			}

			if(is_dir($root))
			{
				$this->ROOT = $root;
			}
			else
			{
				$this->ROOT = "";
				$this->error("Specified ROOT dir [$root] is not a directory");
			}
		}
		else
		{
			// WIN32 box - no testing
			if( (ord($trailer)) != 92 )
			{
				$root = "$root" . chr(92);
			}
			$this->ROOT = $root;
		}

        } // sfirsit set_root()


        // **************************************************************
        // calculeaza microtime curent
        // - este introdusa aici pentru a masura viteza de lucru
        // a acestei clase

	function utime ()
	{
		$time = explode( " ", microtime());
		$usec = (double)$time[0];
		$sec = (double)$time[1];
		return $sec + $usec;
        }

       
        // **************************************************************
        // verificare stricta a template-ului
        // daca este setat la TRUe, atunci trimite avertizari la STDOUT
        // cind se gasesc variabile nedefinite la generare
        // - este utilizata pentru a determina erorile din programe

	function strict ()
	{
		$this->STRICT = true;
	}

        // ************************************************************
        // elimina fara avertizari variabilele nedefinite 
        // gasite in template-uri

	function no_strict ()
	{
		$this->STRICT = false;
	}


        // ************************************************************
        // verificare rapida a template-ului inainte de a fi citit
        // aceasta _NU_ este o verificare sigura datorita modului
        // in care PHP face determinarea daca un fisier poate fi citit

	function is_safe ($filename)
	{
		if(!file_exists($filename))
		{
			$this->error("[$filename] does not exist",0);
			return false;
		}
		return true;
	}


        // ************************************************************
        // citeste un fisier template din directorul de baza si
        // il face ca un  sir text (destul de posibil) foarte mare

	function get_template ($template)
	{
		if(empty($this->ROOT))
		{
			$this->error("FASTTEMPLATE: Nu pot deschide fisierul template. Director de baza eronat.",1);
			return false;
		}

		$filename = "$this->ROOT"."$template";

		$contents = implode("",(@file($filename)));
		if( (!$contents) or (empty($contents)) )
		{
		        $this->error("EROARE get_template(): [$filename] $php_errormsg",1);
		}

		return $contents;

	} // sfirsit get_template


        // ************************************************************
        // afiseaza avertizarile pentru referinte la variabile 
        // inexistente in fisierele template. Utilizata daca
        // STRICT este TRUE

	function show_unknowns ($Line)
	{
		$unknown = array();
		if (ereg("(\{[A-Z0-9_]+\})",$Line,$unknown))
		{
			$UnkVar = $unknown[1];
			if(!(empty($UnkVar)))
			{
			        @error_log("FASTTEMPLATE Avertizare: Nu am gasit o valoare pentru variabila: $UnkVar ",0);
			}
		}
        } // sfirsit show_unknowns()

        // ************************************************************
        // Aceasta rutina este apelata de parse() si face de fapt 
        // inlocuirea [VARIABILA] cu VALOARE in template

	function parse_template ($template, $tpl_array)
	{
		while ( list ($key,$val) = each ($tpl_array) )
		{
			if (!(empty($key)))
			{
				if(gettype($val) != "string")
				{
					settype($val,"string");
				}
				$key = '{' . $key . '}';
				//$template = ereg_replace("$key","$val","$template");
				$template = str_replace($key,$val,$template);
			}
		}

		if(!$this->STRICT)
		{
			// Elimina fara avertizari variabilele inexistente

			$template = ereg_replace("\{([A-Z0-9_]+)\}","",$template);
		}
		else
		{
			// Averizeaza despre variabile de template negasite
			if (ereg("(\{[A-Z0-9_]+\})",$template))
			{
				$unknown = split("\n",$template);
				while (list ($Element,$Line) = each($unknown) )
				{
					$UnkVar = $Line;
					if(!(empty($UnkVar)))
					{
						$this->show_unknowns($UnkVar);
					}
				}
			}
		}
		return $template;

        } // sfirsit parse_template();

//	************************************************************
//	The meat of the whole class. The magic happens here.

	function parse ( $ReturnVar, $FileTags )
	{
		$append = false;
		$this->LAST = $ReturnVar;
		$this->HANDLE[$ReturnVar] = 1;

		if (gettype($FileTags) == "array")
		{
			unset($this->$ReturnVar);	// Clear any previous data

			while ( list ( $key , $val ) = each ( $FileTags ) )
			{
				if ( (!isset($this->$val)) || (empty($this->$val)) )
				{
					$this->LOADED["$val"] = 1;
					if(isset($this->DYNAMIC["$val"]))
					{
						$this->parse_dynamic($val,$ReturnVar);
					}
					else
					{
						$fileName = $this->FILELIST["$val"];
						$this->$val = $this->get_template($fileName);
					}
				}

				//	Array context implies overwrite

				$this->$ReturnVar = $this->parse_template($this->$val,$this->PARSEVARS);

				//	For recursive calls.

				$this->assign( array( $ReturnVar => $this->$ReturnVar ) );

			}
		}	// end if FileTags is array()
		else
		{
			// FileTags is not an array

			$val = $FileTags;

			if( (substr($val,0,1)) == '.' )
			{
				// Append this template to a previous ReturnVar

				$append = true;
				$val = substr($val,1);
			}

			if ( (!isset($this->$val)) || (empty($this->$val)) )
			{
					$this->LOADED["$val"] = 1;
					if(isset($this->DYNAMIC["$val"]))
					{
						$this->parse_dynamic($val,$ReturnVar);
					}
					else
					{
						$fileName = $this->FILELIST["$val"];
						$this->$val = $this->get_template($fileName);
					}
			}

			if($append)
			{
				$this->$ReturnVar .= $this->parse_template($this->$val,$this->PARSEVARS);
			}
			else
			{
				$this->$ReturnVar = $this->parse_template($this->$val,$this->PARSEVARS);
			}

			//	For recursive calls.

			$this->assign(array( $ReturnVar => $this->$ReturnVar) );

		}
		return;
	}	//	End parse()


//	************************************************************

	function FastPrint ( $template = "" )
	{
		if(empty($template))
		{
			$template = $this->LAST;
		}

		if( (!(isset($this->$template))) || (empty($this->$template)) )
		{
			$this->error("Nothing parsed, nothing printed",0);
			return;
		}
		else
		{
			print $this->$template;
		}
		return;
	}

//	************************************************************

	function fetch ( $template = "" )
	{
		if(empty($template))
		{
			$template = $this->LAST;
		}
		if( (!(isset($this->$template))) || (empty($this->$template)) )
		{
			$this->error("Nothing parsed, nothing printed",0);
			return "";
		}

		return($this->$template);
	}


//	************************************************************

	function define_dynamic ($Macro, $ParentName)
	{
		//	A dynamic block lives inside another template file.
		//	It will be stripped from the template when parsed
		//	and replaced with the {$Tag}.

		$this->DYNAMIC["$Macro"] = $ParentName;
		return true;
	}

//	************************************************************

	function parse_dynamic ($Macro,$MacroName)
	{
		// The file must already be in memory.

		$ParentTag = $this->DYNAMIC["$Macro"];
		if( (!$this->$ParentTag) or (empty($this->$ParentTag)) )
		{
			$fileName = $this->FILELIST[$ParentTag];
			$this->$ParentTag = $this->get_template($fileName);
			$this->LOADED[$ParentTag] = 1;
		}
		if($this->$ParentTag)
		{
			$template = $this->$ParentTag;
			$DataArray = split("\n",$template);
			$newMacro = "";
			$newParent = "";
			$outside = true;
			$start = false;
			$end = false;
			while ( list ($lineNum,$lineData) = each ($DataArray) )
			{
				$lineTest = trim($lineData);
				if("<!-- BEGIN DYNAMIC BLOCK: $Macro -->" == "$lineTest" )
				{
					$start = true;
					$end = false;
					$outside = false;
				}
				if("<!-- END DYNAMIC BLOCK: $Macro -->" == "$lineTest" )
				{
					$start = false;
					$end = true;
					$outside = true;
				}
				if( (!$outside) and (!$start) and (!$end) )
				{
					$newMacro .= "$lineData\n"; // Restore linebreaks
				}
				if( ($outside) and (!$start) and (!$end) )
				{
					$newParent .= "$lineData\n"; // Restore linebreaks
				}
				if($end)
				{
					$newParent .= '{' . "$MacroName}\n";
				}
				// Next line please
				if($end) { $end = false; }
				if($start) { $start = false; }
			}	// end While

			$this->$Macro = $newMacro;
			$this->$ParentTag = $newParent;
			return true;

		}	// $ParentTag NOT loaded - MAJOR oopsie
		else
		{
			@error_log("ParentTag: [$ParentTag] not loaded!",0);
			$this->error("ParentTag: [$ParentTag] not loaded!",0);
		}
		return false;
	}

//	************************************************************
//	Strips a DYNAMIC BLOCK from a template.

	function clear_dynamic ($Macro="")
	{
		if(empty($Macro)) { return false; }

		// The file must already be in memory.

		$ParentTag = $this->DYNAMIC["$Macro"];

		if( (!$this->$ParentTag) or (empty($this->$ParentTag)) )
		{
			$fileName = $this->FILELIST[$ParentTag];
			$this->$ParentTag = $this->get_template($fileName);
			$this->LOADED[$ParentTag] = 1;
		}

		if($this->$ParentTag)
		{
			$template = $this->$ParentTag;
			$DataArray = split("\n",$template);
			$newParent = "";
			$outside = true;
			$start = false;
			$end = false;
			while ( list ($lineNum,$lineData) = each ($DataArray) )
			{
				$lineTest = trim($lineData);
				if("<!-- BEGIN DYNAMIC BLOCK: $Macro -->" == "$lineTest" )
				{
					$start = true;
					$end = false;
					$outside = false;
				}
				if("<!-- END DYNAMIC BLOCK: $Macro -->" == "$lineTest" )
				{
					$start = false;
					$end = true;
					$outside = true;
				}
				if( ($outside) and (!$start) and (!$end) )
				{
					$newParent .= "$lineData\n"; // Restore linebreaks
				}
				// Next line please
				if($end) { $end = false; }
				if($start) { $start = false; }
			}	// end While

			$this->$ParentTag = $newParent;
			return true;

		}	// $ParentTag NOT loaded - MAJOR oopsie
		else
		{
			@error_log("ParentTag: [$ParentTag] not loaded!",0);
			$this->error("ParentTag: [$ParentTag] not loaded!",0);
		}
		return false;
	}


//	************************************************************

	function define ($fileList)
	{
		while ( list ($FileTag,$FileName) = each ($fileList) )
		{
			$this->FILELIST["$FileTag"] = $FileName;
		}
		return true;
	}

//	************************************************************

	function clear_parse ( $ReturnVar = "")
	{
		$this->clear($ReturnVar);
	}

//	************************************************************

	function clear ( $ReturnVar = "" )
	{
		// Clears out hash created by call to parse()

		if(!empty($ReturnVar))
		{
			if( (gettype($ReturnVar)) != "array")
			{
				unset($this->$ReturnVar);
				return;
			}
			else
			{
				while ( list ($key,$val) = each ($ReturnVar) )
				{
					unset($this->$val);
				}
				return;
			}
		}

		// Empty - clear all of them

		while ( list ( $key,$val) = each ($this->HANDLE) )
		{
			$KEY = $key;
			unset($this->$KEY);
		}
		return;

	}	//	end clear()

//	************************************************************

	function clear_all ()
	{
		$this->clear();
		$this->clear_assign();
		$this->clear_define();
		$this->clear_tpl();

		return;

	}	//	end clear_all

//	************************************************************

	function clear_tpl ($fileHandle = "")
	{
		if(empty($this->LOADED))
		{
			// Nothing loaded, nothing to clear

			return true;
		}
		if(empty($fileHandle))
		{
			// Clear ALL fileHandles

			while ( list ($key, $val) = each ($this->LOADED) )
			{
				unset($this->$key);
			}
			unset($this->LOADED);

			return true;
		}
		else
		{
			if( (gettype($fileHandle)) != "array")
			{
				if( (isset($this->$fileHandle)) || (!empty($this->$fileHandle)) )
				{
					unset($this->LOADED[$fileHandle]);
					unset($this->$fileHandle);
					return true;
				}
			}
			else
			{
				while ( list ($Key, $Val) = each ($fileHandle) )
				{
					unset($this->LOADED[$Key]);
					unset($this->$Key);
				}
				return true;
			}
		}

		return false;

	}	// end clear_tpl

//	************************************************************

	function clear_define ( $FileTag = "" )
	{
		if(empty($FileTag))
		{
			unset($this->FILELIST);
			return;
		}

		if( (gettype($Files)) != "array")
		{
			unset($this->FILELIST[$FileTag]);
			return;
		}
		else
		{
			while ( list ( $Tag, $Val) = each ($FileTag) )
			{
				unset($this->FILELIST[$Tag]);
			}
			return;
		}
	}

//	************************************************************
//	Aliased function - used for compatibility with CGI::FastTemplate
//	function clear_parse ()
//	{
//		$this->clear_assign();
//	}
//
//	************************************************************
//	Clears all variables set by assign()

	function clear_assign ()
	{
		if(!(empty($this->PARSEVARS)))
		{
			while(list($Ref,$Val) = each ($this->PARSEVARS) )
			{
				unset($this->PARSEVARS["$Ref"]);
			}
		}
	}

//	************************************************************

	function clear_href ($href)
	{
		if(!empty($href))
		{
			if( (gettype($href)) != "array")
			{
				unset($this->PARSEVARS[$href]);
				return;
			}
			else
			{
				while (list ($Ref,$val) = each ($href) )
				{
					unset($this->PARSEVARS[$Ref]);
				}
				return;
			}
		}
		else
		{
			// Empty - clear them all

			$this->clear_assign();
		}
		return;
	}

//	************************************************************

	function assign ($tpl_array, $trailer="")
	{
		if(gettype($tpl_array) == "array")
		{
			while ( list ($key,$val) = each ($tpl_array) )
			{
				if (!(empty($key)))
				{
					//	Empty values are allowed
					//	Empty Keys are NOT

					$this->PARSEVARS["$key"] = $val;
				}
			}
		}
		else
		{
			// Empty values are allowed in non-array context now.
			if (!empty($tpl_array))
			{
				$this->PARSEVARS["$tpl_array"] = $trailer;
			}
		}
	}

        // ************************************************************
//	Return the value of an assigned variable.
//	Christian Brandel cbrandel@gmx.de

	function get_assigned($tpl_name = "")
	{
		if(empty($tpl_name)) { return false; }
		if(isset($this->PARSEVARS["$tpl_name"]))
		{
			return ($this->PARSEVARS["$tpl_name"]);
		}
		else
		{
			return false;
        }
	}

//	************************************************************

	function error ($errorMsg, $die = 0)
	{
		$this->ERROR = $errorMsg;
		echo "EROARE: $this->ERROR <br> \n";
		if ($die == 1)
		{
			exit;
		}

		return;

	} // sfirsit error()


//	************************************************************



//	************************************************************

} // sfirsit  fastTtmplate.php

?>
