<?php
/**
 * Cotonti Banners Module
 * Show banner template
 *
 * @package Banners
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

/** @var brs_model_Banner[] $items */
$items = $this->items;

if(!empty($items)) {
    $i = 0;
    foreach ($items as $itemRow) {
        $i++;
        $banner = $itemRow->getBanner();
        $class = 'brs brs-'.$itemRow->category;
        if(!empty($banner['class'])) $class .= ' '.$banner['class'];

        $attrs = '';
        if($banner['cache']) {
            $attrs .= ' data-category="'.$itemRow->category.'"';
            $attrs .= ' data-order="'.$this->order.'"';
            if(!empty($this->client)) $attrs .= ' data-client="'.$this->client.'"';
        }

        $style = '';
        if($itemRow->width  > 0) $style .= " width: {$itemRow->width}px;";
        if($itemRow->height > 0) $style .= " height: {$itemRow->height}px;";
        if($itemRow->type  == 2) $style .= " cursor:pointer !important;";

        $style = trim($style);
        if(!empty($style)) $style=' style="'.$style.'"';
        ?>
        <div id="brs_<?=$itemRow->number?>" class="<?=$class?>"<?=$attrs.$style ?>>
            <?=$banner['banner']?>
        </div>
<?php }
}