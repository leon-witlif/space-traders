<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Navigation;
use App\Loader\ShipLoader;
use App\Navigation\Navigator;
use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\ApiShorthands;
use App\Storage\ContractStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgentController extends AbstractController
{
    use ApiShorthands;

    public function __construct(
        private readonly ApiRegistry $apiRegistry,
        private readonly ShipLoader $shipLoader,
        private readonly ContractStorage $contractStorage,
        private readonly Navigator $navigator,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $status = $this->getGlobalApi()->status();
            $agent = $this->getAgentApi()->get($agentToken, true);

            $leaderboardAgentKey = array_find_key($status['leaderboards']['mostCredits'], fn (array $leaderboardAgent) => $leaderboardAgent['agentSymbol'] === $agent->symbol);

            $this->navigator->initializeSystem(Navigation::getSystem($agent->headquarters));
            $this->navigator->scanWaypoint();

            // $ship = $this->shipLoader->get('AGENT_SIX-1');

            // $fromWaypoint = array_find($this->navigator->getSystem()->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->symbol === $ship->nav->route->origin->symbol);
            // $toWaypoint = array_find($this->navigator->getSystem()->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->symbol === $ship->nav->route->destination->symbol);

            $headquartersWaypoint = $this->getSystemApi()->waypoint(Navigation::getSystem($agent->headquarters), Navigation::getWaypoint($agent->headquarters));
            $scannedWaypointsInDistance = $this->navigator->getWaypointsWithinDistance($headquartersWaypoint);

            // dump(
            //     $this->systemApi->shipyard('X1-BS3', 'X1-BS3-A2', true),
            //     $this->systemApi->shipyard('X1-BS3', 'X1-BS3-C44', true),
            //     $this->systemApi->shipyard('X1-BS3', 'X1-BS3-H57', true)
            // );

            $parameters = [
                'status' => $status,
                'ranking' => $leaderboardAgentKey ? $leaderboardAgentKey + 1 : '>15',

                'agent' => $agent,
                'faction' => $this->getFactionApi()->get('COSMIC'),
                'contracts' => $this->getContractApi()->list($agentToken),
                'ships' => $this->shipLoader->list(),

                'system' => $this->navigator->getSystem(),
                'headquarters' => $headquartersWaypoint,
                // 'navigator' => $this->navigator->calculateRoute($fromWaypoint, $toWaypoint),

                'acceptedContracts' => $this->contractStorage->list(),
                'scannedWaypoints' => $scannedWaypointsInDistance,

                'navigateForm' => $this->createNavigateForm(),
            ];

            return $this->render('agent.html.twig', dump($parameters));
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    private function createNavigateForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app.ship.navigate', ['shipSymbol' => 'AGENT_ONE-1']))
            ->add('waypointSymbol', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Navigate to waypoint']])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-sm btn-primary']])
            ->getForm();
    }
}
