<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssessmentItem extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'assessment_id',
        'itemable_type',
        'itemable_id',
        'points',
        'position',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /** @return MorphTo<Model, $this> */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
