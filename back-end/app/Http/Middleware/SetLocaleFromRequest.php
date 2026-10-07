<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    /**
     * Apply the locale requested through the Accept-Language header.
     *
     * Only locales listed in `app.supported_locales` are ever applied, so the
     * header can never be used to load an arbitrary language file. Anything
     * unsupported or malformed silently falls back to the default locale.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if ($locale !== null) {
            app()->setLocale($locale);
        }

        $response = $next($request);

        $response->setVary('Accept-Language', false);

        return $response;
    }

    /**
     * Find the best supported locale for the request, if any.
     */
    private function resolveLocale(Request $request): ?string
    {
        /** @var array<int, string> $supportedLocales */
        $supportedLocales = config('app.supported_locales', []);

        foreach ($request->getLanguages() as $language) {
            $code = strtolower(strtok($language, '_-') ?: '');

            if (in_array($code, $supportedLocales, true)) {
                return $code;
            }
        }

        return null;
    }
}
