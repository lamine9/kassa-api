<?php

namespace App\Enums;

enum SupplierReturnStatus: string
{
    case DRAFT = 'draft';
    case VALIDATED = 'validated';
    case CANCELLED = 'cancelled';
}
