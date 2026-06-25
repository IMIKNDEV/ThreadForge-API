<?php

namespace App\Enums;

enum PostStatusEnum: string
{
    case Draft = 'draft';
    case Archived = 'archived';
    case Posted = 'posted';
}
