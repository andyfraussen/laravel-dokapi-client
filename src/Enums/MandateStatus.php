<?php

namespace AndyFraussen\Dokapi\Enums;

enum MandateStatus: string
{
    case PendingUpload = 'PENDING_UPLOAD';
    case Requested = 'REQUESTED';
    case Reviewing = 'REVIEWING';
    case ActionRequired = 'ACTION_REQUIRED';
    case Reviewed = 'REVIEWED';
    case Activating = 'ACTIVATING';
    case Active = 'ACTIVE';
    case Updating = 'UPDATING';
    case Inactivating = 'INACTIVATING';
    case Inactive = 'INACTIVE';
}
