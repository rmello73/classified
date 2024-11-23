<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $theme = '';

    public function __construct()
    {
        if (file_exists(base_path('.env'))) {
            $selected_theme = get_option('default_theme');

            if (($selected_theme == 'default_theme') || $selected_theme == 'classic') {
                $this->theme = 'theme.';
            } else {
                $this->theme = 'theme.'.$selected_theme.'.';
            }
        }
    }
}
