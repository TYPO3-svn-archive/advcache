<?php

if (t3lib_extMgm::isLoaded('nc_staticfilecache')) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_initializeVariables'][] = 'EXT:advcache/Classes/Hooks/NcStaticFileCache.php:Tx_Advcache_Hooks_NcStaticFileCache->createFile_initializeVariables';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['advcache/Classes/Api.php']['clearCacheByIdAndParameters'][] = 'EXT:advcache/Classes/Hooks/NcStaticFileCache.php:Tx_Advcache_Hooks_NcStaticFileCache->clearCacheByIdAndParameters';
}

?>