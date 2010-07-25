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
**	main.c
**
**  Abstract:
**
**	The main source module for the IMAP proxy server.  This source
**	module contains routines to handle server initialization
**	and the main server loop.
**
**  Authors:
**
**	Dave McMurtrie (dgm@pitt.edu)
**
**  RCS:
**
**	$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/main.c,v $
**	$Id: main.c,v 1.9 2003/04/16 12:15:52 dgm Exp $
**      
**  Modification History:
**
**	$Log: main.c,v $
**	Revision 1.9  2003/04/16 12:15:52  dgm
**	Added support for syslog configuration.
**	Removed a few ifdef LINUXs by always storing tcp service port as
**	network short.
**
**	Revision 1.8  2003/02/20 12:55:03  dgm
**	SetBannerAndCapability() now checks to see if the server supports
**	UNSELECT and sets a flag in the global proxy config struct.
**
**	Revision 1.7  2003/02/19 13:01:40  dgm
**	Changes to SetBannerAndCapability() to strip out unsupported AUTH=
**	mechanisms from the capability string.
**
**	Revision 1.6  2003/01/27 13:58:18  dgm
**	patch by Frode Nordahl <frode@powertech.no> to allow
**	compilation on Linux platforms.
**
**	Revision 1.5  2002/12/17 14:25:14  dgm
**	Added support for global configuration structure.
**	Modified supported command line arguments.
**	Minor bugfixes from Gary Mills incorporated.
**
**	Revision 1.4  2002/09/06 13:33:05  dgm
**	Added code to ignore SIGPIPE and SIGHUP.
**
**	Revision 1.3  2002/08/30 17:08:35  dgm
**	oops.  forgot to initialize the trace mutex...
**
**	Revision 1.2  2002/08/28 15:56:24  dgm
**	replaced all internal logging calls with standard syslog calls.
**	Changed call to setrlimit such that it's now done dynamically,
**	based on the max number of connections we want to allow.
**
**	Revision 1.1  2002/07/03 12:07:51  dgm
**	Initial revision
**
**
*/


static char *rcsId = "$Id: main.c,v 1.9 2003/04/16 12:15:52 dgm Exp $";
static char *rcsSource = "$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/main.c,v $";
static char *rcsAuthor = "$Author: dgm $";

#define _REENTRANT

#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/socket.h>
#include <sys/uio.h>
#include <string.h>
#include <errno.h>
#include <netdb.h>
#include "common.h"
#include "imapproxy.h"
#include <pthread.h>
#include <sys/resource.h>
#include <sys/mman.h>
#include <pwd.h>
#include <syslog.h>
#include <signal.h>

#ifdef LINUX
#include <sys/param.h>
#endif


/*
 * Global variables.  Many of these things are global just as an optimization.
 * For example, there's no reason to have to do a hostname lookup every
 * single time we want to connect to the imap server.  We do it once and 
 * store it globally.
 */
char Banner[BUFSIZE];                /* banner line returned from IMAP svr */
unsigned int BannerLen;
char Capability[BUFSIZE];            /* IMAP capability line from server */
unsigned int CapabilityLen;
ISD_Struct ISD;                      /* global imap server descriptor */
ICC_Struct *ICC_free;                /* ICC free listhead */
ICC_Struct *ICC_HashTable[ HASH_TABLE_SIZE ];
IMAPCounter_Struct *IMAPCount;       /* global imap counter struct */
pthread_mutex_t mp;                  /* "main" mutex used for ICC sync */
pthread_mutex_t trace;               /* mutex used for username tracing */
char TraceUser[MAXUSERNAMELEN];      /* username we want to trace */
int Tracefd;                         /* fd of our trace file (always open) */
ProxyConfig_Struct PC_Struct;        /* Global configuration data */

/*
 * Internal Prototypes
 */
static int SetBannerAndCapability( void );
static void ServerInit( void );
static void Usage( void );



