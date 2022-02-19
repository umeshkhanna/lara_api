<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AmbassadorController extends Controller
{
    public function getAmbassadors(){
    	return User::ambassadors()->get();
    }
}
