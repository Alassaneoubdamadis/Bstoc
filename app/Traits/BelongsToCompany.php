<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    public function initializeBelongsToCompany(): void
    {
        $this->mergeFillable(['company_id']);
    }

    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            $companyId = current_company_id();
            if ($companyId) {
                $builder->where($builder->getModel()->getTable().'.company_id', $companyId);

                return;
            }

            if (! app()->runningInConsole()) {
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->company_id) && current_company_id()) {
                $model->company_id = current_company_id();
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
