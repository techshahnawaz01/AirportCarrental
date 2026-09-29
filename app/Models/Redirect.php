<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_url', 'status_code'];

    protected static function booted(): void
    {
        static::saving(fn (Redirect $redirect) => $redirect->from_path = static::normalise($redirect->from_path));
    }

    public static function normalise(string $path): string
    {
        return trim(parse_url($path, PHP_URL_PATH) ?? $path, '/');
    }
}