int main( int argc, char *argv[] )
{
    char *fn = "main()";
    int listensd;                      /* socket descriptor we'll bind to */
    int clientsd;                      /* incoming socket descriptor */
    int addrlen;                       
    struct sockaddr_in srvaddr;
    struct sockaddr_in cliaddr;
    pthread_t ThreadId;                /* thread id of each incoming conn */
    pthread_t RecycleThread;           /* used just for the recycle thread */
    pthread_attr_t attr;               /* generic thread attribute struct */
    int rc, i, fd;
    pid_t pid;                         /* used just for a fork call */
    struct linger lingerstruct;        /* for the socket reuse stuff */
    int flag;                          /* for the socket reuse stuff */
    ICC_Struct *ICC_tptr;             
    extern char *optarg;
    extern int optind;
    char ConfigFile[ MAXPATHLEN ];     /* path to our config file */

    flag = 1;
    ConfigFile[0] = '\0';

    /*
     * Ignore signals we don't want to die from but we don't care enough
     * about to catch.
     */
    signal( SIGPIPE, SIG_IGN );
    signal( SIGHUP, SIG_IGN );
    

    while (( i = getopt( argc, argv, "f:h" ) ) != EOF )
    {
	switch( i )
	{
	case 'f':
	    /* user specified a config filename */
	    strncpy( ConfigFile, optarg, sizeof ConfigFile -1 );
	    syslog( LOG_INFO, "%s: Using configuration file '%s'",
		    fn, ConfigFile );
	    break;
	    
	case 'h':
	    Usage();
	    exit( 0 );

	case '?':
	    Usage();
	    exit( 1 );
	}
    }


    /* 
     * Make sure we know which config file to use and then set our config
     * options.
     */
    if ( ! ConfigFile[0] )
    {
	strncpy( ConfigFile, DEFAULT_CONFIG_FILE, sizeof ConfigFile -1 );
	syslog( LOG_INFO, "%s: Using default configuration file '%s'.",
		fn, ConfigFile );
    }
    
    SetConfigOptions( ConfigFile );
    SetLogOptions();
    

    /*
     * Initialize some stuff.
     */
    rc = pthread_mutex_init(&mp, NULL);
    if ( rc )
    {
	syslog(LOG_ERR, "%s: pthread_mutex_init() returned error [%d] initializing main mutex.  Exiting.", fn, rc );
	exit( 1 );
    }

    rc = pthread_mutex_init(&trace, NULL);
    if ( rc )
    {
	syslog(LOG_ERR, "%s: pthread_mutex_init() returned error [%d] initializing trace mutex.  Exiting.", fn, rc );
	exit( 1 );
    }

    TraceUser[0] = '\0';
    
    syslog( LOG_INFO, "%s: Allocating %d IMAP connection structures.", 
	    fn, PC_Struct.cache_size );

    ICC_free = malloc( ( sizeof ( ICC_Struct ) ) 
		       * PC_Struct.cache_size );
    
    if ( ! ICC_free )
    {
	syslog(LOG_ERR, "%s: malloc() failed to allocate [%d] IMAPConnectionContext structures: %s", fn, PC_Struct.cache_size, strerror( errno ) );
	exit( 1 );
    }
    
    memset( ICC_free, 0, sizeof ( ICC_Struct ) * PC_Struct.cache_size );
    
    ICC_tptr = ICC_free;

    /*
     * Bug fixed by Gary Mills <mills@cc.UManitoba.CA>.  I was pre-incrementing
     * ICC_tptr and then assigning.  I guess gcc evaluates the expression
     * incorrectly, since I never had a problem with this.  Gary had the
     * problem with cc, so it's fixed here.
     */
    for ( i = 0; i < PC_Struct.cache_size - 1; i++ )
    {
	ICC_tptr->next = ICC_tptr + 1;
	ICC_tptr++;
    }
    
    memset( ICC_HashTable, 0, sizeof ICC_HashTable );

    ServerInit();
    
    if ( SetBannerAndCapability() )
    {
	syslog(LOG_ERR, "%s: Failed to get banner string and capability from imap server.  Exiting.", fn);
	exit( 1 );
    }
    
    /* detach from our parent if necessary */
    if (! (getppid() == 1) )
    {
	if ( (pid = fork()) < 0)
	{
	    syslog(LOG_ERR, "%s: initial call to fork() failed: %s", fn, strerror(errno));
	    exit( 1 );
	}
	else if ( pid > 0)
	{
	    exit( 0 );
	}
	
	if (setsid() == -1)
	{
	    syslog(LOG_WARNING, "%s: setsid() failed: %s", 
		   fn, strerror(errno));
	}
	if ( (pid = fork()) < 0)
	{
	    syslog(LOG_ERR, "%s: secondary call to fork() failed: %s", fn, 
		   strerror(errno));
	    exit( 1 );
	}
	else if ( pid > 0)
	{
	    exit( 0 );
	}
    }

    memset( (char *) &srvaddr, 0, sizeof srvaddr );
    srvaddr.sin_family = AF_INET;
    srvaddr.sin_addr.s_addr = htonl(INADDR_ANY);

    syslog(LOG_INFO, "%s: Binding to tcp port %d", fn, PC_Struct.listen_port );
    srvaddr.sin_port = htons(PC_Struct.listen_port);

    listensd = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
    if ( listensd == -1 )
    {
	syslog(LOG_ERR, "%s: socket() failed: %s", fn, strerror(errno));
	exit( 1 );
    }

    setsockopt(listensd, SOL_SOCKET, SO_REUSEADDR, (void *)&flag, 
	       sizeof(flag));
    lingerstruct.l_onoff = 1;
    lingerstruct.l_linger = 5;
    setsockopt(listensd, SOL_SOCKET, SO_LINGER, (void *)&lingerstruct, 
	       sizeof(lingerstruct));
   
    if ( bind(listensd, (struct sockaddr *)&srvaddr, sizeof( srvaddr ) ) < 0 )
    {
	syslog(LOG_ERR, "%s: bind() failed: %s", fn, strerror(errno) );
	exit( 1 );
    }

    /*
     * Create and mmap() our stat file while we're still root.  Since it's
     * configurable, we want to make sure we do this as root so there's the
     * greatest possibility that we'll have permission to write where we
     * need to.
     */
    syslog( LOG_INFO, "%s: Using global statistics file '%s'", fn,
	    PC_Struct.stat_filename );
    
    fd = open( PC_Struct.stat_filename, O_RDWR | O_CREAT, S_IREAD | S_IWRITE );
    if ( fd == -1 )
    {
	syslog(LOG_ERR, "%s: open() failed for '%s': %s -- Exiting.", fn, 
	       PC_Struct.stat_filename, strerror( errno ) );
	exit( 1 );
    }
    
    if ( ( ftruncate( fd, sizeof( IMAPCounter_Struct ) ) ) == -1 )
    {
	syslog(LOG_ERR, "%s: ftruncate() failed: %s -- Exiting.", 
	       fn, strerror( errno ) );
	exit( 1 );
    }
    
    IMAPCount = ( IMAPCounter_Struct *)mmap( 0, sizeof( IMAPCounter_Struct ), 
		    PROT_READ | PROT_WRITE, MAP_SHARED, fd, 0 );
    
    if ( IMAPCount == MAP_FAILED )
    {
	syslog(LOG_ERR, "%s: mmap() failed: %s -- Exiting.", 
	       fn, strerror( errno ) );
	exit( 1 );
    }
    
    memset( IMAPCount, 0, sizeof( IMAPCounter_Struct ) );
    IMAPCount->StartTime = time( 0 );
    IMAPCount->CountTime = time( 0 );

    if ( BecomeNonRoot() )
	exit( 1 );

    /* some misc thread setup */
    rc = pthread_attr_init( &attr );
    if ( rc )
    {
	syslog(LOG_ERR, "%s: pthread_attr_init() failed: [%d]\n", fn, rc);
	exit( 1 );
    }
    
    rc = pthread_attr_setdetachstate( &attr, PTHREAD_CREATE_DETACHED );
    if ( rc )
    {
	syslog(LOG_ERR, "%s: pthread_attr_setdetachstate() failed: [%d]\n", 
	       fn, rc);
	exit( 1 );
    }

    /* launch a recycle thread before we loop */
    pthread_create( &RecycleThread, &attr, (void *)ICC_Recycle_Loop, NULL );

    syslog(LOG_INFO, "%s: Launched ICC recycle thread with id %d", 
	   fn, RecycleThread );

    /*
     * Now start listening and accepting connections.
     */
    if ( listen(listensd, MAX_CONN_BACKLOG) < 0)
    {
	syslog( LOG_ERR, "%s: listen() failed: %s -- Exiting", 
	       fn, strerror(errno));
	exit( 1 );
    }

    syslog( LOG_INFO, "%s: Normal server startup.", fn );

    /*
     * Main server loop
     */
    for ( ;; )
    {
	/*
	 * Bug fixed by Gary Mills <mills@cc.UManitoba.CA>.  I forgot
	 * to initialize addrlen.
	 */
	addrlen = sizeof cliaddr;
	clientsd = accept( listensd, (struct sockaddr *)&cliaddr, &addrlen );
	if ( clientsd == -1 )
	{
	    syslog(LOG_WARNING, "%s: accept() failed: %s -- retrying", 
		   fn, strerror(errno));
	    sleep( 1 );
	    continue;
	}

	IMAPCount->TotalClientConnectionsAccepted++;
	IMAPCount->CurrentClientConnections++;
	
	if ( IMAPCount->CurrentClientConnections > 
	     IMAPCount->PeakClientConnections )
	    IMAPCount->PeakClientConnections = IMAPCount->CurrentClientConnections;
	
	pthread_create( &ThreadId, &attr, (void *)HandleRequest, (void *)clientsd );
	
    }
}

	
    
    
	
