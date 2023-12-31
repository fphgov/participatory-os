<?php

declare(strict_types=1);

namespace App\Entity;

use App\Interfaces\EntityActiveInterface;
use App\Interfaces\EntityInterface;

interface CommentInterface extends EntityInterface, EntityActiveInterface
{
    public const DISABLE_SHOW_DEFAULT = [
        'createdAt',
        'updatedAt',
    ];

    public const DISABLE_DEFAULT_SET = [];

    public function setContent(string $content): void;

    public function getContent(): string;

    public function getSubmitter(): UserInterface;

    public function setSubmitter(UserInterface $submitter): void;

    public function getIdea(): Idea;

    public function setIdea(Idea $idea): void;

    public function setParentComment(?Comment $parentComment = null): void;

    public function getParentComment(): ?Comment;
}
