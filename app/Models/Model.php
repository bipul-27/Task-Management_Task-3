<?php

namespace FluentPlugin\App\Models;

use FluentPlugin\Framework\Database\Orm\Model as BaseModel;

class Model extends BaseModel
{
    protected $guarded = ['id', 'ID'];
}