<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Navigation;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\FactionApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;
use App\Storage\ContractStorage;
use App\Storage\WaypointStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpClient\Exception\ClientException;
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
        private readonly WaypointStorage $waypointStorage,
    ) {
    }

    #[Route('/agent', 'app.agent.detail')]
    public function detailAction(Request $request): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $agent = $this->agentApi->get($agentToken);
            $system = $this->systemApi->get(Navigation::getSystem($agent->headquarters));

            foreach ($system->waypoints as $waypoint) {
                if (!$this->waypointStorage->get($waypoint['symbol'])) {
                    $this->waypointStorage->add(['waypointSymbol' => $waypoint['symbol'], 'scanned' => false]);
                }
            }

            foreach ($this->waypointStorage->list() as $market) {
                if (!$market['scanned']) {
                    $waypointResponse = $this->systemApi->waypoint(Navigation::getSystem($market['waypointSymbol']), Navigation::getWaypoint($market['waypointSymbol']));

                    $data = [
                        'waypointSymbol' => $market['waypointSymbol'],
                        'scanned' => true,
                        'type' => $waypointResponse->type,
                        'x' => $waypointResponse->x,
                        'y' => $waypointResponse->y,
                        'traits' => array_map(fn (array $trait) => $trait['symbol'], $waypointResponse->traits),
                        'factionSymbol' => $waypointResponse->faction?->symbol,
                    ];

                    try {
                        $marketResponse = $this->systemApi->market(Navigation::getSystem($market['waypointSymbol']), Navigation::getWaypoint($market['waypointSymbol']));

                        $data['exports'] = array_map(fn (array $tradeGood) => $tradeGood['symbol'], $marketResponse->exports);
                        $data['exchange'] = array_map(fn (array $tradeGood) => $tradeGood['symbol'], $marketResponse->exchange);
                    } catch (ClientException) {
                        $data['exports'] = [];
                        $data['exchange'] = [];
                    }

                    $this->waypointStorage->update($this->waypointStorage->key($market['waypointSymbol']), $data);

                    break;
                }
            }

            $parameters = [
                'agent' => $agent,
                'faction' => $this->factionApi->get('COSMIC'),
                'contracts' => $this->contractApi->list($agentToken),
                'ships' => $this->shipApi->list($agentToken),

                'system' => $system,
                'waypoint' => $this->systemApi->waypoint(Navigation::getSystem($agent->headquarters), Navigation::getWaypoint($agent->headquarters)),

                'acceptedContracts' => $this->contractStorage->list(),
                'scannedWaypoints' => array_values(array_filter($this->waypointStorage->list(), fn (array $market) => $market['scanned'])),

                'shipControl' => $this->createShipControlForms(),
            ];

            return $this->render('agent.html.twig', dump($parameters));
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    /**
     * @return array<FormView>
     */
    private function createShipControlForms(): array
    {
        $flightModeForm = $this->createFormBuilder()
            ->add('flightMode', ChoiceType::class, ['choices' => ['CRUISE' => 'CRUISE', 'BURN' => 'BURN', 'DRIFT' => 'DRIFT', 'STEALTH' => 'STEALTH']])
            ->getForm()
            ->createView();

        return [
            'flightModeForm' => $flightModeForm,
        ];
    }
}
