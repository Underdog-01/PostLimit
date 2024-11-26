<?php

namespace PostLimit;

class PostLimitNotify extends \SMF_BackgroundTask
{
    protected PostLimitRepository $repository;

    public function execute(): bool
    {
        $this->repository = new PostLimitRepository();

        $this->repository->insertAlert($this->_details);

        return true;
    }
}