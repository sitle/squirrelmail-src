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
**	request.c
**
**  Abstract:
**
**	Routines to handle IMAP client requests...  Specifically, this module
**	contains the handler thread (Handle_Request) that takes care of
**	incoming client connections.  It also contains one function for each
**	of the 5 unauthenticated commands that the proxy server can handle
**	without having to make a connection to the real IMAP server.  Finally,
**	the raw proxy function resides in this module, as well.
**
**  Authors:
**
**	$Author: dgm $
**
**  RCS:
**
**	$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/request.c,v $
**	$Id: request.c,v 1.5 2002/08/30 13:24:43 dgm Exp $
**      
**  Modification History:
**
**	$Log: request.c,v $
**	Revision 1.5  2002/08/30 13:24:43  dgm
**	Added support for total client logins counter
**
**	Revision 1.4  2002/08/28 15:59:25  dgm
**	replaced all internal log function calls with standard syslog calls.
**	Added P_RESETCOUNTERS command.
**	Added logic to time out connections in the raw proxy loop.
**
**	Revision 1.3  2002/08/27 20:15:16  dgm
**	No longer bother doing a dns lookup before calling Get_Server_sd().
**	We'll only pass the ip address string instead of possibly hostname.
**
**	Revision 1.2  2002/07/18 20:43:17  dgm
**	added p_dumpicc and p_newlog commands.  Changed trace
**	command to p_trace.
**
**	Revision 1.1  2002/07/03 12:08:34  dgm
**	Initial revision
**
**
*/


#define _REENTRANT

#include <errno.h>
#include <string.h>
#include "common.h"
#include "imapproxy.h"
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/uio.h>
#include <poll.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/param.h>
#include <netdb.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <syslog.h>

/*
 * There are a few global variables that we care about.  Make sure we know
 * about them here.
 */
extern char Banner[BUFSIZE];
extern int BannerLen;
extern char Capability[BUFSIZE];
extern int CapabilityLen;
extern IMAPCounter_Struct *IMAPCount;
extern ISD_Struct ISD;
extern pthread_mutex_t mp;
extern pthread_mutex_t trace;
extern char TraceUser[MAXUSERNAMELEN];
extern int Tracefd;
extern ICC_Struct *ICC_HashTable[ HASH_TABLE_SIZE ];

/*
 * Function prototypes for internal entry points.
 */
static int cmd_noop( ITD_Struct *, char * );
static int cmd_logout( ITD_Struct *, char * );
static int cmd_capability( ITD_Struct *, char * );
static int cmd_authenticate( ITD_Struct *, char * );
static int cmd_login( ITD_Struct *, char *, char *, int, char * );
static int cmd_trace( ITD_Struct *, char *, char * );
static int cmd_dumpicc( ITD_Struct *, char * );
static int cmd_newlog( ITD_Struct *, char * );
static int cmd_resetcounters( ITD_Struct *, char * );
static int Raw_Proxy( ITD_Struct *, ITD_Struct * );



/*
 * Function definitions.
 */

/*++
 * Function:	cmd_newlog
 *
 * Purpose:	Clear the proxy trace log file.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *              char ptr to Tag sent with this command.
 *
 * Returns:	0 on success
 *		-1 on failure
 *--
 */
static int cmd_newlog( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_newlog";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    int rc;
    
    SendBuf[BUFSIZE - 1] = '\0';

    rc = ftruncate( Tracefd, 0 );
    
    if ( rc != 0 )
    {
	syslog(LOG_ERR, "%s: ftruncate() failed: %s", fn, strerror( errno ) );
	snprintf( SendBuf, BufLen, "%s NO internal server error\r\n", Tag );
    }
    else
    {
	snprintf( SendBuf, BufLen, "%s OK Completed\r\n", Tag );
    }

    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	return( -1 );
    }

    return( rc );
}


/*++
 * Function:	cmd_resetcounters
 *
 * Purpose:	Reset the global high-water marks and total counters.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *		char ptr to Tag sent with this command.
 *
 * Returns:	0 on success.
 *		-1 on failure.
 *
 * Authors:	dgm
 *
 * Notes:	Always key to remember that we don't take out a mutex
 *		anywhere that we update these global counters.  There's
 *		never a guarantee that they'll be exactly correct but
 *		for the performance penalty we'd pay to make them correct
 *		we just don't care.
 *--
 */
