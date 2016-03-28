<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Cotonti Banners Module
 * Main Admin Controller class for the Banners
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class brs_controller_AdminMain
{

    /**
     * Панель управления
     * Список баннеров
     */
    public function indexAction(){
        global $admintitle, $adminpath;

        $admintitle  = cot::$L['brs_banners'];
        $adminpath[] = array(cot_url('admin', array('m'=>'brs')), cot::$L['brs_banners']);

        $sortFields = array(
            'id' => 'ID',
            'title' => cot::$L['Title'],
            'category' => cot::$L['Category'],
            'published' => cot::$L['brs_published'],
            'client' => cot::$L['brs_client'],
            'impressions' => cot::$L['brs_impressions'],
            'clicks' => cot::$L['brs_clicks'],
            'publish_up' => cot::$L['brs_publish_up'],
            'publish_down' => cot::$L['brs_publish_down'],
        );

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)

        $f = cot_import('f', 'G', 'ARR');           // filters

        $maxrowsperpage = cot::$cfg['maxrowsperpage'];
        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for banners list

        $sort = empty($sort) ? 'title' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'asc' : $way;

        $urlParams = array('m' => 'brs');
        if ($sort != 'title') $urlParams['s'] = $sort;
        if ($way  != 'asc')   $urlParams['w'] = $way;

        $condition = array();

        if (!empty($f)){
            foreach($f as $key => $val){
                $val = trim(cot_import($val, 'D', 'TXT'));
                if(empty($val) && $val !== '0') continue;
                if(in_array($key, array('title') )){
                    $condition[] = array($key, "*{$val}*");
                    $urlParams["f[{$key}]"] = $val;

                }else{
                    $condition[] = array($key, $val);
                    $urlParams["f[{$key}]"] = $val;
                }
            }
        }else{
            $f = array();
        }

        $totallines = brs_model_Banner::count($condition);
        $items = brs_model_Banner::find($condition, $maxrowsperpage, $d, $sort.' '.$way);

        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage, 'd', '', true);

        $template = array('brs','admin','list');

        $clients = brs_model_Client::keyValPairs();
        if(!$clients) $clients = array();

        $filterForm = array(
            'hidden' => '',
            'title'  => array(
                'element' => cot_inputbox('text', 'f[title]', $f['title']),
                'label' => brs_model_Banner::fieldLabel('title'),
            ),
            'category'  => array(
                'element' => brs_selectbox_structure('brs', $f['category'], 'f[category]', '', false, false, true),
                'label' => brs_model_Banner::fieldLabel('category'),
            ),
            'client'  => array(
                'element' => cot_selectbox($f['client'], 'f[client]',  array_keys($clients), array_values($clients)),
                'label' => brs_model_Banner::fieldLabel('client'),
            ),
            'published'  => array(
                'element' => cot_selectbox($f['published'], 'f[published]',  array(0,1), array(cot::$L['No'], cot::$L['Yes'])),
                'label' => brs_model_Banner::fieldLabel('published'),
            ),
            'sort' => array(
                'element' => cot_selectbox($sort, 's', array_keys($sortFields), array_values($sortFields), false),
                'label' => cot::$L['adm_sort'],
            ),
            'way' => array(
                'element' => cot_selectbox($way, 'w', array('asc', 'desc'), array(cot::$L['Ascending'], cot::$L['Descending']),
                    false),
            ),
        );
        if(isset(cot::$cfg['plugin']['urleditor']) && cot::$cfg['plugin']['urleditor']['preset'] != 'handy') {
            $filterForm['hidden'] .= cot_inputbox('hidden', 'm', 'brs');
        }

        $view = new View();

        $view->page_title = $admintitle;
        $view->fistNumber = $d + 1;
        $view->items = $items;
        $view->clients = $clients;
        $view->totalitems = $totallines;
        $view->filterForm = $filterForm;
        $view->pagenav = $pagenav;
