/*
 * wfwd.c
 *
 * Writes .forward file for email forwarding, works with sendmail
 *
 * Ritchie Low
 *        
 * Should be owned by root and suid root, 
 */
 
#define BUFSIZE 64

#include <sys/types.h>
#include <sys/stat.h>
#include <sys/wait.h>
#include <unistd.h>
#include <fcntl.h>
#include <syslog.h>
#include <stdlib.h>
#include <stdio.h>
#include <strings.h>
#include <pwd.h>
#include <string.h>


main (argc, argv)
int argc;
char *argv[];
{
     char line[BUFSIZE];
     char *puid, *pemail;
     struct passwd *pw, *getpwnam();
     int c;
     FILE *fd;
 
     if (argc<2) {
        printf("Usage: %s userid email-address\n",argv[0]);
	exit(1);
     } 
     if (argc==3)
          pemail = argv[2];
     else
          pemail = "\0";
     puid = argv[1];
     if ((pw=getpwnam(puid))==NULL)
     {
	printf("Invalid user\n ");
	exit(1);
     }
     setuid (pw->pw_uid);
     setgid (pw->pw_gid);
     sprintf(line,"/home/%s/.forward",puid);
     if ((fd = (FILE *)fopen(line,"w"))==NULL) {
        printf("Cannot open %s\n",line);
        exit(1);
     }
     fprintf(fd,"%s\n",pemail);
     fclose(fd); 
}
