<?php
/**
 * Created by PhpStorm.
 * Project :  laravel8.
 * User: hadie MacBook
 * Date: 26/09/20
 * Time: 13.54
 * https://medium.com/cafe24-ph-blog/build-your-own-laravel-package-in-10-minutes-using-composer-867e8ef875dd
 */

namespace Dankerizer\RajaOngkir;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class RajaOngkir
{
    protected $httpClient;
    protected $searchDriver;
    protected $api_url;


    const ACCOUNT_STARTER = 'starter';
    const ACCOUNT_BASIC = 'basic';
    const ACCOUNT_PRO = 'pro';

    /**
     * RajaOngkir::$accountType
     *
     * RajaOngkir Account Type.
     *
     * @access  protected
     * @type    string
     */
    protected $accountType = 'starter';


    /**
     * RajaOngkir::$apiKey
     *
     * RajaOngkir API key.
     *
     * @access  protected
     * @type    string
     */
    protected $apiKey = null;

    /**
     * List of Supported Account Types
     *
     * @access  protected
     * @type    array
     */
    protected $supportedAccountTypes = [
        'starter',
        'basic',
        'pro',
    ];

    /**
     * Supported Couriers
     *
     * @access  protected
     * @type    array
     * @todo check again
     */
    protected $supportedCouriers = [
        'starter' => [
            'jne',
            'pos',
            'tiki',
        ],
        'basic' => [
            'jne',
            'pos',
            'tiki',
            'pcp',
            'esl',
            'rpx',
        ],
        'pro' => [
            'cahaya',
            'dse',
            'esl',
            'expedito*',
            'first',
            'ide',
            'idl',
            'indah',
            'jet',
            'jne',
            'jnt',
            'lion',
            'ncs',
            'ninja',
            'pahala',
            'pandu',
            'pcp',
            'pos',
            'rex',
            'rpx',
            'sap',
            'sentral',
            'sicepat',
            'slis',
            'star',
            'tiki',
            'wahana',
        ],
    ];

    /**
     * RajaOngkir::$supportedWaybills
     *
     * RajaOngkir supported couriers waybills.
     *
     * @access  protected
     * @type    array
     * @todo check again
     */
    protected $supportedWayBills = [
        'starter' => [],
        'basic' => [
            []
        ],
        'pro' => [
            'pos', 'wahana', 'jnt', 'sap', 'sicepat', 'jet', 'dse', 'first', 'ninja', 'lion', 'idl', 'rex', 'ide', 'sentral'
        ],
    ];


    /**
     * RajaOngkir::$couriersList
     *
     * RajaOngkir courier list.
     *
     * @access  protected
     * @type array
     */
    protected $couriersList = [
        'jne' => 'Jalur Nugraha Ekakurir (JNE)',
        'pos' => 'POS Indonesia (POS)',
        'tiki' => 'Citra Van Titipan Kilat (TIKI)',
        'pcp' => 'Priority Cargo and Package (PCP)',
        'esl' => 'Eka Sari Lorena (ESL)',
        'rpx' => 'RPX Holding (RPX)',
        'pandu' => 'Pandu Logistics (PANDU)',
        'wahana' => 'Wahana Prestasi Logistik (WAHANA)',
        'sicepat' => 'SiCepat Express (SICEPAT)',
        'j&t' => 'J&T Express (J&T)',
        'pahala' => 'Pahala Kencana Express (PAHALA)',
        'cahaya' => 'Cahaya Logistik (CAHAYA)',
        'sap' => 'SAP Express (SAP)',
        'jet' => 'JET Express (JET)',
        'indah' => 'Indah Logistic (INDAH)',
        'slis' => 'Solusi Express (SLIS)',
        'expedito*' => 'Expedito*',
        'dse' => '21 Express (DSE)',
        'first' => 'First Logistics (FIRST)',
        'ncs' => 'Nusantara Card Semesta (NCS)',
        'star' => 'Star Cargo (STAR)',
    ];


    /**
     * RajaOngkir::$response
     *
     * RajaOngkir response.
     *
     * @access  protected
     * @type    mixed
     */
    protected $response;


    /**
     * @var array
     * Response Error
     * @access public
     * @type array
     */
    public $errors = [];


