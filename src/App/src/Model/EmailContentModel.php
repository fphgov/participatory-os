<?php

declare(strict_types=1);

namespace App\Model;

use Mail\Model\EmailContentModelInterface;

final class EmailContentModel implements EmailContentModelInterface
{
    function __construct(
        private string $subject,
        private string $html,
        private string $text
    ) {}

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function getPlainText(): string
    {
        return $this->text;
    }
}
