<?php
defined('COT_CODE') or die('Wrong URL.');

if(empty($GLOBALS['db_banners'])) {
    cot::$db->registerTable('banners');
}

/**
 * Model class for the banners
 *
 * @package Banners
 * @subpackage DB
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright  © Portal30 Studio http://portal30.ru
 *
 * @method static brs_model_Banner getById($pk);
 * @method static brs_model_Banner fetchOne($conditions = array(), $order = '')
 * @method static brs_model_Banner[] find($conditions = array(), $limit = 0, $offset = 0, $order = '');
 *
 * @property int                $id
 * @property int                $type
 * @property string             $title
 * @property string             $category
 * @property string             $file
 * @property int                $width
 * @property int                $height
 * @property string             $alt
 * @property string             $customcode
 * @property string             $clickurl
 * @property string             $description
 * @property bool               $published
 * @property bool               $sticky
 * @property string             $publish_up
 * @property string             $publish_down
 * @property int                $imptotal       Макс. кол-во показов
 * @property int                $impressions    Сколько показано
 * @property string             $lastimp
 * @property int                $clicks
 * @property brs_model_Client   $client
 * @property int                $purchase_type
 * @property int                $track_impressions
 * @property int                $track_clicks
 * @property int                $sort
 * @property string             $created
 * @property int                $created_by
 * @property string             $updated
 * @property int                $updated_by
 *
 *
 * === Динамические свойства ===
 * @property string $categoryTitle    Название категории
 * @property string $clickPercentage  Процент кликов
 *
 * @property array $banner            Данные для вывода баннера
 *
 * @property bool $trackClicks        Вычисленное значение: вести ежедневную статистику кликов?
 * @property bool $trackImpressions   Вычисленное значение: вести ежедневную статистику показов?
 *
 */
class brs_model_Banner extends Som_Model_Abstract {

    // === Типы баннеров ===
    const TYPE_UNKNOWN = 0;
    const TYPE_IMAGE = 1;
    const TYPE_FLASH = 2;
    /**
     * Произвольный код
     */
    const TYPE_CUSTOM = 3;

    /**
     * @var Som_Model_Mapper_Abstract
     */
    protected  static $_db = null;
    protected  static $_tbname = '';
    protected  static $_primary_key = 'id';

    public static $fetchColumns = array();
    public static $fetchJoins = array();

    protected $currFile;

    /**
     * Static constructor
     */
    public static function __init($db = 'db'){
        static::$_tbname = cot::$db->banners;

        static::$fetchJoins[] = "LEFT JOIN ".brs_model_Client::tableName()." as cl ON ".static::$_tbname.".client=cl.id";
        static::$fetchColumns[] = "cl.track_impressions as client_track_impressions";
        static::$fetchColumns[] = "cl.track_clicks as client_track_clicks";

        parent::__init($db);
    }


    /**
     * @param mixed $data Array or Object - свойства
     *   в свойства заполнять только те поля, что есть в таблице + user_name
     */
    public function __construct($data = null) {
        parent::__construct($data);

        $this->currFile = $this->_data['file'];
    }


    public function getCategoryTitle() {
        global $structure;

        if(!empty($structure['brs']) && isset($structure['brs'][$this->_data['category']])) {
            return $structure['brs'][$this->_data['category']]['title'];
        }

        return '';
    }

    /**
     * Процент кликов от количества показов
     * @return int
     */
    public function getClickPercentage(){
        if($this->_data['impressions'] > 0) {
            return round($this->_data['clicks'] / $this->_data['impressions'], 2) * 100;
        }

        return 0;
    }

    /**
     * Вычисленное значение: вести ежедневную статистику кликов?
     * @return bool
     */
    public function getTrackClicks(){
        $trackClicks = false;
        if($this->_data['track_clicks'] == 1){
            $trackClicks = true;

        }elseif($this->_data['track_clicks'] == -1){
            if(intval($this->_data['client']) == 0){
                // Если не установлен клиент, берем настройки по-умолчанию
                if(cot::$cfg['brs']['track_clicks'] == 1) $trackClicks = true;

            }else{
                if($this->_extraData['client_track_clicks'] == 1){
                    $trackClicks = true;

                }elseif($this->_extraData['client_track_clicks'] == -1 && cot::$cfg['brs']['track_clicks'] == 1){
                    $trackClicks = true;
                }
            }
        }

        return $trackClicks;
    }

