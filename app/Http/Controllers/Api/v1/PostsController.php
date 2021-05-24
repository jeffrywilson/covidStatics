<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Validator;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $redis = Redis::connection();
        $covid_latest = json_decode($redis->get('covid_latest'));
        $vaccinations = json_decode($redis->get('vaccinations'));
        // $observations = json_decode($redis->get('observations'));
        $daily_vaccinations_date = "";
        $people_fully_vaccinated_date = "";
        $people_vaccinated_date = "";
        
        
        $reports = json_decode($redis->get('reports'));
        $key = strtoupper($request->ios_code);
        $resp = json_decode ("{}");
        try{
            if($key != "ALL"){
                $covid_latest_resp = $covid_latest->$key;
                $resp->country = $covid_latest_resp->location;
                $resp->total_confirmed = $covid_latest_resp->total_cases;
                $resp->new_cases = $covid_latest_resp->new_cases;
                $resp->total_deaths = $covid_latest_resp->total_deaths;
                $resp->new_deaths = $covid_latest_resp->new_deaths;
                $resp->tested = $covid_latest_resp->new_tests;
                $resp->total_tested = $covid_latest_resp->total_tests;
                $resp->vaccniated = $covid_latest_resp->new_vaccinations_smoothed;
                $resp->fully_vaccinated = $covid_latest_resp->people_fully_vaccinated;
                $resp->total_vaccinated = $covid_latest_resp->people_vaccinated;
                $resp->owid_date = $covid_latest_resp->last_updated_date;

                // if(isset($observations->$key)){
                //     $resp->tested = $observations->$key->daily_change_in_cumulative_total;
                //     $resp->total_tested = $observations->$key->cumulative_total;
                // } else {
                //     $resp->tested = null;
                //     $resp->total_tested = null;
                // }
    
                if(isset($reports->$key)){
                    $resp->total_recovered = $reports->$key->total_recovered;
                    $resp->active_cases = $reports->$key->active_cases;
                    $resp->date = $reports->$key->last_update;
                } else {
                    $resp->total_recovered = null;
                    $resp->active_cases = null;
                    $resp->date = $covid_latest_resp->last_updated_date;
                }

                foreach ($vaccinations as $vacc) {
                    if($vacc->iso_code == $key){
                        $keys = array_keys($vacc->data);
                        $last_key = array_pop($keys);
                        $daily_vaccinations_date = $vacc->data[$last_key]->date;
                        
                        for( $index = $last_key; $index > $last_key - 100; $index--){
                            if(isset($vacc->data[$index]->people_fully_vaccinated)){
                                $people_fully_vaccinated_date = $vacc->data[$index]->date;
                                $people_vaccinated_date = $vacc->data[$index]->date;
                                break;
                            }
                        }
                        break;
                    }
                }
                $resp->vaccniated_date = $daily_vaccinations_date;
                $resp->fully_vaccinated_date = $people_fully_vaccinated_date;
                $resp->total_vaccinated_date = $people_vaccinated_date;

            } else {
                $key = "OWID_WRL";
                $covid_latest_resp = $covid_latest->$key;
                $resp->total_confirmed = $covid_latest_resp->total_cases;
                $resp->new_cases = $covid_latest_resp->new_cases;
                $resp->total_deaths = $covid_latest_resp->total_deaths;
                $resp->new_deaths = $covid_latest_resp->new_deaths;
                $resp->tested = $covid_latest_resp->new_tests;
                $resp->total_tested = $covid_latest_resp->total_tests;
                $resp->vaccniated = $covid_latest_resp->new_vaccinations_smoothed;
                $resp->fully_vaccinated = $covid_latest_resp->people_fully_vaccinated;
                $resp->total_vaccinated = $covid_latest_resp->people_vaccinated;
                $resp->total_recovered = $reports->$key->total_recovered;
                $resp->active_cases = $reports->$key->active_cases;
                $resp->date = $reports->$key->last_update;
                $resp->owid_date = $covid_latest_resp->last_updated_date;
                $resp->vaccniated_date = $covid_latest_resp->last_updated_date;
                $resp->fully_vaccinated_date = $covid_latest_resp->last_updated_date;
                $resp->total_vaccinated_date = $covid_latest_resp->last_updated_date;
                $resp->country = "World Wide";
            }
            return ['success'=>true , 'report'=>$resp];

        } catch(\Exception $e) {
            Log::debug(__FUNCTION__.$e->getMessage());
            return ['success'=>false , 'data'=>null];
        }
    }
}