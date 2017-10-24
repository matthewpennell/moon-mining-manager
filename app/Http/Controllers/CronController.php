<?php

namespace App\Http\Controllers;

use App\Jobs\PollRefineries;
use App\Jobs\PollMiningObservers;
use App\Jobs\PollWallet;
use App\Jobs\GenerateInvoices;

use App\Classes\EsiConnection;
use App\Type;
use App\TaxRate;
use App\ReprocessedMaterial;

class CronController extends Controller
{

    /**
     * Cron task to request information on all of the currently active moon mining observer structures.
     */
    public function pollRefineries()
    {
        PollRefineries::dispatch();
    }

    /**
     * For each moon mining observer, we request details of the current mining activity log,
     * parse it, and insert it into the database. Any unrecognised miners are also added.
     */
    public function pollMiningObservers()
    {
        PollMiningObservers::dispatch();
    }

    /**
     * 
     */
    public function pollWallet()
    {
        PollWallet::dispatch();
    }

    /**
     * Calculate the total amount owing per person, generate invoices, send emails.
     */
    public function generateInvoices()
    {
        GenerateInvoices::dispatch();
    }

    /**
     * Check for any ores that should be taxed but where we don't have any reprocessed material prices.
     */
    public function updateReprocessedMaterials()
    {
        //UpdateReprocessedMaterials::dispatch();

        // Find any tax rates (i.e. ore types) that need to have their reprocessed materials checked.
        $tax_rates = TaxRate::where('check_materials', 1)->get();
        foreach ($tax_rates as $rate)
        {
            // Pull the reprocessed material components for this item and store them in the table if they don't already exist.
            $materials = $rate->reprocessed_materials;
            foreach ($materials as $material)
            {
                $existing_reprocessed_material = ReprocessedMaterial::find($material->materialTypeID);
                if (!isset($existing_reprocessed_material))
                {
                    $x = new ReprocessedMaterial;
                    $x->materialTypeID = $material->materialTypeID;
                    $x->average_price = 0;
                    $x->save();
                }
            }
            // Update the flag to show the materials have been checked and save the tax rate record.
            $rate->check_materials = 0;
            $rate->save();
        }

    }

    /**
     * Update the rolling 14-day average value of all reprocessed materials that might be 
     * obtained from reprocessing the ores mined near refineries.
     */
    public function updateMaterialValues()
    {
        $esi = new EsiConnection;
        $the_forge = 10000002;
        $materials = ReprocessedMaterial::all();
        $rolling_day_average = 14;

        foreach ($materials as $material)
        {
            // Pull history for this material.
            $history = (array) $esi->esi->setQueryString([
                'type_id' => $material->materialTypeID,
            ])->invoke('get', '/markets/{region_id}/history/', [
                'region_id' => $the_forge,
            ]);
            // Loop the history, starting from the end and counting backwards.
            $weight = $rolling_day_average;
            $weighted_average = 0;
            $weighted_total = 0;
            foreach (array_reverse($history) as $row)
            {
                if ($weight > 0) {
                    $weighted_average += $row->average * $weight;
                    $weighted_total += $weight;
                    $weight--;
                } else {
                    break;
                }
            }
            $material->average_price = $weighted_average / $weighted_total;
            $material->save();
        }
    }

    /**
     * Update the stored value of each ore that has been mined.
     */
    public function updateOreValues()
    {

        // Grab all tax rate records, and all stored current values for materials (keyed by id).
        $tax_rates = TaxRate::all();
        $material_values = ReprocessedMaterial::select('materialTypeID', 'average_price')->get()->keyBy('materialTypeID');

        // Loop through the rates, calculating the total value per unit of ore.
        foreach ($tax_rates as $rate)
        {
            $total_unit_cost = 0;
            $materials = $rate->reprocessed_materials;
            
            foreach ($materials as $material)
            {
                $total_unit_cost += $material_values[$material->materialTypeID]->average_price * ($material->quantity * 0.9);
            }
            $rate->value = $total_unit_cost;
            $rate->save();
        }

    }

}
