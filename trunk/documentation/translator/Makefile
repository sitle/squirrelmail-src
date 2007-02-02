#! /bin/make -f
# $Id$
#
# Copyright (c) 2005-2006 The SquirrelMail Project Team
# This file is part of SquirrelMail webmail interface documentation.
#
# SquirrelMail is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# SquirrelMail is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with SquirrelMail; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
# 02110-1301, USA
#

# old multiple commands way
# COMMAND=sgml2

# new single command approach
COMMAND=linuxdoc --backend=

SOURCE=translator.sgml

all: txt html tex dvi ps pdf info lyx rtf
	echo "all output formats are created!"

check:
	$(COMMAND)$@ $(SOURCE)

txt:
	$(COMMAND)$@ $(SOURCE)

html:
	$(COMMAND)$@ $(SOURCE)

tex:
	$(COMMAND)latex -o $@ $(SOURCE)

dvi:
	$(COMMAND)latex -o $@ $(SOURCE)

ps:
	$(COMMAND)latex -o $@ $(SOURCE)

pdf:
	$(COMMAND)latex -o $@ $(SOURCE)

info:
	$(COMMAND)$@ $(SOURCE)

lyx:
	$(COMMAND)$@ $(SOURCE)

rtf:
	$(COMMAND)$@ $(SOURCE)

clean:
	rm -f *.txt *.html *.tex *.dvi *.ps *.pdf *.info *.lyx *.rtf

