<?php

declare(strict_types=1);

namespace App\Controller;

use App\Loader\AgentTokenProvider;
use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\ApiShorthands;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShipController extends AbstractController
{
    use ApiShorthands;

    public function __construct(
        private readonly ApiRegistry $apiRegistry,
        private readonly AgentTokenProvider $agentTokenProvider,
    ) {
    }

    #[Route('/ship/{shipSymbol}/navigate', 'app.ship.navigate')]
    public function navigateAction(Request $request, string $shipSymbol): Response
    {
        $waypointSymbol = $request->request->all()['form']['waypointSymbol'];

        $this->fleetApi->orbit($this->agentTokenProvider->getAgentToken(), $shipSymbol);
        $this->fleetApi->navigate($this->agentTokenProvider->getAgentToken(), $shipSymbol, $waypointSymbol);

        return $this->redirectToRoute('app.agent.detail');
    }

    #[Route('/ship/{shipSymbol}/nav/{flightMode}', 'app.ship.nav')]
    public function navAction(Request $request, string $shipSymbol, string $flightMode): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->fleetApi->nav($agentToken, $shipSymbol, $flightMode);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    #[Route('/ship/{shipSymbol}/refuel', 'app.ship.refuel')]
    public function refuelAction(Request $request, string $shipSymbol): Response
    {
        if ($request->getSession()->get('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->fleetApi->dock($agentToken, $shipSymbol);
            $this->fleetApi->refuel($agentToken, $shipSymbol);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    #[Route('/ship/{shipSymbol}/negotiate', 'app.ship.negotiate')]
    public function negotiateAction(Request $request, string $shipSymbol): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->fleetApi->negotiate($agentToken, $shipSymbol);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    #[Route('/ship/{shipSymbol}/repair', 'app.ship.repair')]
    public function repairAction(Request $request, string $shipSymbol): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->fleetApi->repair($agentToken, $shipSymbol);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
