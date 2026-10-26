<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class DummyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Jika sedang berjalan di artisan / console dan tidak ada user session, jangan filter
        if (app()->runningInConsole() && !Auth::check()) {
            return;
        }

        $isDummy = Auth::check() ? (bool) Auth::user()->is_dummy : false;
        $builder->where($model->getTable() . '.is_dummy', $isDummy);
    }
}
