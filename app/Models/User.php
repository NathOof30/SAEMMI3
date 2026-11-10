<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'team_name', 'current_step', 'phase', 'avatar', 'reward_picked', 'reward_token'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'team_name',
        'current_step',
        'phase',
        'avatar',
        'reward_picked',
        'reward_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'current_step' => 'integer',
            'reward_picked' => 'boolean',
        ];
    }

    public function generateRewardToken(): string
    {
        if (empty($this->reward_token)) {
            $this->reward_token = Str::random(32);
            $this->save();
        }

        return $this->reward_token;
    }

    public function markRewardPicked(): void
    {
        $this->reward_picked = true;
        $this->save();
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(Fragment::class);
    }

    public function unlockedSteps(): BelongsToMany
    {
        return $this->belongsToMany(Step::class, 'fragments');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(Step::class, 'current_step');
    }
}
