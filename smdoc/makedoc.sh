#!/bin/bash
# $Id$ 

#/**
#  * makedoc - PHPDocumentor script to save your settings
#  * 
#  * Put this file inside your PHP project homedir, edit its variables and run whenever you wants to
#  * re/make your project documentation.
#  * 
#  * The version of this file is the version of PHPDocumentor it is compatible.
#  * 
#  * It simples run phpdoc with the parameters you set in this file.
#  * NOTE: Do not add spaces after bash variables.
#  *
#  * @copyright         makedoc.sh is part of PHPDocumentor project {@link http://freshmeat.net/projects/phpdocu/} and its LGPL
#  * @author            Roberto Berto <darkelder (inside) users (dot) sourceforge (dot) net>
#  * @version           Release-1.1.0
#  */


##############################
# should be edited
#
# Note: to generate docs for templates, you need to update
# phpDocumentor.ini to include .tpl as a php file extension.
##############################

#/**
#  * title of generated documentation, default is 'Generated Documentation'
#  * 
#  * @var               string TITLE
#  */
TITLE="SquirrelMail Documentation Framework"

#/** 
#  * name to use for the default package. If not specified, uses 'default'
#  *
#  * @var               string PACKAGES
#  */
PACKAGES="smdoc"

#/** 
#  * name of a directory(s) to parse directory1,directory2
#  * $PWD is the directory where makedoc.sh 
#  *
#  * @var               string PATH_PROJECT
#  */
PATH_PROJECT=$PWD

#/**
#  * path of PHPDoc executable
#  *
#  * @var               string PATH_PHPDOC
#  */
PATH_PHPDOC=$PWD/../phpDocumentor-1.2.3/phpdoc

#/**
#  * where documentation will be put
#  *
#  * @var               string PATH_DOCS
#  */
PATH_DOCS=$PWD/sqmdocs

#/**
#  * what outputformat to use (html/pdf)
#  *
#  * @var               string OUTPUTFORMAT
#  */
OUTPUTFORMAT=HTML

#/** 
#  * converter to be used
#  *
#  * @var               string CONVERTER
#  */
#CONVERTER=Smarty
CONVERTER=frames

#/**
#  * template to use
#  *
#  * @var               string TEMPLATE
#  */
#TEMPLATE=default
TEMPLATE=earthli

#/**
#  * parse elements marked as private
#  *
#  * @var               bool (on/off)           PRIVATE
#  */
PRIVATE=on

#/**
#  * Ignore certain files (comma separated, wildcards enabled)
#  * @var               string IGNORE
#  */
IGNORE=CVS/,*.txt,doc_templates/,docs/,tpls/,config.php,*.sql,*.sh,test.php,*.gif,*.css,*.jpg

#/**
#  * Ignore certain tags (comma separated)
#  * @var               string IGNORE_TAGS
#  */
IGNORE_TAGS=@author

# make documentation
$PATH_PHPDOC -d $PATH_PROJECT -t $PATH_DOCS -ti "$TITLE" -dn $PACKAGES \
-o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE \
-i $IGNORE -it $IGNORE_TAGS


# vim: set expandtab :
