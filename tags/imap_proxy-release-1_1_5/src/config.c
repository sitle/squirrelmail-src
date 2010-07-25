/*
**
**      Copyright (c) 2002 University of Pittsburgh
**
**                      All Rights Reserved
**
** Permission to use, copy, modify, and distribute this software and its 
** documentation for any purpose and without fee is hereby granted, 
** provided that the above copyright notice appears in all copies and that
** both that copyright notice and this permission notice appear in 
** supporting documentation, and that the name of the University of
** Pittsburgh not be used in advertising or publicity pertaining to
** distribution of this software without specific written prior permission.  
** 
** THE UNIVERSITY OF PITTSBURGH DISCLAIMS ALL WARRANTIES WITH REGARD TO
** THIS SOFTWARE, INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
** FITNESS, IN NO EVENT SHALL THE UNIVERSITY OF PITTSBURGH BE LIABLE FOR
** ANY SPECIAL, INDIRECT OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
** RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF
** CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN
** CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
**
**
**  Facility:
**
**	config.c
**
**  Abstract:
**
**	Routines for parsing a config file and setting global configuration
**	options.
**
**  Authors:
**
**      Dave McMurtrie (dgm@pitt.edu)
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/config.c,v $
**      $Id: config.c,v 1.4 2003/04/16 12:13:34 dgm Exp $
**      
**  Modification History:
**
**      $Log: config.c,v $
**      Revision 1.4  2003/04/16 12:13:34  dgm
**      Added commodore logo ascii-art comment at the end.
**      Added support for syslog configuration options.
**
**      Revision 1.3  2003/02/17 14:08:42  dgm
**      added an fclose() that I forgot.
**
**      Revision 1.2  2003/01/27 13:59:15  dgm
**      Patch by Gary Mills <mills@cc.UManitoba.CA> to allow compilation
**      using Sun's cc instead of gcc.
**
**      Revision 1.1  2002/12/17 14:26:49  dgm
**      Initial revision
**
**
*/


#define _REENTRANT
#define MAX_KEYWORD_LEN 128

#include <errno.h>
#include <string.h>
#include <syslog.h>
#include <stdlib.h>
#include <stdio.h>
#include "common.h"
#include "imapproxy.h"


/*
 * External globals
 */
extern ProxyConfig_Struct PC_Struct;


/*
 * Internal prototypes
 */
static void SetStringValue( char *, char **, unsigned int );
static void SetNumericValue( char *, int *, unsigned int );



/*
 * An array of Config_Structs will need to be allocated, one for
 * every possible keyword/value combination (plus a NULL).  Note that
 * I declare an array of 100, meaning that there's a hard limit of 99
 * possible config values right now.  Currently, there are only 9 in use.
 */
struct Config_Struct
{
    char Keyword[MAX_KEYWORD_LEN]; /* The configuration keyword */
    void (*(SetFunction))();       /* ptr to function used to set the value */
    void *StorageAddress;          /* address to store the value */
};

struct Config_Struct ConfigTable[ 100 ];

/*
 * Macro to populate the above ConfigTable
 */
#define ADD_TO_TABLE( KEYWORD, SETFUNCTION, STA, INDEX ) \
        strncpy( ConfigTable[ INDEX ].Keyword, KEYWORD, MAX_KEYWORD_LEN -1 ); \
        ConfigTable[ INDEX ].SetFunction = SETFUNCTION; \
        ConfigTable[ INDEX ].StorageAddress = STA; \
        INDEX++;





/*++
 * Function:	SetStringValue
 *
 * Purpose:	Common routine to assign string values in the config
 *		options struct.
 *
 * Parameters:  ptr to string from config file
 *              ptr to char ptr for dynamically allocated storage for string.
 *              int -- line of config file where the string was read from.
 *
 * Returns:     nada -- exit()s on errors.
 *
 * Authors:     dgm
 *
 * Notes:
 *--
 */
static void SetStringValue( char *String, 
			    char **SavedString, 
			    unsigned int linenum )
{
    char *fn = "SetStringValue()";
    unsigned int Size;
    
    Size = strlen( String ) + 1;
    
    /*
     * Do some reasonable bounds checking before we malloc()
     */
    if ( ( Size < 1 ) || ( Size > 4096 ) )
    {
	syslog( LOG_ERR, "%s: Length of string value at line %d of config file is not within size boundaries -- Exiting.", fn, linenum );
	exit( 1 );
    }
    
    *SavedString = malloc( Size );
    
    if ( ! *SavedString )
    {
	syslog( LOG_ERR, "%s: malloc() failed: %s -- Exiting.", fn,
		strerror( errno ) );
	exit( 1 );
    }
    
    memcpy( *SavedString, String, Size );
    
    return;
}


    


/*++
 * Function:    SetNumericValue
 *
 * Purpose:     Common routine to convert an ascii string to a numeric value
 *              and set a config value accordingly.
 *
 * Parameters:  ptr to the string value to convert. 
 *              ptr to int.  (where to store the converted value)
 *              int -- line of config file where the string was read from.
 *
 * Returns:     nada -- exit()s on failure.
 *
 * Authors:     dgm
 *
 * Notes:
 *--
 */
