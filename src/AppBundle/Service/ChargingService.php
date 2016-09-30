<?php
/**
 * Created by PhpStorm.
 * User: recchia
 * Date: 30/09/16
 * Time: 15:57
 */

namespace AppBundle\Service;


use AppBundle\Repository\ServiceTypeRepository;
use Zend\Soap\Client;

class ChargingService
{
    private $serviceTypeRepository;
    private $path;
    private $username;
    private $password;
    private $wsdl;
    private $providerId;
    private $client;
    private $response;

    /**
     * ChargingService constructor.
     *
     * @param ServiceTypeRepository $repository
     * @param $rootPath
     * @param $username
     * @param $password
     * @param $wsdl
     * @param $providerId
     */
    public function __construct(ServiceTypeRepository $repository, $rootPath, $username, $password, $wsdl, $providerId)
    {
        $this->serviceTypeRepository = $repository;
        $this->path = $rootPath . '/config/wsdl/charging/';
        $this->username = $username;
        $this->password = $password;
        $this->wsdl = $this->path . $wsdl;
        $this->providerId = $providerId;
        $this->client = new Client($this->wsdl, ['encoding' => 'UTF-8', 'soap_version' => SOAP_1_1]);
    }

    /**
     * Make Charge to user account
     *
     * @param $phone
     * @param $serviceTypeId
     *
     * @return array
     */
    public function charging($phone, $serviceTypeId)
    {
        $parameters = $this->buildParams($phone, $serviceTypeId);
        $header = $this->buildSecurityHeader();
        $this->client->addSoapInputHeader($header);
        $this->response = $this->client->call('chargeAmount', $parameters);

        return $this->response;
    }

    /**
     * Build params for client service
     *
     * @param $phone
     * @param $serviceTypeId
     *
     * @return array
     */
    protected function buildParams($phone, $serviceTypeId)
    {
        $params = $this->serviceTypeRepository->getServiceTypeWithCountry($serviceTypeId);
        $code = rand(1000, 9000);

        $chargingInformation = [
            'amount' => $params['amount'],
            'code' => $code,
            'currency' => $params['isoCode'],
            'description' => $params['description']
        ];

        $extraParams["param"] = [
            ['name' => 'providerId', 'value' => $this->providerId],
            ['name' => 'serviceType', 'value' => $params['number']],
            ['name' => 'contentId', 'value' => rand(1000, 9000)],
            ['name' => 'contentDescription', 'value' => $params['description']],
            ['name' => 'retailPrice', 'value' => $params['amount']],
            ['name' => 'calculatedTax', 'value' => $params['tax']],
            ['name' => 'calculatedPromo', 'value' => "0"],
            ['name' => 'downloadFee', 'value' => "0"],
            ['name' => 'region', 'value' => "1"],
            ['name' => 'profile', 'value' => "1"]
        ];

        $parameters = [
            'endUserIdentifier' => $phone,
            'charge' => $chargingInformation,
            'extraParams' => $extraParams,
            'referenceCode' => $code
        ];

        return $parameters;
    }

    /**
     * Build security header for client
     *
     * @return SoapHeader
     */
    private function buildSecurityHeader()
    {
        $digest = $this->getDigest();
        $path = $this->path . 'authentication.xml';
        $content = file_get_contents($path);
        $header = sprintf($content, $this->username, $digest['digest'], $digest['random'], $digest['timestamp'], rand(0, 999));
        $auth = new \SoapVar($header, XSD_ANYXML);
        $soapHeader = new \SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $auth, true);

        return $soapHeader;
    }

    /**
     * Generate secure data for header
     *
     * @return array
     */
    private function getDigest()
    {
        $random = mt_rand(10000, 999999);
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(pack('H*', sha1(pack('a*', $random) . pack('a*', $timestamp) . pack('a*', $this->password))));

        return ['random' => $random, 'timestamp' => $timestamp, 'digest' => $digest];
    }
}