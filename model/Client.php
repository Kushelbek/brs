<?php
defined('COT_CODE') or die('Wrong URL.');

if(empty($GLOBALS['db_banner_clients'])) {
    cot::$db->registerTable('banner_clients');
}

/**
 * Cotonti Banners Module
 * Model class for the clients
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 *
 * @method static brs_model_Client getById($pk);
 * @method static brs_model_Client fetchOne($conditions = array(), $order = '')
 * @method static brs_model_Client[] find($conditions = array(), $limit = 0, $offset = 0, $order = '');
 *
 * @property int        $id
 * @property string     $title
 * @property string     $email
 * @property string     $extrainfo
 * @property bool       $published
 * @property int        $purchase_type
 * @property int        $track_clicks
 * @property string     $track_impressions
 *
 */
class brs_model_Client extends Som_Model_ActiveRecord
{
    // === Типы оплаты ===
    const PURCHASE_DEFAULT = -1;
    const PURCHASE_UNLIMITED = 1;
    const PURCHASE_YEARLY = 2;
    const PURCHASE_MONTHLY = 3;
    const PURCHASE_WEEKLY = 4;
    const PURCHASE_DAILY = 5;

    /**
     * @var Som_Model_Mapper_Abstract
     */
    protected  static $_db = null;
    protected  static $_tbname = '';
    protected  static $_primary_key = 'id';

    public static $fetchColumns = array();
    public static $fetchJoins = array();

    /**
     * Static constructor
     * @param string $db Data base connection config name
     */
    public static function __init($db = 'db'){
        static::$_tbname = cot::$db->banner_clients;
        parent::__init($db);
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns client tags for coTemplate
     *
     * @param brs_model_Client|int $client
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     *
     * @deprecated
     */
    public static function generateTags($client, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L, $usr;

        static $extp_first = null, $extp_main = null;
        static $cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('banners.client.tags.first');
            $extp_main = cot_getextplugins('banners.client.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( is_object($client) && is_array($cache[$client->bac_id]) ) {
            $temp_array = $cache[$client->bac_id];
        }elseif (is_int($client) && is_array($cache[$client])){
            $temp_array = $cache[$client];
        }else{
            if (is_int($client) && $client > 0){
                $client = self::getById($client);
            }
            $purchase = array(
                BaClient::PURCHASE_DEFAULT => $L['Default'],
                BaClient::PURCHASE_UNLIMITED => $L['ba_unlimited'],
                BaClient::PURCHASE_YEARLY => $L['ba_pt_yearly'],
                BaClient::PURCHASE_MONTHLY => $L['ba_pt_monthly'],
                BaClient::PURCHASE_WEEKLY => $L['ba_pt_weekly'],
                BaClient::PURCHASE_DAILY => $L['ba_pt_daily']
            );
            $temp_array = array();
            if ($client->bac_id > 0){
                $item_link = cot_url('admin', array('m'=>'other', 'p'=>'banners', 'n'=>'clients', 'a'=>'edit',
                                                        'id'=>$client->bac_id));
                $temp_array = array(
                    'URL' => $item_link,
                    'ID' => $client->bac_id,
                    'TITLE' => htmlspecialchars($client->bac_title),
                    'PUBLISHED' => $client->bac_published ? $L['Yes'] : $L['No'],
                    'PURCHASE' => $client->bac_purchase_type,
                    'PURCHASE_TEXT' => $purchase[$client->bac_purchase_type],
                );

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $cache[$client->bac_id] = $temp_array;
            }else{
                // Клиента не существует
            }
        }
        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

    public static function fieldList()
    {
        $fields = array(
            'id' =>
                array(
                    'type' => 'int',
                    'description' => 'id',
                    'primary' => true,
                ),
            'title' =>
                array(
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['Title'],
                ),
            'email' =>
                array(
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['Email'],
                ),
            'extrainfo' =>
                array(
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['brs_extrainfo'],
                ),
            'published' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['brs_published'],
                ),
            'purchase_type' =>
                array (
                    'type' => 'tinyint',
                    'default' => -1,
                    'description' => cot::$L['brs_purchase_type'],
                ),
            'track_clicks' =>
                array (
                    'type' => 'tinyint',
                    'default' => -1,
                    'description' => cot::$L['brs_track_clicks'],
                ),
            'track_impressions' =>
                array (
                    'type' => 'tinyint',
                    'default' => -1,
                    'description' => cot::$L['brs_track_impressions'],
                ),
        );
        return $fields;
    }
}

// Class initialization for some static variables
brs_model_Client::__init();