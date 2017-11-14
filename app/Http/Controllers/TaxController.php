<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\TaxRate;
use App\Type;
use App\ReprocessedMaterial;
use App\ReprocessedMaterialsHistory;
use Ixudra\Curl\Facades\Curl;

class TaxController extends Controller
{
    
    public function showTaxRates()
    {
        return view('taxes', [
            'tax_rates' => TaxRate::orderby('value', 'desc')->get(),
        ]);
    }

    /**
     * Show the historical data for the average price of reprocessed materials.
     */
    public function showHistory()
    {
        $materials = ReprocessedMaterial::orderBy('MaterialTypeID')->get();
        $history = [];
        foreach ($materials as $material)
        {
            $history[$material->materialTypeID] = ReprocessedMaterialsHistory::where('type_id', $material->materialTypeID)->orderBy('updated_at', 'asc')->get();
        }

        return view('taxes.history', [
            'materials' => $materials,
            'history' => $history,
        ]);
    }

    /**
     * Update the stored value for a specified item.
     */
    public function updateValue(Request $request, $type_id = NULL)
    {
        if ($type_id == NULL)
        {
            return redirect('/taxes');
        }
        $rate = TaxRate::where('type_id', $type_id)->first();
        $rate->value = $request->input('new_value');
        $rate->updated_by = Auth::user()->id;
        $rate->save();
        return redirect('/taxes');
    }

    /**
     * Update the stored rate of tax for a specified item.
     */
    public function updateTaxRate(Request $request, $type_id = NULL)
    {
        if ($type_id == NULL)
        {
            return redirect('/taxes');
        }
        $rate = TaxRate::where('type_id', $type_id)->first();
        $rate->tax_rate = $request->input('new_tax_rate');
        $rate->updated_by = Auth::user()->id;
        $rate->save();
        return redirect('/taxes');
    }

    /**
     * Update the stored rate of tax for all items.
     */
    public function updateMasterTaxRate(Request $request)
    {
        if (!is_numeric($request->input('new_tax_rate')))
        {
            return redirect('/taxes');
        }
        TaxRate::query()->update([
            'tax_rate' => $request->input('new_tax_rate')
        ]);
        return redirect('/taxes');
    }

    /**
     * Generate the records for all ores (including in the invTypes table if not
     * present) and set a default value and tax rate.
     */
    public function loadInitialTaxRates()
    {
        $moon_ore_market_groups = [2396, 2397, 2398, 2400, 2401];
        foreach ($moon_ore_market_groups as $market_group_id)
        {
            $url = 'https://esi.tech.ccp.is/latest/markets/groups/' . $market_group_id . '/?datasource=singularity';
            $response = json_decode(Curl::to($url)->get());
            foreach ($response->types as $type_id)
            {
                // Insert the ore types into the tax rate database with default values.
                $ore = TaxRate::where('type_id', $type_id)->get();
                if ($ore->isEmpty())
                {
                    $ore = new TaxRate;
                    $ore->type_id = $type_id;
                    $ore->value = 100;
                    $ore->tax_rate = 5;
                    $ore->updated_by = env('ESI_PRIME_USER_ID', 0);
                    $ore->save();
                    echo 'Inserted moon ore type #' . $type_id . ' into the tax_rates table<br>';
                }
                // Check if this item exists in invTypes - if not, add a dummy record.
                $type = Type::where('typeID', $type_id)->first();
                if (!isset($type))
                {
                    $type = new Type;
                    $type->typeID = $type_id;
                }
                $url = 'https://esi.tech.ccp.is/latest/universe/types/' . $type_id . '/?datasource=singularity';
                $response2 = json_decode(Curl::to($url)->get());
                $type->groupID = $response2->group_id;
                $type->typeName = $response2->name;
                $type->description = $response2->description;
                $type->save();
                echo 'Updated/inserted ' . $response2->name . ' in the invTypes table<br>';
            }
        }
    }

}
