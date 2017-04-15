<?php
/**
 * Banners main admin template
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) Portal30 Studio http://portal30.ru
 */

if(class_exists('cpanel')) cpanel::$useDefaultPanel = false;
?>
<div class="button-toolbar">
    <a title="<?=cot::$L['Configuration']?>" href="<?=cot_url('admin', 'm=config&n=edit&o=module&p=brs')?>"
       class="btn btn-default marginbottom10"><span class="fa fa-wrench"></span> <?=cot::$L['Configuration']?></a>

    <a title="<?=cot::$L['Categories']?>" href="<?=cot_url('admin', array('m'=>'structure', 'n'=>'brs'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-sitemap"></span> <?=cot::$L['Categories']?></a>

    <a href="<?=cot_url('admin', array('m'=>'brs'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-picture-o"></span> <?=cot::$L['brs_banners']?></a>

    <a href="<?=cot_url('admin', array('m'=>'brs', 'n'=>'client'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-users"></span> <?=cot::$L['brs_clients']?></a>

    <a href="<?=cot_url('admin', array('m'=>'brs', 'n'=>'track'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-bar-chart"></span> <?=cot::$L['brs_tracks']?></a>
</div>

<?php
// Error and message handling
echo $this->displayMessages();

echo $this->content;