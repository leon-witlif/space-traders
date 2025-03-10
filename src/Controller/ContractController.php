<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\ContractFactory;
use App\Contract\Task\FindAsteroidTask;
use App\SpaceTrader\Endpoint\ContractApi;
use App\SpaceTrader\Endpoint\ShipApi;
use App\Storage\ContractStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContractController extends AbstractController
{
    public function __construct(
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly ContractStorage $contractStorage,
        private readonly ContractFactory $contractFactory,
    ) {
    }

    #[Route('/contract/{contractId}/accept', 'app.contract.accept')]
    public function acceptAction(Request $request, string $contractId): Response
    {
        $agentToken = $request->getSession()->get('agentToken');

        $this->contractApi->accept($agentToken, $contractId);

        $ship = $this->shipApi->list($agentToken)[0];

        $contract = $this->contractFactory->createProcurementContract($agentToken, $contractId, $ship->symbol);
        $contract->setRootTask(FindAsteroidTask::class, $ship->symbol, 'ENGINEERED_ASTEROID');

        $this->contractStorage->add(
            [
                'agentToken' => $agentToken,
                'contractId' => $contractId,
                'shipSymbol' => $ship->symbol,
                'tasks' => $contract,
            ]
        );

        return $this->redirectToRoute('app.agent.detail');
    }

    #[Route('/contract/{contractId}/run', 'app.contract.run')]
    public function runAction(string $contractId): Response
    {
        /*
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
        */

        return $this->redirectToRoute('app.agent.detail');
    }
}
