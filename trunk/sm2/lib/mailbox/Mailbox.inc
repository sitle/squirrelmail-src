<?php

class mailbox {
	var	$name,
		$uidvalidity = 0,
		$uidnext = 0,
		$exists = 0,
		$recent = 0,
		$flagsCache = array(),
		$cacheInfo = array('UIDVALIDITY' => 0, 'UIDNEXT' => 0, 'EXISTS' => 0),
		$unSortedUids,
		$headerCacheHeader = array(), /* available fields in headerCache */
		$headerCache = array(), /* key = uid, value array(boolean, oHdr) */
		$sortCache = array(),
		$uids = array(),
		$backend,
		$permanentflags = array();
		
	
	
	function updateHeaderCache() {
		$aHeaderKeys = array_keys($this->headerCache);
		$aRemoveKeys = array_diff($aHeaderKeys,$this->uids);
		foreach($aRemoveKeys as $unsetKey) {
			unset($this->headerCache[$unsetKey]);
		}
	}
	
	function updateSortCache($sSortField) {
		$aSortHeader = array('UIDVALIDITY' => $this->uidvalidity,
							 'UIDNEXT' => $this->uidnext,
							 'EXISTS' => $this->exists);
		if (isset($this->sortCache[$sSortField])) {
			$aSortCacheInfo = $this->sortCache[$sortField][0];
			if ($aSortCacheInfo['UIDVALIDITY'] == $this->uidvalidity) {
				if ($aSortCacheInfo['UIDNEXT'] == $this->uidnext) {
					$aSortKeys = $this->sortCache[$sortField][1];
					$aRemoveKeys = array_diff($aSortKeys,$this->uids);
					foreach($aRemoveKeys as $unsetKey) {
						unset($SortKeys[$unsetKey]);
					}
					$this->sortCache[$sortField][0] = $aSortHeader;
					$this->sortCache[$sortField][1] = $aSortKeys;
				} else {
					$this->sortCache[$sSortField] = array($aSortHeader,
						$backend->getSortedHeaderList($this,$sSortField));
				}
			} else {
				$this->sortCache[$sSortField] = array($aSortHeader,
					$backend->getSortedHeaderList($this,$sSortField));
			}
		} else {
			$this->sortCache[$sSortField] = array($aSortHeader,
				$backend->getSortedHeaderList($this,$sSortField));
		}
	}
	
	/*
	how to cache ?
	
	vars:  
	uids: array with the current  available uid's
	flags: array keyed by uid containing the message flags
	headerCache: array keyed by uid containing the header objects
	sortCache: arrays keyed by sortField containing an array with the 
		following elements: [0] cacheInfo, [1] array with the sorted uid's
	
	hashInfo: hash element size, hash-entry, valid
	
	*/
	
	function __wakeup() {
		/* retrieve the cache */
	}
	
	function __sleep() {
		/* write the cache */
	}
}	
	


?>