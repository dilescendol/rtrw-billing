<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('current_tenant_id')) {
            $tid = app('current_tenant_id');
            if ($tid) {
                $builder->where($model->getTable().'.tenant_id', $tid);
            }
        } elseif (auth()->check() && auth()->user()->tenant_id) {
            $builder->where($model->getTable().'.tenant_id', auth()->user()->tenant_id);
        }
    }
}
