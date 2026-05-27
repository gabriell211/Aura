<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
    case MAINTENANCE = 'maintenance';
    case ALERT = 'alert';
    case RETIRED = 'retired';
}
