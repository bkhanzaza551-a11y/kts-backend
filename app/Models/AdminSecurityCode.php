<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSecurityCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'label',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateFor(User $user, ?string $label = null): array
    {
        $plainCode = strtoupper(Str::random(8));

        $securityCode = static::create([
            'user_id' => $user->id,
            'code' => Hash::make($plainCode),
            'label' => $label ?? 'Default',
            'is_active' => true,
        ]);

        return [
            'id' => $securityCode->id,
            'code' => $plainCode,
            'label' => $securityCode->label,
        ];
    }

    public function verify(string $code): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!Hash::check($code, $this->code)) {
            return false;
        }

        $this->update(['last_used_at' => now()]);
        return true;
    }
}
