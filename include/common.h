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
**	common.h
**
**  Abstract:
**
**	Function declarations for public entry points to common library
**	functions.
**
**  Authors:
**
**      Dave McMurtrie <davemcmurtrie@hotmail.com>
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/include/RCS/common.h,v $
**      $Id: common.h,v 1.3 2003/05/20 19:14:10 dgm Exp $
**      
**  Modification History:
**
**      $Log: common.h,v $
**      Revision 1.3  2003/05/20 19:14:10  dgm
**      Comment changes only.
**
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
 * Misc. function prototypes.
 */
extern int BecomeNonRoot( void );
extern unsigned int Hash(char *, unsigned int );


#endif /* COMMON_H */



