<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function isValidDate(?string $date): bool
    {
        if (empty($date)) {
            return false;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
