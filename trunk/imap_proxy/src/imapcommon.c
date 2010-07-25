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
**	imapcommon.c
**
**  Abstract:
**
**	Routines common to making IMAP server connections and receiving
**	data from an IMAP server or client. 
**
**  Authors:
**
**	Dave McMurtrie (dgm@pitt.edu)
**
**  RCS:
**
**	$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/imapcommon.c,v $
**	$Id: imapcommon.c,v 1.5 2002/12/18 14:39:55 dgm Exp $
**      
**  Modification History:
**
**	$Log: imapcommon.c,v $
**	Revision 1.5  2002/12/18 14:39:55  dgm
**	Fixed bug in for loop for string literal processing.
**
**	Revision 1.4  2002/12/17 14:22:41  dgm
**	Added support for global configuration structure.
**
**	Revision 1.3  2002/08/29 20:22:12  dgm
**	Fixed nasty socket descriptor leak.
**
**	Revision 1.2  2002/08/28 15:57:49  dgm
**	replaced all internal log function calls with standard syslog calls.
**
**	Revision 1.1  2002/07/03 12:07:26  dgm
**	Initial revision
**
**
*/


#define _REENTRANT

#include <stdio.h>
#include <stdlib.h>
#include "common.h"
#include "imapproxy.h"
#include <string.h>
#include <errno.h>
#include <md5.h>
#include <pthread.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <unistd.h>
#include <fcntl.h>
#include <syslog.h>

/*
 * External globals
 */
extern ICC_Struct *ICC_free;
extern ICC_Struct *ICC_HashTable[ HASH_TABLE_SIZE ];
extern ISD_Struct ISD;
extern pthread_mutex_t mp;
extern pthread_mutex_t trace;
extern IMAPCounter_Struct *IMAPCount;
extern ProxyConfig_Struct PC_Struct;


/*++
 * Function:	LockMutex
 *
 * Purpose:	lock a mutex
 *
 * Parameters:	ptr to the mutex
 *
 * Returns:	nada -- exits on failure
 *
 * Authors:	dgm
 *
 * Notes:
 *--
 */
extern void LockMutex( pthread_mutex_t *mutex )
{
    char *fn = "LockMutex()";
    int rc;
    
    rc = pthread_mutex_lock( mutex );
    if ( rc )
    {
	syslog(LOG_ERR, "%s: pthread_mutex_lock() failed [%d]: Exiting.", fn, rc );
	exit( 1 );
    }
    return;
}


/*++
 * Function:	UnLockMutex
 *
 * Purpose:	unlock a mutex
 *
 * Parameters:	ptr to the mutex
 *
 * Returns:	nada -- exits on failure
 *
 * Authors:	dgm
 *
 * Notes:
 *--
 */
extern void UnLockMutex( pthread_mutex_t *mutex )
{
    char *fn = "UnLockMutex()";
    int rc;
    
    rc = pthread_mutex_unlock( mutex );
    
    if ( rc )
    {
	syslog( LOG_ERR, "%s: pthread_mutex_unlock() failed [%d]: Exiting.", fn, rc );
	exit( 1 );
    }
    return;
}

    

/*++
 * Function:	Get_Server_sd
 *
 * Purpose:	When a client login attempt is made, fetch a usable server
 *              socket descriptor.  This means that either we reuse an
 *              existing sd, or we open a new one.  Hide that abstraction from
 *              the caller...
 *
 * Parameters:	ptr to username string
 *		ptr to password string
 *              const ptr to client hostname or IP string (for logging only)
 *
 * Returns:	int sd on success
 *              -1 on failure
 *
 * Authors:	dgm
 *
 *--
 */
