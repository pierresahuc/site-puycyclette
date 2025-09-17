<?php

declare(strict_types=1);

namespace Deployer;

require 'recipe/common.php';

// --- CONFIGURATION GÉNÉRALE ---
set('application', 'site-puycyclette');
set('repository', 'git@github.com:pierresahuc/site-puycyclette.git');
set('git_tty', true);

set('bin/php', '/usr/bin/php-8.2');
set('bin/composer', 'composer');

set('shared_dirs', [
    'var/log',
    'var/sessions',
    'public/uploads',
    'var/uploads',
]);

set('shared_files', [
    '.env.local',
    '.htaccess',
]);

set('writable_dirs', [
    'var',
    'var/cache',
    'var/log',
    'var/sessions',
    'public/uploads',
    'var/uploads',
]);

set('keep_releases', 3);

// --- SERVEUR CIBLE ---
host('preprod')
    ->setHostname('c65mt.ftp.infomaniak.com')
    ->set('remote_user', 'c65mt_puycyclette')
    ->set('writable_mode', 'chmod')
    ->set('deploy_path', '/home/clients/4e84166ace40952f79d904bbd9ea897e/sites/puycyclette-sulu');

// --- TÂCHES PERSONNALISÉES ---
// Upload du build frontend (Webpack Encore)
desc('Upload frontend build');
task('deploy:assets', function () {
    upload('public/build', '{{release_path}}/public');
});

// Migration SQL
desc('Migrates SQL database');
task('database:migrate:sql', function () {
    run('cd {{release_or_current_path}} && {{bin/php}} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true');
});

// Migration PHPCR
desc('Migrates PHPCR database');
task('database:migrate:phpcr', function () {
    run('cd {{release_or_current_path}} && {{bin/php}} bin/console phpcr:migrations:migrate --no-interaction --allow-no-migration || true');
});

// Installer les assets Symfony + Sulu
desc('Install assets');
task('sulu:assets', function () {
    run('cd {{release_or_current_path}} && {{bin/php}} bin/console assets:install public --symlink --relative');
});

// Mettre à jour l’admin si custom
desc('Update Sulu admin build');
task('sulu:admin:update-build', function () {
    run('cd {{release_or_current_path}} && {{bin/php}} bin/console sulu:admin:update-build');
});

// Clear et warmup du cache Symfony
desc('Clears Symfony cache');
task('deploy:cache:clear', function () {
    run('cd {{release_or_current_path}} && {{bin/php}} bin/console cache:clear --no-warmup --env=prod');
    run('cd {{release_or_current_path}} && {{bin/php}} bin/console cache:warmup --env=prod');
});

// --- WORKFLOW DE DÉPLOIEMENT ---
desc('Deploy project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'database:migrate:sql',
    'database:migrate:phpcr',
    'deploy:cache:clear',
    'sulu:assets',
    // 'sulu:admin:update-build', // décommente si tu modifies l’admin
    'deploy:assets',
    'deploy:publish',
]);

after('deploy:failed', 'deploy:unlock');
