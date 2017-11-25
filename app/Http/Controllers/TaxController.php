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

}
