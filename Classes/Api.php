<?php

/**
 * Advanced caching api
 *
 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
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
		$params = t3lib_div::cHashParams($params);
		unset($params['encryptionKey']);
		$params = t3lib_div::implodeArrayForUrl('', $params);

		// get all cache entries for the current page ...
		$pages = $this->getAllCachePagesForPage($pageId);
		$numberOfDeletedEntries = 0;
		foreach ($pages as $page) {

			// and search for the given parameters in the meta data
			$cacheMetaData = unserialize($page['cache_data']);
			$hash_base = unserialize($cacheMetaData['hash_base']);
			if (is_array($hash_base)) {
				unset($hash_base['cHash']['encryptionKey']);
			}
			$pageParams = t3lib_div::implodeArrayForUrl('', $hash_base['cHash']);

			if ($params == $pageParams) {
				$identifier = $this->useCachingFramework ? $page['identifier'] : $page['hash'];
				$this->removeCachedPageByIdentifier($identifier);
				$numberOfDeletedEntries++;
			}
		}
		return $numberOfDeletedEntries;
	}

	/**
	 * Remove cached page by identifier
	 *
	 * @param string $identifier
	 * @return void
	 */
	protected function removeCachedPageByIdentifier($identifier) {
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