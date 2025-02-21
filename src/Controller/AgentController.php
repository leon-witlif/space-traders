<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Navigation;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;
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
        private readonly SystemApi $systemApi,
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
                'system' => $this->systemApi->loadSystem(Navigation::getSystem($agent->headquarters)),
            ];

            return $this->render('agent.html.twig', $parameters);
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
