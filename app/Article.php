<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
    use AuthorTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author_id',
        'title',
        'content',
        'notification',
        'solution_id',
        'pin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'author_id',
        'solution_id',
        'notification',
        'deleted_at',
        'pin',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'author',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    /* Relationships */

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function solution()
    {
        return $this->hasOne(Comment::class, 'id', 'solution_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    /* Query Scope */

    public function scopeNoComment($query)
    {
        return $query->has('comments', '<', 1);
    }

    public function scopeNotSolved($query)
    {
        return $query->whereNull('solution_id');
    }

    /* Helpers */

    public function isNotice()
    {
        return $this->pin ? true : false;
    }

    public function etag($cacheKey = null)
    {
        $etag = $this->getTable() . $this->getKey();

        if ($this->usesTimestamps()) {
            $etag .= $this->updated_at->timestamp;
        }

        return md5($etag.$cacheKey);
    }
}
