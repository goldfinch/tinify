<?php

namespace Goldfinch\Tinify\Extensions;

use SilverStripe\Core\Extension;

class TinifyImageExtension extends Extension
{
    private static $allowed_actions = [];

    private static $db = [
        'Tinified' => 'Boolean',
    ];
}
