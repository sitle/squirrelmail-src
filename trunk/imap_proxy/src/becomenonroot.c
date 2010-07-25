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
**	becomenonroot.c
**
**  Abstract:
**
**	Routine to switch the unix uid of a running process from root to
**	some other non-zero uid.
**
**  Authors:
**
**      Dave McMurtrie (dgm@pitt.edu)
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/becomenonroot.c,v $
**      $Id: becomenonroot.c,v 1.2 2002/12/17 14:26:17 dgm Exp $
**      
**  Modification History:
**
**      $Log: becomenonroot.c,v $
**      Revision 1.2  2002/12/17 14:26:17  dgm
**      Added suport for global configuration structure.
**
**      Revision 1.1  2002/08/29 16:24:31  dgm
**      Initial revision
**
**
*/

#include <sys/types.h>
#include <strings.h>
#include <errno.h>
#include <stdlib.h>
#include <stdio.h>
#include <pwd.h>
#include <grp.h>
#include <syslog.h>
#include "imapproxy.h"

extern ProxyConfig_Struct PC_Struct;


/*++
 * Function:    BecomeNonRoot
 *
 * Purpose:     If we are running as root, attempt to change that.
 *
 * Parameters:  nada
 *
 * Returns:     0 on success
 *              -1 on failure
 *
 * Authors:     dgm
 *
 * Notes:       Relies on global copy of ProxyConfig_Struct "PC_Struct".
 *              Also worth mentioning that instead of just becoming non-root
 *              this function is now also responsible for chown()ing
 *              the global statistics file.  I had to choose
 *              between doing a passwd and group lookup twice (and further
 *              cluttering main) or doing the chown here where it
 *              doesn't logically belong.  I chose to put it here, but at
 *              least I documented it...  
 *--
 */
extern int BecomeNonRoot( void )
{
    char *fn = "BecomeNonRoot()";
    struct passwd *pwent;                    /* ptr to a passwd file entry */
    struct group *gp;                        /* ptr to a group file entry */
    uid_t newuid;                            /* uid we want to run as */
    gid_t newgid;                            /* gid we want to run as */
    
    if ((pwent = getpwnam( PC_Struct.proc_username )) == NULL)
    {
	syslog(LOG_WARNING, "%s: getpwnam(%s) failed.", 
	       fn, PC_Struct.proc_username);
	return(-1);
    }
    else
    {
	newuid = pwent->pw_uid;
    }
    
    /*
     * Since the whole purpose here is to not run as root, make sure that
     * we don't get UID 0 back for username.
     */
    if ( newuid == 0 )
    {
	syslog( LOG_ERR, "%s: getpwnam returned UID 0 for '%s'.", 
		fn, PC_Struct.proc_username );
	return(-1);
    }
    
    if ((gp = getgrnam( PC_Struct.proc_groupname )) == NULL)
    {
	syslog(LOG_WARNING, "%s: getgrnam(%s) failed.", 
	       fn, PC_Struct.proc_groupname);
	return(-1);
    }
    else
    {
	newgid = gp->gr_gid;
    }
    
    /*
     * The chown() call gets stuck here.  I hate it, but 
     * once in a while there are going to be things in life that I hate.
     */
    if ( chown( PC_Struct.stat_filename, newuid, newgid ) < 0 )
    {
	syslog( LOG_WARNING, "%s: chown() failed to set ownership of file '%s' to '%s:%s': %s", fn, PC_Struct.stat_filename, PC_Struct.proc_username, PC_Struct.proc_groupname, strerror( errno ) );
	return( -1 );
    }
    

    /*
     * Now the whole reason this function exists...  setgid and setuid.
     */

    syslog( LOG_INFO, "%s: Process will run as uid %d (%s) and gid %d (%s).",
	    fn, newuid, PC_Struct.proc_username, 
	    newgid, PC_Struct.proc_groupname );
    
    if ((setgid(newgid)) < 0 )
    {
	syslog(LOG_WARNING, "%s: setgid(%d) failed: %s", fn, 
	       newgid, strerror(errno));
	return(-1);
    }
    
    if ((setuid(newuid)) < 0 )
    {
	syslog(LOG_WARNING,"%s: setuid(%d) failed: %s", fn, 
	       newuid, strerror(errno));
	return(-1);
    }
    
    return(0);
}
