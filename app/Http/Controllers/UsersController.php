<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
	/**
	 * signup
	 * @return [type] [description]
	 */
    public function create()
    {
    	return view('users.create');
    }
}