/*
 * Function definitions.
 */



/*++
 * Function:	Usage
 *
 * Purpose:	Display a usage string to stdout
 *
 * Parameters:	None.
 *
 * Returns:	nada
 * 
 * Authors:	dgm
 * 
 * Notes:
 *--
 */
void Usage( void )
{
    printf("Usage: %s [-f config filename] [-h]\n", PGM );
    return;
}



/*++
 * Function:	ServerInit
 *
 * Purpose:	Initialize some stuff.  Set some global variables.
 *
 * Parameters:	none
 *
 * Returns:	nada -- exits on error
 * 
 * Authors:	dgm
 * 
 * Notes:       relies on global copy of ProxyConfig_Struct "PC_Struct"
 *--
 */
static void ServerInit( void ) 
{
    char *fn = "ServerInit()";
    struct hostent *hp;
    struct servent *sp;
    struct rlimit rl;
    int rc;
    struct passwd *pw;

    
    /* open the global trace file and make proc_username own it */
    syslog( LOG_INFO, "%s: Using '%s' for global protocol logging file.",
	    fn, PC_Struct.protocol_log_filename );
    
    Tracefd = open( PC_Struct.protocol_log_filename,
		    O_RDWR | O_CREAT | O_TRUNC, 0600 );
    
    if ( Tracefd == -1 )
    {
	syslog(LOG_ERR, "%s: open() failed for '%s': %s -- Exiting.", fn,
	       PC_Struct.protocol_log_filename, strerror( errno ) );
	exit( 1 );
    }

    if ( ( pw = getpwnam( PC_Struct.proc_username ) ) == NULL )
    {
	syslog(LOG_ERR, "%s: getpwnam() failed for user '%s' -- Exiting.", fn, 
	       PC_Struct.proc_username );
	exit( 1 );
    }
    
    rc = chown( PC_Struct.protocol_log_filename, pw->pw_uid, pw->pw_gid );
    
    if ( rc )
    {
	syslog(LOG_ERR, "%s: Failed to set ownership of file '%s' to '%s': %s -- Exiting.", fn, PC_Struct.protocol_log_filename, PC_Struct.proc_username, strerror( errno ) );
	exit( 1 );
    }
    
    /* 
     * increase the number of open file descriptors we're allowed.  Base
     * This number on the number of simultaneous connections we allow.
     * Also allow stdin, stdout, stderr and a few misc pipes and doors.
     */
    rl.rlim_cur = ( PC_Struct.cache_size * 2 ) + 10;
    rl.rlim_max = ( PC_Struct.cache_size * 2 ) + 10;

    rc = setrlimit( RLIMIT_NOFILE, &rl );

    if ( rc )
    {
	syslog(LOG_ERR, "%s: setrlimit() failed to set max number of open file descriptors to %d: %s", fn, ( PC_Struct.cache_size * 2 + 10), strerror( errno ) );
	exit(1);
    }
    
    
    /* grab a host entry for the imap server. */
    syslog( LOG_INFO, "%s: proxying to IMAP server '%s'.", fn, 
	    PC_Struct.server_hostname );
    
    hp = gethostbyname( PC_Struct.server_hostname );
    
    if ( !hp )
    {
	syslog(LOG_ERR, "%s: gethostbyname() failed to resolve hostname of remote IMAP server: %s", fn, strerror(errno) );
	exit(1);
    }

    memcpy( &ISD.host, hp, sizeof(struct hostent) );
    
    syslog(LOG_INFO, "%s: Proxying to IMAP port %d", 
	   fn, PC_Struct.server_port );
    ISD.serv.s_port = htons(PC_Struct.server_port);
        
    /* 
     * fill in the address family, the host address, and the
     * service port of our global socket address structure
     */
    ISD.srv.sin_family = AF_INET;
    memcpy( &ISD.srv.sin_addr.s_addr, ISD.host.h_addr, ISD.host.h_length );
    ISD.srv.sin_port = ISD.serv.s_port;
}





