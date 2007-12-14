
CREATE TABLE stats (
  rev char(25) NOT NULL default '',
  team char(10) NOT NULL default '',
  package char(25) NOT NULL default '',
  filename char(40) NOT NULL default '',
  translated int(4) NOT NULL default 0,
  fuzzy int(4) NOT NULL default 0,
  untranslated int(4) NOT NULL default 0,
  total int(4) NOT NULL default 0,
  error int(4) NOT NULL default 0,
  have_po tinyint(1) NOT NULL default 0,
  have_pot tinyint(1) NOT NULL default 0,
  KEY team (team),
  KEY package (package)
) TYPE=MyISAM;

CREATE TABLE sum (
  rev char(25) NOT NULL default '',
  team char(10) NOT NULL default '',
  package char(25) NOT NULL default '',
  translated int(4) NOT NULL default 0,
  fuzzy int(4) NOT NULL default 0,
  untranslated int(4) NOT NULL default 0,
  total int(4) NOT NULL default 0,
  error int(4) NOT NULL default 0,
  KEY team (team),
  KEY package (package)
) TYPE=MyISAM;

CREATE TABLE essential (
  rev char(25) NOT NULL default '',
  sdate date NOT NULL default '0000-00-00',
  team char(10) NOT NULL default '',
  filename char(40) NOT NULL default '',
  translated int(4) NOT NULL default 0,
  total int(4) NOT NULL default 0,
  KEY sdate (sdate),
  KEY team (team)
) TYPE=MyISAM;

CREATE TABLE teams (
  rev char(25) NOT NULL default '',
  teamcode char(10) NOT NULL default '',
  teamname char(50) NOT NULL default '',
  KEY teamcode (teamcode)
) TYPE=MyISAM;

