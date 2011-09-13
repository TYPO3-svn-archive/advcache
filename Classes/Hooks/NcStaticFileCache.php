<?php

require_once t3lib_extMgm::extPath('nc_staticfilecache') . 'class.tx_ncstaticfilecache.php';

/**
 * Hook implementations for nc_staticfilecache
 *
 * @author	Fabrizio Branca <typo3@fabrizio-branca.de>
 * @package TYPO3
 * @subpackage advcache
 */
class Tx_Advcache_Hooks_NcStaticFileCache {

	/**
	 * Add current page hash to the nc_staticfilecache table
	 *
	 * @param array $params
	 * @param tx_ncstaticfilecache $ref
	 * @return void
	 */
	public function createFile_initializeVariables(array $params, tx_ncstaticfilecache $ref) {
		$params['additionalHash'] = $GLOBALS['TSFE']->newHash;
	}

	/**
	 * Delete static file
	 *
	 * @param array $params
	 * @param Tx_Advcache_Api $ref
	 * @return void
	 */
	public function clearCacheByIdAndParameters(array $params, Tx_Advcache_Api $ref) {
		$db = $GLOBALS['TYPO3_DB']; /* @var $db t3lib_db */

		// TODO: support markDirtyInsteadOfDeletion

		$ncStaticFileCache = t3lib_div::makeInstance('tx_ncstaticfilecache'); /* @var $ncStaticFileCache tx_ncstaticfilecache */

		$rows = $db->exec_SELECTgetRows('uid, host, file', 'tx_ncstaticfilecache_file', 'additionalhash = '.$db->fullQuoteStr($params['identifier'], 'tx_ncstaticfilecache_file'));
		foreach ($rows as $row) {

			$cacheDirectory = $row['host'] . dirname($row['file']);
			$result = $ncStaticFileCache->deleteStaticCacheDirectory($cacheDirectory);
			if ($result) {
				$res = $db->exec_DELETEquery('tx_ncstaticfilecache_file', 'uid = '.intval($row['uid']));
				if ($res === false) { throw new Exception('Error while deleting entry from cache_pages'); }
			}
			t3lib_div::devLog('[NcStaticFileCache] Deleted '. $cacheDirectory, 'advcache');
		}


	}

}