/*++
 * Function:	SetBannerAndCapability
 *
 * Purpose:	Connect to an IMAP server as a client and fetch the initial
 *		banner string and the output from a CAPABILITY command.
 *
 * Parameters:	none
 *
 * Returns:	0 on success
 *              -1 on failure
 *
 * Authors:	dgm
 *
 * Notes:       All AUTH mechanisms will be stripped from the capability
 *              string.  AUTH=LOGIN will be added.
 *              The support_unselect flag in the global copy of the
 *              ProxyConfig struct will be set in this function depending on
 *              whether the server supports UNSELECT or not.
 *--
 */
static int SetBannerAndCapability( void )
{
    int sd;
    ITD_Struct itd;
    int BytesRead;
    char *fn = "SetBannerAndCapability()";
    char *CP;

    /* initialize some stuff */
    memset( &itd, 0, sizeof itd );

    sd = socket( AF_INET, SOCK_STREAM, IPPROTO_TCP );
    if ( sd == -1 )
    {
	syslog(LOG_ERR, "%s: socket() failed: %s", fn, strerror(errno) );
	return( -1 );
    }

    if ( connect( sd, (struct sockaddr *)&ISD.srv, sizeof(ISD.srv) ) == -1 )
    {
	syslog(LOG_ERR, "%s: connect() to imap server on socket [%d] failed: %s", fn, sd, strerror(errno));
	close( sd );
	return(-1);
    }
    
    itd.sd = sd;
    
    /*
     * The first thing we get back from the server should be the
     * banner string.
     */
    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	close( itd.sd );
	return( -1 );
    }
    
    
    if ( sizeof Banner < BytesRead )
    {
	syslog(LOG_ERR, "%s: Storing %d byte banner string from IMAP server would cause buffer overflow.", fn, BytesRead );
	close( itd.sd );
	return( -1 );
    }
    
    memcpy( Banner, itd.ReadBuf, BytesRead );
    BannerLen = BytesRead;
	
	      
    /*
     * See if the string we got back starts with "* OK" by comparing the
     * first 4 characters of the buffer.
     */
    if ( strncasecmp( Banner, IMAP_UNTAGGED_OK, strlen(IMAP_UNTAGGED_OK)) )
    {
	syslog(LOG_ERR, "%s: Unexpected response from imap server on initial connection: %s", fn, Banner);
	close( itd.sd );
	return( -1 );
    }


    /* Now we send a CAPABILITY command to the server. */
    if ( send( sd, "1 CAPABILITY\r\n", strlen("1 CAPABILITY\r\n"), 0 ) == -1 )
    {
	syslog(LOG_ERR, "%s: send() failed: %s", fn, strerror(errno) );
	close( itd.sd );
	return( -1 );
    }
    
    /*
     * From RFC2060:
     * The server MUST send a single untagged
     * CAPABILITY response with "IMAP4rev1" as one of the listed
     * capabilities before the (tagged) OK response.
     *
     * The means we should read exactly 2 lines of data back from the server.
     * The first will be the untagged capability line.
     * The second will be the OK response with the tag in it.
     */

    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	close( itd.sd );
	return( -1 );
    }
    
    /*
     * The read buffer should now contain the 
     * untagged response line.  
     */
    if ( sizeof Capability < BytesRead )
    {
	syslog(LOG_ERR, "%s: Storing %d byte capability string from IMAP server would cause buffer overflow.", fn, BytesRead );
	close( itd.sd );
	return( -1 );
    }

    /*
     * strip out all of the AUTH mechanisms except the ones that we support.
     * Right now, this is just AUTH=LOGIN.  Note that the use of
     * non-MT safe strtok is okay here.  This function is called before any
     * other threads are launched and should never be called again.
     */
    itd.ReadBuf[BytesRead - 2] = '\0';
    CP = strtok( itd.ReadBuf, " " );
    
    if ( !CP )
    {
	syslog( LOG_ERR, "%s: No tokens found in capability string sent from IMAP server.", fn);
	close( itd.sd );
	return( -1 );
    }
    
    sprintf( Capability, CP );
    
    /*
     * initially assume that the server doesn't support UNSELECT.
     */
    PC_Struct.support_unselect = UNSELECT_NOT_SUPPORTED;

    for( ; ; )
    {
	CP = strtok( NULL, " " );
	
	if ( !CP )
	    break;

	if ( !strncasecmp( CP, "UNSELECT", strlen( "UNSELECT" ) ) )
	{
	    PC_Struct.support_unselect = UNSELECT_SUPPORTED;
	}
	
	/*
	 * If this token happens to be an auth mechanism, we want to
	 * discard it unless it's an auth mechanism we can support.
	 */
	if ( ! strncasecmp( CP, "AUTH=", strlen( "AUTH=" ) ) &&
	     ( strncasecmp( CP, "AUTH=LOGIN", strlen( "AUTH=LOGIN" ) ) ) )
	    continue;
	
	strcat( Capability, " ");
	strcat( Capability, CP );
    }
    
    strcat( Capability, "\r\n" );
    
    CapabilityLen = strlen( Capability );
    
    /* Now read the tagged response and make sure it's OK */
    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	close( itd.sd );
	return( -1 );
    }
    
    
    if ( strncasecmp( itd.ReadBuf, IMAP_TAGGED_OK, strlen(IMAP_TAGGED_OK) ) )
    {
	syslog(LOG_ERR, "%s: Received non-OK tagged reponse from imap server on CAPABILITY command", fn );
	close( itd.sd );
	return( -1 );
    }
    
    /* Be nice and logout */
    if ( send( sd, "2 LOGOUT\r\n", strlen("2 LOGOUT\r\n"), 0 ) == -1 )
    {
	syslog(LOG_WARNING, "%s: send() failed on LOGOUT: %s -- Returning success anyway.", fn, strerror(errno) );
	close( itd.sd );
	return( 0 );
    }
    
    /* read the final OK logout */
    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	syslog(LOG_WARNING, "%s: IMAP_Line_Read() failed on LOGOUT.  Returning success anyway.", fn );
    }
    
    close( itd.sd );
    return( 0 );
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






