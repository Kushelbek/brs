<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Cotonti Banners Module
 * Main Controller class
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class brs_controller_Main{

    public function clickAction(){

        $id = cot_import('id', 'G', 'INT');
        if(!$id) cot_die_message(404);

        $banner = brs_model_Banner::getById($id);
        if(!$banner) cot_die_message(404);

        $banner->click();

        if(!empty($banner->clickurl)) header('Location: '.$banner->clickurl);

        exit();
    }

    /**
     * Вывод баненров ajax
     */
    public function ajxLoadAction(){
        global $sys;

        $ret = array(
            'error' => ''
        );

        $brs = cot_import('brs', 'P', 'ARR');
        if(!$brs){
            $ret['error'] = 'Nothing to load';
            echo json_encode($ret);
            exit;
        }

        $nullDate	= date('Y-m-d H:i:s', 0);   // 1970-01-01 00:00:00
        
        // Пока выбыраем баненры по одному,
        // @todo оптимизировать
        $baseCondition = array(
            array('published', 1),
            array('publish_up', date('Y-m-d H:i:s', cot::$sys['now']), '<='),
            array('SQL', "publish_down >='".date('Y-m-d H:i:s', cot::$sys['now'])."' OR publish_down ='{$nullDate}'"),
            array('SQL', "imptotal = 0 OR impressions < imptotal"),
        );

        $cnt = 0;
        foreach($brs as $pid => $data){
            $pid = (int)$pid;
            if(empty($data['category'])) {
                $ret['items'][$pid] = '';
                continue;
            } else {
                $cat = cot_import($data['category'], 'D', 'TXT');
            }

            if($pid == 0) continue;
            if(empty($cat)){
                $ret['items'][$pid] = '';
                continue;
            }

            $condition =  $baseCondition;
            $condition[] = array('category', $cat);

            $client = false;
            if(!empty($data['client'])) $client = cot_import($data['client'], 'D', 'INT');
            if($client){
                $condition[] = array('client', $client);
            }
            $order = 'order';
            if(!empty($data['order'])) $order = cot_import($data['order'], 'D', 'TXT');

            $ord = "lastimp ASC";
            if($order == 'rand') $ord = 'RAND()';

//            $banner = brs_model_Banner::find($condition, 1, 0, $ord);
            $banner = brs_model_Banner::fetchOne($condition, $ord);
            if(empty($banner)){
                $ret['items'][$pid] = '';
                continue;
            }
            $banner->impress();

            $url = cot_url('brs', 'a=click&id='.$banner->id);
            switch($banner->type){

                case brs_model_Banner::TYPE_IMAGE:
                    if(!empty($banner->file)){
                        $image = cot_rc('banner_image', array(
                            'file'   => $banner->file,
                            'alt'    => $banner->alt,
                            'width'  => $banner->width,
                            'height' => $banner->height
                        ));
                        if(!empty($banner->clickurl)){
                            $image = cot_rc_link($url, $image, array('target' => '_blank'));
                        }
                        $ret['items'][$pid] = cot_rc('banner', array(
                            'banner' => $image
                        ));
                    }
                    break;

                case brs_model_Banner::TYPE_FLASH:
                    if(!empty($banner->file)){
                        $image = cot_rc('banner_flash', array(
                            'file'   => $banner->file,
                            'width'  => $banner->width,
                            'height' => $banner->height
                        ));
                        if(!empty($banner->clickurl)){
                            $image = cot_rc_link($url, $image, array('target' => '_blank'));
                        }
                        $ret['items'][$pid] = cot_rc('banner', array(
                            'banner' => $image
                        ));
                    }
                    break;

                case brs_model_Banner::TYPE_CUSTOM:
                    $ret['items'][$pid] = cot_rc('banner', array(
                        'banner' => $banner->customcode
                    ));
                    break;
            }

            $cnt++;
        }


        echo json_encode($ret);
        exit;
    }

}