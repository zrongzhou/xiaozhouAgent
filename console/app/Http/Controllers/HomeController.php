<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController
{
    public function index(): View
    {
        return view('welcome', [
            'appName' => config('app.name', 'xiaozhouAgent'),
        ]);
    }
}
