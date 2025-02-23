<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\FactionApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;
use App\Storage\ContractStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgentController extends AbstractController
{
    public function __construct(
        private readonly AgentApi $agentApi,
        private readonly FactionApi $factionApi,
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly SystemApi $systemApi,
        private readonly ContractStorage $contractStorage,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $parameters = [
                'agent' => $this->agentApi->get($agentToken),
                'faction' => $this->factionApi->get('COSMIC'),
                'contracts' => $this->contractApi->list($agentToken),
                'ships' => $this->shipApi->list($agentToken),

                // 'system' => $this->systemApi->get('X1-MQ95'),
                // 'market' => $this->systemApi->market('X1-MQ95', 'X1-MQ95-BA5F'),

                'acceptedContracts' => $this->contractStorage->list(),
            ];

            return $this->render('agent.html.twig', $parameters);
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
