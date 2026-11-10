<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'lat',
    'lng',
    'riddle_text',
    'qr_map_code',
    'qr_mj_code',
    'code_part',
])]
class Step extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'lat',
        'lng',
        'riddle_text',
        'qr_map_code',
        'qr_mj_code',
        'code_part',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(Fragment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'fragments');
    }
}
