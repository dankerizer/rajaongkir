<?php
/**
 * Created by PhpStorm.
 * Project :  laravel8.
 * User: hadie MacBook
 * Date: 26/09/20
 * Time: 13.54
 */

namespace Dankerizer\RajaOngkir;

class RajaOngkir{
    protected $httpClient;
    protected $searchDriver;


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
        'basic'   => [
            'jne',
            'pos',
            'tiki',
            'pcp',
            'esl',
            'rpx',
        ],
        'pro'     => [
            'jne',
            'pos',
            'tiki',
            'rpx',
            'esl',
            'pcp',
            'pandu',
            'wahana',
            'sicepat',
            'jnt',
            'pahala',
            'cahaya',
            'sap',
            'jet',
            'indah',
            'slis',
            'expedito*',
            'dse',
            'first',
            'ncs',
            'star',
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
        'basic'   => [
            'jne',
        ],
        'pro'     => [
            'jne',
            'pos',
            'tiki',
            'pcp',
            'rpx',
            'wahana',
            'sicepat',
            'j&t',
            'sap',
            'jet',
            'dse',
            'first',
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
        'jne'       => 'Jalur Nugraha Ekakurir (JNE)',
        'pos'       => 'POS Indonesia (POS)',
        'tiki'      => 'Citra Van Titipan Kilat (TIKI)',
        'pcp'       => 'Priority Cargo and Package (PCP)',
        'esl'       => 'Eka Sari Lorena (ESL)',
        'rpx'       => 'RPX Holding (RPX)',
        'pandu'     => 'Pandu Logistics (PANDU)',
        'wahana'    => 'Wahana Prestasi Logistik (WAHANA)',
        'sicepat'   => 'SiCepat Express (SICEPAT)',
        'j&t'       => 'J&T Express (J&T)',
        'pahala'    => 'Pahala Kencana Express (PAHALA)',
        'cahaya'    => 'Cahaya Logistik (CAHAYA)',
        'sap'       => 'SAP Express (SAP)',
        'jet'       => 'JET Express (JET)',
        'indah'     => 'Indah Logistic (INDAH)',
        'slis'      => 'Solusi Express (SLIS)',
        'expedito*' => 'Expedito*',
        'dse'       => '21 Express (DSE)',
        'first'     => 'First Logistics (FIRST)',
        'ncs'       => 'Nusantara Card Semesta (NCS)',
        'star'      => 'Star Cargo (STAR)',
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


    public function __construct($apiKey = null, $accountType = null)
    {
        if (isset($apiKey)) {
            if (is_array($apiKey)) {
                if (isset($apiKey[ 'api_key' ])) {
                    $this->apiKey = $apiKey[ 'api_key' ];
                }

                if (isset($apiKey[ 'account_type' ])) {
                    $accountType = $apiKey[ 'account_type' ];
                }
            } elseif (is_string($apiKey)) {
                $this->apiKey = $apiKey;
            }
        }

        if (isset($accountType)) {
            $this->setAccountType($accountType);
        }
    }


    /**
     * RajaOngkir::setApiKey
     *
     * Set RajaOngkir API Key.
     *
     * @param   string $apiKey RajaOngkir API Key
     *
     * @access  public
     * @return  static
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * RajaOngkir::setAccountType
     *
     * Set RajaOngkir account type.
     *
     * @param   string $accountType RajaOngkir Account Type, can be starter, basic or pro
     *
     * @access  public
     * @return  static
     * @throws  \InvalidArgumentException
     */
    public function setAccountType($accountType)
    {
        $accountType = strtolower($accountType);

        if (in_array($accountType, $this->supportedAccountTypes)) {
            $this->accountType = $accountType;
        } else {
            throw new \InvalidArgumentException('RajaOngkir: Invalid Account Type');
        }

        return $this;
    }


    protected function request($path, $params = [], $type = 'GET'){
        $apiUrl = 'https://api.rajaongkir.com';

        switch ($this->accountType) {
            default:
            case 'starter':
                $path = 'starter/' . $path;
                break;

            case 'basic':
                $path = 'basic/' . $path;
                break;

            case 'pro':
                $apiUrl = 'https://pro.rajaongkir.com';
                $path = 'api/' . $path;
                break;
        }


    }
}
