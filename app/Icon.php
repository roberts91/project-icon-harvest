<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;

class Icon extends Model
{
    
    use AlgoliaEloquentTrait;
    
    protected $fillable = ['icon_id', 'code', 'type', 'name', 'tags'];
    
    // Add ranking when sent to Algolia
    public function getAlgoliaRecord()
    {
        $fields = array_merge($this->toArray(), [
            'ranking' => 0
        ]);
        unset($fields['created_at']);
        unset($fields['updated_at']);    
        return $fields;
    }
    
}
