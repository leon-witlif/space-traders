<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Procurement;
use App\Contract\ProcurementAction;
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

        $this->contractApi->accept($agentToken, $contractId);

        $ship = $this->shipApi->list($agentToken)[0];

        $this->contractStorage->add(['agentToken' => $agentToken, 'contractId' => $contractId, 'shipSymbol' => $ship->symbol, 'data' => ['action' => ProcurementAction::FIND_ASTEROID->name]]);

        return $this->redirectToRoute('app.agent.detail');
    }

    #[Route('/contract/{contractId}/run', 'app.contract.run')]
    public function runAction(string $contractId): Response
    {
        $acceptedContract = $this->contractStorage->get($contractId);
        $agentToken = $acceptedContract['agentToken'];

        $procurement = new Procurement($this->agentApi, $this->contractApi, $this->shipApi, $this->systemApi, $acceptedContract['data']);
        $procurement->run($agentToken, $contractId, $acceptedContract['shipSymbol']);

        $contract = $this->contractApi->get($agentToken, $contractId);

        if ($contract->fulfilled) {
            $this->contractStorage->remove($this->contractStorage->key($contractId));
        } else {
            $data = ['agentToken' => $agentToken, 'contractId' => $contractId, 'shipSymbol' => $acceptedContract['shipSymbol'], 'data' => $procurement->getData()];
            $this->contractStorage->update($this->contractStorage->key($contractId), $data);
        }

        return $this->redirectToRoute('app.agent.detail');
    }
}
