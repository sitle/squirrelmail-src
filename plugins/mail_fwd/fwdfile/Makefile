BINDIR = /usr/sbin
LIBDIR = 
CFLAGS = -g
LFLAGS = -g
CCM = cc -Em

OBJECTS = wfwd.o
LIBS = 

wfwd: $(OBJECTS)
	cc -o wfwd $(LFLAGS) $(OBJECTS) $(LIBS)

install: wfwd
	install -g bin -o root -m 4555 wfwd $(BINDIR)

clean:
	rm -f *.o *~* core Makefile.new Makefile.bak wfwd

wfwd.o: wfwd.c
	cc -c $(CFLAGS) wfwd.c
