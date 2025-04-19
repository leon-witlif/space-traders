<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\ContractFactory;
use App\Contract\Procurement;
use App\Contract\Task\FindAsteroidTask;
use App\Loader\AgentTokenProvider;
use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\ApiShorthands;
use App\Storage\ContractStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContractController extends AbstractController
{
    use ApiShorthands;

    public function __construct(
        private readonly ApiRegistry $apiRegistry,
        private readonly AgentTokenProvider $agentTokenProvider,
        private readonly ContractStorage $contractStorage,
        private readonly ContractFactory $contractFactory,
    ) {
    }

    #[Route('/contract/{contractId}/accept', 'app.contract.accept')]
    public function acceptAction(string $contractId): Response
    {
        $agentToken = $this->agentTokenProvider->getAgentToken();

        $this->getContractApi()->accept($agentToken, $contractId);

        $ship = $this->getShipApi()->list($agentToken)[0];

        $contract = $this->contractFactory->createContract(Procurement::class, $agentToken, $contractId, $ship->symbol);
        $contract->setRootTask($contract->initializeTask(new FindAsteroidTask('ENGINEERED_ASTEROID')));

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
