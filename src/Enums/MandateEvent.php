<?php

namespace AndyFraussen\Dokapi\Enums;

enum MandateEvent: string
{
    case Requested = 'mandate.requested';
    case Reviewing = 'mandate.reviewing';
    case ActionRequired = 'mandate.action-required';
    case Reviewed = 'mandate.reviewed';
    case Activating = 'mandate.activating';
    case Active = 'mandate.active';
    case Updating = 'mandate.updating';
    case Inactivating = 'mandate.inactivating';
    case Inactive = 'mandate.inactive';
    case CapabilityRequested = 'mandate.capability.requested';
    case CapabilityReviewing = 'mandate.capability.reviewing';
    case CapabilityActionRequired = 'mandate.capability.action-required';
    case CapabilityReviewed = 'mandate.capability.reviewed';
    case CapabilityActivating = 'mandate.capability.activating';
    case CapabilityActive = 'mandate.capability.active';
    case CapabilityMigratingOut = 'mandate.capability.migrating-out';
    case CapabilityInactivating = 'mandate.capability.inactivating';
    case CapabilityInactive = 'mandate.capability.inactive';
}
