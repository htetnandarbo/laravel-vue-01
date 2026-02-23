<?php

namespace App\Http\Controllers;

use App\Models\CityTownship;
use App\Models\EmailTemplate;
use App\Models\Inquiry;
use App\Models\Plan;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\EmailService;

class WelcomeController extends Controller
{

    public function index(Request $request)
    {

        return Inertia::render('Welcome');
    }
}
