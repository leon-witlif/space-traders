<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Navigation;
use App\Loader\ShipLoader;
use App\Navigation\Navigator;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\FactionApi;
use App\SpaceTrader\Struct\SystemWaypoint;
use App\SpaceTrader\SystemApi;
use App\Storage\ContractStorage;
use App\Storage\WaypointStorage;
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
        private readonly ShipLoader $shipLoader,
        private readonly SystemApi $systemApi,
        private readonly ContractStorage $contractStorage,
        private readonly WaypointStorage $waypointStorage,
        private readonly Navigator $navigator,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $agent = $this->agentApi->get($agentToken);

            $this->navigator->initializeSystem(Navigation::getSystem($agent->headquarters));
            $this->navigator->scanWaypoint();

            $ship = $this->shipLoader->get('AGENT_SIX-1');

            $fromWaypoint = array_find($this->navigator->getSystem()->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->symbol === $ship->nav->route->origin->symbol);
            $toWaypoint = array_find($this->navigator->getSystem()->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->symbol === $ship->nav->route->destination->symbol);

            $parameters = [
                'agent' => $agent,
                'faction' => $this->factionApi->get('COSMIC'),
                'contracts' => $this->contractApi->list($agentToken),
                'ships' => $this->shipLoader->list(),

                'system' => $this->navigator->getSystem(),
                'headquarters' => $this->systemApi->waypoint(Navigation::getSystem($agent->headquarters), Navigation::getWaypoint($agent->headquarters)),
                'navigator' => $this->navigator->calculateRoute($fromWaypoint, $toWaypoint),

                'acceptedContracts' => $this->contractStorage->list(),
                'scannedWaypoints' => array_values(array_filter($this->waypointStorage->list(), fn (array $market) => $market['scanned'])),
            ];

            return $this->render('agent.html.twig', dump($parameters));
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
