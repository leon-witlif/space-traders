<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Procurement;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\Storage\ContractStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContractController extends AbstractController
{
    public function __construct(
        private readonly AgentApi $agentApi,
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly ContractStorage $contractStorage,
    ) {
    }

    #[Route('/contract/{contractId}/accept', 'app.contract.accept')]
    public function acceptAction(Request $request, string $contractId): Response
    {
        $agentToken = $request->getSession()->get('agentToken');

        $contract = $this->contractApi->loadContract($agentToken, $contractId);
        $this->contractApi->acceptContract($agentToken, $contractId);

        $agent = $this->agentApi->loadAgent($agentToken);
        $ships = $this->shipApi->loadShips($agentToken);

        switch ($contract->type) {
            case 'PROCUREMENT':
                $activeContract = new Procurement($contract, $agent, $ships[0]);
                break;
        }

        $this->contractStorage->addContract($agentToken, $activeContract);

        return $this->redirectToRoute('app.agent.detail');
    }

    #[Route('/contract/{contractId}/run', 'app.contract.run')]
    public function runAction(Request $request): Response
    {
        return $this->redirectToRoute('app.agent.detail');
    }
}
