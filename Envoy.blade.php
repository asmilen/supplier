@servers(['web' => 'cuonggt@35.185.132.186'])

@task('deploy', ['on' => 'web'])
    cd /var/www/supplier_management/

    @if ($release)
        git fetch --tags
        git checkout tags/{{ $release }}
    @endif

    composer install --no-interaction --prefer-dist --optimize-autoloader

    echo "" | sudo -S service php7.1-fpm reload

    php artisan migrate --force

    php artisan optimize
@endtask