    /**
     * RajaOngkir constructor.
     * @param null $apiKey
     * @param null $accountType
     *
     */
    public function __construct($apiKey = null, $accountType = null)
    {
        if (isset($apiKey)) {
            if (is_array($apiKey)) {
                if (isset($apiKey['api_key'])) {
                    $this->apiKey = $apiKey['api_key'];
                }

                if (isset($apiKey['account_type'])) {
                    $accountType = $apiKey['account_type'];
                }
            } elseif (is_string($apiKey)) {
                $this->apiKey = $apiKey;
            }
        }

        if (isset($accountType)) {
            $this->setAccountType($accountType);
        } else {
            $this->setAccountType(self::ACCOUNT_STARTER);
        }
        $this->api_url = $this->set_api_url();
        $this->httpClient = new Client(['base_uri' => $this->api_url]);
    }


    /**
     * RajaOngkir::setApiKey
     *
     * Set RajaOngkir API Key.
     *
     * @param string $apiKey RajaOngkir API Key
     *
     * @return  static
     * @access  public
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * RajaOngkir::setAccountType
     *
     * Set RajaOngkir account type.
     *
     * @param string $accountType RajaOngkir Account Type, can be starter, basic or pro
     *
     * @return  static
     * @throws \InvalidArgumentException
     * @access  public
     */
    public function setAccountType(string $accountType)
    {
        $accountType = strtolower($accountType);

        if (in_array($accountType, $this->supportedAccountTypes)) {
            $this->accountType = $accountType;
        } else {
            throw new \InvalidArgumentException('RajaOngkir: Invalid Account Type');
        }

        return $this;
    }


    /**
     * @return string
     */
    protected function set_api_url()
    {
        $apiUrl = 'https://api.rajaongkir.com';

        switch ($this->accountType) {
            default:
            case 'starter':
                $path = '/starter/';
                break;

            case 'basic':
                $path = '/basic/';
                break;

            case 'pro':
                $apiUrl = 'https://pro.rajaongkir.com';
                $path = '/api/';
                break;
        }

        return $apiUrl . $path;

    }


    /**
     * @param $path
     * @param array $params
     * @param string $method
     * @return array|ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request($path, $params = [], $method = 'GET')
    {

        if ($method === 'POST') {
            $params['form_params'] = $params;
            $params['headers'] = [
                'content-type' => 'application/x-www-form-urlencoded'
            ];

        }


        $params['headers'] = [
            'key' => $this->apiKey,
            'Accept' => 'application/json',
        ];


        try {
            $request = $this->httpClient->request($method, $path, $params);
            $body = $request->getBody();


            if ($body instanceof \DOMDocument) {
                $this->errors[404] = 'Page Not Found!';
            } else {
                $content = $body->getContents();
                $json = json_decode($content, true);

                $status = $request->getStatusCode();;

                if ($status === 200) {
                    $body = $json['rajaongkir'];
                    if (isset($body['results'])) {
                        if (count($body['results']) == 1 && isset($body['results'][0])) {
                            return $body['results'][0];
                        } elseif (count($body['results'])) {
                            return $body['results'];
                        } else {

                        }
                    } elseif (isset($body['result'])) {
                        return $body['result'];
                    }
                } else {
                    $this->errors[$status['code']] = $status['description'];
                }
            }


        } catch (RequestException $e) {

            $response = [
                'status' => [
                    'code' => 501,
                    'message' => 'data-not-found',
                    'description' => 'Invalid waybill or courier'
                ],
//                'key' => $this->apiKey,
                'errors' => $e,

            ];

            if (env('APP_DEBUG')) {
                return $response;
            }
        }


        return $this->errors;
    }


    /**
     * @param $response
     */
    protected function validate_response($response)
    {

    }

    /**
     * RajaOngkir::getCouriersList
     *
     * Get list of supported couriers.
     *
     * @access  public
     * @return  array|bool Returns FALSE if failed.
     */
    public function getCouriersList()
    {
        return $this->couriersList;
    }

