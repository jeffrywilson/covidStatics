<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Respectively update covid statics every 2 hr';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get_http_response_code($url) {
        try{
            $headers = get_headers($url);
            return substr($headers[0], 9, 3);
        } catch(\Exception $e) {
            Log::debug(__FUNCTION__.$e->getMessage());
            return "";
        }
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
    
            $codes = json_decode(file_get_contents('http://country.io/iso3.json'), true);
            $names = json_decode(file_get_contents('http://country.io/names.json'), true);
            $name_iso3 = array();
            foreach($codes as $iso2 => $iso3) {
                $name_iso3[$names[$iso2]] = $iso3;
            }
    
            $today = date("m-d-Y");
            $date = new \DateTime();
            $date->modify('-1 day');
            $url_curr = $url . $today . ".csv";
            $url_prev = $url . $date->format("m-d-Y") . ".csv";
    
            if($this->get_http_response_code($url_curr) == "200") {
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
                        if(empty($name_iso3[$country_region])){
                            switch ($country_region) {
                                case 'US':
                                    $ios_code = "USA";
                                    break;
                                case 'Korea, South':
                                    $ios_code = "KOR";
                                    break;
                                case 'North Macedonia':
                                    $ios_code = "MKD";
                                    break;
                                case 'Eswatini':
                                    $ios_code = "SWZ";
                                    break;
                                case 'Czechia':
                                    $ios_code = "CZE";
                                    break;
                                case 'Taiwan*':
                                    $ios_code = "TWN";
                                    break;
                                case 'Cote d\'Ivoire':
                                    $ios_code = "CIV";
                                    break;
                                case 'Congo (Brazzaville)':
                                    $ios_code = "COG";
                                    break;
                                case 'Congo (Kinshasa)':
                                    $ios_code = "COD";
                                    break;
                                case 'Cabo Verde':
                                    $ios_code = "CPV";
                                    break;
                                case 'Timor-Leste':
                                    $ios_code = "TLS";
                                    break;
        
                                default:
                                    $ios_code = "";
                                    break;
                            }
                        } else {
                            $ios_code = $name_iso3[$country_region];
                        }
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
    
            // $url = "https://raw.githubusercontent.com/owid/covid-19-data/master/public/data/testing/covid-testing-all-observations.csv";
            // $ch = curl_init($url);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // $observations_csv = curl_exec($ch);
            // curl_close($ch);
            
    
            // $array = array_map("str_getcsv", explode("\n", $observations_csv));
            // $iso_code = $array[1][1];
            // $daily_change_in_cumulative_total = 0;
            // $cumulative_total = 0;
            // $observations = new \stdClass();
            // foreach ($array as $key => $subarr) {
            //     if ($key === 0) {
            //         continue;
            //     }
            //     if(empty($subarr[1])) 
            //         break;
            //     if($iso_code != $subarr[1]){
            //         $observations->$iso_code = new \stdClass();
            //         $observations->$iso_code->daily_change_in_cumulative_total = $daily_change_in_cumulative_total;
    
            //         $observations->$iso_code->cumulative_total = $cumulative_total;
                    
            //         $iso_code = $subarr[1];
                    
            //     } else {
            //         $daily_change_in_cumulative_total = (int)$subarr[6];
            //         if(!empty($subarr[7])) $cumulative_total = (int)$subarr[7];
            //     }
            // }
            // $response = json_encode($observations);
            // $redis->set("observations", $response);
            
            Log::debug($response);
            $this->info('Successfully updated daily every 2hr');
            
        } catch(\Exception $e) {
            Log::debug(__FUNCTION__.$e->getMessage());
            $this->info(__FUNCTION__.$e->getMessage());
        }
    }
}
