<?php

namespace App\Components;

use Phalcon\Escaper;

class Myescaper
{
    public function sanitize($value) {
        $escaper = new Escaper();
        return $escaper->escapeHtml($value);
    }
}