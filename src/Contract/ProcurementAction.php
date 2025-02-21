<?php

declare(strict_types=1);

namespace App\Contract;

enum ProcurementAction: int
{
    case FIND_ASTEROID = 0;
    case UNDOCK_FROM_HEADQUARTERS = 1;
    case NAVIGATE_TO_ASTEROID = 2;
    case DOCK_ON_ASTEROID = 3;
    case REFUEL_SHIP = 4;
    case UNDOCK_FROM_ASTEROID = 5;
    case EXTRACT_ASTEROID = 6;
    case JETTISON_CARGO = 7;
    case NAVIGATE_TO_DELIVERY_WAYPOINT = 8;
    case DELIVER_CARGO = 9;
    case NAVIGATE_TO_HEADQUARTERS = 10;
    case DOCK_SHIP = 11;
}
