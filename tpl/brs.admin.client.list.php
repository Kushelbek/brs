<?php
/**
 * Clients list template
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

/** @var brs_model_Client[] $items */
$items = $this->items;

?>
<div class="text-right">
    <a href="<?=cot_url('admin', array('m'=>'brs', 'n' => 'client', 'a'=>'edit'))?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>
        <?=cot::$L['Add']?></a>
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
                    <th><?=cot::$L['brs_purchase_type']?></th>
                    <th><?=cot::$L['brs_published']?></th>
                    <th>ID</th>
                    <th></th>
                </tr>
                </thead>
                <?php
                $i = $this->fistNumber;
                foreach($items as $itemRow) {
                $editUrl = cot_url('admin', array('m'=>'brs', 'n'=>'client', 'a'=>'edit', 'id'=>$itemRow->id));
                $delUrlParams = $this->urlParams;
                $delUrlParams['a']= 'delete';
                $delUrlParams['id'] = $itemRow->id;
                if(!empty($this->pagenav['page'])) $delUrlParams['d'] = $this->pagenav['page'];
                $deleteUrl = cot_confirm_url(cot_url('admin', $delUrlParams, '', true));
                ?>
                <tr>
                    <td><?=$i?></td>
                    <td><a href="<?=$editUrl?>"><?=htmlspecialchars($itemRow->title)?></a></td>
                    <td><?=$this->purchase[$itemRow->rawValue('purchase_type')]?></td>
                    <td><?=brs_YesNo($itemRow->published)?></td>
                    <td><?=$itemRow->id?></td>
                    <td>
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
    <a href="<?=cot_url('admin', array('m'=>'brs', 'n' => 'client', 'a'=>'edit'))?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span>
        <?=cot::$L['Add']?></a>
</div>