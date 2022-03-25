<?php

namespace Dankerizer\RajaOngkir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller{

    /**
     * @var RajaOngkir
     */
    protected $client;

    public function __construct() {
        $key = env('RAJAONGKIR_API_KEY');
        $accountType = env('RAJAONGKIR_ACCOUNT_TYPE','pro');
        $this->client = new RajaOngkir($key, $accountType);
    }

    public function get_cost( $request){

        $metrics = isset($request['weight']) ? abs($request['weight']) : 0;
        if (isset($request['metrics'])){
            $metrics = $request['metrics'];
        }
        $origin = [
            'subdistrict' => isset($request['origin']) ? abs($request['origin']) : null,
        ];

        $destination = [
            'subdistrict' => isset($request['destination']) ? abs($request['destination'])  : null,
        ];

       return $this->client->getCost($origin,$destination,$metrics,$request['courier']);

    }

    public function getWaybill(Request $request){

    }
}