extern int Get_Server_sd( char *Username, 
			  char *Password,
			  const char *ClientAddr )
{
    char *fn = "Get_Server_sd()";
    unsigned int HashIndex;
    ICC_Struct *HashEntry = NULL;
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    char md5pw[16];
    char *tokenptr;
    char *endptr;
    char *last;
    ICC_Struct *ICC_Active;
    ICC_Struct *ICC_tptr;
    ITD_Struct Server;
    int rc;
    unsigned int Expiration;

    Expiration = PC_Struct.cache_expiration_time;
    memset( &Server, 0, sizeof Server );
    
    /* need to md5 the passwd regardless, so do that now */
    md5_calc( md5pw, Password, strlen( Password ) );
    
    /* see if we have a reusable connection available */
    ICC_Active = NULL;
    HashIndex = Hash( Username, HASH_TABLE_SIZE );
    
    LockMutex( &mp );
        
    /*
     * Now we just iterate through the linked list at this hash index until
     * we either find the string we're looking for or we find a NULL.
     */
    for ( HashEntry = ICC_HashTable[ HashIndex ]; 
	  HashEntry; 
	  HashEntry = HashEntry->next )
    {
	if ( ( strcmp( Username, HashEntry->username ) == 0 ) &&
	     ( HashEntry->logouttime > 1 ) )
	{
	    ICC_Active = HashEntry;
	    /*
	     * we found this username in our hash table.  Need to know if
	     * the password matches.
	     */
	    if ( memcmp( md5pw, ICC_Active->hashedpw, sizeof md5pw ) )
	    {
		/* the passwords don't match.  Shake this guy. */
		UnLockMutex( &mp );
		syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: password incorrect", Username, ClientAddr );
		return( -1 );
	    }
	
	    /*
	     * We found a matching password on an inactive server socket.  We
	     * can use this guy.  Before we release the mutex, set the
	     * logouttime such that we mark this connection as "active" again.
	     */
	    ICC_Active->logouttime = 0;
	
	    /*
	     * The fact that we have this stored in a table as an open server
	     * socket doesn't really mean that it's open.  The server could've
	     * closed it on us.
	     * We need a speedy way to make sure this is still open.
	     * We'll set the fd to non-blocking and try to read from it.  If we
	     * get a zero back, the connection is closed.  If we get
	     * EWOULDBLOCK (or some data) we know it's still open.  If we do
	     * read data, make sure we read all the data so we "drain" any
	     * puss that may be left on this socket.
	     */
	    fcntl( ICC_Active->server_sd, F_SETFL,
		   fcntl( ICC_Active->server_sd, F_GETFL, 0) | O_NONBLOCK );
	    
	    while ( ( rc = recv( ICC_Active->server_sd, Server.ReadBuf, 
				 sizeof Server.ReadBuf, 0 ) ) > 0 );
	    
	    if ( !rc )
	    {
		syslog(LOG_NOTICE, "%s: Unable to reuse server sd [%d] for user '%s' (%s).  Connection closed by server.", fn, ICC_Active->server_sd, Username, ClientAddr );
		ICC_Active->logouttime = 1;
		continue;
	    }
	    
	    if ( errno != EWOULDBLOCK )
	    {
		syslog(LOG_NOTICE, "%s: Unable to reuse server sd [%d] for user '%s' (%s). recv() error: %s", fn, ICC_Active->server_sd, Username, ClientAddr, strerror( errno ) );
		ICC_Active->logouttime = 1;
		continue;
	    }
	    
	    
	    fcntl( ICC_Active->server_sd, F_SETFL, 
		   fcntl( ICC_Active->server_sd, F_GETFL, 0) & ~O_NONBLOCK );
	    
	    
	    /* now release the mutex and return the sd to the caller */
	    UnLockMutex( &mp );

	    /*
	     * We're reusing an existing server socket.  There are a few
	     * counters we have to deal with.
	     */
	    IMAPCount->RetainedServerConnections--;
	    IMAPCount->InUseServerConnections++;
	    IMAPCount->TotalServerConnectionsReused++;
	    
	    if ( IMAPCount->InUseServerConnections >
		 IMAPCount->PeakInUseServerConnections )
		IMAPCount->PeakInUseServerConnections = IMAPCount->InUseServerConnections;
	    
	    syslog(LOG_INFO, "LOGIN: '%s' (%s) on existing sd [%d]", Username, ClientAddr, ICC_Active->server_sd );
	    return( ICC_Active->server_sd );
	}
    }
    
    UnLockMutex( &mp );
    
    /*
     * We don't have an active connection for this user.
     * Open a connection to the IMAP server so we can attempt to login 
     */
    Server.sd = socket( AF_INET, SOCK_STREAM, IPPROTO_TCP );
    if ( Server.sd == -1 )
    {
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: Unable to open server socket: %s", 
		Username, ClientAddr, strerror( errno ) );
	return( -1 );
    }
    
    if ( connect( Server.sd, (struct sockaddr *)&ISD.srv, 
		  sizeof(ISD.srv) ) == -1 )
    {
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: Unable to connect to IMAP server: %s", Username, ClientAddr, strerror( errno ) );
	close( Server.sd );
	return( -1 );
    }
    
    
    /* Read & throw away the banner line from the server */
    
    if ( IMAP_Line_Read( &Server ) == -1 )
    {
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: No banner line received from IMAP server",
		Username, ClientAddr );
	close( Server.sd );
	return( -1 );
    }
    
    /*
     * Send the login command off to the IMAP server.
     */
    snprintf( SendBuf, BufLen, "A0001 LOGIN %s %s\r\n", 
	      Username, Password );
    
    if ( send( Server.sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: send() failed attempting to send LOGIN command to IMAP server: %s", Username, ClientAddr, strerror( errno ) );
	close( Server.sd );
	return( -1 );
    }
    
    /*
     * Read the server response
     */
    if ( ( rc = IMAP_Line_Read( &Server ) ) == -1 )
    {
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: No response from IMAP server after sending LOGIN command", Username, ClientAddr );
	close( Server.sd );
	return( -1 );
    }
    
    
    /*
     * Try to match up the tag in the server response to the client tag.
     */
    endptr = Server.ReadBuf + rc;
    
    tokenptr = memtok( Server.ReadBuf, endptr, &last );
    
    if ( !tokenptr )
    {
	/* 
	 * no tokens found in server response?  Not likely, but we still
	 * have to check.
	 */
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: server response to LOGIN command contained no tokens.", Username, ClientAddr );
	close( Server.sd );
	return( -1 );
    }
    
    if ( memcmp( (const void *)tokenptr, (const void *)"A0001", 
		 strlen( tokenptr ) ) )
    {
	/* 
	 * non-matching tag read back from the server... Lord knows what this
	 * is, so we'll fail.
	 */
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: server response to LOGIN command contained non-matching tag.", Username, ClientAddr );
	close( Server.sd );
	return( -1 );
    }
    
    
    /*
     * Now that we've matched the tags up, see if the response was 'OK'
     */
    tokenptr = memtok( NULL, endptr, &last );
    
    if ( !tokenptr )
    {
	/* again, not likely but we still have to check... */
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: Malformed server response to LOGIN command", Username, ClientAddr );
	close( Server.sd );
	return( -1 );
    }
    
    if ( memcmp( (const void *)tokenptr, "OK", 2 ) )
    {
	/*
	 * If the server sent back a "NO" or "BAD", we can look at the actual
	 * server logs to figure out why.  We don't have to break our ass here
	 * putting the string back together just for the sake of logging.
	 */
	syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: non-OK server response to LOGIN command", Username, ClientAddr );
	close( Server.sd );
	return( -1 );
    }
    
    /*
     * put this in our used list and remove it from the free list
     */
    for( ; ; )
    {
	LockMutex( &mp );
	
	if ( ICC_free->next )
	{
	    /* generate the hash index */
	    HashIndex = Hash( Username, HASH_TABLE_SIZE );
	    
	    /* temporarily store the address of the next free structure */
	    ICC_tptr = ICC_free->next;
	    
	    /*
	     * We want to add the newest "used" structure at the front of
	     * the list at the hash index.
	     */
	    ICC_free->next = ICC_HashTable[ HashIndex ];
	    ICC_HashTable[ HashIndex ] = ICC_free;
	    
	    /* 
	     * less typing and more readability, set an "active" pointer.
	     */
	    ICC_Active = ICC_free;
	    
	    /* now point the free listhead to the next available free struct */
	    ICC_free = ICC_tptr;
	    
	    /* fill in the newest used (oxymoron?) structure */
	    strncpy( ICC_Active->username, Username, 
		     sizeof ICC_Active->username );
	    memcpy( ICC_Active->hashedpw, md5pw, sizeof ICC_Active->hashedpw );
	    ICC_Active->logouttime = 0;    /* zero means, "it's active". */
	    ICC_Active->server_sd = Server.sd;
	    
	    UnLockMutex( &mp );
	    
	    IMAPCount->InUseServerConnections++;
	    IMAPCount->TotalServerConnectionsCreated++;

	    if ( IMAPCount->InUseServerConnections >
		 IMAPCount->PeakInUseServerConnections )
		IMAPCount->PeakInUseServerConnections = IMAPCount->InUseServerConnections;
	    syslog(LOG_INFO, "LOGIN: '%s' (%s) on new sd [%d]", Username, ClientAddr, Server.sd );
	    return( Server.sd );
	}
	
	/*
	 * There weren't any free ICC structs.  Try to free one.  Make sure
	 * we unlock the mutex, since ICC_Recycle needs to obtain it.
	 */
	UnLockMutex( &mp );
	
	Expiration = abs( Expiration / 2 );
	
	/*
	 * Eventually, we have to fail
	 */
	if ( Expiration <= 2 )
	{
	    syslog(LOG_INFO, "LOGIN: '%s' (%s) failed: Out of free ICC structs.", Username, ClientAddr );
	    close( Server.sd );
	    return( -1 );
	}
	
	ICC_Recycle( Expiration );
	
    }
    
}

    



