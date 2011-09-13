<?php

/**
 * Advanced caching api
 *
 * @author	Fabrizio Branca <typo3@fabrizio-branca.de>
 * @package TYPO3
 * @subpackage advcache
 */
class Tx_Advcache_Api {

	/**
	 * @var boolean
	 */
	protected $useCachingFramework;

	/**
	 * @var t3lib_cache_frontend_AbstractFrontend Frontend cache object to table cache_pages.
	 */
	protected $pageCache;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->useCachingFramework = (defined('TYPO3_UseCachingFramework') && TYPO3_UseCachingFramework);
	}

	/**
	 * Gets the pages cache object (if caching framework is enabled).
	 *
	 * @return t3lib_cache_frontend_AbstractFrontend
	 */
	protected function getPageCache() {
		if (!$this->useCachingFramework) {
			throw new RuntimeException('Caching framework is not enabled.');
		}
		if (!isset($this->pageCache)) {
			$this->pageCache = $GLOBALS['typo3CacheManager']->getCache('cache_pages');
		}
		return $this->pageCache;
	}

	/**
	 * Fetch caching information for page.
	 *
	 * @param	integer		Page ID
	 * @return	array		Page Cache records
	 */
	protected function getAllCachePagesForPage($pageId) {
		$pageId = intval($pageId);
		if (!$this->useCachingFramework) {
			$cachedPages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'id,hash,page_id,reg1,tstamp,expires,cache_data,temp_content',
				'cache_pages',
				'page_id=' . $pageId,
				'',
				'reg1'
			);
		} else {
			$cachedPages = $this->getPageCache()->getByTag('pageId_' . $pageId);
		}
		return $cachedPages;
	}

	/**
	 * Clear cache by page id and parameters
	 *
	 * @param string $id
	 * @param string $params
	 * @return int number of deleted cache entries
	 */
	public function clearCacheByIdAndParameters($pageId, $params) {

			// normalize parameters
		$paramArray = t3lib_div::cHashParams($params);

			// get all cache entries for the current page ...
		$pages = $this->getAllCachePagesForPage($pageId);
		$numberOfDeletedEntries = 0;
		foreach ($pages as $page) {

				// and search for the given parameters in the meta data
			$cacheMetaData = unserialize($page['cache_data']);
			$hash_base = unserialize($cacheMetaData['hash_base']);

			if ($this->paramsMatch($paramArray, $hash_base['cHash'])) {
				$identifier = $this->useCachingFramework ? $page['identifier'] : $page['hash'];

					// Hook: Allow others (nc_staticfilecache, varnish purging,...) to also delete their caches
				$hooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['advcache/Classes/Api.php']['clearCacheByIdAndParameters'];
				if (is_array($hooks)) {
					foreach ($hooks as $hookFunction) {
						$hookParameters = array(
							'pageId' => $pageId,
							'params' => $params,
							'paramArray' => $paramArray,
							'page' => $page,
							'identifier' => $identifier
						);
						t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
					}
				}

				$this->removeCachedPageByIdentifier($identifier);
				$numberOfDeletedEntries++;
			}
		}
		return $numberOfDeletedEntries;
	}

	/**
	 * Check if two url parameter arrays match.
	 * The first array can also contain "*" values, that will match everything
	 *
	 * @param array $params1
	 * @param array $params2
	 * @return bool
	 */
	public function paramsMatch(array $params1, array $params2) {
		if (isset($params1['encryptionKey'])) { unset($params1['encryptionKey']); }
		if (isset($params2['encryptionKey'])) { unset($params2['encryptionKey']); }
		ksort($params1);
		ksort($params2);
		if (array_keys($params1) !== array_keys($params2)) {
			return false;
		}
		foreach ($params1 as $key => $value1) {
			if ($value1 != '*' && $value1 != $params2[$key]) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove cached page by identifier
	 *
	 * @param string $identifier
	 * @return void
	 */
	protected function removeCachedPageByIdentifier($identifier) {
		t3lib_div::devLog('Delete cache with identifier '. $identifier, 'advcache');
		if ($this->useCachingFramework) {
			$this->getPageCache()->remove($identifier);
		} else {
			$db = $GLOBALS['TYPO3_DB']; /* @var $db t3lib_db */
			$res = $db->exec_DELETEquery('cache_pages', 'hash = '.$db->fullQuoteStr($identifier, 'cache_pages'));
			if ($res === false) {
				throw new Exception('Error while deleting entry from cache_pages');
			}
		}
	}

}