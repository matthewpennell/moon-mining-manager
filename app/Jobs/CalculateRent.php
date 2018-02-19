<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Moon;
use App\TaxRate;
use App\Type;
use App\SolarSystem;
use App\Jobs\UpdateReprocessedMaterials;
use App\Jobs\UpdateMaterialValues;
use Illuminate\Support\Facades\Log;

class CalculateRent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    protected $total_ore_volume = 14000000; // 14m m3 represents a thirty day mining cycle, approximately

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Grab all of the moon records and loop through them.
        $moons = Moon::all();
        foreach ($moons as $moon)
        {

            // Save the current month's rental fee.
            $moon->previous_monthly_rental_fee = $moon->monthly_rental_fee;

            // Set the monthly rental value to zero.
            $monthly_rental_fee = 0;

            $monthly_rental_fee += $this->calculateOreTaxValue($moon->mineral_1_type_id, $moon->mineral_1_percent, $moon->solar_system_id);
            $monthly_rental_fee += $this->calculateOreTaxValue($moon->mineral_2_type_id, $moon->mineral_2_percent, $moon->solar_system_id);
            if ($moon->mineral_3_type_id)
            {
                $monthly_rental_fee += $this->calculateOreTaxValue($moon->mineral_3_type_id, $moon->mineral_3_percent, $moon->solar_system_id);
            }
            if ($moon->mineral_4_type_id)
            {
                $monthly_rental_fee += $this->calculateOreTaxValue($moon->mineral_4_type_id, $moon->mineral_4_percent, $moon->solar_system_id);
            }

            // Save the updated rental fee.
            $moon->monthly_rental_fee = $monthly_rental_fee;
            $moon->save();
            Log::info('CalculateRent: updated stored monthly rental fee for moon ' . $moon->id . ' to ' . $monthly_rental_fee);

            // Update the monthly rent figure if this moon is currently rented.
            DB::table('renters')->where('moon_id', $moon->id)->update([
                'monthly_rental_fee' => $monthly_rental_fee,
            ]);

        }
        
    }

    private function calculateOreTaxValue($type_id, $percent, $solar_system_id)
    {
        // Retrieve the value of the mineral from the taxes table.
        $tax_rate = TaxRate::where('type_id', $type_id)->first();

        // If we don't have a stored tax rate for this ore type, queue a job to calculate it.
        if (isset($tax_rate))
        {
            // Grab the stored value of this ore.
            $ore_value = $tax_rate->value;

            // Calculate what volume of the total ore will be this type.
            $ore_volume = $this->total_ore_volume * $percent / 100;
    
            // Based on the volume of the ore type, how many units does that volume represent.
            $type = Type::find($type_id);
            $units = $ore_volume / $type->volume;
    
            // Calculate the tax rate to apply (premium applied in the Impass pocket).
            $tax_rate = (SolarSystem::find($solar_system_id)->constellationID == 20000383) ? 10 : 7;

            // For non-moon ores, apply a 50% discount.
            $discount = (in_array($type->groupID, [1884, 1920, 1921, 1922, 1923])) ? 1 : 0.5;
    
            // Calculate the tax value to be charged for the volume of this ore that can be mined.
            return $ore_value * $units * $tax_rate / 100 * $discount;
        }
        else
        {
            // Add a new record for this unknown ore type.
            $tax_rate = new TaxRate;
            $tax_rate->type_id = $type_id;
            $tax_rate->check_materials = 1;
            $tax_rate->value = 0;
            $tax_rate->tax_rate = 7;
            $tax_rate->updated_by = 0;
            $tax_rate->save();
            Log::info('CalculateRent: unknown ore ' . $type_id . ' found, new tax rate record created');
            // Queue the jobs to update the ore values rather than waiting for the next scheduled job.
            UpdateReprocessedMaterials::dispatch();
            UpdateMaterialValues::dispatch();
        }
    }

}
