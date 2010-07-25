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
**	common.h
**
**  Abstract:
**
**	Function declarations for public entry points to common library
**	functions.
**
**  Authors:
**
**      Dave McMurtrie (dgm@pitt.edu)
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/include/RCS/common.h,v $
**      $Id: common.h,v 1.2 2002/12/19 21:43:35 dgm Exp $
**      
**  Modification History:
**
**      $Log: common.h,v $
**      Revision 1.2  2002/12/19 21:43:35  dgm
**      modified parameter list of becomenonroot to support global config.
**
**      Revision 1.1  2002/08/29 16:31:19  dgm
**      Initial revision
**
**
*/

#ifndef __COMMON_H
#define __COMMON_H


#define HASH_TABLE_SIZE         1024

/*
 * Function prototypes for public entry points to common Pitt functions.
 */

/*
 * Misc. Functions.
 */
extern int BecomeNonRoot( void );
extern unsigned int Hash(char *, unsigned int );


#endif /* COMMON_H */



