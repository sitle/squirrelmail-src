#### Makefile for IMAP proxy
#### Contributed by Gary Mills <mills@cc.UManitoba.CA>

## Tuneables

# Paths

INSTALL = /usr/ucb/install
EBIN = /usr/local/sbin
MAN = /usr/local/man/man
ETC = /etc

# Compiler flags 
CC = gcc
RANLIB = :
CFLAGS = -I./include
LDLIBS = 

# Man sections
MANLIB = 3
MANADM = 1m

## Nothing to change after this point

# Libraries

# Solaris
XYD_LIB = -lpthread -lmd5 -lsocket -lnsl
# Linux
#XYD_LIB = -lpthread -lnsl -lcrypto

TAT_LIB = -lcurses

# Object files

XYD_OBJ = ./src/icc.o ./src/main.o ./src/imapcommon.o ./src/request.o ./src/hash.o ./src/becomenonroot.o ./src/config.o ./src/base64.o
TAT_OBJ = ./src/pimpstat.o ./src/config.o

# Final targets

XYD_BIN = ./bin/in.imapproxyd
TAT_BIN = ./bin/pimpstat

# Rules

all: $(XYD_BIN) $(TAT_BIN)

$(XYD_OBJ) $(TAT_OBJ): $(MAKEFILE) ./include/common.h ./include/imapproxy.h

.c.o:
	$(CC) $(CFLAGS) $(CPPFLAGS) -c -o $@ $<

$(XYD_BIN): $(XYD_OBJ)
	$(CC) -o $@ $(XYD_OBJ) $(XYD_LIB)

$(TAT_BIN): $(TAT_OBJ)
	$(CC) -o $@ $(TAT_OBJ) $(TAT_LIB)

clean:
	rm -f ./src/core  ./src/$(XYD_OBJ) ./src/$(TAT_OBJ) $(XYD_BIN) $(TAT_BIN)

install: $(XYD_BIN) $(TAT_BIN)
	$(INSTALL) -c -o bin -g bin -m 0755 $(XYD_BIN) $(EBIN)
	$(INSTALL) -c -o bin -g bin -m 0755 $(TAT_BIN) $(EBIN)

install-init:
	$(INSTALL) -c -o root -g sys -m 0755 ./scripts/imapproxy $(ETC)/init.d
	ln -s ../init.d/imapproxy /etc/rc2.d/S99imapproxy
	ln -s ../init.d/imapproxy /etc/rc0.d/K10imapproxy

install-conf:
	$(INSTALL) -c -o root -g bin -m 0755 ./scripts/imapproxy.conf $(ETC)


####
