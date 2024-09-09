<?php

namespace EveryWidget\Extension;

use EveryWidget\Model\EveryWidget;
use SilverStripe\ORM\DataExtension;


/**
 * This class defines the File model, its relations and its permissions
 * 
 * <b>Properties</b>
 * 
 * @property string $Slug Unique Identifier
 * @property datetime $DeletionDate File deletion date
 * 
 */

class EveryWidgetDataStoreExtension extends DataExtension  {

  private static $has_many = [
        'Widgets' => EveryWidget::class
    ];

}