    /**
     * RajaOngkir::getProvinces
     *
     * Get list of provinces.
     *
     * @access  public
     * @return array|ResponseInterface|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function getProvinces()
    {
        return $this->request('province');
    }



    // ------------------------------------------------------------------------

    /**
     * RajaOngkir::getProvince
     *
     * Get detail of single province.
     *
     * @param int $idProvince Province ID
     *
     * @access  public
     * @return array|ResponseInterface|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProvince($idProvince)
    {
        return $this->request('province', ['id' => $idProvince]);
    }


    /**
     * RajaOngkir::getCities
     *
     * Get list of province cities.
     *
     * @param int $idProvince Province ID
     *
     * @access  public
     * @return array|ResponseInterface|void
     */
    public function getCities($idProvince = null)
    {
        $params = [];

        if (!is_null($idProvince)) {
            $params['province'] = $idProvince;
        }

        return $this->request('city', $params);
    }


    // ------------------------------------------------------------------------

    /**
     * RajaOngkir::getCity
     *
     * Get detail of single city.
     *
     * @param int $idCity City ID
     *
     * @access  public
     * @return  array|bool Returns FALSE if failed.
     */
    public function getCity($idCity)
    {
        return $this->request('city', ['id' => $idCity]);
    }



    // ------------------------------------------------------------------------

    /**
     * RajaOngkir::getSubdistricts
     *
     * Get list of city subdisctricts.
     *
     * @param int $idCity City ID
     *
     * @access  public
     * @return array|false|ResponseInterface|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSubdistricts($idCity)
    {
        if ($this->accountType === 'starter') {
            $this->errors[302] = 'Unsupported Subdistricts Request. Tipe akun starter tidak mendukung hingga tingkat kecamatan.';

            return false;
        } elseif ($this->accountType === 'basic') {
            $this->errors[302] = 'Unsupported Subdistricts Request. Tipe akun basic tidak mendukung hingga tingkat kecamatan.';

            return false;
        }

        return $this->request('subdistrict', ['city' => $idCity]);
    }


    /**
     * RajaOngkir::getSubdistrict
     *
     * Get detail of single subdistrict.
     *
     * @param int $idSubdistrict Subdistrict ID
     *
     * @access  public
     * @return array|bool|void
     */
    public function getSubdistrict($idSubdistrict)
    {
        if ($this->accountType === 'starter') {
            $this->errors[302] = 'Unsupported Subdistricts Request. Tipe akun starter tidak mendukung hingga tingkat kecamatan.';

            return false;
        } elseif ($this->accountType === 'basic') {
            $this->errors[302] = 'Unsupported Subdistricts Request. Tipe akun basic tidak mendukung hingga tingkat kecamatan.';

            return false;
        }

        return $this->request('subdistrict', ['id' => $idSubdistrict]);
    }


    /**
     * RajaOngkir::getInternationalOrigins
     *
     * Get list of supported international origins.
     *
     * @param int $idProvince Province ID
     *
     * @access  public
     * @return array|bool|void
     */
    public function getInternationalOrigins($idProvince = null)
    {
        if ($this->accountType === 'starter') {
            $this->errors[301] = 'Unsupported International Origin Request. Tipe akun starter tidak mendukung tingkat international.';

            return false;
        }

        $params = [];

        if (isset($idProvince)) {
            $params['province'] = $idProvince;
        }

        return $this->request('v2/internationalOrigin', $params);
    }


    // ------------------------------------------------------------------------

    /**
     * RajaOngkir::getInternationalOrigin
     *
     * Get list of supported international origins by city and province.
     *
     * @param int $idCity City ID
     * @param int $idProvince Province ID
     *
     * @access  public
     * @return array|bool|void
     */
    public function getInternationalOrigin($idCity = null, $idProvince = null)
    {
        if ($this->accountType === 'starter') {
            $this->errors[301] = 'Unsupported International Origin Request. Tipe akun starter tidak mendukung tingkat international.';

            return false;
        }

        if (isset($idCity)) {
            $params['id'] = $idCity;
        }

        if (isset($idProvince)) {
            $params['province'] = $idProvince;
        }

        return $this->request('v2/internationalOrigin', $params);
    }


    // ------------------------------------------------------------------------

