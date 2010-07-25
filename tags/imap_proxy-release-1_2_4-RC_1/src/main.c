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
**	Dave McMurtrie <davemcmurtrie@hotmail.com>
**
**  RCS:
**
**	$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/main.c,v $
**	$Id: main.c,v 1.25 2005/06/15 12:05:25 dgm Exp $
**      
**  Modification History:
**
**	$Log: main.c,v $
**	Revision 1.25  2005/06/15 12:05:25  dgm
**	Included config.h.
**
**	Revision 1.24  2005/06/07 12:08:44  dgm
**	Added missing include directives to avoid implicit declarations.
**
**	Revision 1.23  2005/01/12 17:50:45  dgm
**	Applied patch by David Lancaster to provide force_tls
**	config option.
**
**	Revision 1.22  2005/01/12 17:11:30  dgm
**	Patch by Joseph Tam to prevent SIGSEGV in RAND_egd().
**
**	Revision 1.21  2004/11/10 15:32:02  dgm
**	Explictly NULL terminate all strings that are the result
**	of strncpy.  Also enforce checking of LiteralBytesRemaining
**	after any calls to IMAP_Line_Read.
**
**	Revision 1.20  2004/10/11 18:23:19  dgm
**	Added foreground_mode option.
**
**	Revision 1.19  2004/02/24 15:17:20  dgm
**	Added ParseBannerAndCapability() function to allow for
**	parsing the banner string and capability strings.  Can
**	now handle capability string in explicit capability
**	response, or as part of the banner string.
**
**	Added SELECT caching stuff.
**
**	Revision 1.18  2003/11/14 14:59:44  dgm
**	Applied patches by Geoffrey Hort <g.hort@unsw.edu.au> to allow
**	configurable listen address.  Discard token "SASL-IR" if server
**	advertises it as a capability (previously, we were discarding
**	"SASL").  Reference Revision 1.17 of this source module.
**
**	Revision 1.17  2003/10/10 15:07:02  dgm
**	Patch by Ken Murchison <ken@oceana.com> applied.  Discard "SASL"
**	capability if advertised by the server.  This is a new extension
**	that will be supported in Cyrus v2.2.2.
**
**	Revision 1.16  2003/10/09 13:03:52  dgm
**	Added configurable tcp keepalive support.  Added retry logic for
**	initial socket connection to imap server in SetBannerAndCapability()
**	so it won't just fatally exit on ECONNREFUSED (submitted by Gary
**	Mills <mills@cc.UManitoba.CA>).
**
**	Revision 1.15  2003/07/14 16:39:58  dgm
**	Applied patch by William Yodlowsky <wyodlows@andromeda.rutgers.edu>
**	to allow TLS to work on machines without /dev/random.
**
**	Applied patch by Gary Windham <windhamg@email.arizona.edu> to add
**	tcp wrappers support.
**
**	Revision 1.14  2003/05/20 19:04:23  dgm
**	Comment changes only.
**
**	Revision 1.13  2003/05/15 11:34:34  dgm
**	Patch by Ken Murchison <ken@oceana.com> to clean up build process:
**	Conditionally include sys/param.h instead of defining MAXPATHLEN.
**
**	Revision 1.12  2003/05/13 14:19:00  dgm
**	Changed all uses of AF_INET constant to PF_INET.
**
**	Revision 1.11  2003/05/13 11:40:16  dgm
**	Patches by Ken Murchison <ken@oceana.com> to clean up build process.
**
**	Revision 1.10  2003/05/06 12:12:21  dgm
**	Applied patches by Ken Murchison <ken@oceana.com> to include SSL
**	support.
**
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


static char *rcsId = "$Id: main.c,v 1.25 2005/06/15 12:05:25 dgm Exp $";
static char *rcsSource = "$Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/main.c,v $";
static char *rcsAuthor = "$Author: dgm $";

#define _REENTRANT

#include <config.h>

