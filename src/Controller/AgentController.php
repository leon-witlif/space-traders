<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\Storage\ContractStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgentController extends AbstractController
{
    public function __construct(
        private readonly AgentApi $agentApi,
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly ContractStorage $contractStorage,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $agent = $this->agentApi->loadAgent($agentToken);

            $parameters = [
                'agent' => $agent,
                'contracts' => $this->contractApi->loadContracts($agentToken),
                'ships' => $this->shipApi->loadShips($agentToken),

                'activeContracts' => $this->contractStorage->getContracts(),
            ];

            return $this->render('agent.html.twig', $parameters);
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
