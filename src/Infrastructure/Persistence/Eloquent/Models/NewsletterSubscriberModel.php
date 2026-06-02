<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsletterSubscriberModel extends Model
{
    use SoftDeletes;

    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function subscribe(): void
    {
        $this->update([
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }
}
