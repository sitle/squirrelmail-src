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
**      $Source: /afs/andrew.cmu.edu/usr18/dave64/work/IMAP_Proxy/include/RCS/common.h,v $
**      $Id: common.h,v 1.9 2008/01/28 13:11:55 dave64 Exp $
**      
**  Modification History:
**
**      $Log: common.h,v $
**      Revision 1.9  2008/01/28 13:11:55  dave64
**      updated version to 1.2.6
**
**      Revision 1.8  2007/11/15 11:13:13  dave64
**      updated version to 1.2.6rc2.
**
**      Revision 1.7  2007/05/31 12:13:30  dave64
**      Updated version string to 1.2.6rc1
**
**      Revision 1.6  2007/01/30 15:19:29  dave64
**      Updated version string to 1.2.5.
**
**      Revision 1.5  2006/10/03 12:21:54  dave64
**      Updated version string to 1.2.5rc2.
**
**      Revision 1.4  2006/02/16 18:32:30  dave64
**      Added IMAP_PROXY_VERSION patch by Matt Selsky.
**
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
#define IMAP_PROXY_VERSION      "1.2.6"

/*
 * Misc. function prototypes.
 */
extern int BecomeNonRoot( void );
extern unsigned int Hash(char *, unsigned int );


#endif /* COMMON_H */



