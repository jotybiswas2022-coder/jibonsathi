<?php

namespace App\Notifications;

use App\Models\Report;

class NewReportNotification extends BaseNotification
{
    public function __construct(public Report $report)
    {
    }

    public function content(): array
    {
        return [
            'title' => 'New profile report',
            'message' => $this->report->reporter?->name.' reported '.$this->report->reportedUser?->name.' for '.$this->report->reasonLabel().'.',
            'url' => route('backend.reports.show', $this->report, absolute: false),
            'tone' => 'danger',
            'icon' => 'flag',
        ];
    }
}
