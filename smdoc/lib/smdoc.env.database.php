<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

include_once(PATH.'env.database.php');
include_once(PATH.'db.mysql.php');

class smdoc_db_mysql extends foowd_db_mysql {

    var $makeTableFunction;

	/**
	 * Constructs a new database object.
	 *
	 * @param object foowd The foowd environment object.
	 * @param array database The database array passed into <code>{@link foowd::foowd}</code>.
	 */
	function smdoc_db_mysql(&$foowd, $database) {
        parent::foowd_db_mysql($foowd, $database);
        $this->makeTableFunction = NULL;
    }

	/**
	 * Database factory method.
	 *
	 * Used for creating a database object of the correct sub-class. Given the
	 * name of the DB layer to use, the method will load the corrisponding DB
	 * layer class if it has not already been loaded.
	 *
	 * @param object foowd The foowd environment object.
	 * @param array database The database array passed into <code>{@link foowd::foowd}</code>.
	 * @return mixed The new database object or FALSE on failure.
	 */
	function factory(&$foowd, $database) {
        return new smdoc_db_mysql($foowd, $database);
    }
    
	/**
	 * Set database table.
	 *
	 * Switch the table used for data storage and retrieval in this environment
	 * object.
	 *
	 * @param mixed table Array containing old table name and create function.
	 * @return mixed Array containing old table name and create function.
	 */
	function setTable($table) {
        $oldTable['name'] = $this->table;
        $oldTable['function'] = $this->makeTableFunction;
        $this->table = $table['name'];
        $this->makeTableFunction = $table['function'];
		return $oldTable;
	}

	/**
	 * Make a Foowd database table.
	 *
	 * When a database query fails due to a non-existant database table, this
	 * method is envoked to create the missing table and execute the SQL
	 * statement again.
	 *
	 * @param object foowd The foowd environment object.
	 * @param str SQLString The original SQL string that failed to execute due to missing database table.
	 * @return mixed The resulting database query resource or FALSE on failure.
	 */
	function makeTable(&$foowd, $SQLString) {
        if ( isset($this->makeTableFunction) )
            return call_user_func($this->makeTableFunction, $foowd, $SQLString);
       
        return parent::makeTable($foowd, $SQLString);
    }

	/**
	 * Execute database query.
	 *
	 * @param str SQLString The SQL statement to execute.
	 * @return mixed Query resource or FALSE on failure.
	 */
	function query($SQLString) {
		$className = 'foowd_db_mysql_query';
		return foowd_db_query::factory($className, $this, $SQLString);
	}


}
