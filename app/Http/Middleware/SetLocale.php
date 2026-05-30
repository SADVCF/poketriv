<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'es');

        if (!in_array($locale, ['es', 'en'])) {
            $locale = 'es';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