/*++
 * Function:	imparse_isatom
 *
 * Purpose:	determine if a string is an "atom" according to RFC2060
 *		which basically means (if you convolute your way through
 *		the BNF notation in the back of the RFC):  1 or more of
 *		any character except"(" / ")" / "{" / SPACE / CTL / "%" /
 *		"*".
 *
 * Parameters:	const char ptr to the string to examine.
 *
 * Returns:	non-zero if the string is an atom.
 *
 * Authors:	This was taken directly and exactly from cyrus-imap-1.5.14
 *--
 */
extern int imparse_isatom( const char *s )
{
    int len = 0;
    
    if (!*s) return 0;
    for (; *s; s++) 
    {
        len++;
        if (*s & 0x80 || *s < 0x1f || *s == 0x7f ||
            *s == ' ' || *s == '{' || *s == '(' || *s == ')' ||
            *s == '\"' || *s == '%' || *s == '*' || *s == '\\') return 0;
    }
    if (len >= 1024) return 0;
    return 1;
}



/*++
 * Function:	memtok
 *
 * Purpose:	similar to strtok_r, except doesn't require a NULL
 *		terminated string.  Since this is only for IMAP use,
 *              it also doesn't require a "separator" to be passed in,
 *              it assumes a single space will always be the token separator.
 *
 * Parameters:	char ptr.  Beginning of buffer (or NULL)
 *		char ptr.  End of buffer.
 *		ptr to char ptr.  Last position in buffer (for
 *		                  subsequent calls).
 *
 * Returns:	char pointer to the first character of the token.
 *		NULL pointer if no tokens are found.
 *
 * Authors:     dgm
 *--
 */
