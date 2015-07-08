<?php
/**
 * Cotonti Banners Module
 * Admin statistic template
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

//var_dump_($this->items);

// Filters ?>
<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=cot::$L['Filters']?></div>
    <div class="panel-body">
        <form method="get" action="<?=(cot_url('admin', array('m'=>'brs', 'n'=>'track')))?>" class="form-inline">
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
                <label><?=$this->filterForm['type']['label']?>: </label>
                <?=$this->filterForm['type']['element']?>
            </div>

            <div class="margintop10">
                <div class="form-group">
                    <label><?=cot::$L['Date']?> <?=$this->filterForm['date_from']['label']?>: </label>
                    <?=$this->filterForm['date_from']['element']?>
                </div>

                <div class="form-group">
                    <label><?=$this->filterForm['date_to']['label']?>: </label>
                    <?=$this->filterForm['date_to']['element']?>
                </div>
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
                <a href="<?=cot_url('admin', array('m'=>'brs', 'n'=>'track'))?>" class="btn btn-default"><span class="fa fa-remove"></span></a>

                <button id="clearStats" class="btn btn-danger pull-right" name="a" value="clear" onclick="return confirm('<?=cot::$L['brs_clear_tracks_param_confirm']?>')">
                    <span class="fa fa-trash-o"></span> <?=cot::$L['brs_clear_tracks_param']?></button>
                <div class="clearfix"></div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <?php if(!empty($this->items)) { ?>
        <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th><?=cot::$L['Date']?></th>
                <th><?=cot::$L['Title']?></th>
                <th><?=cot::$L['brs_client']?></th>
                <th><?=cot::$L['Type']?></th>
                <th><?=cot::$L['Count']?></th>
            </tr>
            </thead>
            <?php
            $i = $this->fistNumber;
            foreach($this->items as $itemRow) {
                $bannerEditUrl = cot_url('admin', array('m'=>'brs', 'a'=>'edit', 'id'=>$itemRow['banner']));
            ?>
                <tr>
                    <td><?=$i?></td>
                    <td>
                        <?php if(!empty($itemRow['date'])) {
                            echo cot_date('datetime_medium', cot_date2stamp($itemRow['date'], 'auto'));
                        } else {
                            echo brs_YesNo(false);
                        } ?>
                    </td>
                    <td>
                        <a href="<?=$bannerEditUrl?>"><?=htmlspecialchars($itemRow['title'])?></a>
                        <div class="text-muted"><?=htmlspecialchars($itemRow['categoryTitle'])?></div>
                    </td>
                    <td>
                        <?php if($itemRow['client_id'] > 0) {
                            $clientEditUrl = cot_url('admin', array('m'=>'brs', 'n'=>'client', 'a'=>'edit', 'id'=>$itemRow['client_id']));
                            ?>
                            <a href="<?=$clientEditUrl?>"><?=htmlspecialchars($itemRow['client_title'])?></a>
                        <?php } else {
                            echo brs_YesNo(false);
                        } ?>
                    </td>
                    <td><?=$itemRow['track_typeTitle']?></td>
                    <td><?=$itemRow['track_count']?></td>
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

