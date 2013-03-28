<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * @file    default.php
 * @author  Arie W. Subagja <arie @ malam.or.id>
 */

/* @var $items array */

?><ul <?php echo HTML::attributes($attributes); ?>><?php

foreach ($items as $item)
{
    /* @var $item Mymenu_Item */

    ?>
    <li <?php echo HTML::attributes($item->attributes()); ?>>
        <?php echo HTML::anchor($item->url(), $item->title()); ?>
        <?php if ($item->has_children()) echo $item->children()->render(); ?>
    </li><?php
}

?></ul>