extern char *memtok( char *Begin, char *End, char **Last )
{
    char *CP;
    char *First;

    /*
     * If the Begin address is NULL, pick up one character beyond
     * where we left off.  Check to make sure we didn't leave off at the
     * end of our buffer, though.
     */
    if ( ! Begin )
    {
	if ( *Last == End )
	{
	    return( NULL );
	}
	else
	{
	    First = *Last + 1;
	}
    }
    else
    {
	First = Begin;
    }
    
    if ( ! First )
	return( NULL );

    /* Look for a space */
    CP = memchr( First, ' ', End - First );

    /* 
     * If we don't find a space, maybe we're at the last token in the line.
     * If that's the case, we should be able to find a CR.
     */
    if ( !CP )
	CP = memchr( First, '\r', End - First );
	
    if ( ! CP )
	return( NULL );

    /*
     * If we found the token where we started out, we have a double space, 
     * a /r/r, or a /r at the beginning of the line.  In any case, it's not
     * correct as far as RFC2060 goes.
     */
    if ( CP == First )
	return( NULL );
    
    *Last = CP;
    *CP = '\0';
    
    return( First );
}

	
    

/*++
 * Function:	IMAP_Literal_Read
 *
 * Purpose:	Read IMAP string literals from a socket.
 *
 * Parameters:	ptr to a IMAPTransactionDescriptor structure
 * 
 * Returns:	number of bytes read on success
 *		-1 on failure
 * Authors:	dgm
 *
 * Notes:	
 *--
 */
