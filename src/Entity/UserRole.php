<?php

namespace App\Entity;

enum UserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';
    case OverviewManager = 'ROLE_OVERVIEW_MANAGER';
}
