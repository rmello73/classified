<?php

namespace App\Providers;

use App\Models\Option;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class InitialConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            $options = Option::all()->pluck('option_value', 'option_key')->toArray();
            $allOptions = [];
            $allOptions['options'] = $options;
            config($allOptions);

            /**
             * Set dynamic configuration for third party services
             */
            $amazonS3Config = [
                'filesystems.disks.s3' => [
                    'driver' => 's3',
                    'key' => get_option('amazon_key'),
                    'secret' => get_option('amazon_secret'),
                    'region' => get_option('amazon_region'),
                    'bucket' => get_option('bucket'),
                ],
            ];
            $facebookConfig = [
                'services.facebook' => [
                    'client_id' => get_option('fb_app_id'),
                    'client_secret' => get_option('fb_app_secret'),
                    'redirect' => url('login/facebook-callback'),
                ],
            ];
            $googleConfig = [
                'services.google' => [
                    'client_id' => get_option('google_client_id'),
                    'client_secret' => get_option('google_client_secret'),
                    'redirect' => url('login/google-callback'),
                ],
            ];
            config($amazonS3Config);
            config($facebookConfig);
            config($googleConfig);

            view()->composer('*', function ($view) {
                $enable_monetize = get_option('enable_monetize');
                $loggedUser = null;
                if (Auth::check()) {
                    $loggedUser = Auth::user();
                }
                $view->with(['lUser' => $loggedUser, 'enable_monetize' => $enable_monetize]);
            });
        } catch (\Exception $e) {
            if ('artisan' !== array_get($_SERVER, 'PHP_SELF')) {
                if ('42S02' === $e->getCode()) {
                    $this->run_migration_if_tables_is_not_exists();
                } else {
                    echo "<code>{$e->getMessage()}</code>";
                    echo "<p>To resolve this issue, you should check your database configuration settings in the <code style='color:#ff7274'>.env</code>  file. Make sure that the database credentials (such as username and password) are correct, and that the database server is running and accessible from your application. If you're still experiencing issues after verifying your configuration, double-check that you have the required database driver installed and configured correctly.</p>";
                }

                exit();
            }
        }
    }

    public function run_migration_if_tables_is_not_exists()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol.'://'.$domain.rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        if (! isset($_GET['command']) || ! isset($_GET['migration_token'])) {
            $token = str_random(32);
            Cache::put('migration-action:'.$token, true, 5); // Store the token in cache for 5 minutes

            echo $this->btn_css();

            echo "<p>It appears that the database has not been imported. To import the database, please click the <a class='migration-btn' href='{$baseUrl}/?command=run_migration&migration_token={$token}'> <svg aria-hidden='true' focusable='false' data-prefix='fas' data-icon='wrench' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path fill='currentColor' d='M507.6 122.8c-2.904-12.09-18.25-16.13-27.04-7.338l-76.55 76.56l-83.1-.0002l0-83.1l76.55-76.56c8.791-8.789 4.75-24.14-7.336-27.04c-23.69-5.693-49.34-6.111-75.92 .2484c-61.45 14.7-109.4 66.9-119.2 129.3C189.8 160.8 192.3 186.7 200.1 210.1l-178.1 178.1c-28.12 28.12-28.12 73.69 0 101.8C35.16 504.1 53.56 512 71.1 512s36.84-7.031 50.91-21.09l178.1-178.1c23.46 7.736 49.31 10.24 76.17 6.004c62.41-9.84 114.6-57.8 129.3-119.2C513.7 172.1 513.3 146.5 507.6 122.8zM80 456c-13.25 0-24-10.75-24-24c0-13.26 10.75-24 24-24s24 10.74 24 24C104 445.3 93.25 456 80 456z'></path></svg> Import The Database</a> button </p>";
        } elseif ('run_migration' === $_GET['command']) {
            $token = $_GET['migration_token'];

            $valid = Cache::get('migration-action:'.$token, false);
            if ($valid) {
                Cache::forget('migration-action:'.$token);

                Artisan::call('app:import-db');
            }

            header('Location: '.$baseUrl);
            exit();
        }
    }

    public function btn_css()
    {
        return '
        <style>
        .migration-btn {
  padding: 0.5rem 1rem;
  height: 2rem;
  white-space: nowrap;
  border-bottom: 1px solid;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: bold;
  border-radius: 0.125rem;
  box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.25);
  transition: all 0.2s;
  opacity: 1;
  margin-bottom: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  background-color: #10B981;
  border-color: rgba(16, 185, 129, 0.25);
  color: #fff;
  text-decoration: none;
}

.migration-btn:hover {
  box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.25);
}

.migration-btn:active {
  box-shadow: inset 0px -2px 2px rgba(0, 0, 0, 0.25);
  transform: translateY(1px);
}

.migration-btn svg {
  fill: currentColor;
  opacity: 0.5;
  margin-right: 0.5rem;
  height: 14px;
}
        
</style>
        ';
    }
}
