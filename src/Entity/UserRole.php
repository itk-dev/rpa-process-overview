<?php

namespace App\Entity;

enum UserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';
    case SuperAdmin = 'ROLE_SUPER_ADMIN';
    case OverviewEditor = 'ROLE_OVERVIEW_EDITOR';
    case OverviewViewer = 'ROLE_OVERVIEW_VIEWER';
    case ProcessStepRunner = 'ROLE_PROCESS_STEP_RUNNER';
}
