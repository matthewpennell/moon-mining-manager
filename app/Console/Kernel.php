<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\PollWallet;
use App\Jobs\UpdateReprocessedMaterials;
use App\Jobs\PollRefineries;
use App\Jobs\UpdateMaterialValues;
use App\Jobs\PollMiningObservers;
use App\Jobs\GenerateInvoices;
use App\Jobs\PollStructures;
use App\Jobs\ArchiveReprocessedMaterialsHistory;
use App\Jobs\PollExtractions;
use App\Jobs\ProcessMiningActivity;
use App\Jobs\GenerateRentalInvoices;
use App\Jobs\CorporationChecks;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // Poll all corporation structures to look for refineries.
        $schedule->job(new PollStructures)->dailyAt('00:00');

        // Poll all refineries for information about upcoming extraction cycles.
        $schedule->job(new PollExtractions)->dailyAt('00:10');

        // Check for any newly active refineries.
        $schedule->job(new PollRefineries)->dailyAt('00:20');

        // Check for miners making payments to the corporation wallet.
        $schedule->job(new PollWallet)->hourlyAt(30);

        // Pull the mining activity for the day and store it.
        $schedule->job(new PollMiningObservers)->dailyAt('02:00');

        // Check for any new ores that have been mined where we don't have details of their component materials.
        $schedule->job(new UpdateReprocessedMaterials)->twiceDaily(4, 16);
        
        // Update the stored prices for materials and ores.
        $schedule->job(new UpdateMaterialValues)->dailyAt('05:00');
        
        // Process any new mining activity.
        $schedule->job(new ProcessMiningActivity)->dailyAt('06:00');
        
        // Archive old price history records.
        $schedule->job(new ArchiveReprocessedMaterialsHistory)->dailyAt('06:55');

        // Send weekly invoices.
        $schedule->job(new GenerateInvoices)->weekly()->mondays()->at('07:00');

        // Send monthly rental invoices.
        $schedule->job(new GenerateRentalInvoices)->monthlyOn(1, '09:00');

        // Monthly check of miner corporation membership.
        $schedule->job(new CorporationChecks)->monthlyOn(15, '23:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
