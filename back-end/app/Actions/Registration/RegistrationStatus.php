<?php

namespace App\Actions\Registration;

use App\Models\Setting;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Single source of truth for whether public self-registration is open.
 *
 * Security model:
 *  - The `REGISTRATION_ENABLED` env flag is a hard kill-switch. When it is
 *    false, registration is closed no matter what the database says.
 *  - The runtime toggle lives in the `settings` table and can only be changed
 *    from the server via Artisan (`registration:enable` / `registration:disable`).
 *    There is intentionally no HTTP endpoint that can write it.
 *  - Fail closed: any unexpected stored value or database error results in
 *    registration being treated as disabled.
 */
class RegistrationStatus
{
    public const SETTING_KEY = 'registration.enabled';

    /**
     * Determine whether new users may currently register.
     */
    public function isEnabled(): bool
    {
        if ($this->isForcedOffByConfig()) {
            return false;
        }

        try {
            $storedValue = Setting::query()
                ->where('key', self::SETTING_KEY)
                ->value('value');
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        if ($storedValue === null) {
            return true;
        }

        return $storedValue === true;
    }

    /**
     * Determine whether registration is hard-disabled by the environment kill-switch.
     */
    public function isForcedOffByConfig(): bool
    {
        return config('auth.registration.enabled') !== true;
    }

    /**
     * Determine whether the "registration is closed" notice should be shown to visitors.
     *
     * The notice is only shown when registration was closed deliberately through
     * the runtime toggle. When the environment kill-switch is OFF the sign-up UI
     * is hidden silently, so the public cannot tell which layer closed it.
     */
    public function shouldShowDisabledNotice(): bool
    {
        return ! $this->isForcedOffByConfig() && ! $this->isEnabled();
    }

    /**
     * Open registration (subject to the environment kill-switch).
     */
    public function enable(): void
    {
        $this->store(true);
    }

    /**
     * Close registration.
     */
    public function disable(): void
    {
        $this->store(false);
    }

    /**
     * Abort the current request if registration is closed.
     *
     * This must run before any input validation so that a closed registration
     * endpoint cannot be used as an oracle (e.g. to enumerate existing emails).
     *
     * @throws HttpException
     */
    public function ensureEnabled(): void
    {
        if (! $this->isEnabled()) {
            abort(403, __('messages.registration_disabled'));
        }
    }

    /**
     * Persist the runtime registration toggle.
     */
    private function store(bool $isEnabled): void
    {
        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => $isEnabled],
        );
    }
}
