<?php
/**
 * Cotonti Banners Module
 * Banner rotation with statistics
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

$R['banner_image_admin'] = '<img src="{$file}" alt="{$alt}" style="width:{$width}px; height:{$height}px" />';
$R['banner_image'] = '<img src="{$file}" alt="{$alt}" style="width:{$width}px; height:{$height}px" />';
$R['banner_flash_admin'] = '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{$width}" height="{$height}" style="display:block;margin:auto;">
    <param name="movie" value="{$file}" />
    <param name="wmode" value="transparent">
    <!--[if !IE]>-->
    <object type="application/x-shockwave-flash" data="{$file}" width="{$width}" height="{$height}">
        <param name="wmode" value="transparent">
    <!--<![endif]-->
    <!--[if !IE]>-->
    </object>
    <!--<![endif]-->
</object>';
$R['banner_flash'] = '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{$width}" height="{$height}" style="display:block;margin:auto;">
    <param name="movie" value="{$file}" />
    <param name="wmode" value="transparent">
    <!--[if !IE]>-->
    <object type="application/x-shockwave-flash" data="{$file}" width="{$width}" height="{$height}">
        <param name="wmode" value="transparent">
    <!--<![endif]-->
    <!--[if !IE]>-->
    </object>
    <!--<![endif]-->
</object>';
$R['admin_banner'] = '<div class="admin-banner-preview marginbottom10">{$banner}</div>';
$R['banner'] = '{$banner}';
$R['banner_load'] = '<div style="width: {$width}px; height: {$height}px; line-height: {$height}px; text-align: center; vertical-align: middle; overflow: hidden"><img src="/images/spinner.gif"></div>';
$R['banner_yes'] = '<span class="text-success glyphicon glyphicon-ok"></span>';
$R['banner_no'] = '<span class="glyphicon glyphicon-minus"></span>';