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
    'target_radius',
    'riddle_text',
    'story_rp',
    'heritage_info',
    'wrong_scan_intro',
    'wrong_scan_mismatch',
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
        'target_radius',
        'riddle_text',
        'story_rp',
        'heritage_info',
        'wrong_scan_intro',
        'wrong_scan_mismatch',
        'qr_map_code',
        'qr_mj_code',
        'code_part',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'target_radius' => 'integer',
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

    public function cleanName(): string
    {
        $cleaned = preg_replace('/^[EÉeé]tape\s*\d+\s*:\s*/iu', '', $this->name);

        return trim((string) $cleaned);
    }

    public function buildWrongMapFeedback(self $expectedStep): string
    {
        $intro = $this->wrong_scan_intro ?: "Ça, c’est {$this->cleanName()} !";
        $mismatch = $expectedStep->wrong_scan_mismatch ?: "mais ce lieu ne correspond pas à l’énigme de l’étape {$expectedStep->id} !";

        return "{$intro}… {$mismatch}";
    }

    /**
     * Identify the QR code type ('map' or 'mj') and associated Step, if any.
     *
     * @return array{type: 'map'|'mj'|null, step: ?self, step_number: ?int}
     */
    public static function resolveFromQrCode(string $rawCode): array
    {
        $upperCode = strtoupper(trim($rawCode));
        if ($upperCode === '') {
            return ['type' => null, 'step' => null, 'step_number' => null];
        }

        $steps = self::query()->orderBy('id')->get();

        foreach ($steps as $step) {
            if (strtoupper($step->qr_map_code) === $upperCode) {
                return ['type' => 'map', 'step' => $step, 'step_number' => $step->id];
            }
            if (strtoupper($step->qr_mj_code) === $upperCode) {
                return ['type' => 'mj', 'step' => $step, 'step_number' => $step->id];
            }
        }

        if (preg_match('/^MAP[-_](?:LOC|STEP)[-_](\d+)$/i', $upperCode, $matches)) {
            $stepNumber = (int) $matches[1];

            return [
                'type' => 'map',
                'step' => $steps->firstWhere('id', $stepNumber),
                'step_number' => $stepNumber,
            ];
        }

        if (preg_match('/^MJ[-_](?:LOC|STEP)[-_](\d+)$/i', $upperCode, $matches)) {
            $stepNumber = (int) $matches[1];

            return [
                'type' => 'mj',
                'step' => $steps->firstWhere('id', $stepNumber),
                'step_number' => $stepNumber,
            ];
        }

        if (preg_match('/^MAP[-_]/i', $upperCode)) {
            return ['type' => 'map', 'step' => null, 'step_number' => null];
        }

        if (preg_match('/^MJ[-_]/i', $upperCode)) {
            return ['type' => 'mj', 'step' => null, 'step_number' => null];
        }

        return ['type' => null, 'step' => null, 'step_number' => null];
    }
}