extern int IMAP_Literal_Read( ITD_Struct *ITD )
{
    char *fn = "IMAP_Literal_Read()";
    int Status, i, j;

    /*
     * If there aren't any LiteralBytesRemaining, just return 0.
     */
    if ( ! ITD->LiteralBytesRemaining )
	return( 0 );

    
    /* scoot the buffer */
    for ( i = ITD->ReadBytesProcessed, j = 0; 
	  i <= ITD->BytesInReadBuffer; 
	  i++, j++ )
    {
	ITD->ReadBuf[j] = ITD->ReadBuf[i];
    }
    ITD->BytesInReadBuffer -= ITD->ReadBytesProcessed;
    ITD->ReadBytesProcessed = 0;

    /*
     * If we have any data in our buffer, return what we have.
     */
    if ( ITD->BytesInReadBuffer > 0 )
    {
	if ( ITD->BytesInReadBuffer >= ITD->LiteralBytesRemaining )
	{
	    ITD->ReadBytesProcessed = ITD->LiteralBytesRemaining;
	    ITD->LiteralBytesRemaining = 0;
	    return( ITD->ReadBytesProcessed );
	}
	else
	{
	    ITD->ReadBytesProcessed += ITD->BytesInReadBuffer;
	    ITD->LiteralBytesRemaining -= ITD->BytesInReadBuffer;
	    return( ITD->ReadBytesProcessed );
	}
    }
	
    /*
     * No data left in the buffer.  Have to call read.  Read either
     * the number of literal bytes left, or the rest of our buffer --
     * whichever is smaller.
     */
    Status = recv(ITD->sd, &ITD->ReadBuf[ITD->BytesInReadBuffer],
		  (sizeof ITD->ReadBuf - ITD->BytesInReadBuffer ), 0 );
    
    
    if ( Status == 0 )
    {
	syslog(LOG_WARNING, "%s: connection closed prematurely.", fn);
	return(-1);
    }
    else if ( Status == -1 )
    {
	syslog(LOG_ERR, "%s: recv() failed: %s", fn, strerror(errno) );
	return(-1);
    }
    
    /*
     * update the bytes remaining and return the byte count read.
     */
    ITD->BytesInReadBuffer += Status;
    
    if ( ITD->BytesInReadBuffer >= ITD->LiteralBytesRemaining )
    {
	ITD->ReadBytesProcessed = ITD->LiteralBytesRemaining;
	ITD->LiteralBytesRemaining = 0;
	return( ITD->ReadBytesProcessed );
    }
    else
    {
	ITD->ReadBytesProcessed += ITD->BytesInReadBuffer;
	ITD->LiteralBytesRemaining -= ITD->BytesInReadBuffer;
	return( ITD->ReadBytesProcessed );
    }
}





