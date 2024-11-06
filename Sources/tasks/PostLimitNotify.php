<?php

namespace tasks;

use PostLimit\PostLimitRepository;
use PostLimit\PostLimitService;

class PostLimitNotify extends SMF_BackgroundTask
{
    protected PostLimitRepository $repository;
    protected array $_details;

    public function __construct()
    {
        $this->repository = new PostLimitRepository();
    }
    public function execute(): bool
    {
        $this->repository->deleteAlerts((int) $this->_details['idUser']);
        $this->repository->insertAlert($this->_details);

        return true;
    }
}