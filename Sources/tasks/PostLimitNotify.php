<?php

namespace tasks;

use PostLimit\PostLimitRepository;
use PostLimit\PostLimitService;

class PostLimitNotify extends \SMF_BackgroundTask
{
    protected PostLimitRepository $repository;

    public function __construct()
    {
        $this->repository = new PostLimitRepository();
    }
    public function execute(): bool
    {
        $this->repository->insertAlert($this->_details);

        return true;
    }
}