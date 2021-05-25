<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Helper;

class HourlyQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update covid testing all observations every 2 hr';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $redis = Redis::connection();
        
            $url = "https://raw.githubusercontent.com/owid/covid-19-data/master/public/data/testing/covid-testing-all-observations.csv";
            $observations_csv = file_get_contents($url);
    
            $array = array_map("str_getcsv", explode("\n", $observations_csv));
            $iso_code = $array[1][1];
            $daily_change_in_cumulative_total = 0;
            $daily_change_in_cumulative_totals = 0;
            $cumulative_total = 0;
            $cumulative_totals = 0;
            $date_cumulative = "";
            $observations = new \stdClass();

            foreach ($array as $key => $subarr) {
                if ($key === 0) {
                    continue;
                }
                if(empty($subarr[1])) 
                    break;
                if($iso_code != $subarr[1]){
                    $observations->$iso_code = new \stdClass();
                    $observations->$iso_code->daily_change_in_cumulative_total = $daily_change_in_cumulative_total;
                    $observations->$iso_code->date_cumulative = $date_cumulative;
                    $observations->$iso_code->cumulative_total = $cumulative_total;
                    $daily_change_in_cumulative_totals += $daily_change_in_cumulative_total;
                    $cumulative_totals += $cumulative_total; 
                    $iso_code = $subarr[1];
                    
                } else {
                    $date_cumulative = $subarr[2];
                    if(!empty($subarr[6])) $daily_change_in_cumulative_total = (int)$subarr[6];
                    if(!empty($subarr[7])) $cumulative_total = (int)$subarr[7];
                }
            }

            $observations->OWID_WRL = new \stdClass();
            $observations->OWID_WRL->daily_change_in_cumulative_total = $daily_change_in_cumulative_totals;
            $observations->OWID_WRL->cumulative_total = $cumulative_totals;

            $response = json_encode($observations);
            $redis->set("observations", $response);
            
            Log::debug('Successfully updated covid observations every 2hr');
            $this->info('Successfully updated covid observations every 2hr');
            
        } catch(\Exception $e) {
            Log::debug("HourlyQuote -> ".$e->getMessage());
            $this->info("HourlyQuote -> ".$e->getMessage());
        }
    }
}
