
#ifndef _IMAPROXY_CONFIG_H
#define _IMAPROXY_CONFIG_H

@TOP@

/* Define if you have the nfds_t typedef.  */
#undef HAVE_NFDS_T

@BOTTOM@

#if HAVE_SYS_MMAN_H
#include <sys/mman.h>
#endif

#ifndef MAP_FAILED
#define MAP_FAILED	((void *) -1)
#endif

#if HAVE_SYS_PARAM_H
#include <sys/param.h>
#endif

#ifndef MAXPATHLEN
#define MAXPATHLEN	4096
#endif

#ifndef HAVE_NFDS_T
typedef unsigned int nfds_t;
#endif

#endif /* _IMAPROXY_CONFIG_H */
