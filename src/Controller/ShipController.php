<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\Endpoint\ShipApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShipController extends AbstractController
{
    public function __construct(private readonly ShipApi $shipApi)
    {
    }

    #[Route('/ship/{shipSymbol}/nav/{flightMode}', 'app.ship.nav')]
    public function navAction(Request $request, string $shipSymbol, string $flightMode): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->shipApi->nav($agentToken, $shipSymbol, $flightMode);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    #[Route('/ship/{shipSymbol}/refuel', 'app.ship.refuel')]
    public function refuelAction(Request $request, string $shipSymbol): Response
    {
        if ($request->getSession()->get('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->shipApi->dock($agentToken, $shipSymbol);
            $this->shipApi->refuel($agentToken, $shipSymbol);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }

    #[Route('/ship/{shipSymbol}/negotiate', 'app.ship.negotiate')]
    public function negotiateAction(Request $request, string $shipSymbol): Response
    {
        if ($request->getSession()->has('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');

            $this->shipApi->negotiate($agentToken, $shipSymbol);

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
