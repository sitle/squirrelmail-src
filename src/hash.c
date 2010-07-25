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
**      $Id: hash.c,v 1.1 2002/08/29 16:27:23 dgm Exp $
**      
**  Modification History:
**
**      $Log: hash.c,v $
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
