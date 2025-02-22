<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Procurement;
use App\Contract\ProcurementAction;
use App\Helper\Navigation;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;
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
        private readonly SystemApi $systemApi,
        private readonly ContractStorage $contractStorage,
    ) {
    }

    #[Route('/contract/{contractId}/accept', 'app.contract.accept')]
    public function acceptAction(Request $request, string $contractId): Response
    {
        $agentToken = $request->getSession()->get('agentToken');

        $this->contractApi->acceptContract($agentToken, $contractId);

        $this->contractStorage->addContract($agentToken, $contractId, ['action' => ProcurementAction::FIND_ASTEROID->name]);

        return $this->redirectToRoute('app.agent.detail');
    }

    #[Route('/contract/{contractId}/run', 'app.contract.run')]
    public function runAction(Request $request, string $contractId): Response
    {
        if ($request->getSession()->get('agentToken')) {
            $agentToken = $request->getSession()->get('agentToken');
            $activeContract = $this->contractStorage->getContract($contractId);

            $agent = $this->agentApi->loadAgent($agentToken);
            $contract = $this->contractApi->loadContract($agentToken, $contractId);
            $ship = $this->shipApi->loadShips($agentToken)[0];
            $system = $this->systemApi->loadSystem(Navigation::getSystem($agent->headquarters));

            $procurement = new Procurement($this->contractApi, $this->shipApi, $activeContract['data']);
            $procurement->run($agentToken, $agent, $contract, $ship, $system);

            $contract = $this->contractApi->loadContract($agentToken, $contractId);

            if ($contract->fulfilled) {
                $this->contractStorage->removeContract($contractId);
            } else {
                $this->contractStorage->updateContract($contractId, $procurement->getData());
            }

            return $this->redirectToRoute('app.agent.detail');
        }

        return $this->redirectToRoute('app.auth.logout');
    }
}