#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#if HAVE_UNISTD_H
#include <unistd.h>
#endif
#include <fcntl.h>
#include <sys/socket.h>
#include <sys/uio.h>
#include <string.h>
#include <errno.h>
#include <netdb.h>
#include <pthread.h>
#include <sys/resource.h>
#include <sys/mman.h>
#include <pwd.h>
#include <syslog.h>
#include <signal.h>
#include <openssl/rand.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#if HAVE_SYS_PARAM_H
#include <sys/param.h>
#endif

#ifdef HAVE_LIBWRAP
#include <tcpd.h>
#endif

#include "common.h"
#include "imapproxy.h"


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

#if HAVE_LIBSSL
SSL_CTX *tls_ctx;
static int verify_depth = 5;
static int verify_error = X509_V_OK;

static int verify_callback( int, X509_STORE_CTX *);
static int set_cert_stuff( SSL_CTX *, const char *, const char * );
#endif

#ifdef HAVE_LIBWRAP
int allow_severity = LOG_DEBUG;
int deny_severity = LOG_ERR;
char *service;
#endif

/*
 * Internal Prototypes
 */
static void SetBannerAndCapability( void );
static int ParseBannerAndCapability( char *, unsigned int,
				      char *, unsigned int );
static void ServerInit( void );
static void Usage( void );



int main( int argc, char *argv[] )
{
    char *fn = "main()";
    char f_randfile[ PATH_MAX ];
    int listensd;                      /* socket descriptor we'll bind to */
    int clientsd;                      /* incoming socket descriptor */
    int addrlen;                       
    struct sockaddr_in srvaddr;
    struct sockaddr_in cliaddr;
    pthread_t ThreadId;                /* thread id of each incoming conn */
    pthread_t RecycleThread;           /* used just for the recycle thread */
    pthread_attr_t attr;               /* generic thread attribute struct */
    int rc, i, fd;
    unsigned int ui;
    pid_t pid;                         /* used just for a fork call */
    struct linger lingerstruct;        /* for the socket reuse stuff */
    int flag;                          /* for the socket reuse stuff */
    ICC_Struct *ICC_tptr;             
    extern char *optarg;
    extern int optind;
    char ConfigFile[ MAXPATHLEN ];     /* path to our config file */
#ifdef HAVE_LIBWRAP
    struct request_info r;             /* request struct for libwrap */
#endif

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
	    ConfigFile[ sizeof ConfigFile - 1 ] = '\0';
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
	ConfigFile[ sizeof ConfigFile - 1 ] = '\0';
	syslog( LOG_INFO, "%s: Using default configuration file '%s'.",
		fn, ConfigFile );
    }
    
    SetConfigOptions( ConfigFile );
    SetLogOptions();

    /*
     * Just for logging purposes, are we doing SELECT caching or not?
     */
    if ( PC_Struct.enable_select_cache )
	syslog( LOG_INFO, "%s: SELECT caching is enabled", fn );
    else
	syslog( LOG_INFO, "%s: SELECT caching is disabled", fn );
	
#ifdef HAVE_LIBWRAP
    /*
     * Set our tcpd service name
     */
    if (service = strrchr(argv[0], '/'))
	    service++;
    else
	    service = argv[0];
