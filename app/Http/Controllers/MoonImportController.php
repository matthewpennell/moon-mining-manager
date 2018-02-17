<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Moon;
use App\Region;
use App\SolarSystem;
use App\Miner;
use App\Type;

class MoonImportController extends Controller
{

    public function index()
    {
        $moons = Moon::orderBy('region_id')->orderBy('solar_system_id')->orderBy('planet')->orderBy('moon')->get();
        return view('moons.import', [
            'moons' => $moons,
        ]);
    }

    public function import(Request $request)
    {

        // Convert the dump of spreadsheet data into a structured array.
        $data = [];
        $lines = explode("\n", $request->input('data'));
        foreach ($lines as $line)
        {
            $data[] = explode("\t", $line);
        }

        // Loop through each row and process it into the database.
        foreach ($data as $row)
        {
            $moon = new Moon;
            $moon->region_id = Region::where('regionName', $row[0])->first()->regionID;
            $moon->solar_system_id = SolarSystem::where('solarSystemName', $row[1])->first()->solarSystemID;
            $moon->planet = $row[2];
            $moon->moon = $row[3];
            /*
            if ($row[4])
            {
                $moon->renter_id = Miner::where('name', $row[4])->first()->eve_id;
            }
            */
            $moon->mineral_1_type_id = Type::where('typeName', $row[5])->first()->typeID;
            $moon->mineral_1_percent = str_replace(',', '.', $row[6]);
            $moon->mineral_2_type_id = Type::where('typeName', $row[7])->first()->typeID;
            $moon->mineral_2_percent = str_replace(',', '.', $row[8]);
            if ($row[9])
            {
                $moon->mineral_3_type_id = Type::where('typeName', $row[9])->first()->typeID;
                $moon->mineral_3_percent = str_replace(',', '.', $row[10]);
            }
            if ($row[11])
            {
                $moon->mineral_4_type_id = Type::where('typeName', $row[11])->first()->typeID;
                $moon->mineral_4_percent = str_replace(',', '.', $row[12]);
            }
            $moon->monthly_rental_fee = 0;
            $moon->save();
        }

        // Redirect back to the list.
        return redirect('/moons');

    }

}
