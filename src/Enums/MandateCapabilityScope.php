<?php

namespace AndyFraussen\Dokapi\Enums;

enum MandateCapabilityScope: string
{
    case Sending = 'sending';
    case Receiving = 'receiving';
    case Reporting = 'reporting';
}
