<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class Course extends Model
{
    protected $fillable = ['name'];
	/**
     * The attributes that should be Converted
     *
     * @var array
     */
    protected $casts = [
        'is_public' => 'boolean'
    ];
    /**
     * Course Search
     *
     * @param Builder $query  query
     * @param Request $search search
     *
     * @return mixed
     */
    public function scopeSearch($query, Request $search)
    {
        $query->where(
            function ($query) use ($search) {

                if (($keyword = $search->get('keyword')) !== false) {
                    $query->where("name", "LIKE", "%$keyword%");
                }
            }
        );

        $sortable_fields = ['name','created_at','active.name'];
        $sort_by = 'name';

        if (in_array($search->get('sortBy'), $sortable_fields)) {
            $sort_by = $search->get('sortBy');
        }
        $sort_order = [
            'ascending' => 'ASC',
            'descending' => 'DESC',
        ];

        $order = $sort_order[$search->get('order', 'ascending')];

        return $query->orderBy($sort_by, $order);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
    
    public function getStatusName()
    {
        return $this->is_public ? 'Active' : 'Inactive';
    }
    /**
     * Update is_active state
     *
     * @param bool $value true or false
     *
     * @return bool
     */
    public function changeIsActiveState($value)
    {
        $this->is_public = $value;

        if ($this->save()) {
            $this->fireModelEvent('statusChanged', false);
            return true;
        }

        return false;
    }
}
