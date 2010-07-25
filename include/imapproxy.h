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
**	imapproxy.h
**
**  Abstract:
**
**	Common definitions and function prototypes for the imap proxy server.
**
**  Authors:
**
**      Dave McMurtrie <davemcmurtrie@hotmail.com>
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/include/RCS/imapproxy.h,v $
**      $Id: imapproxy.h,v 1.20 2004/11/10 15:35:13 dgm Exp $
**      
**  Modification History:
**
**      $Log: imapproxy.h,v $
**      Revision 1.20  2004/11/10 15:35:13  dgm
**      Changed LiteralBytesRemaining from signed long to unsigned long.
**
**      Revision 1.19  2004/10/11 18:00:42  dgm
**      Added foreground_mode configuration option.
**
**      Revision 1.18  2004/03/11 15:17:58  dgm
**      SELECT_BUF_SIZE size changed from 1024 to BUFSIZE which is
**      currently 4096
**
**      Revision 1.17  2004/02/24 15:21:01  dgm
**      Added support for SELECT caching.
**
**      Revision 1.16  2003/11/14 15:06:14  dgm
**      Patch by Geoffrey Hort <g.hort@unsw.edu.au> to include listen_address
**      config option.  Also, I changed the default buffer size from
**      1024 to 4096.
**
**      Revision 1.15  2003/10/09 15:05:01  dgm
**      Added tcp keepalive support.
**
**      Revision 1.14  2003/07/14 16:41:18  dgm
**      Applied patch by William Yodlowsky <wyodlows@andromeda.rutgers.edu> to
**      allow TLS to work on machines without /dev/random.
**
**      Revision 1.13  2003/05/20 19:18:00  dgm
**      Comment changes only.
**
**      Revision 1.12  2003/05/15 12:30:39  dgm
**      include netinet/in.h
**
**      Revision 1.11  2003/05/13 11:38:53  dgm
**      Patches by Ken Murchison <ken@oceana.com> to clean up build process.
**
**      Revision 1.10  2003/05/06 12:09:12  dgm
**      Applied patches by Ken Murchison <ken@oceana.com> to add SSL
**      support and remove old base64 functions.
**
**      Revision 1.9  2003/04/16 12:19:29  dgm
**      Added support for syslog configuration.
**      Added base64 routine prototypes that I previously forgot.
**
**      Revision 1.8  2003/03/19 13:24:50  dgm
**      Applied patch by Devrim Seral  <devrim@gazi.edu.tr> to allow
**      the default configfile to be configurable via a configure script.
**      (Lots of configures in that sentence, huh?)
**
**      Revision 1.7  2003/02/20 12:40:08  dgm
**      Added UNSELECT support.
**
**      Revision 1.6  2003/02/19 13:03:35  dgm
**      Added LITERAL_PASSWORD and NON_LITERAL_PASSWORD definitions.
**
**      Revision 1.5  2003/01/22 15:33:53  dgm
**      Changed Get_Server_sd() function prototype to reflect the addition of
**      the literal password flag.
**
**      Revision 1.4  2002/12/19 21:41:32  dgm
**      Added support for global configuration.
**
**      Revision 1.3  2002/08/30 13:21:42  dgm
**      Added total client logins counter to IMAPCounter struct
**
**      Revision 1.2  2002/08/29 16:33:46  dgm
**      Added CountTime field to struct IMAPCounter.
**      Removed #define for max number of open file descriptors since
**      we now determine rlimit dynamically instead.
**      Added POLL_TIMEOUT stuff.
**
**      Revision 1.1  2002/07/03 11:21:12  dgm
**      Initial revision
**
**
*/


#ifndef __IMAPPROXY_H
#define __IMAPPROXY_H

#include <netdb.h>
#include <pthread.h>
#include <netinet/in.h>
#include "config.h"

#if HAVE_LIBSSL
#include <openssl/ssl.h>
#include <openssl/rand.h>
#include <limits.h>
#endif


/* 
 * Common definitions 
 */
