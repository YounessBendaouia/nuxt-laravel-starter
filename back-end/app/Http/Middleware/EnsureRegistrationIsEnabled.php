<?php

namespace App\Http\Middleware;

use App\Actions\Registration\RegistrationStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationIsEnabled
{
    public function __construct(private RegistrationStatus $registrationStatus) {}

    /**
     * Reject the request before it reaches validation when registration is closed.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->registrationStatus->ensureEnabled();

        return $next($request);
    }
}
