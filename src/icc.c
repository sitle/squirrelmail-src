/*
** 
**               Copyright (c) 2002,2003 Dave McMurtrie
**
** This file is part of imapproxy.
**
** imapproxy is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** imapproxy is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with imapproxy; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
**
**
**  Facility:
**
**	icc.c
**
**  Abstract:
**
**	Routines to manipulate IMAP Connection Context structures.
**
**  Authors:
**
**	Author: Dave McMurtrie <davemcmurtrie@hotmail.com>
**
**  RCS:
**
**	$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/icc.c,v $
**	$Id: icc.c,v 1.5 2003/05/20 18:46:49 dgm Exp $
**      
**  Modification History:
**
**	$Log: icc.c,v $
**	Revision 1.5  2003/05/20 18:46:49  dgm
**	Comment changes only.
**
**	Revision 1.4  2003/05/06 12:11:12  dgm
**	Applied patches by Ken Murchison <ken@oceana.com> to include SSL support.
**
**	Revision 1.3  2002/12/17 14:23:12  dgm
**	Added support for global configuration structure.
**
**	Revision 1.2  2002/08/28 15:55:13  dgm
**	replaced all internal logging calls with standard syslog calls.
**
**	Revision 1.1  2002/07/03 12:06:58  dgm
**	Initial revision
**
**
*/


#define _REENTRANT

#include <errno.h>
#include <string.h>
#include <syslog.h>

#include "common.h"
#include "imapproxy.h"

/*
 * External globals
 */
extern ICC_Struct *ICC_free;
extern ICC_Struct *ICC_HashTable[ HASH_TABLE_SIZE ];
extern pthread_mutex_t mp;
extern IMAPCounter_Struct *IMAPCount;
extern ProxyConfig_Struct PC_Struct;

/*
 * internal prototypes
 */
static void _ICC_Recycle( unsigned int );



/*++
 * Function:	_ICC_Recycle
 *
 * Purpose:	core logic to implement the ICC_Recycle() & ICC_Recycle_Loop()
 *              functions.
 *
 * Parameters:	unsigned int -- ICC expiration time
 *
 * Returns:	nada
 *	
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *--
 */
static void _ICC_Recycle( unsigned int Expiration )
{
    char *fn = "_ICC_Recycle()";
    time_t CurrentTime;
    int rc;
    unsigned int HashIndex;
    ICC_Struct *HashEntry;
    ICC_Struct *Previous;
    
    CurrentTime = time(0);

    LockMutex( &mp );
    
    /*
     * Need to iterate through every single item in our hash table
     * to decide if we can free it or not.
     */
    for ( HashIndex = 0; HashIndex < HASH_TABLE_SIZE; HashIndex++ )
    {
	
	Previous = NULL;
	HashEntry = ICC_HashTable[ HashIndex ];
	
	while ( HashEntry )
	{
	    /*
	     * If the last logout time is non-zero, and it's been logged 
	     * out for longer than our default expiration time, free it.
	     * Note that this allows for the logouttime to be explicitly
	     * set to 1 (such as in the Get_Server_conn code) if we want to
	     * reap a connection before waiting the normal expiration
	     * cycle.
	     */
	    if ( HashEntry->logouttime &&
		 ( ( CurrentTime - HashEntry->logouttime ) > 
		   Expiration ) )
	    {
		syslog(LOG_INFO, "Expiring server sd [%d]", HashEntry->server_conn->sd);
		/* Close the server socket. */
#if HAVE_LIBSSL
		if ( HashEntry->server_conn->tls )
		{
		    SSL_shutdown( HashEntry->server_conn->tls );
		    SSL_free( HashEntry->server_conn->tls );
		}
#endif
		close( HashEntry->server_conn->sd );
		free( HashEntry->server_conn );
		
		/*
		 * This was being counted as a "retained" connection.  It was
		 * open, but not in use.  Now that we're closing it, we have
		 * to decrement the number of retained connections.
		 */
		IMAPCount->RetainedServerConnections--;
		

		if ( Previous )
		{
		    Previous->next = HashEntry->next;
		    HashEntry->next = ICC_free;
		    ICC_free = HashEntry;
		    HashEntry = Previous->next;
		}
		else
		{
		    ICC_HashTable[ HashIndex ] = HashEntry->next;
		    HashEntry->next = ICC_free;
		    ICC_free = HashEntry;
		    HashEntry = ICC_HashTable[ HashIndex ];
		}
	    }
	    else
	    {
		Previous = HashEntry;
		HashEntry = HashEntry->next;
	    }
	}
    }
    
    UnLockMutex( &mp );
}



/*++
 * Function:	ICC_Recycle
 *
 * Purpose:	Reclaim dormant ICC structures.  This function is intended to
 *              be called any time a free ICC is needed, but none exist.  It
 *              can be passed shorter than default expiration durations to try
 *              to free more ICCs if necessary.
 *
 * Parameters:	unsigned int -- ICC expiration duration
 *
 * Returns:	nada
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *--
 */
extern void ICC_Recycle( unsigned int Expiration )
{
    char *fn = "ICC_Recycle()";
    
    _ICC_Recycle( Expiration );
}



/*++
 * Function:	ICC_Recycle_Loop
 *
 * Purpose:	Reclaim dormant ICC structures.  This function is intended
 *              to be run continuously as a single thread.
 *
 * Parameters:	nada
 *
 * Returns:	nada
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *
 * Notes:       Relies on global copy of ProxyConfig_Struct "PC_Struct" for
 *              expiration time.
 *--
 */
extern void ICC_Recycle_Loop( void )
{
    char *fn = "ICC_Recycle_Loop()";

    for( ;; )
    {
	sleep( 60 );
	_ICC_Recycle( PC_Struct.cache_expiration_time );
    }
}



/*++
 * Function:	ICC_Logout
 *
 * Purpose:	set the last logout time for an IMAP connection context.
 *
 * Parameters:	char *Username
 *		int server-side socket descriptor
 *		
 * Returns:	nada
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *--
 */
extern void ICC_Logout( char *Username, ICD_Struct *conn )
{
    char *fn = "ICC_Logout()";
    unsigned int HashIndex;
    ICC_Struct *HashEntry = NULL;
    ICC_Struct *ICC_Active = NULL;
    int rc;
    
    IMAPCount->InUseServerConnections--;
    IMAPCount->RetainedServerConnections++;

    if ( IMAPCount->RetainedServerConnections > 
	 IMAPCount->PeakRetainedServerConnections )
	IMAPCount->PeakRetainedServerConnections = IMAPCount->RetainedServerConnections;
    
    
    HashIndex = Hash( Username, HASH_TABLE_SIZE );
    
    LockMutex( &mp );
    
    for ( HashEntry = ICC_HashTable[ HashIndex ]; 
	  HashEntry; 
	  HashEntry = HashEntry->next )
    {
	if ( ( strcmp( Username, HashEntry->username ) == 0 ) &&
	     ( HashEntry->server_conn->sd == conn->sd ) )
	{
	    ICC_Active = HashEntry;
	}
    }
    
    if ( !ICC_Active )
    {
	UnLockMutex( &mp );
	
	syslog(LOG_WARNING, "%s: Cannot find ICC for '%s' on server sd %d to set logout time.", fn, Username, conn->sd );
	return;
    }
    
    ICC_Active->logouttime = time(0);

    UnLockMutex( &mp );
    
    syslog(LOG_INFO, "LOGOUT: '%s' from server sd [%d]", Username, conn->sd );
    
    return;
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
