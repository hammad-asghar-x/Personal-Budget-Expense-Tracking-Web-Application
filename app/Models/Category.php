<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'icon',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function getTotalSpentForMonth($month, $year): float
    {
        return $this->expenses()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected static function booted(): void
    {
        static::deleting(function (Category $category) {
            if ($category->expenses()->exists()) {
                throw new \Exception('Cannot delete category with existing expenses.');
            }
        });
    }
}