static void SetNumericValue( char *StringValue, 
			     int *Value, 
			     unsigned int linenum )
{
    char *fn = "SetNumericValue()";

    /*
     * Need to generalize this routine.  atoi() seems to only set errno
     * on Solaris, making this very platform specific.
     */
    *Value = atoi( (const char *)StringValue );

    if ( ( ! *Value ) &&
	 ( errno == EINVAL ) )
    {
	syslog( LOG_ERR, "%s: numeric value specified at line %d of config file is invalid -- Exiting.", fn, linenum );
	exit( 1 );
    }

    return;
}


/*++
 * Function:	SetConfigOptions
 *
 * Purpose:	Set global configuration options by reading and parsing
 *		the configuration file.
 *
 * Parameters:	char pointer to config filename path.
 *
 * Returns:	nada.  exit()s on any error.
 *
 * Authors:	dgm
 *
 * Notes:       Sets values in global ProxyConfig_Struct PC_Struct.
 *--
 */
extern void SetConfigOptions( char *ConfigFile )
{
    FILE *FP;
    char *fn = "SetConfigOptions()";
    char Buffer[1024];
    unsigned int LineNumber;
    unsigned int index;
    unsigned int i;
    char *CP;
    char *Keyword;
    char *Value;
   
    index = LineNumber = 0;

    /*
     * initialize the proxy config struct
     */
    memset( &PC_Struct, 0, sizeof PC_Struct );
    

    /*
     * Build our config option table.
     */
    ADD_TO_TABLE( "server_hostname", SetStringValue, 
		  &PC_Struct.server_hostname, index );

    ADD_TO_TABLE( "listen_port", SetNumericValue, 
		  &PC_Struct.listen_port, index );

    ADD_TO_TABLE( "server_port", SetNumericValue, 
		  &PC_Struct.server_port, index );

    ADD_TO_TABLE( "cache_size", SetNumericValue, 
		  &PC_Struct.cache_size, index );

    ADD_TO_TABLE( "cache_expiration_time", SetNumericValue, 
		  &PC_Struct.cache_expiration_time, index );

    ADD_TO_TABLE( "proc_username", SetStringValue,
		  &PC_Struct.proc_username, index );
    
    ADD_TO_TABLE( "proc_groupname", SetStringValue,
		  &PC_Struct.proc_groupname, index );
    
    ADD_TO_TABLE( "stat_filename", SetStringValue,
		  &PC_Struct.stat_filename, index );

    ADD_TO_TABLE( "syslog_facility", SetStringValue,
		  &PC_Struct.syslog_facility, index );
    
    ADD_TO_TABLE( "protocol_log_filename", SetStringValue,
		  &PC_Struct.protocol_log_filename, index );

    ADD_TO_TABLE( "syslog_prioritymask", SetStringValue,
		  &PC_Struct.syslog_prioritymask, index );
    

    ConfigTable[index].Keyword[0] = '\0';
    
    FP = fopen( ConfigFile, "r" );
    
    if ( !FP )
    {
	syslog(LOG_ERR, "%s: Unable to open config file '%s': %s -- Exiting",
	       fn, ConfigFile, strerror( errno ) );
	exit( 1 );
    }
    
    for ( ;; )
    {
	if ( !fgets( Buffer, sizeof Buffer, FP ) )
	    break;
	    
	LineNumber++;
	    
	/*
	 * Nullify comments, and CRLFs
	 */
	CP = strchr( Buffer, '#' );
	if ( CP ) *CP = 0;
	
	CP = strchr( Buffer, '\n' );
	if ( CP ) *CP = 0;
	
	CP = strchr( Buffer, '\r' );
	if ( CP ) *CP = 0;
	
	/*
	 * Any line that started with a comment or CRLF, we'll
	 * skip.
	 */
	if ( !strlen( Buffer ) )
	    continue;
	
	CP = strtok( Buffer, " " );
	if ( !CP )
	{
	    syslog(LOG_ERR, "%s: parse error reading config file at line %d.  Exiting.", fn, LineNumber );
	    exit( 1 );
	}
	
	Keyword = CP;
	
	CP = strtok( NULL, " " );
	
	if ( !CP )
	{
	    syslog(LOG_ERR, "%s: parse error reading config file at line %d.  Exiting.", fn, LineNumber );
	    exit( 1 );
	}
	
	Value = CP;
	
	for (i = 0; ConfigTable[i].Keyword[0] != '\0'; i++ )
	{
	    if ( ! strcasecmp( (const char *)Keyword, ConfigTable[i].Keyword ) )
	    {
		( ConfigTable[i].SetFunction )( Value, 
						ConfigTable[i].StorageAddress,
						LineNumber );
		break;
	    }
	    
	}
	
	/*
	 * If we get here and we're at our NULL value at the end of our
	 * keyword array, we cycled through the entire array and never
	 * matched any keyword.
	 */
	if ( ! ConfigTable[i].Keyword )
	{
	    syslog( LOG_ERR, "%s: unknown keyword '%s' found at line %d of config file -- Exiting.", fn, Keyword, LineNumber );
	    exit( 1 );
	}
	
    }

    fclose( FP );
}


/*
 *                            _________
 *                           /        |
 *                          /         |
 *                         /    ______|
 *                        /    /       ________
 *                       |    |        |      /
 *                       |    |        |_____/
 *                       |    |        ______
 *                       |    |        |     \
 *                       |    |        |______\
 *                        \    \_______
 *                         \           |
 *                          \          |
 *                           \_________|
 */
