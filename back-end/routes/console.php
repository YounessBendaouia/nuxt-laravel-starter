<?php

use App\Actions\Registration\RegistrationStatus;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Registration toggle commands.
 *
 * These are the ONLY way to change the runtime registration toggle; there is
 * deliberately no HTTP endpoint for it, so changing it requires shell access
 * to the server. Every change is written to the application log for auditing.
 */
$registrationAuditContext = fn (): array => [
    'os_user' => getenv('USER') ?: getenv('USERNAME') ?: get_current_user(),
    'host' => gethostname() ?: 'unknown',
    'environment' => app()->environment(),
];

Artisan::command('registration:enable {--force : Skip the confirmation prompt when running in production}', function (RegistrationStatus $registrationStatus) use ($registrationAuditContext) {
    if (app()->isProduction() && ! $this->option('force')
        && ! $this->confirm('You are about to OPEN public registration in production. Continue?')) {
        $this->components->warn('Aborted. Registration was not changed.');

        return Command::FAILURE;
    }

    $registrationStatus->enable();

    Log::warning('Public registration ENABLED via console.', $registrationAuditContext());

    if ($registrationStatus->isForcedOffByConfig()) {
        $this->components->warn('Runtime toggle set to enabled, but REGISTRATION_ENABLED=false in the environment keeps registration CLOSED.');

        return Command::SUCCESS;
    }

    $this->components->info('Public registration is now ENABLED.');

    return Command::SUCCESS;
})->purpose('Open public user registration');

Artisan::command('registration:disable', function (RegistrationStatus $registrationStatus) use ($registrationAuditContext) {
    $registrationStatus->disable();

    Log::warning('Public registration DISABLED via console.', $registrationAuditContext());

    $this->components->info('Public registration is now DISABLED.');

    return Command::SUCCESS;
})->purpose('Close public user registration');

Artisan::command('registration:status', function (RegistrationStatus $registrationStatus) {
    $this->components->twoColumnDetail('Environment kill-switch (REGISTRATION_ENABLED)', $registrationStatus->isForcedOffByConfig() ? '<fg=red>OFF</>' : '<fg=green>ON</>');
    $this->components->twoColumnDetail('Effective registration status', $registrationStatus->isEnabled() ? '<fg=green>ENABLED</>' : '<fg=red>DISABLED</>');

    return Command::SUCCESS;
})->purpose('Show whether public user registration is open');
