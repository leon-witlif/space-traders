<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\APIClient as SpaceTraderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agent', 'app.agent')]
class AgentController extends AbstractController
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('', '.detail')]
    public function detailAction(Request $request): Response
    {
        $agent = null;
        $contracts = null;

        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $agent = $this->spaceTraderApi->loadAgent($agentToken);
            $contracts = $this->spaceTraderApi->loadContracts($agentToken);
        }

        $parameters = [
            'agent' => $agent,
            'contracts' => $contracts,
        ];

        return $this->render('agent.html.twig', $parameters);
    }
}