    /**
     * RajaOngkir::getInternationalDestinations
     *
     * Get list of international destinations.
     *
     * @param int $id_country Country ID
     *
     * @access  public
     * @return array|bool|void
     */
    public function getInternationalDestinations()
    {
        if ($this->accountType === 'starter') {
            $this->errors[301] = 'Unsupported International Destination Request. Tipe akun starter tidak mendukung tingkat international.';

            return false;
        }

        return $this->request('v2/internationalDestination');
    }



    // ------------------------------------------------------------------------

    /**
     * RajaOngkir::getInternationalDestination
     *
     * Get International Destination
     *
     * @param int $idCountry Country ID
     *
     * @access  public
     * @return array|bool|void
     */
    public function getInternationalDestination($idCountry = null)
    {
        if ($this->accountType === 'starter') {
            $this->errors[301] = 'Unsupported International Destination Request. Tipe akun starter tidak mendukung tingkat international.';

            return false;
        }

        $params = [];

        if (isset($idCountry)) {
            $params['id'] = $idCountry;
        }

        return $this->request('v2/internationalDestination', $params);
    }


    /**
     * RajaOngkir::getCost
     *
     * Get cost calculation.
     *
     * @param array $origin City, District or Subdistrict Origin
     * @param array $destination City, District or Subdistrict Destination
     * @param array|integer|string $metrics Array of Specification
     *                                  weight      int     weight in gram (required)
     *                                  length      number  package length dimension
     *                                  width       number  package width dimension
     *                                  height      number  package height dimension
     *                                  diameter    number  package diameter
     * @param string $courier Courier Code
     *
     * @access   public
     * @return array|bool|\Exception|GuzzleException
     * @see      http://rajaongkir.com/dokumentasi/pro
     *
     * @example
     * $rajaongkir->getCost(
     *      ['city' => 1],
     *      ['subdistrict' => 12],
     *      ['weight' => 100, 'length' => 100, 'width' => 100, 'height' => 100, 'diameter' => 100],
     *      'jne'
     * );
     *
     */
    public function getCost(array $origin, array $destination, $metrics, $courier)
    {
        if (is_array($courier)) {
            $courier = implode(':', $courier);
        }
        $params['courier'] = strtolower($courier);

        $params['originType'] = strtolower(key($origin));
        $params['destinationType'] = strtolower(key($destination));

        if ($params['originType'] !== 'city') {
            $params['originType'] = 'subdistrict';
        }

        if (!in_array($params['destinationType'], ['city', 'country'])) {
            $params['destinationType'] = 'subdistrict';
        }

        if (is_array($metrics)) {
            if (!isset($metrics['weight']) and
                isset($metrics['length']) and
                isset($metrics['width']) and
                isset($metrics['height'])
            ) {
                $metrics['weight'] = (($metrics['length'] * $metrics['width'] * $metrics['height']) / 6000) * 1000;
            } elseif (isset($metrics['weight']) and
                isset($metrics['length']) and
                isset($metrics['width']) and
                isset($metrics['height'])
            ) {
                $weight = (($metrics['length'] * $metrics['width'] * $metrics['height']) / 6000) * 1000;

                if ($weight > $metrics['weight']) {
                    $metrics['weight'] = $weight;
                }
            }

            foreach ($metrics as $key => $value) {
                $params[$key] = $value;
            }
        } elseif (is_numeric($metrics)) {
            $params['weight'] = $metrics;
        }

        switch ($this->accountType) {
            case 'starter':

                if ($params['destinationType'] === 'country') {
                    $this->errors[301] = 'Unsupported International Destination. Tipe akun starter tidak mendukung pengecekan destinasi international.';

                    return false;
                } elseif ($params['originType'] === 'subdistrict' or $params['destinationType'] === 'subdistrict') {
                    $this->errors[302] = 'Unsupported Sub-district Origin-Destination. Tipe akun starter tidak mendukung pengecekan ongkos kirim sampai kecamatan.';

                    return false;
                }

                if (!isset($params['weight']) and
                    isset($params['length']) and
                    isset($params['width']) and
                    isset($params['height'])
                ) {
                    $this->errors[304] = 'Unsupported Dimension. Tipe akun starter tidak mendukung pengecekan biaya kirim berdasarkan dimensi.';

                    return false;
                } elseif (isset($params['weight']) and $params['weight'] > 30000) {
                    $this->errors[305] = 'Unsupported Weight. Tipe akun starter tidak mendukung pengecekan biaya kirim dengan berat lebih dari 30000 gram (30kg).';

                    return false;
                }

                if (!in_array($params['courier'], $this->supportedCouriers[$this->accountType])) {
                    $this->errors[303] = 'Unsupported Courier. Tipe akun starter tidak mendukung pengecekan biaya kirim dengan kurir ' . $this->couriersList[$courier] . '.';

                    return false;
                }

                break;

            case 'basic':

                if ($params['originType'] === 'subdistrict' or $params['destinationType'] === 'subdistrict') {
                    $this->errors[302] = 'Unsupported Subdistrict Origin-Destination. Tipe akun basic tidak mendukung pengecekan ongkos kirim sampai kecamatan.';

                    return false;
                }

                if (!isset($params['weight']) and
                    isset($params['length']) and
                    isset($params['width']) and
                    isset($params['height'])
                ) {
                    $this->errors[304] = 'Unsupported Dimension. Tipe akun basic tidak mendukung pengecekan biaya kirim berdasarkan dimensi.';

                    return false;
                } elseif (isset($params['weight']) and $params['weight'] > 30000) {
                    $this->errors[305] = 'Unsupported Weight. Tipe akun basic tidak mendukung pengecekan biaya kirim dengan berat lebih dari 30000 gram (30kg).';

                    return false;
                } elseif (isset($params['weight']) and $params['weight'] < 30000) {
                    unset($params['length'], $params['width'], $params['height']);
                }

                if (!in_array($params['courier'], $this->supportedCouriers[$this->accountType])) {
                    $this->errors[303] = 'Unsupported Courier. Tipe akun basic tidak mendukung pengecekan biaya kirim dengan kurir ' . $this->couriersList[$courier] . '.';

                    return false;
                }

                break;
        }

        $params['origin'] = $origin[key($origin)];
        $params['destination'] = $destination[key($destination)];

        $path = key($destination) === 'country' ? 'internationalCost' : 'cost';
        try {
            return $this->request($path, $params, 'POST');
        } catch (GuzzleException $e) {
            return $e;
        }
    }


