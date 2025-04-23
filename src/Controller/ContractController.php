<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Contract\Procurement;
use App\Contract\Invoker\ContractParentInvoker;
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
        private readonly ContractParentInvoker $contractParentInvoker,
    ) {
    }

    #[Route('/contract/{contractId}/accept', 'app.contract.accept')]
    public function acceptAction(string $contractId): Response
    {
        $agentToken = $this->agentTokenProvider->getAgentToken();

        $this->contractApi->accept($agentToken, $contractId);

        $ship = $this->fleetApi->list($agentToken)[0];

        $contract = new Procurement($agentToken, $contractId, $ship->symbol);
        $this->contractParentInvoker->invoke($contract);

        // TODO: Check if we have to call Contract::invokeTaskParent here
        $contract->setRootTask(new FindAsteroidTask('ENGINEERED_ASTEROID'));

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
}
