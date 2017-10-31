<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $with = ['options'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class);
    }

    public function addOption(AttributeOption $option)
    {
        $this->options()->save($option);
    }

    public function hasValue($value, $exceptId = null)
    {
        return $this->options()->where(function ($query) use ($value, $exceptId) {
            $query->where('value', $value);

            if ($exceptId) {
                $query->where('id', '<>', $exceptId);
            }
        })->exists();
    }


}
