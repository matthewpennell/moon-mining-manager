<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Moon;

class MoonController extends Controller
{

    public function index()
    {
        $moons = Moon::orderBy('region_id')->orderBy('solar_system_id')->orderBy('planet')->orderBy('moon')->get();

        // We want to display information differently to administrators and prospective renters.
        
        return view('moons.public', [
            'moons' => $moons,
        ]);
    }

}
