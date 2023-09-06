<?php

declare(strict_types=1);

namespace App\Model;

use function explode;

class VoteableProjectFilterModel
{
    private string $query = '';
    private array $tag = [];
    private string $theme = '';
    private string|int $location = '';
    private int $page = 1;
    private ?string $rand = null;
    private ?string $orderBy = null;

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query)
    {
        $this->query = $query;
    }

    public function getTag(): array
    {
        return $this->tag;
    }

    public function setTag(string $tag)
    {
        $tags = explode(',', $tag);

        if (isset($tags[0]) && $tags[0] === "") {
            return [];
        }

        $this->tag = $tags;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme)
    {
        $this->theme = $theme;
    }

    public function getLocation(): string|int
    {
        return $this->location;
    }

    public function setLocation(string|int $location)
    {
        $this->location = $location;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int|string $page)
    {
        $this->page = (int)$page !== 0 ? (int)$page : 1;
    }

    public function getRand(): ?string
    {
        return $this->rand;
    }

    public function setRand(?string $rand = null)
    {
        $this->rand = $rand;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(?string $orderBy = null)
    {
        $this->orderBy = $orderBy;
    }
}
