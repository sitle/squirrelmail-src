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
**	imapproxy.h
**
**  Abstract:
**
**	Common definitions and function prototypes for the imap proxy server.
**
**  Authors:
**
**      Dave McMurtrie (dgm@pitt.edu)
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/include/RCS/imapproxy.h,v $
**      $Id: imapproxy.h,v 1.4 2002/12/19 21:41:32 dgm Exp $
**      
**  Modification History:
**
**      $Log: imapproxy.h,v $
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


/* 
 * Common definitions 
 */
#define IMAP_UNTAGGED_OK        "* OK "           /* untagged OK response    */
#define IMAP_TAGGED_OK          "1 OK "           /* tagged OK response      */
#define BUFSIZE                 1024              /* default buffer size     */
#define MAX_CONN_BACKLOG        5                 /* tcp connection backlog  */
#define MAXTAGLEN               256               /* max IMAP tag length     */
#define MAXUSERNAMELEN          64                /* max username length     */
#define MAXPASSWDLEN            64                /* max passwd length       */
#define POLL_TIMEOUT_MINUTES    30                /* Poll timeout in minutes */
#define POLL_TIMEOUT            (POLL_TIMEOUT_MINUTES * 60000)
#define DEFAULT_CONFIG_FILE     "/etc/imapproxy.conf"


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
 * IMAPTransactionDescriptors facilitate multi-line buffered reads from
 * IMAP servers and clients.
 */
struct IMAPTransactionDescriptor
{
    int sd;                          /* socket descriptor                    */
    char ReadBuf[ BUFSIZE ];         /* Read Buffer                          */
    unsigned int BytesInReadBuffer;  /* bytes left in read buffer            */
    unsigned int ReadBytesProcessed; /* bytes already processed in read buf  */
    long LiteralBytesRemaining;      /* num of bytes left to read as literal */
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
    int server_sd;                      /* server-side socket descriptor     */
    char username[64];                  /* username connected on this sd     */
    char hashedpw[16];                  /* md5 hash copy of password         */
    unsigned long logouttime;           /* time the user logged out last     */
    struct IMAPConnectionContext *next; /* linked list next pointer          */
};


/*
 * One ProxyConfig structure will be used globally to keep track of
 * configurable options.
 */
struct ProxyConfig
{
    unsigned int listen_port;                 /* port we bind to */
    char *server_hostname;                    /* server we proxy to */
    unsigned int server_port;                 /* port we proxy to */
    unsigned long cache_size;                 /* number of cache slots */
    unsigned long cache_expiration_time;      /* cache exp time in seconds */
    char *proc_username;                      /* username to run as */
    char *proc_groupname;                     /* groupname to run as */
    char *stat_filename;                      /* mmap()ed stat filename */
    char *protocol_log_filename;              /* global trace filename */
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
};

   

typedef struct IMAPServerDescriptor ISD_Struct;
typedef struct IMAPTransactionDescriptor ITD_Struct;
typedef struct IMAPConnectionContext ICC_Struct;
typedef struct IMAPCounter IMAPCounter_Struct;
typedef struct ProxyConfig ProxyConfig_Struct;


/*
 * Function prototypes for external entry points.
 */
extern int IMAP_Line_Read( ITD_Struct * );
extern int IMAP_Literal_Read( ITD_Struct * );
extern void HandleRequest( int );
extern char *memtok( char *, char *, char ** );
extern int imparse_isatom( const char * );
extern int Get_Server_sd( char *, char *, const char * );
extern void ICC_Logout( char *, int );
extern void ICC_Recycle( unsigned int );
extern void ICC_Recycle_Loop( void );
extern void LockMutex( pthread_mutex_t * );
extern void UnLockMutex( pthread_mutex_t * );
extern void SetConfigOptions( char * );

#endif /* __IMAPPROXY_H */

