<?php

namespace WongDoody\PhpUmlCrafter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \WongDoody\PhpUmlCrafter\PhpUmlCrafter
 */
class PhpUmlCrafter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \WongDoody\PhpUmlCrafter\PhpUmlCrafter::class;
    }
}
