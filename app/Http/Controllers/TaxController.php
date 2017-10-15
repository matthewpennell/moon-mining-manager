<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\TaxRate;

class TaxController extends Controller
{
    
    public function showTaxRates()
    {
        // If no current logged in user, show the login page.
        if (!Auth::check())
        {
            return view('login');
        }

        return view('taxes', [
            'user' => Auth::user(),
            'tax_rates' => TaxRate::orderby('type_id')->get(),
        ]);
    }

    /**
     * Update the stored value for a specified item.
     */
    public function updateValue(Request $request, $id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/taxes');
        }
        $rate = TaxRate::find($id);
        $rate->value = $request->input('new_value');
        $rate->updated_by = Auth::user()->id;
        $rate->save();
        return redirect('/taxes');
    }

    /**
     * Update the stored rate of tax for a specified item.
     */
    public function updateTaxRate(Request $request, $id = NULL)
    {
        if ($id == NULL)
        {
            return redirect('/taxes');
        }
        $rate = TaxRate::find($id);
        $rate->tax_rate = $request->input('new_tax_rate');
        $rate->updated_by = Auth::user()->id;
        $rate->save();
        return redirect('/taxes');
    }

}