static int cmd_resetcounters( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_resetcounters";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE -1;
    
    SendBuf[BufLen] = '\0';
    
    IMAPCount->CountTime = time( 0 );
    IMAPCount->PeakClientConnections = 0;
    IMAPCount->PeakInUseServerConnections = 0;
    IMAPCount->PeakRetainedServerConnections = 0;
    IMAPCount->TotalClientConnectionsAccepted = 0;
    IMAPCount->TotalServerConnectionsCreated = 0;
    IMAPCount->TotalServerConnectionsReused = 0;
    
    snprintf( SendBuf, BufLen, "%s OK Completed\r\n", Tag );
    
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	return( -1 );
    }
    
    return( 0 );
}



/*++
 * Function:	cmd_dumpicc
 *
 * Purpose:	Dump the contents of all imap connection context structs.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *              char ptr to Tag sent with this command.
 *
 * Returns:	0 on success
 *		-1 on failure
 *--
 */
static int cmd_dumpicc( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_dumpicc";
    char SendBuf[BUFSIZE];
    unsigned int HashIndex;
    ICC_Struct *HashEntry;
    unsigned int BufLen = BUFSIZE - 1;
    
    SendBuf[BUFSIZE - 1] = '\0';
    
    LockMutex( &mp );
    
    for ( HashIndex = 0; HashIndex < HASH_TABLE_SIZE; HashIndex++ )
    {
	HashEntry = ICC_HashTable[ HashIndex ];
	
	while ( HashEntry )
	{
	    snprintf( SendBuf, BufLen, "* %d %s %s\r\n", HashEntry->server_sd,
		      HashEntry->username,
		      ( ( HashEntry->logouttime ) ? "Cached" : "Active" ) );
	    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	    {
		UnLockMutex( &mp );
		syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
		return( -1 );
	    }
	    HashEntry = HashEntry->next;
	}
    }
    
    UnLockMutex( &mp );
    
    snprintf( SendBuf, BufLen, "%s OK Completed\r\n", Tag );
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	return( -1 );
    }
    
    return( 0 );
}



/*++
 * Function:	cmd_trace
 *
 * Purpose:	turn on per-user tracing in the proxy server.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *              char ptr to Tag sent with this command.
 *              char ptr to the username we want to trace (NULL to turn
 *              off tracing)
 *
 * Returns:	0 on success
 *		-1 on failure
 *--
 */
static int cmd_trace( ITD_Struct *itd, char *Tag, char *Username )
{
    char *fn = "cmd_trace";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    
    SendBuf[BUFSIZE - 1] = '\0';
    
    /*
     * Here are the tracing semantics:
     *
     * Tracing is to be limited to only one user at a time.  This decision was
     * made for a few different reasons.  First, to conserve system resources
     * such as disk space.  Second, to improve overall server performance --
     * tracing will slow a thread down.  Third, so a sysadmin doesn't forget
     * that tracing is turned on for a user (like I commonly do when I enable
     * tracing in cyrus imapd).  Fourth, it's just easier this way.
     */    
    
    LockMutex( &trace );
    
    if ( !Username )
    {
	snprintf( SendBuf, BufLen, "\n\n-----> C= %s PROXY: user tracing disabled. Expect further output until client logout.\n", TraceUser );
	write( Tracefd, SendBuf, strlen( SendBuf ) );
	
	memset( TraceUser, 0, sizeof TraceUser );
	snprintf( SendBuf, BufLen, "%s OK Tracing disabled\r\n", Tag );
	if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	{
	    syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	    UnLockMutex( &trace );
	    return( -1 );
	}

	UnLockMutex( &trace );
	return( 0 );
    }

    if ( TraceUser[0] )
    {
	/* guarantee no runaway strings */
	TraceUser[sizeof TraceUser - 1] = '\0';
	snprintf( SendBuf, BufLen, "%s BAD Tracing already enabled for user %s\r\n", Tag, TraceUser );
	if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	{
	    syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	    UnLockMutex( &trace );
	    return( -1 );
	}
	
	UnLockMutex( &trace );
	return( 0 );
	
    }
    
    strncpy( TraceUser, Username, sizeof TraceUser - 1 );
    
    snprintf( SendBuf, BufLen, "%s OK Tracing enabled\r\n", Tag );
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	UnLockMutex( &trace );
	return( -1 );
    }

    snprintf( SendBuf, BufLen, "\n\n-----> C= %s PROXY: user tracing enabled.\n", TraceUser );
    write( Tracefd, SendBuf, strlen( SendBuf ) );
    
    UnLockMutex( &trace );
    return( 0 );
}



