<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Cotonti Banners Module
 * Widget Controller class
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class brs_controller_Widget {

    public static $count = 0;

    /**
     * Generates a banner widget.
     *
     * @param string $tpl
     * @param array|string $cat  Category, semicolon separated
     * @param string $order  'order' OR 'rand'
     * @param int $cnt  Banner count
     * @param int|bool $client
     * @param int|bool $subcats
     * @return string
     *
     */
    public static function banner($cat = '', $cnt = 1, $tpl = 'brs.banner', $order = 'order', $client = false, $subcats = false){
        global $cache_ext;

        $cats = array();
        $client = (int)$client;
        $cnt = (int)$cnt;

        if(!empty($cat)) {
            if (is_array($cat)) {
                $cats = $cat;

            } elseif ($cat != '') {
                $categs = explode(';', $cat);
                if (is_array($categs)) {
                    foreach ($categs as $tmp) {
                        $tmp = trim($tmp);
                        if (empty($tmp)) continue;
                        if ($subcats) {
                            // Specific cat
//                    var_dump(cot_structure_children('banners', $tmp));
                            $cats = array_merge($cats, cot_structure_children('brs', $tmp, true, true, false, false));
                        } else {
                            $cats[] = $tmp;
                        }
                    }
                }
                $cats = array_unique($cats);
            }
        }

        $condition = array(
            array('published', 1),
            array('publish_up', date('Y-m-d H:i:s', cot::$sys['now']), '<='),
            array('SQL', "publish_down >='".date('Y-m-d H:i:s', cot::$sys['now'])."' OR publish_down ='0000-00-00 00:00:00'"),
            array('SQL', "imptotal = 0 OR impressions < imptotal"),
        );
        if(count($cats) > 0){
            $condition[] = array('category', $cats);
        }
        if($client){
            $condition[] = array('client', $client);
        }
        $ord = "lastimp ASC";
        if($order == 'rand') $ord = 'RAND()';

        $items = brs_model_Banner::find($condition, $cnt, 0, $ord);

        if(!$items) return '';

        // Display the items
        $t = new XTemplate(cot_tplfile($tpl, 'plug'));

        foreach($items as $itemRow){
            // Если включено кеширование и это незарег не засчитываем показ. Баннер будет запрошен аяксом
            if (!(!empty($cache_ext) && cot::$usr['id'] == 0 && cot::$cfg['cache_' . $cache_ext])){
                $itemRow->impress();
            }

            self::$count++;
            // Порядковый номер баннера на странице
            $itemRow->number = self::$count;
        }

        $view = new View();
        $view->items = $items;
        $view->order = $order;
        $view->client = $client;
        return $view->render($tpl);
    }
}