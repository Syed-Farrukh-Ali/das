<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        dd(request()->user());

        return 'Authenticated!';
    }
}
