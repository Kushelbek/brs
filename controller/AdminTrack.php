<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Cotonti Banners Module
 * Statistics Admin Controller class for the Banners
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class brs_controller_AdminTrack
{

    /**
     * основная статистика кликов и показов
     */
    public function indexAction(){
        global $admintitle, $adminpath, $structure;

        $admintitle  = cot::$L['brs_tracks'];
        $adminpath[] = array(cot_url('admin', array('m'=>'brs', 'n'=>'track')), cot::$L['brs_tracks']);

        $sortFields = array(
            'b.title'       => cot::$L['Title'],
            'b.category'    => cot::$L['Category'],
            'b.client'      => cot::$L['brs_client'],
            't.type'        => cot::$L['Type'],
            't.track_count' => cot::$L['Count'],
            't.date'        => cot::$L['Date'],
        );

        $sort = cot_import('s', 'G', 'TXT');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)

        $f = cot_import('f', 'G', 'ARR');  // filters
        $f['date_from'] = cot_import_date('f_df', true, false, 'G');
        $f['date_to']   = cot_import_date('f_dt', true, false, 'G');

        $maxrowsperpage = cot::$cfg['maxrowsperpage'];
        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for banners list

        $sort = empty($sort) ? 't.date' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'desc' : $way;

        $urlParams = array('m' => 'brs', 'n'=>'track');
        if ($sort != 't.date') $urlParams['s'] = $sort;
        if ($way  != 'desc')   $urlParams['w'] = $way;

        $where = array();
        $params = array();

        if (!empty($f)){
            foreach($f as $key => $val){
                $val = trim(cot_import($val, 'D', 'TXT'));
                if(empty($val) && $val !== '0') continue;

                if(in_array($key, array('b.title') )){
                    $kkey = str_replace('.', '_', $key);
                    $params[$kkey] = "%{$val}%";
                    $where['filter'][] = "{$key} LIKE :$kkey";
                    $urlParams["f[{$key}]"] = $val;

                }elseif($key == 'date_from'){
                    if($f[$key] == 0) continue;
                    $where['filter'][] = "t.date >= '".date('Y-m-d H:i:s', $f[$key])."'";
                    $urlParams["f_df[year]"]    = cot_date('Y', $f[$key]);
                    $urlParams["f_df[month]"]   = cot_date('m', $f[$key]);
                    $urlParams["f_df[day]"]     = cot_date('d', $f[$key]);

                }elseif($key == 'date_to'){
                    if($f[$key] == 0) continue;
                    $where['filter'][] = "t.date <= '".date('Y-m-d H:i:s', $f[$key])."'";
                    $urlParams["f_dt[year]"]    = cot_date('Y', $f[$key]);
                    $urlParams["f_dt[month]"]   = cot_date('m', $f[$key]);
                    $urlParams["f_dt[day]"]     = cot_date('d', $f[$key]);

                }else{
                    $kkey = str_replace('.', '_', $key);
                    $params[$kkey] = $val;
                    $where['filter'][] = "$key = :$kkey";
                    $urlParams["f[{$key}]"] = $val;
                }
            }
            empty($where['filter']) || $where['filter'] = implode(' AND ', $where['filter']);
        }else{
            $f = array();
        }

        $orderby = "$sort $way";

        $where = array_filter($where);
        $where = ($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT `t`.`date`, `t`.`type` , `t`.`track_count`, `t`.`banner`, b.title, b.category, cl.title as client_title,
                cl.id as client_id
            FROM ".cot::$db->banner_tracks." AS t
            LEFT JOIN ".cot::$db->banners." AS b ON b.id=t.banner
            LEFT JOIN ".cot::$db->banner_clients." AS cl ON cl.id=b.client
            $where ORDER BY $orderby LIMIT {$d}, {$maxrowsperpage}";

        $sqlCount = "SELECT COUNT(*)
            FROM ".cot::$db->banner_tracks." AS t
            LEFT JOIN ".cot::$db->banners." AS b ON b.id=t.banner
            LEFT JOIN ".cot::$db->banner_clients." AS cl ON cl.id=b.client
            $where";

        $totallines = cot::$db->query($sqlCount, $params)->fetchColumn();
        $sqllist = cot::$db->query($sql, $params);

        // Без Ajax, а то дата пропадает (UI datetime)
        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage);

        $track_types = array(
            1 => cot::$L['brs_impressions'],
            2 => cot::$L['brs_clicks']
        );

        $items = $sqllist->fetchAll();

        if($items){
            foreach ($items as $key => $itemRow){
                $items[$key]['categoryTitle'] = '';
                if(!empty($itemRow['category']) && !empty($structure['brs'][$itemRow['category']])) {
                    $items[$key]['categoryTitle'] = $structure['brs'][$itemRow['category']]['title'];
                }
                $items[$key]['track_typeTitle'] = $track_types[$itemRow['type']];
            }
        }

        $clients = brs_model_Client::keyValPairs();
        if(!$clients) $clients = array();

        $filterForm = array(
            'hidden' => cot_inputbox('hidden', 'n', 'track'),
            'title'  => array(
                'element' => cot_inputbox('text', 'f[b.title]', $f['b.title']),
                'label' => brs_model_Banner::fieldLabel('title'),
            ),
            'category'  => array(
                'element' => brs_selectbox_structure('brs', $f['b.category'], 'f[b.category]', '', false, false, true),
                'label' => brs_model_Banner::fieldLabel('category'),
            ),
            'client'  => array(
                'element' => cot_selectbox($f['b.client'], 'f[b.client]', array_keys($clients), array_values($clients)),
                'label' => brs_model_Banner::fieldLabel('client'),
            ),
            'type'  => array(
                'element' => cot_selectbox($f['t.type'], 'f[t.type]', array_keys($track_types), array_values($track_types)),
                'label' => cot::$L['Type'],
            ),
            'date_from'  => array(
                'element' => cot_selectbox_date($f['date_from'], 'short', 'f_df'),
                'label' => cot::$L['brs_from'],
            ),
            'date_to'  => array(
                'element' => cot_selectbox_date($f['date_to'], 'short', 'f_dt'),
                'label' => cot::$L['brs_to'],
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

        $template = array('brs','admin','track');

        $view = new View();

        $view->page_title = $admintitle;
        $view->fistNumber = $d + 1;
        $view->items = $items;
        $view->clients = $clients;
        $view->track_types = $track_types;
        $view->totalitems = $totallines;
        $view->filterForm = $filterForm;
        $view->pagenav = $pagenav;
//        $view->addNewUrl = $addNewUrl;
        $view->urlParams = $urlParams;
        $view->filter = $f;

        /* === Hook === */
        foreach (cot_getextplugins('brs.admin.track.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    public function clearAction(){

        $sort = cot_import('s', 'G', 'TXT');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)

        $f = cot_import('f', 'G', 'ARR');  // filters
        $f['date_from'] = cot_import_date('f_df', true, false, 'G');
        $f['date_to']   = cot_import_date('f_dt', true, false, 'G');

        $sort = empty($sort) ? 't.date' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'desc' : $way;

        $urlParams = array('m' => 'brs', 'n'=>'track');
        if ($sort != 't.date') $urlParams['s'] = $sort;
        if ($way  != 'desc')   $urlParams['w'] = $way;

        $where = array();
        $params = array();

        $baWhere = array();

        if (!empty($f)){
            foreach($f as $key => $val){
                $val = trim(cot_import($val, 'D', 'TXT'));
                if(empty($val) && $val !== '0') continue;

                if(in_array($key, array('b.title') )){
                    $kkey = str_replace('.', '_', $key);
                    $params[$kkey] = "%{$val}%";
                    $baWhere[] = "{$key} LIKE :$kkey";
                    $urlParams["f[{$key}]"] = $val;

                }elseif($key == 'date_from'){
                    if($f[$key] == 0) continue;
                    $where['filter'][] = "date >= '".date('Y-m-d H:i:s', $f[$key])."'";
                    $urlParams["f_df[year]"]    = cot_date('Y', $f[$key]);
                    $urlParams["f_df[month]"]   = cot_date('m', $f[$key]);
                    $urlParams["f_df[day]"]     = cot_date('d', $f[$key]);

                }elseif($key == 'date_to'){
                    if($f[$key] == 0) continue;
                    $where['filter'][] = "date <= '".date('Y-m-d H:i:s', $f[$key])."'";
                    $urlParams["f_dt[year]"]    = cot_date('Y', $f[$key]);
                    $urlParams["f_dt[month]"]   = cot_date('m', $f[$key]);
                    $urlParams["f_dt[day]"]     = cot_date('d', $f[$key]);

                }else{
                    $kkey = str_replace('.', '_', $key);
                    $params[$kkey] = $val;
                    if(mb_strpos($key, 'b.') === 0){
                        $baWhere[] = "$key = :$kkey";
                    }else{
                        $where['filter'][] = "$key = :$kkey";
                    }
                    $urlParams["f[{$key}]"] = $val;
                }
            }
            empty($where['filter']) || $where['filter'] = implode(' AND ', $where['filter']);
        }else{
            $f = array();
        }

        if(!empty($baWhere)){
            $where['banners'] = "banner IN (SELECT b.id FROM ".cot::$db->banners." AS b WHERE ".implode(' AND ', $baWhere)." )";
        }

        $where = implode(' AND ', $where);

        $res = cot::$db->delete(cot::$db->banner_tracks, $where, $params);

        if($res > 0){
            cot_message(sprintf(cot::$L['brs_deleted_records'], $res));
        }else{
            cot_message(cot::$L['brs_deleted_no']);
        }

        cot_redirect(cot_url('admin', $urlParams, '', true));
    }

}