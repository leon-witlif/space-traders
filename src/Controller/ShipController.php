<?php

declare(strict_types=1);

namespace App\Controller;

use App\SpaceTrader\APIClient as SpaceTraderAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShipController extends AbstractController
{
    public function __construct(
        private readonly SpaceTraderAPI $spaceTraderApi,
    ) {
    }

    #[Route('/ship/{ship}/dock', 'app.ship.dock')]
    public function dockAction(Request $request, string $ship): Response
    {
        $agentToken = $request->getSession()->get('agentToken');

        $this->spaceTraderApi->dockShip($agentToken, $ship);

        return $this->redirectToRoute('app.agent.detail');
    }

    #[Route('/ship/{ship}/orbit', 'app.ship.orbit')]
    public function orbitAction(Request $request, string $ship): Response
    {
        $agentToken = $request->getSession()->get('agentToken');

        $this->spaceTraderApi->orbitShip($agentToken, $ship);

        return $this->redirectToRoute('app.agent.detail');
    }
}
