<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{

    public function index()
    {
        return view('auth.notifications');
    }

}
