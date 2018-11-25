<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Sascha Sedar
 * Date: 27.07.2018
 * Time: 12:56
 */

namespace Superbox\SyliusDataPortabilityPlugin\Controller;

use Superbox\SyliusDataPortabilityPlugin\Services\DataAggregator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

use Sylius\Component\Resource\Repository\RepositoryInterface;

final class DataPortabilityController extends Controller
{
    /**
     * @return Response
     */
    public function dataPortabilityAction(Request $request)
    {
        $defaultData = array();

        $form = $this->createFormBuilder($defaultData)
            ->add('email', EmailType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Email()
                )
            ))
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

           // $userData = $this->collectData($form->getData()['email']);
            // Transform and send data
            $dataAggregator = $this->get('app.services.data_aggregator');
            $dataAggregator->collectData($form->getData()['email']);

            if (file_exists($form->getData()['email'].'.csv')) {
                $this->get('sylius.email_sender')->send('data_portability', array($form->getData()['email']), array(), array($form->getData()['email'] . '.csv'));
             #   unlink($form->getData()['email'].'.csv');
            }

            $this->addFlashMessage('success','superbox.data_portability.success');
        }

        return $this->render('@SuperboxDataPortabilityPlugin/data_portability_page.html.twig',
            array(
                'form' => $form->createView()));
    }

    private function addFlashMessage(string $type, string $message)
    {
        $locale = $this->get('sylius.context.locale')->getLocaleCode();
        $message = $this->get('translator')->trans($message,array(),'flashes',$locale);

        $this->addFlash($type, $message);
    }
}