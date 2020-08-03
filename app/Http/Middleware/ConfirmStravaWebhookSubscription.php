<?php

namespace App\Http\Middleware;

use Closure;

class ConfirmStravaWebhookSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('hub_challenge')) {
            return response()->json([
                'hub.challenge' => $request->input('hub_challenge'),
            ]);
        }

        return $next($request);
    }
}
