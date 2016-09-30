<?php

namespace AppBundle\Controller;

use AppBundle\Form\ChargingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ServiceTypeController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Method("GET")
     */
    public function indexAction()
    {
        $results = $this->get('app.service_type_repository')->getResults();

        return $this->render('AppBundle:ServiceType:index.html.twig', array(
            'results' => $results
        ));
    }

    /**
     * @Route("/fill/{id}", name="fill")
     * @Method("GET")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fillAction($id)
    {
        $serviceType = $this->get('app.service_type_repository')->find($id);

        $form = $this->createForm(ChargingType::class);
        $form->get('service_type')->setData($serviceType->getId());

        return $this->render('AppBundle:ServiceType:fill.html.twig', ['form' => $form->createView(), 'serviceType' => $serviceType]);
    }

    /**
     * @Route("/charging", name="charging")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chargingAction(Request $request)
    {
        $form = $this->createForm(ChargingType::class);
        $form->handleRequest($request);

        if($form->isValid()) {
            $data = $form->getData();

            try {
                $chargingService = $this->get('app.charging_service');
                $response = $chargingService->charging($data['phone'], $data['service_type']);
            } catch (\Exception $e) {
                return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $errors = $this->getErrorMessages($form);

            return new JsonResponse($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($response);
    }

    /**
     * Get error messages in form
     *
     * @param Form $form
     * @return array
     */
    private function getErrorMessages(Form $form) {
        $errors = array();
        foreach ($form->getErrors(true, false) as $error) {
            $errors[] = $error->current()->getMessage();
        }

        return $errors;
    }

}
