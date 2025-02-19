<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\APIClient as SpaceTraderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('/', 'app.index')]
    public function indexAction(Request $request): Response
    {
        $registerAgentForm = $this->createFormBuilder()
            ->add('symbol', TextType::class)
            ->add('faction', ChoiceType::class, ['choices' => ['COSMIC' => 'COSMIC']])
            ->add('submit', SubmitType::class)
            ->getForm();

        $registerAgentForm->handleRequest($request);

        if ($registerAgentForm->isSubmitted() && $registerAgentForm->isValid()) {
            $data = $registerAgentForm->getData();

            $agent = $this->spaceTraderApi->registerAgent($data['symbol'], $data['faction']);
            // Until there is a way to store agents we use the session
            $request->getSession()->set('agentToken', $agent['data']['token']);

            return $this->redirectToRoute('app.index');
        }

        $agent = null;
        $contracts = null;

        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $agent = $this->spaceTraderApi->loadAgent($agentToken);
            $contracts = $this->spaceTraderApi->loadContracts($agentToken);
        }

        $parameters = [
            'registerAgentForm' => $registerAgentForm,
            'agent' => $agent,
            'contracts' => $contracts,
        ];

        return $this->render('base.html.twig', $parameters);
    }

    #[Route('/logout', 'app.logout')]
    public function logoutAction(Request $request): Response
    {
        $request->getSession()->remove('agentToken');

        return $this->redirectToRoute('app.index');
    }
}
