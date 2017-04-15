<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Cotonti Banners Module
 * Clients Admin Controller the Banners
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class brs_controller_AdminClient
{
    /**
     * Список клиентов
     */
    public function indexAction(){
        global $admintitle, $adminpath;

        $admintitle  = cot::$L['brs_clients'];
        $adminpath[] = array(cot_url('admin', array('m'=>'brs')), cot::$L['brs_clients']);

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)

        $sort = empty($sort) ? 'title' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'asc' : $way;

        $maxrowsperpage = cot::$cfg['maxrowsperpage'];
        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for clients list

        $urlParams = array('m' => 'brs', 'n' => 'client');
        if ($sort != 'title') $urlParams['s'] = $sort;
        if ($way  != 'asc')   $urlParams['w'] = $way;

        $condition = array();

        $items = brs_model_Client::findByCondition($condition, $maxrowsperpage, $d, $sort.' '.$way);
        if(!$items) $items = array();
        $totallines = brs_model_Client::count($condition);

        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage, 'd', '', true);

        $purchase = array(
            brs_model_Client::PURCHASE_DEFAULT      => cot::$L['Default'],
            brs_model_Client::PURCHASE_UNLIMITED    => cot::$L['brs_unlimited'],
            brs_model_Client::PURCHASE_YEARLY       => cot::$L['brs_pt_yearly'],
            brs_model_Client::PURCHASE_MONTHLY      => cot::$L['brs_pt_monthly'],
            brs_model_Client::PURCHASE_WEEKLY       => cot::$L['brs_pt_weekly'],
            brs_model_Client::PURCHASE_DAILY        => cot::$L['brs_pt_daily']
        );

        $template = array('brs','admin', 'client','list');

        $view = new View();

        $view->page_title = $admintitle;
        $view->fistNumber = $d + 1;
        $view->items = $items;
        $view->purchase = $purchase;
        $view->totalitems = $totallines;
        $view->pagenav = $pagenav;
//        $view->addNewUrl = $addNewUrl;
        $view->urlParams = $urlParams;

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.client.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);

    }

    /**
     * Создание / редактирование клиента
     * @return string
     */
    public function editAction(){
        global $admintitle, $adminpath;

        $adminpath[] = array(cot_url('admin', array('m'=>'brs', 'n'=>'client')), cot::$L['brs_clients']);

        $id = cot_import('id', 'G', 'INT');
        $act = cot_import('act', 'P', 'ALP');

        if(!$id){
            $id = 0;
            $item = new brs_model_Client();

            $admintitle  = $title = cot::$L['brs_client_new'];

            $adminpath[] = array(cot_url('admin', array('m'=>'brs', 'n'=>'client', 'a'=>'edit')), cot::$L['Add']);

        }else{
            $item = brs_model_Client::getById($id);
            if(!$item) {
                cot_error(cot::$L['brs_err_not_found']);
                cot_redirect(cot_url('admin', array('m'=>'brs'), '', true));
            }

            $title = htmlspecialchars($item->title).' ['.cot::$L['Edit'].']';
            $admintitle  = cot::$L['brs_banner_edit'];

            $adminpath[] = array(cot_url('admin', array('m'=>'brs', 'n'=>'client', 'a'=>'edit', 'id'=>$id)), $title);
        }

        if ($act == 'save'){

            $data = $_POST;

            $item->setData($data);
            $item->validate();

            $backUrl = array('m'=>'brs', 'n'=>'client', 'a'=>'edit');
            if($item->id > 0) $backUrl['id'] = $item->id;

            if(!cot_error_found()){
                if ($id = $item->save()){
                    cot_message(cot::$L['Saved']);
                }
                $backUrl['id'] = $item->id;
                cot_redirect(cot_url('admin', $backUrl, '', true));
            }

            cot_redirect(cot_url('admin', $backUrl, '', true));
        }

        $deleteUrl = '';
        if($item->id > 0){
            $deleteUrl = cot_confirm_url(cot_url('admin', array('m'=>'brs', 'n'=>'client', 'a'=>'delete', 'id' => $item->id)), 'admin');
        }

        $purchase = array(
            brs_model_Client::PURCHASE_DEFAULT      => cot::$L['Default'],
            brs_model_Client::PURCHASE_UNLIMITED    => cot::$L['brs_unlimited'],
            brs_model_Client::PURCHASE_YEARLY       => cot::$L['brs_pt_yearly'],
            brs_model_Client::PURCHASE_MONTHLY      => cot::$L['brs_pt_monthly'],
            brs_model_Client::PURCHASE_WEEKLY       => cot::$L['brs_pt_weekly'],
            brs_model_Client::PURCHASE_DAILY        => cot::$L['brs_pt_daily']
        );

        $track = array(
            -1  => cot::$L['Default'],
             0  => cot::$L['No'],
             1  => cot::$L['Yes']
        );

        $published = $item->published;
        if($item->id == 0 && !isset($_POST['published'])) $published = 1;

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.client.edit.main') as $pl) {
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
                'label' => brs_model_Client::fieldLabel('title')
            ),
            'email' => array(
                'element' => cot_inputbox('text', 'email', $item->rawValue('email')),
                'label' => brs_model_Client::fieldLabel('email')
            ),
            'purchase_type' => array(
                'element' => cot_selectbox($item->rawValue('purchase_type'), 'purchase_type', array_keys($purchase),
                    array_values($purchase), false),
                'label' => brs_model_Client::fieldLabel('purchase_type')
            ),
            'track_impressions' => array(
                'element' => cot_selectbox($item->rawValue('track_impressions'), 'track_impressions', array_keys($track),
                    array_values($track), false),
                'label' => brs_model_Client::fieldLabel('track_impressions'),
                'hint' => cot::$L['brs_track_impressions_hint']
            ),
            'track_clicks' => array(
                'element' => cot_selectbox($item->rawValue('track_clicks'), 'track_clicks', array_keys($track),
                    array_values($track), false),
                'label' => brs_model_Client::fieldLabel('track_clicks'),
                'hint' => cot::$L['brs_track_clicks_hint']
            ),
            'extrainfo' => array(
                'element' => cot_textarea('extrainfo', $item->rawValue('extrainfo'), 5, 60),
                'label' => brs_model_Client::fieldLabel('extrainfo'),
            ),
            'published' => array(
                'element' => cot_checkbox($published, 'published', brs_model_Client::fieldLabel('published')),
            ),
        );

        $actionParams = array(
            'm' => 'brs',
            'n' => 'client',
            'a' => 'edit'
        );
        if($item->id > 0) $actionParams['id'] = $item->id;

        $template = array('brs', 'admin','client', 'edit');

        $view = new View();

        $view->page_title = $title;
        $view->item = $item;
        $view->deleteUrl = $deleteUrl;
        $view->formElements = $formElements;
        $view->formAction = cot_url('admin', $actionParams);

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.client.edit.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    public function deleteAction() {

        $id = cot_import('id', 'G', 'INT');
        $d = cot_import('d', 'G', 'INT');

        $backUrlParams = array('m'=>'brs', 'n'=>'client');
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
            cot_error(cot::$L['brs_err_client_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $item = brs_model_Client::getById($id);
        if(!$item) {
            cot_error(cot::$L['brs_err_client_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $title = $item->title;
        $item->delete();

        cot_message(sprintf(cot::$L['brs_client_deleted'], $title));
        cot_redirect(cot_url('admin', $backUrlParams, '', true));
    }
}