//        $view->addNewUrl = $addNewUrl;
        $view->urlParams = $urlParams;
        $view->filter = $f;

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    /**
     * Создание / редактирование купона
     * @todo произвольный урл баннера
     * @return string
     */
    public function editAction(){
        global $admintitle, $adminpath, $structure, $cot_import_filters;

        $adminpath[] = array(cot_url('admin', array('m'=>'brs')), cot::$L['brs_banners']);


       if(empty($structure['brs'])) cot_error(cot::$L['brs_category_no']);

        $id = cot_import('id', 'G', 'INT');
        $act = cot_import('act', 'P', 'ALP');

        if(!$id){
            $id = 0;
            $item = new brs_model_Banner();

            $admintitle  = $title = cot::$L['brs_banner_new'];

            $adminpath[] = array(cot_url('admin', array('m'=>'brs', 'a'=>'edit')), cot::$L['brs_banner_new']);


        }else{
            $item = brs_model_Banner::getById($id);
            if(!$item) {
                cot_error(cot::$L['brs_err_not_found']);
                cot_redirect(cot_url('admin', array('m'=>'brs'), '', true));
            }

            $title = htmlspecialchars($item->title).' ['.cot::$L['Edit'].']';
            $admintitle  = cot::$L['brs_banner_edit'];

            $adminpath[] = array(cot_url('admin', array('m'=>'brs', 'a'=>'edit', 'id'=>$id)), $title);
        }

        if ($act == 'save'){
            unset($_POST['id']);
            $data = $_POST;

            $nullDate	= date('Y-m-d H:i:s', 0);   // 1970-01-01 00:00:00
            
            // Импортируем файл
            $file = brs_importFile('file', $item->file);
            $delFile = cot_import('del_file', 'P', 'BOL') ? 1 : 0;
            if($delFile) $data['file'] = '';

            // Импортируем даты
            $data['publish_up'] = cot_import_date('publish_up');
            if(!empty($data['publish_up'])) {
                $data['publish_up'] = date('Y-m-d H:i:s', $data['publish_up']);
            } else {
                $data['publish_up'] = $nullDate;
            }

            $data['publish_down'] = cot_import_date('publish_down');
            if(!empty($data['publish_down'])) {
                $data['publish_down'] = date('Y-m-d H:i:s', $data['publish_down']);
            } else {
                $data['publish_down'] = $nullDate;
            }

            $bannerType = cot_import('banner_type', 'P', 'INT');
            unset($data['banner_type']);

            // Отключим html-фильтры для установк произвольного кода:
            $tmp = $cot_import_filters['HTM'] = array();

            $item->setData($data);
            if($bannerType == brs_model_Banner::TYPE_CUSTOM) $item->type =  $bannerType;

            $cot_import_filters['HTM'] = $tmp;

            if(!empty($file)) {
                $fileProps = brs_fileProperties($file);
                if(empty($fileProps)) {
                    $item->type = brs_model_Banner::TYPE_UNKNOWN;
                    cot_error(cot::$L['brs_err_inv_file_type'], 'file');

                } else {
                    if(empty($item->width)){
                        $item->width = $fileProps['width'];
                    }
                    if(empty($item->height)){
                        $item->height = $fileProps['height'];
                    }
                    if($item->type != brs_model_Banner::TYPE_CUSTOM){
                        $item->type = $fileProps['type'];
                    }
                    if($fileProps['type'] == brs_model_Banner::TYPE_UNKNOWN) {
                        $item->type = brs_model_Banner::TYPE_UNKNOWN;
                        cot_error(cot::$L['brs_err_inv_file_type'], 'file');
                    }
                }

            } elseif($bannerType != brs_model_Banner::TYPE_CUSTOM) {
                // Если файл не передан
                if($delFile) {
                    $item->type = brs_model_Banner::TYPE_UNKNOWN;

                } elseif(!empty($item->file)) {
                    $item->type = brs_type($item->file);
                }
            }

            $item->validate();

            $backUrl = array('m'=>'brs', 'a'=>'edit');
            if($item->id > 0) $backUrl['id'] = $item->id;

            if(!cot_error_found()){
                if(!empty($file)){
                    $item->file = $file;
                }

                if ($id = $item->save()){
                    cot_message(cot::$L['Saved']);

                } else {
                    // Удалим загруженный файл
                    if(!empty($file) && file_exists($file)) unlink($file);
                }
                $backUrl['id'] = $item->id;
                cot_redirect(cot_url('admin', $backUrl, '', true));

            }else{
                // Удалим загруженный файл
                if(!empty($file) && file_exists($file)) unlink($file);
                cot_redirect(cot_url('admin', $backUrl, '', true));
            }
        }


        $deleteUrl = '';
        if($item->id > 0){
            $deleteUrl = cot_confirm_url(cot_url('admin', array('m'=>'brs', 'a'=>'delete', 'id' => $item->id)), 'admin');
        }

        $types = array(
            '0' => cot::$L['brs_type_file'],
            brs_model_Banner::TYPE_CUSTOM => cot::$L['brs_custom_code']
        );

        $clients = brs_model_Client::keyValPairs();
        if(!$clients) $clients = array();
        $purchase = array(
            brs_model_Client::PURCHASE_DEFAULT      => cot::$L['brs_client_default'],
            brs_model_Client::PURCHASE_UNLIMITED    => cot::$L['brs_unlimited'],
            brs_model_Client::PURCHASE_YEARLY       => cot::$L['brs_pt_yearly'],
            brs_model_Client::PURCHASE_MONTHLY      => cot::$L['brs_pt_monthly'],
            brs_model_Client::PURCHASE_WEEKLY       => cot::$L['brs_pt_weekly'],
            brs_model_Client::PURCHASE_DAILY        => cot::$L['brs_pt_daily']
        );

        $track = array(
            -1 => cot::$L['brs_client_default'],
            0  => cot::$L['No'],
            1  => cot::$L['Yes']
        );

        $formFile = cot_inputbox('file', 'file', $item->file);
        if(!empty($item->file)) $formFile .= cot_checkbox(false, 'del_file', cot::$L['Delete']);

        $published = $item->published;
        if($item->id == 0 && !isset($_POST['published'])) $published = 1;

        $showForm = true;
        if(empty($structure['brs'])) $showForm = false;

        $bannerType = 0;
        if($item->type == brs_model_Banner::TYPE_CUSTOM) $bannerType = brs_model_Banner::TYPE_CUSTOM;

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.edit.main') as $pl) {
            include $pl;
        }
        /* ===== */

        $formElements = array(
            'hidden' => array(
                'element' => cot_inputbox('hidden', 'act', 'save')
            ),
            'title' => array(
                'element' => cot_inputbox('text', 'title', $item->rawValue('title')),
                'required' => true,
                'label' => brs_model_Banner::fieldLabel('title')
            ),
            'category' => array(
                'element' => cot_selectbox_structure('brs', $item->rawValue('category'), 'category', '', false, false),
                'required' => true,
                'label' => brs_model_Banner::fieldLabel('category')
            ),
            'type' => array(
                'element' => cot_selectbox($bannerType, 'banner_type', array_keys($types), array_values($types), false),
                'label' => brs_model_Banner::fieldLabel('type')
            ),
            'file' => array(
                'element' => $formFile,
                'label' => brs_model_Banner::fieldLabel('file')
            ),
            'width' => array(
                'element' => cot_inputbox('text', 'width', $item->width),
                'label' => brs_model_Banner::fieldLabel('width')
            ),
            'height' => array(
                'element' => cot_inputbox('text', 'height', $item->height),
                'label' => brs_model_Banner::fieldLabel('height')
            ),
            'alt' => array(
                'element' => cot_inputbox('text', 'alt', $item->alt),
                'label' => brs_model_Banner::fieldLabel('alt')
            ),
            'customcode' => array(
                'element' => cot_textarea('customcode', $item->customcode, 5, 60),
                'label' => brs_model_Banner::fieldLabel('customcode')
            ),
            'clickurl' => array(
                'element' => cot_inputbox('text', 'clickurl', $item->clickurl),
                'label' => brs_model_Banner::fieldLabel('clickurl')
            ),
            'description' => array(
                'element' => cot_textarea('description', $item->description, 5, 60),
                'label' => brs_model_Banner::fieldLabel('description')
            ),
            'sticky' => array(
//                'element' => cot_radiobox($item->sticky, 'sticky', array(1, 0), array(cot::$L['Yes'], cot::$L['No'])),
                'element' => cot_checkbox($item->sticky, 'sticky', brs_model_Banner::fieldLabel('sticky')),
//                'label' => brs_model_Banner::fieldLabel('sticky'),
                'hint' => cot::$L['brs_sticky_tip'],
            ),
            'publish_up' => array(
                'element' => cot_selectbox_date(cot_date2stamp($item->publish_up, 'auto'), 'long', 'publish_up'),
                'label' => brs_model_Banner::fieldLabel('publish_up')
            ),
            'publish_down' => array(
                'element' => cot_selectbox_date(cot_date2stamp($item->publish_down, 'auto'), 'long', 'publish_down'),
                'label' => brs_model_Banner::fieldLabel('publish_down')
            ),
            'imptotal' => array(
                'element' => cot_inputbox('text', 'imptotal', $item->imptotal),
                'label' => brs_model_Banner::fieldLabel('imptotal'),
                'hint' => '0 - '.cot::$L['brs_unlimited'],
            ),
            'impressions' => array(
                'element' => cot_inputbox('text', 'impressions', $item->impressions),
                'label' => brs_model_Banner::fieldLabel('impressions')
            ),
            'clicks' => array(
                'element' => cot_inputbox('text', 'clicks', $item->clicks),
                'label' => brs_model_Banner::fieldLabel('clicks')
            ),
            'client' => array(
                'element' => cot_selectbox($item->rawValue('client'), 'client', array_keys($clients), array_values($clients),
                    true),
                'label' => brs_model_Banner::fieldLabel('client')
            ),
            'purchase_type' => array(
                'element' => cot_selectbox($item->rawValue('purchase_type'), 'purchase_type', array_keys($purchase),
                    array_values($purchase), false),
                'label' => brs_model_Banner::fieldLabel('purchase_type')
            ),
            'track_impressions' => array(
                'element' => cot_selectbox($item->rawValue('track_impressions'), 'track_impressions', array_keys($track),
                    array_values($track), false),
                'label' => brs_model_Banner::fieldLabel('track_impressions'),
                'hint' => cot::$L['brs_track_impressions_hint']
            ),
            'track_clicks' => array(
                'element' => cot_selectbox($item->rawValue('track_clicks'), 'track_clicks', array_keys($track),
                    array_values($track), false),
                'label' => brs_model_Banner::fieldLabel('track_clicks'),
                'hint' => cot::$L['brs_track_clicks_hint']
            ),
            'published' => array(
//                'element' => cot_radiobox( isset($item->published) ? $item->published : 1,
//                    'published', array(1, 0), array(cot::$L['Yes'], cot::$L['No'])),
                'element' => cot_checkbox($published, 'published', brs_model_Banner::fieldLabel('published')),
                //'label' => brs_model_Banner::fieldLabel('published')
            ),
        );

        // Превью загруженного файла
        $banner_image = '';
        if(!empty($item->file)){
            $type = $item->type;
            $imgArr = array(brs_model_Banner::TYPE_IMAGE, brs_model_Banner::TYPE_FLASH);

            if(in_array($item->type, $imgArr)) {
                $w = $item->width;
                $h = $item->height;

            } else {
                $fileProps = brs_fileProperties($item->file);
                if(!empty($fileProps)) {
                    $type = $fileProps['type'];
                    $w = $fileProps['width'];
                    $h = $fileProps['height'];
                }
            }

            if(in_array($type,  $imgArr)) {
                // расчитаем размеры картинки:
                if ($h > 100) {
                    $k = $w / $h;
                    $h = 100;
                    $w = intval($h * $k);
                }
                if($type == brs_model_Banner::TYPE_IMAGE){
                    $rc = 'banner_image_admin';

                } elseif($type == brs_model_Banner::TYPE_FLASH){
                    $rc = 'banner_flash_admin';
                }
                $image = cot_rc($rc, array(
                    'file'      => $item->file,
                    'alt'       => $item->alt,
                    'width'     => $w,
                    'height'    => $h
                ));
                $banner_image = cot_rc('admin_banner', array(
                    'banner' => $image
                ));

            } else {
                // Просто выведем путь к файлу:
                $banner_image = cot_rc('admin_banner', array(
                    'banner' => $item->file
                ));
            }
        }
        // /Превью загруженного файла

        $actionParams = array(
            'm' => 'brs',
            'a' => 'edit'
        );
        if($item->id > 0) $actionParams['id'] = $item->id;

        $template = array('brs', 'admin', 'edit');

        $view = new View();

        $view->page_title = $title;
        $view->showForm = $showForm;
        $view->item = $item;
        $view->deleteUrl = $deleteUrl;
        $view->banner_image = $banner_image;
        $view->formElements = $formElements;
        $view->formAction = cot_url('admin', $actionParams);

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.edit.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }


    public function deleteAction() {

        $id = cot_import('id', 'G', 'INT');
        $d = cot_import('d', 'G', 'INT');

        $backUrlParams = array('m'=>'brs');
        if(!empty($d)) $backUrlParams['d'] = $d;

        // Фильтры из списка
        $f = cot_import('f', 'G', 'ARR');
        if(!empty($f)) {
            foreach ($f as $key => $val) {
                if($key == 'id') continue;
                $backUrlParams["f[{$key}]"] = $val;
            }
        }

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way  = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)
        if ($sort != 'title') $backUrlParams['s'] = $sort;
        if ($way  != 'asc')   $backUrlParams['w'] = $way;

        if(!$id) {
            cot_error(cot::$L['brs_err_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $item = brs_model_Banner::getById($id);
        if(!$item) {
            cot_error(cot::$L['brs_err_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $title = $item->title;
        $item->delete();

        cot_message(sprintf(cot::$L['brs_deleted'], $title));
        cot_redirect(cot_url('admin', $backUrlParams, '', true));
    }

}