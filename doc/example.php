<?php

/**
 * Example: Clearing cache of all version (typoscripts conditions, feuser login status, ...) of page
 *
 * index.php?id=6&b=2&a=1
 *
 * This is useful if this page contains plugin displaying several single views. If one single view changes,
 * you don't have to clear the complete cache for that page...
 */

require_once t3lib_extMgm::extPath('advcache').'Classes/Api.php';
$api = t3lib_div::makeInstance('Tx_Advcache_Api'); /* @var $api Tx_Advcache_Api */
$api->clearCacheByIdAndParameters(6, '&b=2&a=1');

?>