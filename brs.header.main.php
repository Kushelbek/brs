<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=header.main
[END_COT_EXT]
==================== */

/**
 * Cotonti Banners Module
 * Banner rotation with statistics
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

defined('COT_CODE') or die('Wrong URL.');

$brsInclided = false;
if(cot::$cfg['headrc_consolidate'] && cot::$cache) {
    Resources::addFile(cot::$cfg['modules_dir']."/brs/js/brs.js");
    $brsInclided = true;
}

if (!empty($cache_ext) && cot::$usr['id'] == 0 && cot::$cfg['cache_' . $cache_ext]){
    Resources::embedFooter(
        "var brsX = '".cot::$sys['xk']."'"
    );
    if(!$brsInclided) Resources::linkFileFooter(cot::$cfg['modules_dir']."/brs/js/brs.js");
}