<?php

namespace App\Models\Traits;

use App\Models\Scopes\DummyScope;
use Illuminate\Support\Facades\Auth;

trait HasDummyScope
{
    public static function bootHasDummyScope(): void
    {
        static::addGlobalScope(new DummyScope);

        static::creating(function ($model) {
            if (!isset($model->is_dummy) || $model->is_dummy === null) {
                $model->is_dummy = Auth::check() ? (bool) Auth::user()->is_dummy : false;
            }
        });
    }
}
