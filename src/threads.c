/*
** 
**               Copyright (c) 2002-2007 Dave McMurtrie
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
**    threads.c
**
**  Abstract:
**
**    Routines to provide threadsafe interaction with OpenSSL libraries.
**
**  Authors:
**
**    Jan Grant, based on http://www.openssl.org/docs/crypto/threads.html
**
**  RCS:
**
**    $Source: /afs/andrew.cmu.edu/usr18/dave64/work/IMAP_Proxy/src/RCS/threads.c,v $
**    $Id: threads.c,v 1.2 2007/05/31 11:58:17 dave64 Exp $
**
**  Modification History:
**
**    $Log: threads.c,v $
**    Revision 1.2  2007/05/31 11:58:17  dave64
**    Added license information and updated comment block.
**
**
*/

#define _REENTRANT

#include <config.h>

#define OPENSSL_THREAD_DEFINES
#include <openssl/opensslconf.h>
#if defined(OPENSSL_THREADS)


#include <stdio.h>
#include <syslog.h>
#include <pthread.h>

#include <openssl/crypto.h>

#include "common.h"
#include "imapproxy.h"

static void locking_function(int mode, int n, const char *file, int line);
static unsigned long id_function(void);

static pthread_mutex_t *locks;

/* We use pthreads, since the rest of imapproxy does. */

void ssl_thread_setup(const char *fn) {
    int i, rc;

    syslog(LOG_NOTICE, "Initialising %d pthread_mutexes", CRYPTO_num_locks() );
    locks = malloc(CRYPTO_num_locks() * sizeof(pthread_mutex_t));
    if (locks == NULL) {
        syslog(LOG_ERR, "Cannot allocate space for SSL mutexes");
        exit(1);
    }

    for (i = 0; i < CRYPTO_num_locks(); i++) {
        rc = pthread_mutex_init(&locks[i], NULL);
        if ( rc ) {
            syslog(LOG_ERR, "%s: pthread_mutex_init() returned error [%d] initializing OpenSSL mutexes.  Exiting.", fn, rc );
            exit( 1 );
        }
    }

    CRYPTO_set_id_callback(id_function);
    CRYPTO_set_locking_callback(locking_function);
}

void locking_function(int mode, int n, const char *file, int line) {
    if (mode & CRYPTO_LOCK)
        pthread_mutex_lock(&locks[n]);
    else
        pthread_mutex_unlock(&locks[n]);
}

unsigned long id_function(void) {
    return (unsigned long) pthread_self();
}


#else	/* defined(OPENSSL_THREADS) */
   #error OpenSSL compiled without thread support
#endif	/* defined(OPENSSL_THREADS) */

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