    /**
     * Вычисленное значение: вести ежедневную статистику показов?
     * @return bool
     */
    public function getTrackImpressions(){
        $track = false;
        if($this->_data['track_impressions'] == 1){
            $track = true;

        }elseif($this->_data['track_impressions'] == -1){
            if(empty($this->_data['client'])){
                // Если не установлен клиент, берем настройки по-умолчанию
                if(cot::$cfg['brs']['track_impressions'] == 1) $track = true;

            }else{
                if($this->_extraData['client_track_impressions'] == 1){
                    $track = true;

                }elseif($this->_extraData['client_track_impressions'] == -1 && cot::$cfg['brs']['track_impressions'] == 1) {
                    $track = true;
                }
            }
        }

        return $track;
    }

    /**
     * Засчитать показ
     */
    public function impress(){
        $this->_data['impressions'] += 1;
        $this->_data['lastimp'] = microtime(true);
        $this->save();

        // Ежедневная статистика
        if($this->trackImpressions){
            $trackDate = date('Y-m-d H', cot::$sys['now']).':00:00';

            $sql = "SELECT track_count FROM ".cot::$db->banner_tracks."
                WHERE type=1 AND banner={$this->_data['id']} AND date='{$trackDate}'";

            $count = cot::$db->query($sql)->fetchColumn();

            if ($count){
                // update count
                $data = array(
                    'track_count' => $count + 1
                );
                cot::$db->update(cot::$db->banner_tracks, $data,
                    "type=1 AND banner={$this->_data['id']} AND date='{$trackDate}'");

            }else{
                // insert new count
                $data = array(
                    'track_count' => 1,
                    'type' => 1,
                    'banner' => (int)$this->_data['id'],
                    'date' => $trackDate
                );
                cot::$db->insert(cot::$db->banner_tracks, $data);
            }
        }
    }

    /**
     * Засчитать клик
     */
    public function click(){
        $this->_data['clicks'] += 1;
        $this->save();

        // Ежедневная статистика
        if($this->trackClicks){
            $trackDate = date('Y-m-d H', cot::$sys['now']).':00:00';

            $sql = "SELECT `track_count` FROM ".cot::$db->banner_tracks."
                WHERE type=2 AND banner={$this->_data['id']} AND date='{$trackDate}'";

            $count = cot::$db->query($sql)->fetchColumn();

            if ($count){
                // update count
                $data = array(
                    'track_count' => $count + 1
                );
                cot::$db->update(cot::$db->banner_tracks, $data,
                    "type=2 AND banner={$this->_data['id']} AND date='{$trackDate}'");
            }else{
                // insert new count
                $data = array(
                    'track_count' => 1,
                    'type' => 2,
                    'banner' => (int)$this->_data['id'],
                    'date' => $trackDate
                );
                cot::$db->insert(cot::$db->banner_tracks, $data);
            }
        }
    }

