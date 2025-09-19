<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarlyBirdDiscount extends Model
{
    protected $fillable = [
        'campaign_id','name','starts_at','cutoff_date',
        'discount_type','discount_value','channels','stackable','is_active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'cutoff_date' => 'date',
        'channels' => 'array',
        'stackable' => 'bool',
        'is_active' => 'bool',
    ];

    public const CHANNEL_STORE       = 'store';       // 店頭
    public const CHANNEL_TOKUSHIMARU = 'tokushimaru'; // とくし丸
    public const CHANNEL_WEB         = 'web';         // Web

    /* relations */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(EarlyBirdScope::class);
    }

    /* queries */
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /* ラベル系 */
    public function getDisplayValueAttribute(): string
    {
        return $this->discount_type === 'percent'
            ? $this->discount_value . '% OFF'
            : number_format($this->discount_value) . '円引き';
    }

    public function channelsLabel(): string
    {
        $map = [
            self::CHANNEL_STORE => '店頭',
            self::CHANNEL_TOKUSHIMARU => 'とくし丸',
            self::CHANNEL_WEB => 'Web',
        ];
        return collect($this->channels ?? [])->map(fn($c)=>$map[$c] ?? $c)->join(' / ');
    }

    /**
     * 適用候補の中から、商品・店舗・チャネル・日付で適用ルールを1つ返す
     * 優先順位: 商品指定 > 全商品（store指定の一致は優先）
     */
    public static function resolveOne(
        int $campaignId,
        int $productId,
        ?int $storeId,
        string $channel,
        \DateTimeInterface $now
    ): ?self {
        $today = \Illuminate\Support\Carbon::instance($now)->toDateString();

        $candidates = self::query()
            ->active()
            ->where('campaign_id', $campaignId)
            ->where(function($q) use ($today){
                $q->whereNull('starts_at')->orWhere('starts_at','<=',$today);
            })
            ->where('cutoff_date','>=',$today)
            ->where(function($q) use ($channel){
                $q->whereNull('channels') // null = 全チャネル
                ->orWhereJsonContains('channels', $channel);
            })
            ->with('scopes')
            ->get();

        // 1) 商品+店舗一致 2) 商品一致（店舗ALL） 3) 全商品+店舗一致 4) 全商品（全店舗）
        $match = $candidates->first(function(self $r) use ($productId,$storeId){
            return $r->scopes->contains(fn($s)=>$s->product_id===$productId && $s->store_id===$storeId);
        }) ?? $candidates->first(function(self $r) use ($productId){
            return $r->scopes->contains(fn($s)=>$s->product_id===$productId && $s->store_id===null);
        }) ?? $candidates->first(function(self $r) use ($storeId){
            return $r->scopes->isEmpty() ? false : $r->scopes->contains(fn($s)=>$s->product_id===null && $s->store_id===$storeId);
        }) ?? $candidates->first(function(self $r){
            return $r->scopes->isEmpty(); // 全商品・全店舗
        });

        return $match;
    }

    /** このルールを与えた時の割引額を返す（単価ベース／切り捨て） */
    public function calcDiscount(int $unitPrice): int
    {
        if ($this->discount_type === 'percent') {
            return (int) floor($unitPrice * $this->discount_value / 100);
        }
        return min($unitPrice, $this->discount_value);
    }
}
