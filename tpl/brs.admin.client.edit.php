<?php
/**
 *  Client edit template
 *
 * @package Banners
 * @author Alex
 * @copyright Portal30 2013 http://portal30.ru
 */

/** @var brs_model_Client $item */
$item = $this->item;

$labelClass = 'col-xs-12 col-md-3';
$elementClass = 'col-xs-12 col-md-9';

$formElements = $this->formElements;
unset($this->formElements);
?>
<div class="panel panel-default">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <form action="<?=$this->formAction?>" enctype="multipart/form-data" method="post" name="banner-form"
              class="form-horizontal" role="form">
            <?php
            echo $formElements['hidden']['element'];
            foreach($formElements as $fldName => $element) {
                if($fldName == 'hidden') continue;

                $elClass = $elementClass;
                if(empty($element['label'])) $elClass .= ' col-md-offset-3';

                ?>
                <div class="form-group <?=cot_formGroupClass($fldName)?>">
                    <?php if(!empty($element['label'])) { ?>
                        <label class="<?=$labelClass?> control-label">
                            <?=$element['label']?>
                            <?php if(!empty($element['required'])) echo ' *';?>
                            :
                        </label>
                    <?php }

                    ?>
                    <div class="<?=$elClass?>">
                        <?php
                        echo $element['element'];
                        if(isset($element['hint']) && $element['hint'] != '') { ?>
                            <span class="help-block"><?=$element['hint']?></span>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button type="submit" class="btn btn-primary"><span class="fa fa-floppy-o"></span>
                        <?=cot::$L['Save']?></button>

                    <?php if($item->id > 0 && !empty($this->deleteUrl)) { ?>
                        <a href="<?=$this->deleteUrl?>" class="btn btn-danger confirmLink">
                            <span class="fa fa-trash-o"></span> <?=cot::$L['Delete']?></a>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
</div>

