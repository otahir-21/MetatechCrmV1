<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InternalEmployeeViewController extends Controller
{
    /**
     * Show form to create internal employee
     */
    public function showCreateForm()
    {
        return view('internal-employee.create');
    }
}
