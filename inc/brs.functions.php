<?php
/**
 * Cotonti Banners Module
 * Banner rotation with statistics
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

//if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

cot::$db->registerTable('banners');
cot::$db->registerTable('banner_clients');
cot::$db->registerTable('banner_tracks');

$brs_allowed_ext = array('bmp', 'gif', 'jpg', 'jpeg', 'swf');
$brs_files_dir = 'datas/brs/';

// Requirements
require_once cot_langfile('brs', 'module');
require_once cot_incfile('brs', 'module', 'resources');

/**
 * Generates a banner widget.
 * Wrapper for brs_controller_Widget::banner
 *
 * Use it as CoTemplate callback.
 *
 * @param string $tpl
 * @param string $cat  Category, semicolon separated
 * @param string $order  'order' OR 'rand'
 * @param int $cnt  Banner count
 * @param int|bool $client
 * @param int|bool $subcats
 * @return string
 *
 */
function banner_widget($cat = '', $cnt = 1, $tpl = 'brs.banner', $order = 'order', $client = false, $subcats = false){
    return brs_controller_Widget::banner($cat, $cnt, $tpl, $order, $client, $subcats);
}

/**
 * Импортировать файл
 */
function brs_importFile($inputname, $oldvalue = ''){
    global $lang, $cot_translit, $brs_allowed_ext, $brs_files_dir, $cfg;

    $import = !empty($_FILES[$inputname]) ? $_FILES[$inputname] : array();
    $import['delete'] = cot_import('del_' . $inputname, 'P', 'BOL') ? 1 : 0;

    // Если пришел файл или надо удалить существующий
    if (is_array($import) && !$import['error'] && !empty($import['name'])){
        $fname = mb_substr($import['name'], 0, mb_strrpos($import['name'], '.'));
        $ext = mb_strtolower(mb_substr($import['name'], mb_strrpos($import['name'], '.') + 1));

        if(!file_exists($brs_files_dir)) mkdir($brs_files_dir);

        //check extension
        if(empty($brs_allowed_ext) || in_array($ext, $brs_allowed_ext)){
            if ($lang != 'en'){
                require_once cot_langfile('translit', 'core');
                $fname = (is_array($cot_translit)) ? strtr($fname, $cot_translit) : '';
            }
            $fname = str_replace(' ', '_', $fname);
            $fname = preg_replace('#[^a-zA-Z0-9\-_\.\ \+]#', '', $fname);
            $fname = str_replace('..', '.', $fname);
            $fname = (empty($fname)) ? cot_unique() : $fname;

            $fname .= (file_exists("{$brs_files_dir}/$fname.$ext") && $oldvalue != $fname . '.' . $ext) ? date("YmjGis") : '';
            $fname .= '.' . $ext;

            $file['old'] = (!empty($oldvalue) && ($import['delete'] || $import['tmp_name'])) ? $oldvalue : '';
            $file['tmp'] = (!$import['delete']) ? $import['tmp_name'] : '';
            $file['new'] = (!$import['delete']) ? $brs_files_dir.$fname : '';

            if (!empty($file['old']) && file_exists($file['old'])) unlink($file['old']);
            if (!empty($file['tmp']) && !empty($file['tmp'])) {
                move_uploaded_file($file['tmp'], $file['new']);
            }

            return $file['new'];

        }else{
            cot_error(cot::$L['brs_err_inv_file_type'], $inputname);
            return '';
        }
    }
}

/**
 * @param $file
 * @return array|bool
 *
 * @todo Возможна еще проверка mime типа и выставление $item->type в зависимости от него через cot_files_getMime()
 */
function brs_fileProperties($file) {

    if(!$file) return false;

    $ret = array('width' => 0, 'height' => 0, 'type' => brs_model_Banner::TYPE_UNKNOWN);

    // Try to get image size
    @$gd = getimagesize($file);
    if (!$gd){
        return false;

    }else{
        $ret['width'] = $gd[0];
        $ret['height'] = $gd[1];

        // Get image type
        switch ($gd[2]) {
            //case 1: // IMAGE
            case IMAGETYPE_GIF:
            case IMAGETYPE_JPEG:
            case IMAGETYPE_PNG:
            case IMAGETYPE_BMP:
                $ret['type'] = brs_model_Banner::TYPE_IMAGE;
                break;

            //case 4: // SWF ( Flash)
            case IMAGETYPE_SWF:
            case IMAGETYPE_SWC:
                $ret['type'] = brs_model_Banner::TYPE_FLASH;
                break;

        }
    }
    return $ret;
}

