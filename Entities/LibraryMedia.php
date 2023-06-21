<?php

namespace Modules\MediaLibrary\Entities;


use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Permission\Traits\Authorisations;
use Modules\Starter\Entities\BaseModel;

class LibraryMedia extends BaseModel
{
    use Authorisations;

    protected $table = 'library_medias';


    protected $casts = [
        'media' => 'array',
    ];

    protected $appends = [
        'created_at_datetime',
        'created_at_human'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
