<?php

namespace A17\CmsToolkit\Models;

use A17\CmsToolkit\Models\Behaviors\HasMedias;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Block extends BaseModel
{
    use HasMedias;

    public $timestamps = false;

    protected $fillable = [
        'blockable_id',
        'blockable_type',
        'position',
        'content',
        'type',
        'child_key',
        'parent_id',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function blockable()
    {
        return $this->morphTo();
    }

    public function input($name)
    {
        return $this->content[$name] ?? null;
    }

    public function translatedInput($name, $forceLocale = null)
    {
        $value = $this->content[$name] ?? null;
        $locale = $forceLocale ?? app()->getLocale();
        return $value[$locale] ?? null;
    }
}
