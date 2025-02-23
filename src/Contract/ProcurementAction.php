<?php

declare(strict_types=1);

namespace App\Contract;

enum ProcurementAction: int
{
    case FIND_ASTEROID = 0;
    case NAVIGATE_TO_ASTEROID = 1;
    case REFUEL_SHIP = 2;
    case EXTRACT_ASTEROID = 3;
    case JETTISON_CARGO = 4;
    case SELL_CARGO = 5;
    case NAVIGATE_TO_DELIVERY = 6;
    case DELIVER_CARGO = 7;
    case NAVIGATE_TO_HEADQUARTERS = 8;
    case FINISH_CONTRACT = 9;
}
