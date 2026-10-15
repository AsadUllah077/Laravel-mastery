<?php

use App\Models\Lesson;
use App\Models\Module;
use App\Models\ProjectGuide;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('academy:status', function () {
    $connection = DB::connection();
    $function = $connection->getDriverName() === 'sqlite' ? 'sqlite_version()' : 'VERSION()';
    $version = $connection->selectOne('SELECT '.$function.' AS version');
    $this->line('Database server: '.$version->version);
    $this->table(['Modules', 'Lessons', 'Quizzes', 'Project guides'], [[
        Module::count(), Lesson::count(),
        Quiz::count(), ProjectGuide::count(),
    ]]);
})->purpose('Show database version and actual curriculum counts without printing credentials');

Artisan::command('academy:create-admin {email} {--name=Administrator}', function () {
    $email = $this->argument('email');
    $password = $this->secret('Choose an administrator password (at least 12 characters)');
    $validator = validator(['email' => $email, 'password' => $password], ['email' => 'required|email|unique:users,email', 'password' => 'required|string|min:12']);
    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return 1;
    }
    $user = new User(['name' => $this->option('name'), 'email' => $email, 'password' => $password]);
    $user->is_admin = true;
    $user->email_verified_at = now();
    $user->save();
    $this->info('Administrator created and verified.');

    return 0;
})->purpose('Create a verified administrator using a hidden password prompt');

Artisan::command('academy:demo', function () {
    if (! app()->environment('local')) {
        $this->error('Demo accounts are only available in the local environment.');

        return 1;
    }
    $lines = ['Local development accounts. Keep this ignored file private.', ''];
    foreach ([['Learner', 'learner@academy.test', false], ['Administrator', 'admin@academy.test', true]] as [$name, $email, $admin]) {
        if (User::where('email', $email)->exists()) {
            $lines[] = "$email already exists; password unchanged.";

            continue;
        }
        $password = Str::password(20);
        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->is_admin = $admin;
        $user->email_verified_at = now();
        $user->save();
        $lines[] = "$name: $email\nPassword: $password\n";
    }
    $path = storage_path('app/demo-access.txt');
    if (file_exists($path)) {
        file_put_contents($path, "\n".implode("\n", $lines), FILE_APPEND);
    } else {
        file_put_contents($path, implode("\n", $lines));
    }
    $this->info('Demo access details saved to storage/app/demo-access.txt (ignored by Git).');

    return 0;
})->purpose('Create local demo accounts with generated passwords without changing existing accounts');
