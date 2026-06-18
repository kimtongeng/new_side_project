<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PosController extends Controller
{
    /**
     * Display the Point of Sale page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('Loan::pos.index');
    }
}