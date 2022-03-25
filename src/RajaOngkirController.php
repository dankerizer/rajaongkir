<?php

namespace Dankerizer\RajaOngkir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller{

    public function __construct() {
        //
    }

    public function get_cost(Request $request){
        $key = env('RAJAONGKIR_API_KEY');
        $accountType = env('RAJAONGKIR_ACCOUNT_TYPE','pro');
        $client = new RajaOngkir($key, $accountType);


    }
}
