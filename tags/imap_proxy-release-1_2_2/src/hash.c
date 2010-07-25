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
**	Hashing routines
**
**  Abstract:
**
**	Routines to provide an easy interface to hashing functions.
**
**  Authors:
**
**      Ben Carter
**
**  RCS:
**
**      $Source: /afs/pitt.edu/usr12/dgm/work/IMAP_Proxy/src/RCS/hash.c,v $
**      $Id: hash.c,v 1.2 2003/05/20 18:43:52 dgm Exp $
**      
**  Modification History:
**
**      $Log: hash.c,v $
**      Revision 1.2  2003/05/20 18:43:52  dgm
**      comment changes only.
**
**      Revision 1.1  2002/08/29 16:27:23  dgm
**      Initial revision
**
**
*/


#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <syslog.h>
#include "common.h"

/*++
 * Function:	Hash
 *
 * Purpose:	Generate a hash key.
 *
 * Parameters:	pointer to char -- input key
 *		unsigned int -- maximum length of the input key
 *		unsigned int -- hash table size
 *
 * Returns:	unsigned int -- hash key
 *
 * Authors:	bhc
 *--
 */
unsigned int Hash(char *Input_Key, unsigned int	Table_Size )
{
  unsigned int	i;
  unsigned int  Size;
  unsigned int	Longwords;
  unsigned int	*I_Pointer;
  unsigned int	Hash_Value=0;
  char	Hash_Buffer[1024];
  Size = strlen( Input_Key );

  if ( Size > sizeof Hash_Buffer )
  {
    syslog(LOG_ERR, "Hash(): Maximum of %d for '%s' exceeds architectural limit of %d", Size, Input_Key, sizeof Hash_Buffer );
    exit(1);
  }

  Longwords = ( ( Size + 3 ) / 4 );
  memset( Hash_Buffer, 0, Longwords*4 );
  memcpy( Hash_Buffer, Input_Key, Size );
  I_Pointer = (unsigned int *) Hash_Buffer;
  
  for ( i=0; i<Longwords; i++ )
  {
    Hash_Value = Hash_Value + *I_Pointer;
    I_Pointer++;
  }
  
  Hash_Value = Hash_Value + Size;
  
  Hash_Value = Hash_Value % Table_Size;
  
  return(Hash_Value);
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
