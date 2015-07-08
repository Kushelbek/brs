<?php
/**
 * Cotonti Banners Module
 * Banner rotation with statistics
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

/**
 * Module Title & Subtitle
 */
$L['info_name'] = 'Баннеры';
$L['info_desc'] = 'Модуль показа баннеров со статистикой.';

$L['brs_alt'] = "Альтернативный текст";
$L['brs_banner_edit'] = "Редактировать баннер";
$L['brs_banner_new'] = "Создание баннера";
$L['brs_banners'] = "Баннеры";
$L['brs_category_no'] = "Для создания баннера нужно создать хотябы одну категорию";
$L['brs_clear_tracks_param'] = "Удалить статистику по выбранным параметрам";
$L['brs_clear_tracks_param_confirm'] = "Данное действие очистит статистику в соотвествии с выбранными фильтрами";
$L['brs_click_url'] = "Url для перехода";
$L['brs_clicks'] = "Клики";
$L['brs_clicks_all'] = "Всего кликов";
$L['brs_client'] = "Клиент";
$L['brs_client_default'] = "Использовать параметры клиента по умолчанию";
$L['brs_client_deleted'] = 'Клиент «%1$s» удален';
$L['brs_client_edit'] = "Редактировать клиента";
$L['brs_client_new'] = "Создание клиента";
$L['brs_clients'] = "Клиенты";
$L['brs_custom_code'] = "Произвольный код";
$L['brs_deleted'] = 'Баннер «%1$s» удален';
$L['brs_deleted_no'] = 'ничего не удалено';
$L['brs_deleted_records'] = 'Удалено %1$s записей';
$L['brs_extrainfo'] = "Дополнительная информация";
$L['brs_from'] = "c";
$L['brs_height'] = "Высота";
$L['brs_impressions'] = "Общее число показов";
$L['brs_imptotal'] = "Максимальное количество показов";
$L['brs_impressions'] = "Показы";
$L['brs_for_file_only'] = "только для изображения / flash";
$L['brs_published'] = "Опубликовано";
$L['brs_publish_down'] = "Завершение публикации";
$L['brs_publish_up'] = "Начало публикации";
$L['brs_purchase_type'] = "Тип оплаты";
//$L['brs_saved'] = "Сохранено";
$L['brs_sticky'] = "Прикреплен";
$L['brs_sticky_tip'] = "Определяет - является ли баннер 'прикреплённым'. Если один или несколько баннеров в категории являются 'прикреплёнными', они будут показаны первыми по отношению к прочим баннерам. К примеру, если два баннера в категории являются 'прикреплёнными', а третий - нет, то третий баннер не будет показан, если в настройках модуля, отображающего баннеры, выставлена настройка 'Прикреплённые, случайно'. Будут показаны только два первых баннера.";
$L['brs_to'] = "по";
$L['brs_track_clicks'] = "Отслеживать клики";
$L['brs_track_clicks_hint'] = "Регистрировать ежедневное число кликов по баннеру.";
$L['brs_track_impressions'] = "Отслеживать показы";
$L['brs_track_impressions_hint'] = "Регистрировать ежедневное число показов (просмотров) баннеров.";
$L['brs_tracks'] = "Статистика";
$L['brs_type_file'] = "Изображение / Flash";
$L['brs_unlimited'] = "Неограничено";
$L['brs_width'] = "Ширина";


/**
 * purchase type
 */
$L['brs_pt_yearly']     = "Ежегодно";
$L['brs_pt_monthly']    = "Ежемесячно";
$L['brs_pt_weekly']     = "Еженедельно";
$L['brs_pt_daily']      = "Ежедневно";

/**
 * Error, Message
 */
$L['brs_err_client_not_found'] = 'Клиент не найден';
$L['brs_err_inv_file_type'] = "Недопустимый тип файла";
$L['brs_err_not_found'] = 'Баннер не найден';

/**
 * Module config
 */
$L['cfg_purchase_type'] = array($L['brs_purchase_type'], "Эти параметры применяются для всех клиентов, но могут быть
    переопределены для некоторых из них индивидуально.");
$L['cfg_purchase_type_params'] = array(
   $L['brs_unlimited'],
   $L['brs_pt_yearly'],
   $L['brs_pt_monthly'],
   $L['brs_pt_weekly'],
   $L['brs_pt_daily']
);
$L['cfg_track_impressions'] = $L['brs_track_impressions'];
$L['cfg_track_impressions_hint'] = $L['brs_track_impressions_hint'];
$L['cfg_track_clicks'] = $L['brs_track_clicks'];
$L['cfg_track_clicks_hint'] = $L['brs_track_clicks_hint'];