    /**
     * RajaOngkir::getWaybill
     *
     * Get detail of waybill.
     *
     * @param int $idWaybill Receipt ID
     * @param null|string $courier Courier Code
     *
     * @access  public
     * @return  array|bool Returns FALSE if failed.
     */
    public function getWaybill($idWaybill, $courier)
    {
        $courier = strtolower($courier);

        if (in_array($courier, $this->supportedWayBills[$this->accountType])) {
            return $this->request('waybill', [
                'key' => $this->apiKey,
                'waybill' => $idWaybill,
                'courier' => $courier,
            ], 'POST');
        }

        return false;
    }


    /**
     * Rajaongkir::getCurrency
     *
     * Get Rajaongkir currency.
     *
     * @access  public
     * @return  array|bool Returns FALSE if failed.
     */
    public function getCurrency()
    {
        if ($this->accountType !== 'starter') {
            return $this->request('currency');
        }

        $this->errors[301] = 'Unsupported Get Currency. Tipe akun starter tidak mendukung pengecekan currency.';

        return false;
    }


    /**
     * Rajaongkir::getSupportedCouriers
     *
     * Gets list of supported couriers by your account.
     *
     * @return array|bool Returns FALSE if failed.
     */
    public function getSupportedCouriers()
    {
        if (isset($this->supportedCouriers[$this->accountType])) {
            return $this->supportedCouriers[$this->accountType];
        }

        return false;
    }

    /**
     * Rajaongkir::getSupportedWayBills
     *
     * Gets list of supported way bills based on account type.
     *
     * @return array|bool Returns FALSE if failed.
     */
    public function getSupportedWayBills()
    {
        if (isset($this->supportedWayBills[$this->accountType])) {
            return $this->supportedWayBills[$this->accountType];
        }

        return false;
    }

    public function getResponse()
    {
        return $this->response;
    }


}
