<?php
/**
 * Cotonti Banners Module
 * Banners list template
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

/** @var brs_model_Banner[] $items */
$items = $this->items;

?>
<div class="text-right">
    <a href="<?=cot_url('admin', array('m'=>'brs', 'a'=>'edit'))?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>
        <?=cot::$L['Add']?></a>
</div>
<?php

// Filters ?>
<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=cot::$L['Filters']?></div>
    <div class="panel-body">
        <form method="get" action="<?=(cot_url('admin', array('m'=>'brs')))?>" class="form-inline">
            <?=$this->filterForm['hidden']?>


            <div class="form-group">
                <label><?=$this->filterForm['title']['label']?>: </label>
                <?=$this->filterForm['title']['element']?>
            </div>

            <div class="form-group">
                <label><?=$this->filterForm['category']['label']?>: </label>
                <?=$this->filterForm['category']['element']?>
            </div>

            <div class="form-group">
                <label><?=$this->filterForm['client']['label']?>: </label>
                <?=$this->filterForm['client']['element']?>
            </div>

            <div class="form-group">
                <label><?=$this->filterForm['published']['label']?>: </label>
                <?=$this->filterForm['published']['element']?>
            </div>

            <div class="margintop10">
                <div class="form-group">
                    <label><?=$this->filterForm['sort']['label']?>: </label>
                    <?=$this->filterForm['sort']['element']?>
                </div>

                <div class="form-group">
                    <?=$this->filterForm['way']['element']?>
                </div>

            </div>

            <div class="margintop10">
                <button type="submit" class="btn btn-default"><span class="fa fa-filter"></span> <?=cot::$L['Show']?></button>
                <a href="<?=cot_url('admin', array('m'=>'brs'))?>" class="btn btn-default"><span class="fa fa-remove"></span></a>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <?php if(!empty($items)) { ?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th></th>
                    <th><?=cot::$L['Title']?></th>
                    <th><?=cot::$L['Category']?></th>
                    <th><?=cot::$L['brs_sticky']?></th>
                    <th><?=cot::$L['brs_published']?></th>
                    <th><?=cot::$L['brs_client']?></th>
                    <th><?=cot::$L['brs_impressions']?></th>
                    <th><?=cot::$L['brs_clicks_all']?></th>
                    <th>ID</th>
                    <th></th>
                </tr>
                </thead>
                <?php
                $i = $this->fistNumber;
                foreach($items as $itemRow) {
                    $editUrl = cot_url('admin', array('m'=>'brs', 'a'=>'edit', 'id'=>$itemRow->id));
                    $delUrlParams = $this->urlParams;
                    $delUrlParams['a']= 'delete';
                    $delUrlParams['id'] = $itemRow->id;
                    if(!empty($this->pagenav['page'])) $delUrlParams['d'] = $this->pagenav['page'];
                    $deleteUrl = cot_confirm_url(cot_url('admin', $delUrlParams, '', true));
                    ?>
                <tr>
                    <td><?=$i?></td>
                    <td><a href="<?=$editUrl?>"><?=htmlspecialchars($itemRow->title)?></a></td>
                    <td><?=htmlspecialchars($itemRow->categoryTitle)?></td>
                    <td><?=brs_YesNo($itemRow->sticky)?></td>
                    <td><?=brs_YesNo($itemRow->published)?></td>
                    <td>
                        <?php
                        $client = $itemRow->rawValue('client');
                        if(!empty($client) && isset($this->clients[$client])) {
                            $clientEditUrl = cot_url('admin', array('m'=>'brs', 'n'=>'client', 'a'=>'edit', 'id'=>$client)); ?>
                            <a href="<?=$clientEditUrl?>"><?=htmlspecialchars($this->clients[$client])?></a>
                        <?php } else {
                            echo brs_YesNo(false);
                        } ?>
                    </td>
                    <td>
                        <?=$itemRow->impressions?> /
                        <?php if($itemRow->imptotal > 0) {
                            echo $itemRow->imptotal;
                        } else {
                            echo cot::$L['brs_unlimited'];
                        }?>
                    </td>
                    <td>
                        <?=$itemRow->clicks?> /
                        <?=$itemRow->clickPercentage?> %
                    </td>
                    <td><?=$itemRow->id?></td>
                    <td>
                        <?php // TODO ссылка на статистику ?>

                        <a href="<?=$editUrl?>" class="btn btn-xs btn-default" title="<?=cot::$L['Edit']?>" data-toggle="tooltip">
                            <span class="fa fa-edit"></span></a>

                        <a href="<?=$deleteUrl?>" class="btn btn-xs btn-danger confirmLink" title="<?= cot::$L['Delete'] ?>"
                           data-toggle="tooltip"><span class="fa fa-trash-o"></span></a>
                    </td>
                </tr>
                <?php
                $i++;
                } ?>
            </table>
            <?php

            if(!empty($this->pagenav['main'])) { ?>
                <div class="text-right">
                    <nav>
                        <ul class="pagination" style="margin: 0"><?=$this->pagenav['prev']?><?=$this->pagenav['main']?><?=$this->pagenav['next']?></ul>
                    </nav>
                    <span class="help-block">
                        <?=cot::$L['Total']?>: <?=$this->pagenav['entries']?>, <?=cot::$L['Onpage']?>: <?=$this->pagenav['onpage']?>
                    </span>
                </div>
            <?php }

        } else { ?>
            <h4 class="text-muted text-center"><?=cot::$L['None']?></h4>
        <?php } ?>
    </div>
</div>
<div class="text-right">
    <a href="<?=cot_url('admin', array('m'=>'brs', 'a'=>'edit'))?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>
        <?=cot::$L['Add']?></a>
</div>