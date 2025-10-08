<?php

namespace Happytodev\Blogr\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1);
        $availableLocales = config('blogr.locales.available', ['en']);
        $defaultLocale = config('blogr.locales.default', 'en');

        // Check if the first segment is a valid locale
        if (in_array($locale, $availableLocales)) {
            app()->setLocale($locale);
            Carbon::setLocale($locale);
            $request->attributes->set('locale', $locale);
        } else {
            // Use default locale if no valid locale in URL
            app()->setLocale($defaultLocale);
            Carbon::setLocale($defaultLocale);
            $request->attributes->set('locale', $defaultLocale);
        }

        return $next($request);
    }
}