    protected function beforeInsert(){
        $this->_data['created'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['created_by'] = cot::$usr['id'];

        $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['updated_by'] = cot::$usr['id'];

        return parent::beforeInsert();
    }

    protected function afterInsert(){
        cot_log("Added new banner # {$this->_data['id']} - {$this->_data['title']}", 'adm');

        // Обновить структуру
        $count = brs_model_Banner::count(array(array('category', $this->_data['category'])));
        static::$_db->update(cot::$db->structure, array('structure_count' => $count),
            "structure_area='brs' AND structure_code=?", $this->_data['category']);

        cot::$cache && cot::$cache->db->remove('structure', 'system');

        return parent::afterInsert();
    }

    protected function beforeUpdate(){
        $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['updated_by'] = cot::$usr['id'];

        // Проверка файла и удаление при необходимости
        if(!empty($this->_oldData['file']) && isset($this->_data['file']) && $this->_oldData['file'] !=  $this->_data['file'] &&
            file_exists($this->_oldData['file'])) {
            unlink($this->_oldData['file']);
        }

        return parent::beforeUpdate();
    }

    protected function afterUpdate(){
        global $structure;

        cot_log("Edited banner # {$this->_data['id']} - {$this->_data['title']}", 'adm');

        // Обновить структуру, если она изменилась
        if(!empty($this->_oldData['category'])) {
            $count = brs_model_Banner::count(array(array('category', $this->_data['category'])));
            static::$_db->update(cot::$db->structure, array('structure_count' => $count),
                "structure_area='brs' AND structure_code=?", $this->_data['category']);

            if(!empty($structure['brs'][$this->_oldData['category']])) {
                $count = brs_model_Banner::count(array(array('category', $this->_oldData['category'])));
                static::$_db->update(cot::$db->structure, array('structure_count' => $count),
                    "structure_area='brs' AND structure_code = ?", $this->_oldData['category']);
            }
            cot::$cache && cot::$cache->db->remove('structure', 'system');
        }

        return parent::afterUpdate();
    }

    protected function beforeDelete(){

        $id = $this->_data['id'];

        // Удалить файл
        if(file_exists($this->_data['file'])) unlink($this->_data['file']);
        if(!empty($this->_oldData['file']) && file_exists($this->_oldData['file'])) unlink($this->_oldData['file']);

        // Удалить статистику
        static::$_db->delete(cot::$db->banner_tracks, "banner={$id}");


        return parent::beforeDelete();
    }

    protected function afterDelete() {

        // Обновить структуру
        $count = brs_model_Banner::count(array(array('category', $this->_data['category'])));
        static::$_db->update(cot::$db->structure, array('structure_count' => $count),
            "structure_area='brs' AND structure_code=?", $this->_data['category']);

        cot::$cache && cot::$cache->db->remove('structure', 'system');
    }

    // === Методы для работы с шаблонами ===
    /**
     * Данные для вывода баннера
     * @return array - Массив
     *   'class'  - класс для контейнера
     *   'cache'  - включено ли кеширование
     *   'banner' - html код баннера
     */
    public function getBanner() {
        global $cache_ext;

        $ret = array(
            'class' => '',
            'cache' => 0,
            'banner' => ''
        );

        if (!empty($cache_ext) && cot::$usr['id'] == 0 && cot::$cfg['cache_' . $cache_ext]){
            // учесть кеширование - запрашивать баннер аяксом
            $ret['class'] = 'brs-loading';
            $ret['cache'] = 1;
            $image = cot_rc('banner_load', array(
                'width'  => $this->_data['width'],
                'height' => $this->_data['height'],
            ));
            $ret['banner'] = cot_rc('banner', array(
                'banner' => $image
            ));

        }else{
            // Вывод обычным образом
            $url = cot_url('brs', 'a=click&id='.$this->_data['id']);

            if(!empty($this->_data['file'])){
                $image = false;
                if($this->_data['type'] ==  brs_model_Banner::TYPE_IMAGE){
                    // расчитаем размеры картинки:
                    $w = $this->_data['width'];
                    $h = $this->_data['height'];
                    $image = cot_rc('banner_image', array(
                        'file'   => $this->_data['file'],
                        'alt'    => $this->_data['alt'],
                        'width'  => $w,
                        'height' => $h
                    ));

                }elseif($this->_data['type'] ==  brs_model_Banner::TYPE_FLASH){
                    $w = $this->_data['width'];
                    $h = $this->_data['height'];
                    $image = cot_rc('banner_flash', array(
                        'file'   => $this->_data['file'],
                        'width'  => $w,
                        'height' => $h
                    ));
                }

                if(!empty($image)){
                    if(!empty($this->_data['clickurl'])){
                        $image = cot_rc_link($url, $image, array('target' => '_blank'));
                    }
                    $ret['banner'] = cot_rc('banner', array(
                        'banner' => $image
                    ));
                }
            }

            if($this->_data['type'] ==  brs_model_Banner::TYPE_CUSTOM){
                $ret['banner'] = cot_rc('banner', array(
                    'banner' => $this->_data['customcode']
                ));
            }
        }

        return $ret;
    }


    /**
     * Returns banner tags for coTemplate
     *
     * @param brs_model_Banner|int $banner BaBanner object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     * @todo при включенном кеше, если в категории кол-во баннеров равно кол-ву выводимых - ajax не исползовать
     *       для показа баннеров, но в этом случае нужно чистить кеш при добавлении/редактировании баннера
     *       очищать весь кеш для большого сайта - накладно
     * @deprecated
     */
    public static function generateTags($banner, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L, $usr, $structure, $cache_ext;

        static $extp_first = null, $extp_main = null;
        static $cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('banners.tags.first');
            $extp_main = cot_getextplugins('banners.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( is_object($banner) && is_array($cache[$banner->ba_id]) ) {
            $temp_array = $cache[$banner->ba_id];
        }elseif (is_int($banner) && is_array($cache[$banner])){
            $temp_array = $cache[$banner];
        }else{
            if (is_int($banner) && $banner > 0){
                $banner = self::getById($banner);
            }
            if ($banner->ba_id > 0){
                $item_link = cot_url('admin', array('m'=>'other', 'p'=>'banners', 'a'=>'edit',
                                                    'id'=>$banner->ba_id));

                $temp_array = array(
                    'EDIT_URL' => $item_link,
                    'URL' => $banner->ba_clickurl,
                    'ID' => $banner->ba_id,
                    'TITLE' => htmlspecialchars($banner->ba_title),
                    'STICKY' => $banner->ba_sticky,
                    'STICKY_TEXT' => $banner->ba_sticky ? $L['Yes'] : $L['No'],
                    'CLIENT_TITLE' => htmlspecialchars($banner->client->bac_title),
                    'IMPTOTAL' => $banner->ba_imptotal,
                    'IMPTOTAL_TEXT' => ($banner->ba_imptotal > 0) ? $banner->ba_imptotal : $L['ba_unlimited'],
                    'IMPMADE' => $banner->impressions,
                    'CLICKS' => $banner->ba_clicks,
                    'CATEGORY' => $banner->ba_cat,
                    'CATEGORY_TITLE' => htmlspecialchars($structure['banners'][$banner->ba_cat]['title']),
                    'CLICKS_PERSENT' => ($banner->ba_impmade > 0) ?
                                        round($banner->ba_clicks / $banner->ba_impmade * 100 , 0)." %" : '0 %',
                    'WIDTH' => $banner->ba_width,
                    'HEIGHT' => $banner->ba_height,
                    'TYPE' => $banner->ba_type,
                    'PUBLISHED' => $banner->ba_published ? $L['Yes'] : $L['No'],
                    'CLASS' => '',
                    'CACHE' => 0

                );

                if (!empty($cache_ext) && $usr['id'] == 0 && $cfg['cache_' . $cache_ext]){
                    // учесть кеширование - запрашивать баннер аяксом
                    $temp_array['CLASS'] = 'banner-loading';
                    $temp_array['CACHE'] = 1;
                    $image = cot_rc('banner_load', array(
                        'width' => $banner->ba_width,
                        'height' => $banner->ba_height
                    ));
                    $temp_array['BANNER'] = cot_rc('banner', array(
                        'banner' => $image
                    ));
                }else{
                    // Вывод обычным образом
                    $url = cot_url('banners', 'a=click&id='.$banner->ba_id);

                    if(!empty($banner->ba_file)){
                        $image = false;
                        if($banner->ba_type ==  BaBanner::TYPE_IMAGE){
                            // расчитаем размеры картинки:
                            $w = $banner->ba_width;
                            $h = $banner->ba_height;
                            $image = cot_rc('banner_image', array(
                                'file' => $banner->ba_file,
                                'alt' => $banner->ba_alt,
                                'width' => $w,
                                'height' => $h
                            ));

                        }elseif($banner->ba_type ==  BaBanner::TYPE_FLASH){
                            $w = $banner->ba_width;
                            $h = $banner->ba_height;
                            $image = cot_rc('banner_flash', array(
                                'file' => $banner->ba_file,
                                'width' => $w,
                                'height' => $h
                            ));
                        }
                        if(!empty($image)){
                            if(!empty($banner->ba_clickurl)){
                                $image = cot_rc_link($url, $image, array('target' => '_blank'));
                            }
                            $temp_array['BANNER'] = cot_rc('banner', array(
                                'banner' => $image
                            ));
                        }
                    }
                    if($banner->ba_type ==  BaBanner::TYPE_CUSTOM){
                        $temp_array['BANNER'] = cot_rc('banner', array(
                            'banner' => $banner->ba_customcode
                        ));
                    }
                }


                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $cache[$banner->ba_id] = $temp_array;
            }else{
                // Диалога не существует
            }
        }
        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

    public static function fieldList(){
        $nullDate	= date('Y-m-d H:i:s', 0);   // 1970-01-01 00:00:00
        $fields = array (
            'id' =>
                array (
                    'type' => 'int',
                    'description' => 'id',
                    'primary' => true,
                ),
            'type' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => cot::$L['Type'],
                ),
            'title' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['Title'],
                ),
            'category' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['Category'],
                ),
            'file' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['Image'],
                ),
            'width' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => cot::$L['brs_width'],
                ),
            'height' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' =>  cot::$L['brs_height'],
                ),
            'alt' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['brs_alt'],
                ),
            'customcode' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['brs_custom_code'],
                ),
            'clickurl' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['brs_click_url'],
                ),
            'description' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['Description'],
                ),
            'published' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['brs_published'],
                ),
            'sticky' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['brs_sticky'],
                ),
            'publish_up' =>
                array (
                    'type' => 'datetime',
                    'default' => $nullDate,
                    'description' => cot::$L['brs_publish_up'],
                ),
            'publish_down' =>
                array (
                    'type' => 'datetime',
                    'default' => $nullDate,
                    'description' => cot::$L['brs_publish_down'],
                ),
            'imptotal' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => cot::$L['brs_imptotal'],
                ),
            'impressions' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => cot::$L['brs_impressions'],
                ),
            'lastimp' =>
                array (
                    'type' => 'double',
                    'default' => 0,
                    'description' => '',
                ),
            'clicks' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => cot::$L['brs_clicks_all'],
                ),
            'client' =>
                array(
                    'type'        => 'link',
//                    'nullable'    => false,
                    'default'     => 0,
                    'description' => cot::$L['brs_client'],
                    'link'        =>
                        array(
                            'model'    => 'brs_model_Client',
                            'relation' => Som::TO_ONE_NULL,
                            'label'    => 'title',
                        ),
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
            'purchase_type' =>
                array (
                    'type' => 'tinyint',
                    'default' => -1,
                    'description' => cot::$L['brs_purchase_type'],
                ),
            'sort' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => '',
                ),
            'created' =>
                array (
                    'type' => 'datetime',
                    'default' => date('Y-m-d H:i:s', cot::$sys['now']),
                    'description' => '',
                ),
            'created_by' =>
                array (
                    'type' => 'int',
                    'default' => cot::$usr['id'],
                    'description' => '',
                ),
            'updated' =>
                array (
                    'type' => 'datetime',
                    'default' => date('Y-m-d H:i:s', cot::$sys['now']),
                    'description' => '',
                ),
            'updated_by' =>
                array (
                    'type' => 'int',
                    'default' => cot::$usr['id'],
                    'description' => '',
                ),
        );
        return $fields;
    }
}

// Class initialization for some static variables
brs_model_Banner::__init();