/*++
 * Function:	cmd_noop
 *
 * Purpose:	implement the NOOP IMAP command.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *
 * Returns:	0 on success
 *		-1 on failure
 *--
 */
static int cmd_noop( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_noop";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    
    SendBuf[BUFSIZE - 1] = '\0';
    
    snprintf( SendBuf, BufLen, "%s OK Completed\r\n", Tag );
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	return( -1 );
    }
    
    return( 0 );
}



/*++
 * Function:	cmd_logout
 *
 * Purpose:	implement the LOGOUT IMAP command.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *
 * Returns:	0 on success
 *		-1 on failure
 *--
 */
static int cmd_logout( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_logout";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    
    SendBuf[BUFSIZE - 1] = '\0';
    
    snprintf( SendBuf, BufLen, "* BYE LOGOUT received\r\n%s OK Completed\r\n",
	      Tag );
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() to client failed on sd [%d]: %s", fn, itd->sd, strerror(errno) );
	return( -1 );
    }
    
    return( 0 );
}



/*++
 * Function:	cmd_capability
 *
 * Purpose:	implement the CAPABILITY IMAP command.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *
 * Returns:	0 on success
 *		-1 on failure
 *--
 */
static int cmd_capability( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_capability";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    
    SendBuf[BUFSIZE - 1] = '\0';
    
    snprintf( SendBuf, BufLen, "%s%s OK Completed\r\n",Capability, Tag );
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	return( -1 );
    }
    
    return( 0 );
}




/*++
 * Function:	cmd_authenticate
 *
 * Purpose:	implement the AUTHENTICATE IMAP command.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *
 * Returns:	0 on success
 *		-1 on failure
 *
 * Notes:	This will need to be changed such that the entire
 *		session is proxied.  For now, we'll just drop it.
 *--
 */
