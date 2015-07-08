<?php
/**
 * Cotonti Plugin Banners
 * Banner rotation plugin with statistics
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright  Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

global $brs_files_dir;

require(cot_incfile('brs', 'module'));

// Удалить категорию с баннерами
if(file_exists($brs_files_dir)){
    brs_removeDir($brs_files_dir);
}