/**
 * Recalculates banner category counters
 *
 * @param string $cat Cat code
 * @return int
 * @global CotDB $db
 */
function cot_brs_sync($cat){
    $cond = array(
        array('category',$cat)
    );

    return brs_model_Banner::count($cond);
}

/**
 * Update banner category code
 *
 * @param string $oldcat Old Cat code
 * @param string $newcat New Cat code
 * @return bool
 * @global CotDB $db
 */
function cot_brs_updatecat($oldcat, $newcat){

    return (bool) cot::$db->update(brs_model_Banner::tableName(), array("category" => $newcat), "category='".cot::$db->prep($oldcat)."'");
}


/**
 * Renders stucture dropdown
 *
 * @param string $extension Extension code
 * @param string $check Seleced value
 * @param string $name Dropdown name
 * @param string $subcat Show only subcats of selected category
 * @param bool $hideprivate Hide private categories
 * @param bool $is_module TRUE for modules, FALSE for plugins
 * @param bool $add_empty
 * @return string
 * @global CotDB $db
 */
function brs_selectbox_structure($extension, $check, $name, $subcat = '', $hideprivate = true, $is_module = true, $add_empty = false){
    global $structure;

    $structure[$extension] = (is_array($structure[$extension])) ? $structure[$extension] : array();

    $result_array = array();
    foreach ($structure[$extension] as $i => $x)
    {
        $display = ($hideprivate && $is_module) ? cot_auth($extension, $i, 'W') : true;
        if ($display && !empty($subcat) && isset($structure[$extension][$subcat]))
        {
            $mtch = $structure[$extension][$subcat]['path'].".";
            $mtchlen = mb_strlen($mtch);
            $display = (mb_substr($x['path'], 0, $mtchlen) == $mtch || $i === $subcat);
        }

        if ((!$is_module || cot_auth($extension, $i, 'R')) && $i!='all' && $display)
        {
            $result_array[$i] = $x['tpath'];
        }
    }
    $result = cot_selectbox($check, $name, array_keys($result_array), array_values($result_array), $add_empty);

    return($result);
}

/**
 * Получить тип баннера по файлу
 * @param $file
 * @return int|bool
 */
function brs_type($file) {
    if(empty($file)) return false;

    $props = brs_fileProperties($file);

    if(empty($props)) return false;

    return $props['type'];
}

/**
 * Remove dir
 * @param $path
 * @return bool
 */
function brs_removeDir($path)
{
    if(empty($path) && $path != '0') return false;

    if(file_exists($path) && is_dir($path)){
        $dirHandle = opendir($path);
        while (false !== ($file = readdir($dirHandle))){
            if ($file!='.' && $file!='..') {// исключаем папки с назварием '.' и '..'
                $tmpPath=$path.'/'.$file;
                chmod($tmpPath, 0777);

                // если папка
                if (is_dir($tmpPath)){
                    brs_removeDir($tmpPath);

                } else {
                    // удаляем файл
                    if(file_exists($tmpPath)) unlink($tmpPath);
                }
            }
        }
        closedir($dirHandle);

        // удаляем текущую папку
        if(file_exists($path)) rmdir($path);

    }else {
        echo "Deleting directory not exists or it's file!";
        return false;
    }

    return true;
}

/**
 * Files list in folder
 * @param $folder
 * @return array
 */
function brs_getFilesList($folder){
    $all_files = array();
    $fp = opendir($folder);
    while($cv_file = readdir($fp)) {
        if(is_file($folder."/".$cv_file)) {
            $all_files[]=$folder."/".$cv_file;

        }elseif($cv_file!="." && $cv_file!=".." && is_dir($folder."/".$cv_file)){
            brs_getFilesList($folder."/".$cv_file,$all_files);
        }
    }
    closedir($fp);
    return $all_files;
}

function brs_YesNo($cond){
    if($cond) return cot::$R['banner_yes'];

    return cot::$R['banner_no'];
}