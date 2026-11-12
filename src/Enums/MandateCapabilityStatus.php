<?php

namespace AndyFraussen\Dokapi\Enums;

enum MandateCapabilityStatus: string
{
    case Requested = 'REQUESTED';
    case Reviewing = 'REVIEWING';
    case ActionRequired = 'ACTION_REQUIRED';
    case Reviewed = 'REVIEWED';
    case Activating = 'ACTIVATING';
    case Active = 'ACTIVE';
    case MigratingOut = 'MIGRATING_OUT';
    case Inactivating = 'INACTIVATING';
    case Inactive = 'INACTIVE';
}