static int cmd_authenticate( ITD_Struct *itd, char *Tag )
{
    char *fn = "cmd_authenticate";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    
    SendBuf[BUFSIZE - 1] = '\0';
    
    snprintf( SendBuf, BufLen, "%s NO AUTHENTICATE failed\r\n", Tag );
    if ( send( itd->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed: %s", fn, strerror(errno) );
	return( -1 );
    }
    
    return( 0 );
}



/*++
 * Function:	cmd_login
 *
 * Purpose:	implement the LOGIN IMAP command.
 *
 * Parameters:	ptr to ITD_Struct for client connection.
 *              ptr to username
 *              ptr to password
 *              int length of password buffer
 *              ptr to client tag
 *
 * Returns:	0 on success prior to authentication
 *              1 on success after authentication (we caught a logout)
 *		-1 on failure
 *
 * Note:        Not too many things are really considered "failure" in the
 *              context of this routine, because returning failure would
 *              result in the client connection being closed.  When most
 *              things fail, we'll send a failure response to the client,
 *              but return success to the caller.  That will allow the
 *              client to know that something broke and try again if it
 *              wants to.
 *
 *--
 */
static int cmd_login( ITD_Struct *Client, 
		      char *Username, 
		      char *Password,
		      int passlen,
		      char *Tag )
{
    char *fn = "cmd_login()";
    char SendBuf[BUFSIZE];
    unsigned int BufLen = BUFSIZE - 1;
    ITD_Struct Server;
    int rc;
    int sd;
    char TraceFileName[ MAXPATHLEN ];
    struct sockaddr_in cli_addr;
    int addrlen;
    char *hostaddr;

    memset( &Server, 0, sizeof Server );

    addrlen = sizeof( struct sockaddr_in );

    if ( getpeername( Client->sd, (struct sockaddr *)&cli_addr, &addrlen ) < 0 )
    {
	syslog(LOG_INFO, "LOGIN: '%s' failed: getpeername() failed for client sd: %s", Username, strerror( errno ) );
	return( -1 );
    }
    
    hostaddr = inet_ntoa( ( ( struct sockaddr_in *)&cli_addr )->sin_addr );

    if ( !hostaddr )
    {
	syslog(LOG_INFO, "LOGIN: '%s' failed: inet_ntoa() failed for client sd: %s", Username, strerror( errno ) );
	return( -1 );
    }
    
    sd = Get_Server_sd( Username, Password, hostaddr );

    /*
     * wipe out the passwd so we don't have it sitting in memory somewhere.
     */
    memset( Password, 0, passlen );
    
        
    if ( sd == -1 )
    {
	/*
	 * All logging is done in Get_Server_sd, so don't bother to
	 * log anything here.
	 */
	snprintf( SendBuf, BufLen, "%s NO LOGIN failed\r\n", Tag );
	
	if ( send( Client->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	{
	    syslog(LOG_ERR, "%s: Unable to send failure message back to client: %s", fn, strerror(errno) );
	    return( -1 );
	}
	return( 0 );
    }
    
    Server.sd = sd;

    /*
     * Send a success message back to the client
     * and go into raw proxy mode.
     */
    snprintf( SendBuf, BufLen, "%s OK User logged in\r\n", Tag );
    if ( send( Client->sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
    {
	/*
	 * This really sux.  We successfully logged the user in, but now
	 * we can't communicate with the client...
	 */
	IMAPCount->InUseServerConnections--;
	close( Server.sd );
	syslog(LOG_ERR, "%s: Unable to send successful login message back to client: %s -- closing connection.", fn, strerror(errno) );
	return( -1 );
    }

    IMAPCount->TotalClientLogins++;
    
    /* turn on tracing for this session if necessary */
    LockMutex( &trace );
    if ( ! strcmp( Username, TraceUser ) )
    {
	Client->TraceOn = 1;
	Server.TraceOn = 1;
    }
    else
    {
	Client->TraceOn = 0;
	Server.TraceOn = 0;
    }
    UnLockMutex( &trace );
	 
    rc = Raw_Proxy( Client, &Server );

    /*
     * It's not necessary to take out the trace mutex here.  The reason
     * we take it out when we check above is because the trace username
     * could change at any time.  When we disable tracing here, we're
     * doing it regardless of what the trace username is, so we don't 
     * take out the mutex.
     */
    Client->TraceOn = 0;
    Server.TraceOn = 0;
    
    /* update the logout time for this cached connection */
    ICC_Logout( Username, Server.sd );
    
    return( rc );
}




/*++
 * Function:	Raw_Proxy
 *
 * Purpose:	Start a raw proxy session to the IMAP server, catching only
 *		LOGOUT commands from the client.
 *
 * Parameters:	ptr to client ITD_Struct
 *		ptr to server ITD_Struct
 *
 * Returns:	1 if we caught a logout
 *		-1 on failure
 *
 * Authors:	dgm
 *--
 */
static int Raw_Proxy( ITD_Struct *Client, ITD_Struct *Server )
{
    char *fn = "Raw_Proxy()";
    struct pollfd fds[2];
    nfds_t nfds;
    int status;
    unsigned int FailCount;
    int BytesSent;
    char *CP;
    char TraceBuf[ BUFSIZE ];
    
#define SERVER 0
#define CLIENT 1
    
    FailCount = 0;
    nfds = 2;
    
    /*
     * Set up our fds structs.
     */
    fds[ SERVER ].fd = Server->sd;
    fds[ CLIENT ].fd = Client->sd;
    
    fds[ SERVER ].events = POLLIN;
    fds[ CLIENT ].events = POLLIN;

    

    /*
     * POLL loop
     */
    for ( ; ; )
    {
	fds[ SERVER ].revents = 0;
	fds[ CLIENT ].revents = 0;
	
	status = poll( fds, nfds, POLL_TIMEOUT );
	
	/*
	 * poll returns a non-negative value on success.
	 * it returns 0 if it times out before any revents are modified.
	 * -1 is returned on failure.
	 */
	if ( !status )
	{
	    /*
	     * We timed out.  End result is that we want the server and
	     * client sides of this connection to close.  The nicest way
	     * to have this happen will be to simply return failure to
	     * cmd_login().
	     * We were called by HandleRequest() & then cmd_login().  When
	     * we return -1 to cmd_login(), he'll call ICC_Logout() to
	     * update the logout time for this user on this server sd.
	     * Eventually, the ICC_Recycle thread will wake up and reap the
	     * server-side of the connection.
	     * cmd_login() will return our -1 to Handle_Request
	     * and HandleRequest will close the client-side socket.
	     */
	    syslog( LOG_WARNING, "%s: poll() timed out. server sd [%d]. client sd [%d].", fn, Server->sd, Client->sd );
	    return( -1 );
	}
	
	if ( status < 0 )
	{
	    /* If we were interrupted by a signal, just continue the loop. */
	    if ( errno == EINTR )
	    {
		syslog(LOG_INFO, "%s: poll() was interrupted by a signal -- continuing.", fn);
		continue;
	    }
	    
	    
	    /* resource issue -- try again. */
	    if ( errno == EAGAIN )
	    {
		FailCount++;
		if ( FailCount == 5 )
		{
		    syslog(LOG_ERR, "%s: poll() returned EAGAIN.  Exceeded retry limit.  Returning failure.", fn );
		    return( -1 );
		}
		
		syslog(LOG_WARNING,"%s: poll() returned EAGAIN.  Retrying.", fn );
		sleep(5);
		continue;
	    }

	    /* anything else, we're really jacked about it. */
	    syslog(LOG_ERR, "%s: poll() failed: %s -- Returning failure.", fn, strerror( errno ) );
	    return( -1 );
	}
	
	FailCount = 0;
	

	/*
	 * PROXY LOOPS
	 */

	/*
	 * Check the server first to see if he has any data to send.  I don't
	 * know if the order (client or server) matters, but my logic is that
	 * the client should always be ready to hear from the server since the
	 * server is allowed to send unsolicited data and the client has to
	 * be able to deal with it.
	 */
	if ( fds[ SERVER ].revents )
	{
	    for ( ; ; )
	    {
		status = recv( Server->sd, Server->ReadBuf, 
			       sizeof Server->ReadBuf, 0 );
		
		if ( status == -1 )
		{
		    if ( errno == EINTR )
			continue;
		    
		    syslog(LOG_WARNING, "%s: recv() failed reading from IMAP server on sd [%d]: %s", fn, Server->sd, strerror( errno ) );
		    return( -1 );
		}
		break;
	    }
	    
	    if ( status == 0 )
	    {
		/* the server closed the connection, dammit */
		syslog(LOG_ERR, "%s: IMAP server unexpectedly closed the connection on sd %d", fn, Server->sd );
		return( -1 );
	    }
	    
	    if ( Server->TraceOn )
	    {
		snprintf( TraceBuf, sizeof TraceBuf - 1, "\n\n-----> C= %s SERVER: sd [%d]\n", ( (TraceUser) ? TraceUser : "Null username" ), Server->sd );
		write( Tracefd, TraceBuf, strlen( TraceBuf ) );
		write( Tracefd, Server->ReadBuf, status );
	    }
	    
	    /* whatever we read from the server, ship off to the client */
	    for ( ; ; )
	    {
		BytesSent = send( Client->sd, Server->ReadBuf, status, 0 );
		
		if ( BytesSent == -1 )
		{
		    if ( errno == EINTR )
			continue;
		    
		    syslog(LOG_ERR, "%s: send() failed sending data to client on sd [%d]: %s", fn, Client->sd, strerror( errno ) );
		    return( -1 );
		}
		break;
	    }  /* end of infinite for loop for send() to client */
	}          /* end of if conditional -- were there server sd events? */
	
	
	/*
	 * Now we proxy from the client to the server.  We have to watch
	 * this side a little bit closer...
	 */
	if ( ! fds[ CLIENT ].revents )
	{
	    continue;
	}

	do
	{
	    do 
	    {
		status = IMAP_Line_Read( Client );
		
		if ( status == -1 )
		{
		    syslog(LOG_NOTICE, "%s: Failed to read line from client on socket %d", fn, Client->sd );
		    return( -1 );
		}
	    
		if ( Client->TraceOn )
		{
		    snprintf( TraceBuf, sizeof TraceBuf - 1, "\n\n-----> C= %s CLIENT: sd [%d]\n", ( (TraceUser) ? TraceUser : "Null username" ), Client->sd );
		    write( Tracefd, TraceBuf, strlen( TraceBuf ) );
		    write( Tracefd, Client->ReadBuf, status );
		}
		
	    
		/* this is a command -- is it logout? */
		CP = memchr(Client->ReadBuf, ' ',
			    Client->ReadBytesProcessed );
	    
		if ( CP )
		{
		    CP++;
		    
		    if ( !strncasecmp( CP, "LOGOUT", 6 ) )
		    {
			/*
			 * Since we want to potentially reuse this server
			 * connection, we want to return it to an unselected 
			 * state.  
			 *
			 * This may not be entirely necessary, so don't go
			 * crazy trying to check return codes, etc...  Also,
			 * make a half-hearted attempt to eat whatever the
			 * server sends back.
			 */
			send( Server->sd, "ZZZ CLOSE\r\n",
			      strlen("ZZZ CLOSE\r\n"), 0 );
			recv( Server->sd, Server->ReadBuf, 
			      sizeof Server->ReadBuf, 0 );
			memset( Server->ReadBuf, 0, sizeof Server->ReadBuf );
			
			return( 1 );
		    }
		}
		
		/*
		 * it's some command other than a LOGOUT...
		 * just ship it over
		 */
		for ( ; ; )
		{
		    BytesSent = send( Server->sd, Client->ReadBuf, status, 0 );
		    if ( BytesSent == -1 )
		    {
			if ( errno == EINTR )
			    continue;
			
			syslog(LOG_ERR, "%s: send() failed sending data to client on sd [%d]: %s", fn, Client->sd, strerror( errno ) );
			return( -1 );
		    }
		    break;
		}
		
		/*
		 * Don't just rely on our do/while condition.  There
		 * may still be stuff in our buffer, but it could be
		 * literal data.  Check and break out of the loop if that's
		 * the case.
		 */
		if ( Client->LiteralBytesRemaining )
		    break;
		
	    } while ( Client->BytesInReadBuffer > Client->ReadBytesProcessed );
	    
	    
	    /* 
	     * If there are literal bytes to read, get them and blast them
	     * off to the server.  Only do this, however, if it's a non
	     * synchronous literal since the server has to send a "go ahead"
	     * otherwise.
	     */
	    if ( ! Client->LiteralBytesRemaining )
		continue;
	    
	    
	    /*
	     * Do we have to wait for a "go-ahead" from the server?
	     */
	    if ( ! Client->NonSyncLiteral )
	    {
		/* we have to wait for a go-ahead */
		status = IMAP_Line_Read( Server );
		if ( Server->TraceOn )
		{
		    snprintf( TraceBuf, sizeof TraceBuf - 1, "\n\n-----> C= %s SERVER: sd [%d]\n", ( (TraceUser) ? TraceUser : "Null username" ), Server->sd );
		    write( Tracefd, TraceBuf, strlen( TraceBuf ) );
		    write( Tracefd, Server->ReadBuf, status );
		}

		for ( ; ; )
		{
		    BytesSent = send( Client->sd, Server->ReadBuf, status, 0 );
		    if ( BytesSent == -1 )
		    {
			if ( errno == EINTR )
			    continue;
			
			syslog(LOG_ERR, "%s: send() failed sending data to client on sd [%d]: %s", fn, Client->sd, strerror( errno ) );
			return( -1 );
		    } 
		    break;
		}
	    }
	

	    while ( Client->LiteralBytesRemaining )
	    {
		status = IMAP_Literal_Read( Client );
		
		if ( status == -1 )
		{
		    syslog(LOG_NOTICE, "%s: Failed to read string literal from client on socket %d", fn, Client->sd );
		    return( -1 );
		}

		if ( Client->TraceOn )
		{
		    snprintf( TraceBuf, sizeof TraceBuf - 1, "\n\n-----> C= %s CLIENT: sd [%d]\n", ( (TraceUser) ? TraceUser : "Null username" ), Client->sd );
		    write( Tracefd, TraceBuf, strlen( TraceBuf ) );
		    write( Tracefd, Client->ReadBuf, status );
		}
		
		/* send any literal data back to the server */
		for ( ; ; )
		{
		    BytesSent = send( Server->sd, Client->ReadBuf, status, 0 );
		    if ( BytesSent == -1 )
		    {
			if ( errno == EINTR )
			    continue;
			
			syslog(LOG_ERR, "%s: send() failed sending data to client on sd [%d]: %s", fn, Client->sd, strerror( errno ) );
			return( -1 );
		    }
		    break;
		}
		
	    }
	    
	} while ( Client->BytesInReadBuffer > Client->ReadBytesProcessed );
	
    }
    
}



    




/*++
 * Function:	HandleRequest
 *
 * Purpose:	Handle incoming imap requests (as a thread)
 *
 * Parameters:	int, client socket descriptor
 *
 * Returns:	nada
 *
 * Authors:	dgm
 *
 * Notes:	This function actually only handles unauthenticated
 *		traffic from an imap client.  As such it can only make sense
 *		of the following IMAP commands (rfc 2060):  NOOP, CAPABILITY,
 *		AUTHENTICATE, LOGIN, and LOGOUT.  None of these commands should
 *		ever send enough data to fill our buffer.  None of these
 *		commands should ever send a string literal specifier.  For
 *		these reasons, you'll notice that both of these conditions are
 *		checked after our call to IMAP_Line_Read() such that we can
 *		boot any client trying to send us rubbish.  Our behaviour
 *		may not be identical to a "real" IMAP server implementation
 *		in this regard, but a "real" client should never send us
 *		crap like this in the first place.  This is a simple, but
 *              not graceful way to handle the problem.
 *--
 */
extern void HandleRequest( int clientsd )
{
    char *fn = "HandleRequest";
    ITD_Struct Client;
    char *Tag;
    char *Command;
    char *Username;
    char *Password;
    char *Lasts;
    char *EndOfLine;
    char SendBuf[BUFSIZE];
    int BytesRead;
    int rc;
    unsigned int BufLen = BUFSIZE - 1;
    char S_UserName[MAXUSERNAMELEN];
    char S_Tag[MAXTAGLEN];
    char S_Password[MAXPASSWDLEN];
    
    struct pollfd fds[1];
    nfds_t nfds;
    int PollFailCount;
    
    PollFailCount = 0;
    
    /* initialize the client ITD */
    memset( &Client, 0, sizeof( ITD_Struct ) );
    Client.sd = clientsd;


    /* send the banner to the client */
    if ( send( Client.sd, Banner, BannerLen, 0 ) == -1 )
    {
	syslog(LOG_ERR, "%s: send() failed: %s.  Closing client connection.", fn, strerror( errno ) );
	IMAPCount->CurrentClientConnections--;
	close( Client.sd );
	return;
    }
    

    /* set up our poll fd structs */
    nfds = 1;
    
    fds[ 0 ].fd = Client.sd;
    fds[ 0 ].events = POLLIN;
    
    /* start a command loop */
    for ( ; ; )
    {
	fds[ 0 ].revents = 0;
	
	rc = poll( fds, nfds, POLL_TIMEOUT );
	
	if ( !rc )
	{
	    /*
	     * our client timeout was exceeded.  Drop this connection.
	     */
	    syslog(LOG_ERR, "%s: no data received from client for %d minutes.  Closing client connection.", fn, POLL_TIMEOUT_MINUTES );
	    IMAPCount->CurrentClientConnections--;
	    close( Client.sd );
	    return;
	}
	
	if ( rc < 0 )
	{
	    /* If we were interrupted by a signal, just continue the loop. */
	    if ( errno == EINTR )
	    {
		syslog(LOG_INFO, "%s: poll() was interrupted by a signal -- continuing.", fn);
		continue;
	    }
	    
	    
	    /* resource issue -- try again. */
	    if ( errno == EAGAIN )
	    {
		PollFailCount++;
		if ( PollFailCount == 5 )
		{
		    syslog(LOG_ERR, "%s: poll() returned EAGAIN.  Exceeded retry limit.  Closing client connection.", fn );
		    IMAPCount->CurrentClientConnections--;
		    close( Client.sd );
		    return;
		}
		
		syslog(LOG_WARNING, "%s: poll() returned EAGAIN.  Retrying.", fn );
		sleep(5);
		continue;
	    }
	    
	    /* anything else, we're really jacked about it. */
	    syslog(LOG_ERR, "%s: poll() failed: %s -- Closing connection.", fn, strerror( errno ) );
	    IMAPCount->CurrentClientConnections--;
	    close( Client.sd );
	    return;
	}
	
	PollFailCount = 0;

	BytesRead = IMAP_Line_Read( &Client );
	
	if ( Client.MoreData || Client.LiteralBytesRemaining )
	{
	    syslog(LOG_WARNING, "%s: Received junk from unauthenticated client.  Disconnecting.", fn );
	    IMAPCount->CurrentClientConnections--;
	    close( Client.sd );
	    return;
	}
	
	if ( BytesRead == -1 )
	{
	    IMAPCount->CurrentClientConnections--;
	    close( Client.sd );
	    return;
	}
	
	
    	/* First grab the tag */
	
	EndOfLine = Client.ReadBuf + BytesRead;
	
	Tag = memtok( Client.ReadBuf, EndOfLine, &Lasts );
	if ( ( !Tag ) ||
	     ( !imparse_isatom( Tag ) ) ||
	     ( Tag[0] == '*' && !Tag[1] ) )
	{
	    snprintf( SendBuf, BufLen, "* BAD Invalid tag\r\n" );
	    if ( send( Client.sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	    {
		IMAPCount->CurrentClientConnections--;
		close( Client.sd );
		return;
	    }
	    continue;
	}
	

	Command = memtok( NULL, EndOfLine, &Lasts );
	if ( !Command )
	{
	    /* Tag with no command */
	    snprintf( SendBuf, BufLen, "%s BAD Null command\r\n", Tag );
	    if ( send( Client.sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	    {
		IMAPCount->CurrentClientConnections--;
		close( Client.sd );
		return;
	    }
	    continue;
	}
	
	/*
	 * We should have a valid tag and command now.  React as
	 * appropriate...
	 */
	strncpy( S_Tag, Tag, MAXTAGLEN - 1 );
	if ( ! strcasecmp( (const char *)Command, "NOOP" ) )
	{
	    cmd_noop( &Client, S_Tag );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "CAPABILITY" ) )
	{
	    cmd_capability( &Client, S_Tag );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "AUTHENTICATE" ) )
	{
	    cmd_authenticate( &Client, S_Tag );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "LOGOUT" ) )
	{
	    cmd_logout( &Client, S_Tag );
	    IMAPCount->CurrentClientConnections--;
	    close( Client.sd );
	    return;
	}
	else if ( ! strcasecmp( (const char *)Command, "P_TRACE" ) )
	{
	    Username = memtok( NULL, EndOfLine, &Lasts );
	    cmd_trace( &Client, S_Tag, Username );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "P_DUMPICC" ) )
	{
	    cmd_dumpicc( &Client, S_Tag );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "P_RESETCOUNTERS" ) )
	{
	    cmd_resetcounters( &Client, S_Tag );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "P_NEWLOG" ) )
	{
	    cmd_newlog( &Client, S_Tag );
	    continue;
	}
	else if ( ! strcasecmp( (const char *)Command, "LOGIN" ) )
	{
	    /*
	     * Got a LOGIN command.  validate that we got all four required
	     * tokens (Tag, Command, Username, Password) before we waste
	     * a connection to the IMAP server.
	     */
	    Username = memtok( NULL, EndOfLine, &Lasts );
	    if ( !Username )
	    {
		/* no username -- complain back to the client */
		snprintf( SendBuf, BufLen, "%s BAD Missing required argument to Login\r\n", Tag );
		if ( send( Client.sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
		{
		    IMAPCount->CurrentClientConnections--;
		    close( Client.sd );
		    return;
		}
		continue;
	    }
	    
	    Password = memtok( NULL, EndOfLine, &Lasts );
	    if ( !Password )
	    {
		/* no password -- complain back to the client */
		snprintf( SendBuf, BufLen, "%s BAD Missing required argument to Login\r\n", Tag );
		if ( send( Client.sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
		{
		    IMAPCount->CurrentClientConnections--;
		    close( Client.sd );
		    return;
		}
		continue;
	    }
	    
	    /* 
	     * All looks well at this point.  We're almost ready to call our
	     * login handler.  It's key to note, however that the pointer to
	     * our Username is pointing to storage allocated in our client
	     * read buffer.  That's storage that's likely to be wiped out as
	     * soon as we read more data from the client.  Since we'll need to
	     * keep track of the username, make a static copy of it first and
	     * then pass a pointer to the static copy into our login handler.
	     * Do the same for the password...
	     */
	    strncpy( S_UserName, Username, sizeof S_UserName - 1 );
	    strncpy( S_Password, Password, sizeof S_Password - 1 );
	    
	    /*
	     * wipe out the the client read buffer since a copy of the
	     * password lives in there.
	     */
	    memset( &Client.ReadBuf, 0, sizeof Client.ReadBuf );
	    
	    rc = cmd_login( &Client, S_UserName, S_Password, sizeof S_Password, S_Tag );
	    
	    if ( rc == 0)
		continue;
	    
	    if ( rc == 1)
	    {
		/*
		 * We caught a LOGOUT from the client.  Respond with
		 * a successful logout back to the client.
		 */
		Tag = memtok( Client.ReadBuf, EndOfLine, &Lasts );
		if ( Tag )
		{
		    strncpy( S_Tag, Tag, MAXTAGLEN - 1 );
		    cmd_logout( &Client, S_Tag );
		}
	    }
	    
	    /* 
	     * close the client side socket.
	     */
	    IMAPCount->CurrentClientConnections--;
	    close( Client.sd );
	    return;
	    
	}
	else
	{
	    /*
	     * We got a command that we don't understand.  Treat this the
	     * same way the cyrus implementation does -- tell the client to
	     * log in first.
	     */
	    snprintf( SendBuf, BufLen, "%s BAD Please login first\r\n", Tag, Command );
	    if ( send( Client.sd, SendBuf, strlen(SendBuf), 0 ) == -1 )
	    {
		IMAPCount->CurrentClientConnections--;
		close( Client.sd );
		return;
	    }
	    continue;
	    
	}
	
	
    }  /* End of infinite for loop */
    
    
    /* should never reach this code */
    IMAPCount->CurrentClientConnections--;
    close( Client.sd );
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