/*++
 * Function:	IMAP_Line_Read
 *
 * Purpose:	Line-oriented buffered reads from the imap server
 *
 * Parameters:	ptr to a IMAPTransactionDescriptor structure
 *
 * Returns:	Number of bytes on success
 *              -1 on error
 *
 * Authors:	dgm
 *
 * Notes:	caller must be certain that the IMAPTransactionDescriptor
 *		is initialized to zero on the first call.
 *
 *
 *		Callers must check RemainingLiteralBytes after calling this
 *		function.  If this is set and a caller ignores it, it will
 *		play havoc...  Actually, it will exit() and kill the entire
 *              process.
 *--
 */
extern int IMAP_Line_Read( ITD_Struct *ITD )
{
    char *CP;
    int Status, i, j;
    char *fn = "IMAP_Line_Read()";
    char *EndOfBuffer;

    /*
     * Sanity check.  This function should never be called if there are
     * literal bytes remaining to be processed.
     */
    if ( ITD->LiteralBytesRemaining )
    {
	syslog(LOG_ERR, "%s: Sanity check failed! Literal bytestream has not been fully processed (%d bytes remain) and line-oriented read function was called again.  Exiting!", fn, ITD->LiteralBytesRemaining);
	exit(1);
    }
    

    /* Point End to the end of our buffer */
    EndOfBuffer = &ITD->ReadBuf[sizeof ITD->ReadBuf - 1];


    /* 
     * Shift the contents of our buffer.  This will erase any previous
     * line that we already gave to a caller.
     */
    for ( i = ITD->ReadBytesProcessed, j = 0; 
	  i <= ITD->BytesInReadBuffer; 
	  i++, j++ )
    {
	ITD->ReadBuf[j] = ITD->ReadBuf[i];
    }
    ITD->BytesInReadBuffer -= ITD->ReadBytesProcessed;
    ITD->ReadBytesProcessed = 0;

    for (;;)
    {
	/*
	 * If we've been called before, we may already have another line
	 * in the buffer that we can return to the caller.
	 */
	CP = (char *)memchr( ITD->ReadBuf, '\n', ITD->BytesInReadBuffer );
	
	if ( CP )
	{
	    /*
	     * found a '\n'.  Check to see if it's preceded by a '\r'
	     * but make sure we catch the case where '\n' is the first
	     * character sent.  If we find this, it could be the result
	     * of a "more data" scenerio.
	     */
	    if ( CP != ITD->ReadBuf )
	    {
		/* reset the moredata flag */
		ITD->MoreData = 0;

		if ( *(CP - 1) == '\r' )
		{
		    /*
		     * Set ReadBytesProcessed to the length of the line
		     * we just found.  Just subtract the address of the
		     * beginning of the buffer from the address of our
		     * "/n" in the buffer.  Always need to add one.
		     */
		    ITD->ReadBytesProcessed = ( CP - ITD->ReadBuf + 1);
		    
		    /*
		     * As if this isn't already ugly enough, now we have
		     * to check whether this is a line that indicates a
		     * string literal is coming next.  How do we know?
		     * If it is, the line will end with {bytecount}.
		     */
		    if ( ((CP - ITD->ReadBuf + 1) > 2 ) && ( *(CP - 2) == '}' ))
		    {
			char *LiteralEnd;
			char *LiteralStart;
			
			LiteralStart = NULL;
			
			/*
			 * Found a '}' as the last character on the line.
			 * Save it's place and then look for the
			 * beginning '{'.
			 */
			LiteralEnd = CP - 2;
			CP -= 2;
			
			for ( ; LiteralEnd >= ITD->ReadBuf; CP-- )
			{
			    if ( *CP == '{' )
			    {
				LiteralStart = CP;
				break;
			    }
			}
			
			if ( !LiteralStart )
			{
			    /* 
			     * we have a line ending with } but no beginning
			     * '{'.
			     */
			    syslog(LOG_WARNING, "%s: Found line ending with '}' (expected string literal byte specification) but no opening '{' was found.  No action taken.", fn);
			    return(0);
			}
			
			/*
			 * We found an opening and closing { } pair.  The
			 * thing in between should be a number specifying
			 * a byte count.  That would be as much as we needed
			 * to know if it wasn't for the fact that RFC 2088
			 * allows for a + sign in the literal specification
			 * that has a different meaning when a client is
			 * sending a literal to a server.
			 */
			if ( *(LiteralEnd - 1) == '+' )
			    ITD->NonSyncLiteral = 1;
			

			/* To grab the number, bump our
			 * starting char pointer forward a byte and temporarily
			 * turn the closing '}' into a NULL.  Don't worry about
			 * the potential '+' sign, atol won't care.
			 */
			LiteralStart++;
			*LiteralEnd = '\0';
			ITD->LiteralBytesRemaining = atol( LiteralStart );
			
			if ( ! ITD->LiteralBytesRemaining && errno )
			{
			    syslog(LOG_WARNING, "%s: atol() failed on string '%s': %s.", fn, CP, strerror(errno) );
			    *LiteralEnd = '}';
			    return(0);
			}
			
			*LiteralEnd = '}';
		    }
		    /*
		     * This looks redundant, huh?
		     */
		    return( ITD->ReadBytesProcessed );
		}
		else
		{
		    /*
		     * found a '\n' that's not preceded by a '\r'.
		     */
		    syslog(LOG_WARNING, "%s: Protocol error.  Line terminated by LF, not CRLF", fn);
		    return(-1);
		}
	    }
	    else
	    {
		/*
		 * We just processed a line that has '\n' as the first
		 * character.  This may or may not be a problem.
		 */
		if ( ITD->MoreData )
		{
		    /*
		     * When we set the MoreData flag, we return 20 bytes fewer
		     * than what's in our buffer.  It's possible for that
		     * to send the CR but not the LF back to the caller,
		     * leaving it as the first thing in the buffer now.
		     * we can't be sure that this is the case, but the client
		     * and the server can fight it out if it's not.
		     */
		    ITD->ReadBytesProcessed = 1;
		    return( ITD->ReadBytesProcessed );
		}
		else
		{
		    syslog(LOG_WARNING, "%s: Protocol error.  Line begins with LF.", fn);
		    return(-1);
		}
		
	    }
	}
	
	/* 
	 * There weren't any "lines" in our buffer.  We need to get more data
	 * from the server.  Ultimately, we'll want to call recv and
	 * add on to the end of the existing buffer.  
	 *
	 * Before we go off and wildly start calling recv() we should really
	 * make sure that we have space left in our buffer.  If not,
	 * set the "more to come" flag and return what we have to the caller.
	 */
	if ( ( sizeof ITD->ReadBuf - ITD->BytesInReadBuffer ) < 1 )
	{
	    /*
	     * less than one byte of storage left in our buffer.  Return what
	     * we have, but get a little bit tricky -- don't return everything
	     * that we have.  The reason for this is because the last thing
	     * in our buffer right now could just happen to be {80 (the
	     * beginning of a string literal specifier).  If we just return
	     * this and come back to start reading more, we'll completely miss
	     * the fact that a literal is coming next.  If we return all but 20
	     * bytes of the current buffer, we completely negate that potential
	     * problem.
	     */
	    ITD->MoreData = 1;
	    CP = EndOfBuffer - 20;
	    
	    /*
	     * Set ReadBytesProcessed to the length of the line
	     * we're gonna return.
	     */
	    ITD->ReadBytesProcessed = ( CP - ITD->ReadBuf + 1);
	    return( ITD->ReadBytesProcessed );
	}
	
	Status = recv(ITD->sd, &ITD->ReadBuf[ITD->BytesInReadBuffer],
		      (sizeof ITD->ReadBuf - ITD->BytesInReadBuffer ), 0 );
	
	if ( Status == 0 )
	{
	    syslog(LOG_WARNING, "%s: connection closed prematurely.", fn);
	    return(-1);
	}
	else if ( Status == -1 )
	{
	    syslog(LOG_ERR, "%s: recv() failed: %s", fn, strerror(errno) );
	    return(-1);
	}
		
	/*
	 * update the buffer count and head back to the top of the
	 * for loop.
	 */
	ITD->BytesInReadBuffer += Status;
    }
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
