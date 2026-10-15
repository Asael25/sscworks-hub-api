<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUnit extends Model
{
    use HasFactory;

    protected $table = 'system_units';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    /**
     * Calculate dynamic count of users assigned to this unit.
     */
    public function calculateMembersCount(): int
    {
        $unitName = $this->name;
        $cleanTarget = self::normalizeUnitName($unitName);

        return User::all()->filter(function ($user) use ($cleanTarget) {
            $primaryClean = self::normalizeUnitName($user->primary_unit ?? '');
            if ($primaryClean === $cleanTarget) {
                return true;
            }

            $additional = $user->additional_units ?? [];
            if (is_array($additional)) {
                foreach ($additional as $addUnit) {
                    if (self::normalizeUnitName($addUnit) === $cleanTarget) {
                        return true;
                    }
                }
            }

            return false;
        })->count();
    }

    /**
     * Normalize unit string to ignore dash variance (em-dash vs hyphen).
     */
    public static function normalizeUnitName(?string $name): string
    {
        if (!$name) return '';
        return strtolower(trim(str_replace(['—', '–'], '-', $name)));
    }

    /**
     * Format into frontend-expected summary.
     */
    public function toFrontendSummary(): array
    {
        $members = $this->calculateMembersCount();
        $isPermanent = $this->type === 'Head Office';

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'parent' => $this->parent ?: '—',
            'status' => $this->status,
            'inUse' => $isPermanent || $members > 0,
            'members' => $members,
        ];
    }
}