#endif

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

    ICC_free = (ICC_Struct *)malloc( ( sizeof ( ICC_Struct ) ) 
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
    for ( ui = 0; ui < PC_Struct.cache_size - 1; ui++ )
    {
	ICC_tptr->next = ICC_tptr + 1;
	ICC_tptr++;
    }
    
    memset( ICC_HashTable, 0, sizeof ICC_HashTable );

    ServerInit();

    /* detach from our parent if necessary */
    if (! (getppid() == 1) && ( ! PC_Struct.foreground_mode ) )
    {
	syslog( LOG_INFO, "%s: Configured to run in background mode.", fn );
	
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
    else
    {
	syslog( LOG_INFO, "%s: Configured to run in foreground mode.", fn );
    }
    

    SetBannerAndCapability();
    
    if ( PC_Struct.login_disabled || PC_Struct.force_tls )
    {
	syslog( LOG_INFO, "%s: Enabling STARTTLS.", fn );
#if HAVE_LIBSSL
	if ( PC_Struct.support_starttls )
	{
	    /* Initialize SSL_CTX */
	    SSL_library_init();
	    
            /* Need to seed PRNG, too! */
            if ( RAND_egd( ( RAND_file_name( f_randfile, sizeof( f_randfile ) ) == f_randfile ) ? f_randfile : "/.rnd" ) ) 
	    {
                /* Not an EGD, so read and write it. */
                if ( RAND_load_file( f_randfile, -1 ) )
                    RAND_write_file( f_randfile );
            }
	
	    SSL_load_error_strings();
	    tls_ctx = SSL_CTX_new( TLSv1_client_method() );
	    if ( tls_ctx == NULL )
	    {
		syslog(LOG_ERR, "%s: Failed to create new SSL_CTX.  Exiting.", fn);
		exit( 1 );
	    }

	    /* Work around all known bugs */
	    SSL_CTX_set_options( tls_ctx, SSL_OP_ALL );

	    if ( ! SSL_CTX_load_verify_locations( tls_ctx,
						  PC_Struct.tls_ca_file,
						  PC_Struct.tls_ca_path ) ||
		 ! SSL_CTX_set_default_verify_paths( tls_ctx ) )
	    {
		syslog(LOG_ERR, "%s: Failed to load CA data.  Exiting.", fn);
		exit( 1 );
	    }

	    if ( ! set_cert_stuff( tls_ctx,
				   PC_Struct.tls_cert_file,
				   PC_Struct.tls_key_file ) )
	    {
		syslog(LOG_ERR, "%s: Failed to load cert/key data.  Exiting.", fn);
		exit( 1 );
	    }

	    SSL_CTX_set_verify(tls_ctx, SSL_VERIFY_NONE, verify_callback);
	}
	else
#endif /* HAVE_LIBSSL */
	{
	    /* We're screwed!  We won't be able to login without SASL */
	    syslog(LOG_ERR, "%s: IMAP server has LOGINDISABLED and we can't do STARTTLS.  Exiting.", fn);
	    exit( 1 );
	}
    }
    
    memset( (char *) &srvaddr, 0, sizeof srvaddr );
    srvaddr.sin_family = PF_INET;
    if ( !PC_Struct.listen_addr )
    {
	srvaddr.sin_addr.s_addr = htonl(INADDR_ANY);
    }
    else 
    {
	srvaddr.sin_addr.s_addr = inet_addr( PC_Struct.listen_addr ); 
	if ( srvaddr.sin_addr.s_addr  == -1 )
	{
	    syslog( LOG_ERR, "%s: bad bind address: '%s' specified in config file.  Exiting.", fn, PC_Struct.listen_addr );
	    exit( 1 );
	}
    }
    

    syslog(LOG_INFO, "%s: Binding to tcp %s:%d", fn, PC_Struct.listen_addr ?
	   PC_Struct.listen_addr : "*", PC_Struct.listen_port );
    srvaddr.sin_port = htons(PC_Struct.listen_port);

    listensd = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
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

    if ( PC_Struct.send_tcp_keepalives )
    {
	lingerstruct.l_onoff = 1;
	syslog( LOG_INFO, "%s: Enabling SO_KEEPALIVE.", fn );
	setsockopt( listensd, SOL_SOCKET, SO_KEEPALIVE, (void *)&lingerstruct.l_onoff, sizeof lingerstruct.l_onoff );
    }

	    
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

#ifdef HAVE_LIBWRAP
	request_init(&r, RQ_DAEMON, service, 0);
	request_set(&r, RQ_FILE, clientsd, 0);
	sock_host(&r);
	if (!hosts_access(&r))
	{
	    shutdown(clientsd, SHUT_RDWR);
	    close(clientsd);
	    syslog(deny_severity, "refused connection from %s", eval_client(&r));
	    continue;
	}
#endif

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
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
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
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 * 
 * Notes:       relies on global copy of ProxyConfig_Struct "PC_Struct"
 *--
 */
static void ServerInit( void ) 
{
    char *fn = "ServerInit()";
    struct hostent *hp;
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
    ISD.srv.sin_family = PF_INET;
    memcpy( &ISD.srv.sin_addr.s_addr, ISD.host.h_addr, ISD.host.h_length );
    ISD.srv.sin_port = ISD.serv.s_port;
}




/*++
 * Function:	ParseBannerAndCapability
 *
 * Purpose:	Weed out stuff that imapproxy does not support from a banner
 *              line or a capability line.
 *
 * Parameters:	char * - Buffer for storing the cleaned up string.
 *              unsigned int - buflen
 *              char * - ptr to the string that needs to be cleaned up.
 *              unsigned int - buflen
 *
 * Returns:	Number of bytes in the output string.
 *              exit()s on failure.
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *
 * Notes:       The buffer that the third argument points to will be tokenized
 *              so make a copy of it first if you care about that.
 *--
 */
static int ParseBannerAndCapability( char *DestBuf,
				      unsigned int DestBufSize,
				      char *SourceBuf,
				      unsigned int SourceBufSize )
{
    char *fn = "ParseBannerAndCapability";
    char *CP;
    
    if ( SourceBufSize >= DestBufSize )
    {
	syslog( LOG_ERR, "%s: Unable to parse banner or capability because it would cause a buffer overflow -- exiting.", fn );
	exit( 1 );
    }
    
    
    /*
     * strip out all of the AUTH mechanisms except the ones that we support.
     * Right now, this is just AUTH=LOGIN.  Note that the use of
     * non-MT safe strtok is okay here.  This function is called before any
     * other threads are launched and should never be called again.
     */
    SourceBuf[SourceBufSize - 2] = '\0';
    CP = strtok( SourceBuf, " " );
    
    if ( !CP )
    {
	syslog( LOG_ERR, "%s: No tokens found in banner or capability from IMAP server -- exiting.", fn);
	exit( 1 );
    }
    
    sprintf( DestBuf, CP );
    
    /*
     * initially assume that the server doesn't support UNSELECT.
     */
    PC_Struct.support_unselect = UNSELECT_NOT_SUPPORTED;

    /*
     * initially assume that the server doesn't support STARTTLS.
     */
    PC_Struct.support_starttls = STARTTLS_NOT_SUPPORTED;

    /*
     * initially assume that the server doesn't disable LOGIN.
     */
    PC_Struct.login_disabled = LOGIN_NOT_DISABLED;

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
	{
	    continue;
	}
	
	/*
	 * If this token happens to be SASL-IR, we want to discard it
	 * since we don't support any auth mechs that can use it.
	 */
	if ( !strncasecmp( CP, "SASL-IR", strlen( "SASL-IR" ) ) )
	{
	    continue;
	}

	/*
	 * If this token happens to be STARTTLS, we want to discard it
	 * since we don't support it on the client-side.
	 */
	if ( ! strncasecmp( CP, "STARTTLS", strlen( "STARTTLS" ) ) )
	{
	    PC_Struct.support_starttls = STARTTLS_SUPPORTED;
	    continue;
	}
	
	/*
	 * If this token happens to be LOGINDISABLED, we want to discard it
	 * since we don't support it on the client-side.
	 */
	if ( ! strncasecmp( CP, "LOGINDISABLED", strlen( "LOGINDISABLED" ) ) )
	{
	    PC_Struct.login_disabled = LOGIN_DISABLED;
	    continue;
	}
	
	strcat( DestBuf, " ");
	strcat( DestBuf, CP );
    }
    
    
    strcat( DestBuf, "\r\n" );
    
    return( strlen( DestBuf ) );
}


/*++
 * Function:	SetBannerAndCapability
 *
 * Purpose:	Connect to an IMAP server as a client and fetch the initial
 *		banner string and the output from a CAPABILITY command.
 *
 * Parameters:	none
 *
 * Returns:	nuttin -- exits if there's a problem
 *
 * Authors:	Dave McMurtrie <davemcmurtrie@hotmail.com>
 *
 * Notes:       All AUTH mechanisms will be stripped from the capability
 *              string.  AUTH=LOGIN will be added.
 *              The support_unselect flag in the global copy of the
 *              ProxyConfig struct will be set in this function depending on
 *              whether the server supports UNSELECT or not.
 *--
 */
static void SetBannerAndCapability( void )
{
    int sd;
    ITD_Struct itd;
    ICD_Struct conn;
    int BytesRead;
    char *fn = "SetBannerAndCapability()";
    int NumRef = 0;

    /* initialize some stuff */
    memset( &itd, 0, sizeof itd );

    for ( ;; )
    {
	sd = socket( PF_INET, SOCK_STREAM, IPPROTO_TCP );
	if ( sd == -1 )
	{
	    syslog(LOG_ERR, "%s: socket() failed: %s -- exiting", fn, 
		   strerror(errno) );
	    exit( 1 );
	}
	
	if ( connect( sd, (struct sockaddr *)&ISD.srv, sizeof(ISD.srv) ) == -1 )
	{
	    syslog(LOG_ERR, "%s: connect() to imap server on socket [%d] failed: %s", fn, sd, strerror(errno));
	    close( sd );
	    
	    if ( errno == ECONNREFUSED && ++NumRef < 10 )
	    {
		sleep( 60 );    /* IMAP server may not be started yet. */
		continue;
	    }
	    syslog( LOG_ERR, "%s: unable to connect() to imap server and retry limit exceeded -- exiting.", fn );
	    exit( 1 );
	}
	break;  /* Success */
    }

    
    memset( &conn, 0, sizeof ( ICD_Struct ) );
    itd.conn = &conn;
    itd.conn->sd = sd;
    
    /*
     * The first thing we get back from the server should be the
     * banner string.
     */
    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	syslog( LOG_ERR, "%s: Error reading banner line from server on initial connection: %s -- Exiting.", fn, strerror( errno ) );
	close( itd.conn->sd );
	exit( 1 );
    }

    if ( itd.LiteralBytesRemaining )
    {
	syslog( LOG_ERR, "%s: Server sent unexpected literal specifier in banner response -- Exiting.", fn );
	exit( 1 );
    }
    

    BannerLen = ParseBannerAndCapability( Banner, sizeof Banner - 1,
					  itd.ReadBuf, BytesRead );
    
    /*
     * See if the string we got back starts with "* OK" by comparing the
     * first 4 characters of the buffer.
     */
    if ( strncasecmp( Banner, IMAP_UNTAGGED_OK, strlen(IMAP_UNTAGGED_OK)) )
    {
	syslog(LOG_ERR, "%s: Unexpected response from imap server on initial connection: %s -- Exiting.", fn, Banner);
	close( itd.conn->sd );
	exit( 1 );
    }


    /* Now we send a CAPABILITY command to the server. */
    if ( IMAP_Write( itd.conn, "1 CAPABILITY\r\n", strlen("1 CAPABILITY\r\n") ) == -1 )
    {
	syslog(LOG_ERR, "%s: Unable to send capability command to server: %s -- exiting.", fn, strerror(errno) );
	close( itd.conn->sd );
	exit( 1 );
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
	syslog( LOG_ERR, "%s: Failed to read capability response from server: %s --  exiting.", fn, strerror( errno ) );
	close( itd.conn->sd );
	exit( 1 );
    }

    if ( itd.LiteralBytesRemaining )
    {
	syslog( LOG_ERR, "%s: Server sent unexpected literal specifier in CAPABILITY response -- Exiting.", fn );
	close( itd.conn->sd );
	exit ( 1 );
	
    }
    
    CapabilityLen = ParseBannerAndCapability( Capability, sizeof Capability - 1,
					      itd.ReadBuf, BytesRead );
    
    
    /* Now read the tagged response and make sure it's OK */
    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	syslog( LOG_ERR, "%s: Failed to read capability response from server: %s -- exiting.", fn, strerror( errno ) );
	close( itd.conn->sd );
	exit( 1 );
    }

    if ( itd.LiteralBytesRemaining )
    {
	syslog( LOG_ERR, "%s: Server sent unexpected literal specifier in tagged CAPABILITY response -- exiting.", fn );
	exit( 1 );
    }
    
    if ( strncasecmp( itd.ReadBuf, IMAP_TAGGED_OK, strlen(IMAP_TAGGED_OK) ) )
    {
	syslog(LOG_ERR, "%s: Received non-OK tagged reponse from imap server on CAPABILITY command -- exiting.", fn );
	close( itd.conn->sd );
	exit( 1 );
    }
    
    /* Be nice and logout */
    if ( IMAP_Write( itd.conn, "2 LOGOUT\r\n", strlen("2 LOGOUT\r\n") ) == -1 )
    {
	syslog(LOG_WARNING, "%s: IMAP_Write() failed on LOGOUT: %s -- Ignoring", fn, strerror(errno) );
	close( itd.conn->sd );
	return;
    }
    
    /* read the final OK logout */
    BytesRead = IMAP_Line_Read( &itd );
    if ( BytesRead == -1 )
    {
	syslog(LOG_WARNING, "%s: IMAP_Line_Read() failed on LOGOUT -- Ignoring", fn );
    }

    if ( itd.LiteralBytesRemaining )
    {
	syslog( LOG_WARNING, "%s: Server sent unexpected literal specifier in LOGOUT response -- Ignoring", fn );
    }
        
    close( itd.conn->sd );
    return;
}
    