#define PGM                     "in.imapproxyd"
#define IMAP_UNTAGGED_OK        "* OK "           /* untagged OK response    */
#define IMAP_TAGGED_OK          "1 OK "           /* tagged OK response      */
#define BUFSIZE                 4096              /* default buffer size     */
#define MAX_CONN_BACKLOG        5                 /* tcp connection backlog  */
#define MAXTAGLEN               256               /* max IMAP tag length     */
#define MAXMAILBOXNAME          512               /* max mailbox name length */
#define MAXUSERNAMELEN          64                /* max username length     */
#define MAXPASSWDLEN            64                /* max passwd length       */
#define POLL_TIMEOUT_MINUTES    30                /* Poll timeout in minutes */
#define POLL_TIMEOUT            (POLL_TIMEOUT_MINUTES * 60000)
#define SELECT_BUF_SIZE         BUFSIZE           /* max length of a SELECT  */
						  /* string we can cache     */
#define SELECT_CACHE_EXP        10                /* # of seconds before we  */
                                                  /* expire a SELECT cache   */
#define SELECT_STATUS_BUF_SIZE  256               /* size of select status   */

#ifndef DEFAULT_CONFIG_FILE
#define DEFAULT_CONFIG_FILE     "/etc/imapproxy.conf"
#endif

#define LITERAL_PASSWORD        1
#define NON_LITERAL_PASSWORD    0
#define UNSELECT_SUPPORTED      1
#define UNSELECT_NOT_SUPPORTED  0
#define STARTTLS_SUPPORTED      1
#define STARTTLS_NOT_SUPPORTED  0
#define LOGIN_DISABLED          1
#define LOGIN_NOT_DISABLED      0

/*
 * One IMAPServerDescriptor will be globally allocated such that each thread
 * can save the time of doing host lookups, service lookups, and filling
 * in the sockaddr_in struct.
 */
struct IMAPServerDescriptor
{
    struct hostent host;             /* IMAP host entry                    */
    struct servent serv;             /* IMAP service entry                 */
    struct sockaddr_in srv;          /* IMAP socket address                */
};


/*
 * IMAPSelectCaches provide for caching of SELECT output from an IMAP server
 */
struct IMAPSelectCache
{
    time_t ISCTime;
    char MailboxName[ MAXMAILBOXNAME ];
    char SelectString[ SELECT_BUF_SIZE ];
    char SelectStatus[ SELECT_STATUS_BUF_SIZE ];
};


/*
 * IMAPConnectionDescriptors contain the info needed to communicate on an
 * IMAP connection.
 */
struct IMAPConnectionDescriptor
{
    int sd;                          /* socket descriptor                    */
#if HAVE_LIBSSL
    SSL *tls;                        /* TLS connection context               */
#endif
    struct IMAPSelectCache ISC;      /* Cached SELECT data                   */
};


/*
 * IMAPTransactionDescriptors facilitate multi-line buffered reads from
 * IMAP servers and clients.
 */
struct IMAPTransactionDescriptor
{
    struct IMAPConnectionDescriptor *conn;
    char ReadBuf[ BUFSIZE ];         /* Read Buffer                          */
    unsigned int BytesInReadBuffer;  /* bytes left in read buffer            */
    unsigned int ReadBytesProcessed; /* bytes already processed in read buf  */
    unsigned long LiteralBytesRemaining; /* num of bytes left as literal     */
    unsigned char NonSyncLiteral;    /* rfc2088 alert flag                   */
    unsigned char MoreData;          /* flag to tell caller "more data"      */
    unsigned char TraceOn;           /* trace this transaction?              */
};


/*
 * IMAPConnectionContext structures are used to cache connection info on
 * a per-user basis.
 */
struct IMAPConnectionContext
{
    struct IMAPConnectionDescriptor *server_conn;
    char username[64];                  /* username connected on this sd     */
    char hashedpw[16];                  /* md5 hash copy of password         */
    unsigned long logouttime;           /* time the user logged out last     */
    struct IMAPConnectionContext *next; /* linked list next pointer          */
};


/*
 * One ProxyConfig structure will be used globally to keep track of
 * configurable options.  All of these options are set by reading values
 * from the global config file except for support_unselect.  That's set
 * based on the CAPABILITY string from the real imap server.
 */
