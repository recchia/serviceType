<?php

namespace AppBundle\Controller;

use AppBundle\Form\ChargingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zend\Soap\Client;

class ServiceTypeController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('AppBundle:ServiceType')->getResults();

        return $this->render('AppBundle:ServiceType:index.html.twig', array(
            'results' => $results
        ));
    }

    /**
     * @Route("/fill/{id}", name="fill")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fillAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $serviceType = $em->find('AppBundle:ServiceType', $id);

        $form = $this->createForm(ChargingType::class);
        $form->get('service_type')->setData($id);

        return $this->render('AppBundle:ServiceType:fill.html.twig', ['form' => $form->createView(), 'serviceType' => $serviceType]);
    }

    /**
     * @Route("/charging", name="charging")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chargingAction(Request $request)
    {
        $params = $this->buildParams($request);

        $wsdl = $this->getParameter('kernel.root_dir') . '/config/wsdl/charging/' . $this->getParameter('service_wsdl');
        $user = $this->getParameter('service_username');
        $password = $this->getParameter('service_password');

        try {
            $client = new Client($wsdl, ['encoding' => 'UTF-8', 'soap_version' => SOAP_1_1]);
            $header = $this->buildSecurityHeader($user, $password);
            $client->addSoapInputHeader($header);
            $response = $client->call('chargeAmount', $params);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($response);
    }

    /**
     * @param $username
     * @param $password
     * @return SoapHeader
     */
    private function buildSecurityHeader($username, $password)
    {
        $digest = $this->getDigest($password);

        $auth = '
			<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				<wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
					<wsse:Username>' . trim($username) . '</wsse:Username>
					<wsse:Password Type="...#PasswordDigest">' . $digest['digest'] . '</wsse:Password>
					<wsse:Nonce>' . $digest['random'] . '</wsse:Nonce>
					<wsu:Created>' . $digest['timestamp'] . '</wsu:Created>
				</wsse:UsernameToken>
			</wsse:Security>
			<tns:RequestSOAPHeader xmlns:tns="http://www.huawei.com/schema/common/v2_1">
				<tns:AppId>service0001</tns:AppId>
				<tns:TransId>200903241230451' . rand(0, 999) . '</tns:TransId>
				<tns:OA>tel:08612345678900</tns:OA>
				<tns:FA>tel:08612345678900</tns:FA>
			</tns:RequestSOAPHeader>';
        $authvalues = new \SoapVar($auth, XSD_ANYXML);
        $header = new \SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "Security", $authvalues, true);

        return $header;
    }

    /**
     * @param $password
     * @return string
     */
    private function getDigest($password)
    {
        $random = mt_rand(10000, 999999);
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(pack('H*', sha1(pack('a*', $random) . pack('a*', $timestamp) . pack('a*', $password))));

        return ['random' => $random, 'timestamp' => $timestamp, 'digest' => $digest];
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function buildParams(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $em->getRepository('AppBundle:ServiceType')->getServiceTypeWithCountry($request->request->get('charging')['service_type']);
        $code = rand(1000, 9000);

        $chargingInformation = [
            'amount' => $params['amount'],
            'code' => $code,
            'currency' => $params['isoCode'],
            'description' => $params['description']
        ];

        $extraParams["param"] = [
            ['name' => 'providerId', 'value' => $this->getParameter('service_provider_id')],
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
            'endUserIdentifier' => $request->request->get('charging')['phone'],
            'charge' => $chargingInformation,
            'extraParams' => $extraParams,
            'referenceCode' => $code
        ];

        return $parameters;
    }

}
