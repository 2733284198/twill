<?php

namespace A17\CmsToolkit\Models;

use A17\CmsToolkit\Models\Behaviors\HasPresenter;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;

abstract class Model extends BaseModel implements TaggableInterface
{
    use HasPresenter, SoftDeletes, TaggableTrait;

    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();
        static::setTagsModel(Tag::class);
    }

    public function scopePublished($query)
    {
        return $query->wherePublished(true);
    }

    public function isNotLockedByCurrentUser()
    {
        if ($this->isLockable()) {
            if ($this->lockedBy() != null && $this->lockedBy()->id != Auth::user()->id) {
                return true;
            }
        }

        return false;
    }

    public function isLockedByCurrentUser()
    {
        if ($this->isLockable()) {
            if ($this->lockedBy() != null && $this->lockedBy()->id == Auth::user()->id) {
                return true;
            }
        }

        return false;
    }

    public function isLockable()
    {
        if (classHasTrait(get_class($this), 'A17\CmsToolkit\Models\Behaviors\HasLock')) {
            return true;
        }

        return false;
    }
}
