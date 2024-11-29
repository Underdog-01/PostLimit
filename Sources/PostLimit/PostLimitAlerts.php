<?php

namespace PostLimit;

class PostLimitAlerts
{
    protected PostLimitService $service;

    public function __construct()
    {
        $this->service = new PostLimitService();
    }
    public function handle(array &$alerts, array &$formats): void
    {
        $postLimitAlert = [];
        $refId = 0;
        foreach ($alerts as $id => $alert) {
            if ($alert['content_type'] === strtolower(PostLimit::NAME)) {
                $postLimitAlert = $alert;
                $refId = $id;
            }
        }

        if ($refId === 0) {
            return;
        }

        $postLimitAlert['text'] = $this->buildAlertText($postLimitAlert);
        $alerts[$refId] = $postLimitAlert;
    }

    protected function buildAlertText($postLimitAlert): string
    {
        $entity = $this->service->getEntityByUser((int) $postLimitAlert['sender_id']);
        $alertPercentage = $this->service->calculatePercentage($entity);

        return strtr('You have reached {percentage} of your {limit} {frequency} post limit. You have {postsLeft} left.',
            [
                '{frequency}' => $this->service->utils->text('alert_frequency'),
                '{limit}' => $alertPercentage['limit'],
                '{postsLeft}' => $alertPercentage['postsLeft'],
                '{percentage}' => $alertPercentage['percentage'],
            ],
        );
    }
}