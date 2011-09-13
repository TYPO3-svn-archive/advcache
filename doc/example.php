<?php

die();

// require_once t3lib_extMgm::extPath('advcache').'Classes/Api.php'; // should be autoloaded...
$api = t3lib_div::makeInstance('Tx_Advcache_Api'); /* @var $api Tx_Advcache_Api */

/**
 * Example: Clearing cache of all versions (typoscripts conditions, feuser login status, ...) of page  index.php?id=6&b=2&a=1
 *
 * This is useful if this page contains plugin displaying several single views. If one single view changes,
 * you don't have to clear the complete cache for that page...
 */
$api->clearCacheByIdAndParameters(6, '&b=2&a=1');

/**
 * Example: Clearing cache of all versions of page  index.php?id=6&b=2&L=* for every value of parameter "L" (incl. no value)
 *
 * This can be used if you want to delete all language versions of a single view or to delete all output of a selected extension
 */
$api->clearCacheByIdAndParameters(6, '&b=2&L=*');

?>