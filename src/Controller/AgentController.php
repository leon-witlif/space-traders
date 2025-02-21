<?php

declare(strict_types=1);

namespace App\Controller;

use App\Handler\AgentHandler;
use App\Helper\Navigation;
use App\SpaceTrader\APIClient as SpaceTraderAPI;
use App\SpaceTrader\Contract;
use App\SpaceTrader\Ship;
use App\SpaceTrader\System;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgentController extends AbstractController
{
    public function __construct(
        private readonly AgentHandler $agentHandler,
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $agent = $this->agentHandler->load($agentToken);

            $parameters = [
                'agent' => $agent,
                'contracts' => array_map(fn (array $contract) => new Contract(...$contract), $this->spaceTraderApi->loadContracts($agentToken)),
                'ships' => array_map(fn (array $ship) => new Ship(...$ship), $this->spaceTraderApi->loadShips($agentToken)),
                'system' => new System(...$this->spaceTraderApi->loadSystem(Navigation::getSystem($agent->headquarters))),
            ];

            return $this->render('agent.html.twig', $parameters);
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
