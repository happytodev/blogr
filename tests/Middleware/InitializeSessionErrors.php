<?php

namespace Happytodev\Blogr\Tests\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

/**
 * Test middleware to ensure session errors bag is initialized
 * 
 * This fixes ViewErrorBag::put() null errors in Livewire tests
 * where ShareErrorsFromSession middleware doesn't initialize the bag correctly
 */
class InitializeSessionErrors
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure errors bag exists in session AND view shared data
        // Livewire's SupportValidation reads from view()->shared('errors')
        // which is populated by ShareErrorsFromSession, but in tests
        // this sometimes returns null causing ViewErrorBag::put() type errors
        
        $errorBag = new ViewErrorBag();
        
        // Put in session so ShareErrorsFromSession can find it
        if (!$request->session()->has('errors')) {
            $request->session()->put('errors', $errorBag);
        }
        
        // Also ensure view has it shared (this is what Livewire actually uses)
        view()->share('errors', $errorBag);
        
        return $next($request);
    }
}
