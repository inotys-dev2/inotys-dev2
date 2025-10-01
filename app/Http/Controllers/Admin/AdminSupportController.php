<?php

namespace App\Http\Controllers\Admin;

class AdminSupportController
{

    public function index()
    {
        return view('admin.support.index');
    }

    public function createTicket()
    {
        return view('admin.support.create');
    }

}
