<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Helper;

class DailyQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update covid statics every 2 hr';

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
        
            $url = "https://raw.githubusercontent.com/owid/covid-19-data/master/public/data/latest/owid-covid-latest.json";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            if(!empty($response)) $redis->set("covid_latest", $response);
    
            $url = "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_daily_reports/";
            date_default_timezone_set('America/Los_Angeles');
            
            $today = date("m-d-Y");
            $date = new \DateTime();
            $date->modify('-1 day');
            $url_curr = $url . $today . ".csv";
            $url_prev = $url . $date->format("m-d-Y") . ".csv";
    
            if(Helper::get_http_response_code($url_curr) == "200") {
                $csv = file_get_contents($url_curr);
            } else {
                $csv = file_get_contents($url_prev);
            }

            if(!empty($csv)){
                $array = array_map("str_getcsv", explode("\n", $csv));
                $country_region = $array[1][3];
                $total_recovered = 0;
                $total_recovered_world = 0;
                $total_active_cases_world = 0;
                $active_cases = 0;
                $reports = new \stdClass();
                $last_update = $array[1][4];
                foreach ($array as $key => $subarr) {
                    
                    if ($key === 0) {
                        continue;
                    }
                    if(empty($subarr[3])) 
                        break;
                    if(!empty($subarr[9])) $total_recovered_world += $subarr[9];
                    if(!empty($subarr[10])) $total_active_cases_world += $subarr[10];

                    if($country_region != $subarr[3]){
                        $ios_code = Helper::iso3_code($country_region);
                        if(!empty($ios_code)){
                            $reports->$ios_code = new \stdClass();
                            $reports->$ios_code->total_recovered = $total_recovered;
                            $reports->$ios_code->last_update = $last_update;
                            $reports->$ios_code->active_cases = $active_cases;
                        }
                        
                        $country_region = $subarr[3];
                        $total_recovered = (int)$subarr[9];
                        $active_cases = (int)$subarr[10];
                    } else {
                        $total_recovered += (int)$subarr[9];
                        $active_cases += (int)$subarr[10];
                    }
                }
                $reports->OWID_WRL = new \stdClass();
                $reports->OWID_WRL->total_recovered = $total_recovered_world;
                $reports->OWID_WRL->last_update = $last_update;
                $reports->OWID_WRL->active_cases = $total_active_cases_world;
        
                $response = json_encode($reports);
                $redis->set("reports", $response);
            }

            $url = "https://raw.githubusercontent.com/owid/covid-19-data/master/public/data/vaccinations/vaccinations.json";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $redis->set("vaccinations", $response);
            Log::debug('Successfully updated covid statics every 2hr');
            $this->info('Successfully updated covid statics every 2hr');
            
        } catch(\Exception $e) {
            Log::debug("DailyQuote -> ".$e->getMessage());
            $this->info("DailyQuote -> ".$e->getMessage());
        }
    }
}
