<?php

declare(strict_types=1);

namespace App\Entity;

interface WorkflowStateInterface
{
    public const STATUS_RECEIVED           = 100;
    public const STATUS_PUBLISHED          = 110;
    public const STATUS_PUBLISHED_WITH_MOD = 111;
    public const STATUS_PRE_COUNCIL        = 120;
    public const STATUS_PROF_DONE          = 121;
    public const STATUS_VOTING_LIST        = 130;
    public const STATUS_UNDER_CONSTRUCTION = 140;
    public const STATUS_READY              = 200;
    public const STATUS_STATUS_REJECTED    = 510;
    public const STATUS_COUNCIL_REJECTED   = 530;
    public const STATUS_NOT_VOTED          = 540;
    public const STATUS_USER_DELETED       = 600;
    public const STATUS_TRASH              = 610;

    public const DISABLE_SHOW_DEFAULT = [];

    public const DISABLE_DEFAULT_SET = [];

    public function getId(): int;

    public function setId(int $id): void;

    public function setCode(string $code): void;

    public function getCode(): string;

    public function setTitle(string $title): void;

    public function getTitle(): string;

    public function setDescription(string $description): void;

    public function getDescription(): string;
}
