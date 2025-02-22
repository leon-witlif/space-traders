<?php

declare(strict_types=1);

namespace App\Contract;

enum ProcurementAction: int
{
    case FIND_ASTEROID = 0;
    case NAVIGATE_TO_ASTEROID = 2;
    case REFUEL_SHIP = 4;
    case EXTRACT_ASTEROID = 6;
    case JETTISON_CARGO = 7;
    case NAVIGATE_TO_DELIVERY_WAYPOINT = 8;
    case DELIVER_CARGO = 9;
    case NAVIGATE_TO_HEADQUARTERS = 10;
    case FINISH_CONTRACT = 11;
}
