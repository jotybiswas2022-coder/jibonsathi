<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $viewer, Report $report): bool
    {
        return $viewer->is_admin || $report->reporter_id === $viewer->id;
    }

    public function moderate(User $viewer): bool
    {
        return $viewer->is_admin;
    }
}