#if HAVE_LIBSSL
/* taken from OpenSSL apps/s_cb.c */

static int verify_callback(int ok, X509_STORE_CTX * ctx)
{
    char    buf[256];
    X509   *err_cert;
    int     err;
    int     depth;

    syslog(LOG_ERR,"Doing a peer verify");

    err_cert = X509_STORE_CTX_get_current_cert(ctx);
    err = X509_STORE_CTX_get_error(ctx);
    depth = X509_STORE_CTX_get_error_depth(ctx);

    X509_NAME_oneline(X509_get_subject_name(err_cert), buf, sizeof(buf));
    if (ok==0)
    {
      syslog(LOG_ERR, "verify error:num=%d:%s", err,
	     X509_verify_cert_error_string(err));
      
	if (verify_depth >= depth) {
	    ok = 0;
	    verify_error = X509_V_OK;
	} else {
	    ok = 0;
	    verify_error = X509_V_ERR_CERT_CHAIN_TOO_LONG;
	}
    }
    switch (ctx->error) {
    case X509_V_ERR_UNABLE_TO_GET_ISSUER_CERT:
	X509_NAME_oneline(X509_get_issuer_name(ctx->current_cert), buf, sizeof(buf));
	syslog(LOG_NOTICE, "issuer= %s", buf);
	break;
    case X509_V_ERR_CERT_NOT_YET_VALID:
    case X509_V_ERR_ERROR_IN_CERT_NOT_BEFORE_FIELD:
	syslog(LOG_NOTICE, "cert not yet valid");
	break;
    case X509_V_ERR_CERT_HAS_EXPIRED:
    case X509_V_ERR_ERROR_IN_CERT_NOT_AFTER_FIELD:
	syslog(LOG_NOTICE, "cert has expired");
	break;
    }

    return (ok);
}


static int set_cert_stuff(SSL_CTX * ctx,
			  const char *cert_file, const char *key_file)
{
    if (cert_file != NULL) {
	if (SSL_CTX_use_certificate_file(ctx, cert_file,
					 SSL_FILETYPE_PEM) <= 0) {
	    syslog(LOG_ERR, "unable to get certificate from '%s'", cert_file);
	    return (0);
	}
	if (key_file == NULL)
	    key_file = cert_file;
	if (SSL_CTX_use_PrivateKey_file(ctx, key_file,
					SSL_FILETYPE_PEM) <= 0) {
	    syslog(LOG_ERR, "unable to get private key from '%s'", key_file);
	    return (0);
	}
	/* Now we know that a key and cert have been set against
         * the SSL context */
	if (!SSL_CTX_check_private_key(ctx)) {
	    syslog(LOG_ERR,
		   "Private key does not match the certificate public key");
	    return (0);
	}
    }
    return (1);
}
#endif /* HAVE_LIBSSL */


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






