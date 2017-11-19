<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Refinery;

class TimerController extends Controller
{
    public function home()
    {
        return view('timers', [
            'timers' => Refinery::where('name', 'LIKE', '% BRAVE %')->whereNotNull('extraction_start_time')->orderBy('chunk_arrival_time')->get(),
        ]);
    }
}