struct ProxyConfig
{
    unsigned int listen_port;                 /* port we bind to */
    char *listen_addr;                        /* address we bind to */
    char *server_hostname;                    /* server we proxy to */
    unsigned int server_port;                 /* port we proxy to */
    unsigned long cache_size;                 /* number of cache slots */
    unsigned long cache_expiration_time;      /* cache exp time in seconds */
    unsigned int send_tcp_keepalives;         /* flag to send keepalives */
    unsigned int enable_select_cache;         /* flag to enable select cache */
    unsigned int foreground_mode;             /* flag to enable fg mode */
    char *proc_username;                      /* username to run as */
    char *proc_groupname;                     /* groupname to run as */
    char *stat_filename;                      /* mmap()ed stat filename */
    char *protocol_log_filename;              /* global trace filename */
    char *syslog_facility;                    /* syslog log facility */
    char *syslog_prioritymask;                /* syslog priority mask */
    char *tls_ca_file;                        /* file with CA certs */
    char *tls_ca_path;                        /* path to directory CA certs */
    char *tls_cert_file;                      /* file with client cert */
    char *tls_key_file;                       /* file with client priv key */
    unsigned char support_unselect;           /* unselect support flag */
    unsigned char support_starttls;           /* starttls support flag */
    unsigned char login_disabled;             /* login disabled flag */
};


/*
 * One IMAPCounter structure will be used globally to keep track of
 * several different things that we want to keep a count of, purely for
 * diagnostic, or usage tracking purposes.
 *
 * IMPORTANT NOTE: No attempt is made to guarantee that these counters
 * will be completely accurate.  No mutex is ever taken out when these
 * counters are updated.  This was done for performance -- these numbers
 * aren't considered important enough to waste time locking a mutex to
 * guarantee their accuracy.
 */
struct IMAPCounter
{
    time_t StartTime;
    time_t CountTime;
    unsigned int CurrentClientConnections;
    unsigned int PeakClientConnections;
    unsigned int InUseServerConnections;
    unsigned int PeakInUseServerConnections;
    unsigned int RetainedServerConnections;
    unsigned int PeakRetainedServerConnections;
    unsigned long TotalClientConnectionsAccepted;
    unsigned long TotalClientLogins;
    unsigned long TotalServerConnectionsCreated;
    unsigned long TotalServerConnectionsReused;
    unsigned long TotalSelectCommands;
    unsigned long SelectCacheHits;
    unsigned long SelectCacheMisses;
};

   

typedef struct IMAPServerDescriptor ISD_Struct;
typedef struct IMAPTransactionDescriptor ITD_Struct;
typedef struct IMAPConnectionDescriptor ICD_Struct;
typedef struct IMAPConnectionContext ICC_Struct;
typedef struct IMAPCounter IMAPCounter_Struct;
typedef struct ProxyConfig ProxyConfig_Struct;
typedef struct IMAPSelectCache ISC_Struct;

/*
 * Function prototypes for external entry points.
 */
extern int IMAP_Write( ICD_Struct *, const void *, int );
extern int IMAP_Read( ICD_Struct *, void *, int );
extern int IMAP_Line_Read( ITD_Struct * );
extern int IMAP_Literal_Read( ITD_Struct * );
extern void HandleRequest( int );
extern char *memtok( char *, char *, char ** );
extern int imparse_isatom( const char * );
extern ICD_Struct *Get_Server_conn( char *, char *, const char *, in_port_t, unsigned char );
extern void ICC_Logout( char *, ICD_Struct * );
extern void ICC_Recycle( unsigned int );
extern void ICC_Recycle_Loop( void );
extern void LockMutex( pthread_mutex_t * );
extern void UnLockMutex( pthread_mutex_t * );
extern void SetConfigOptions( char * );
extern void SetLogOptions( void );
extern int Handle_Select_Command( ITD_Struct *, ITD_Struct *, ISC_Struct *, char *, int );
extern unsigned int Is_Safe_Command( char *Command );
extern void Invalidate_Cache_Entry( ISC_Struct * );

#endif /* __IMAPPROXY_H */

