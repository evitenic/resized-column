<?php

namespace Evitenic\ResizedColumn\Plugin\Concerns;

trait CanResizedColumn
{
    protected bool $isPreserveOnDbEnabled = false;

    protected bool $isPreserveOnSessionEnabled = true;

    protected bool $isPreserveOnSessionBrowserEnabled = false;

    public function preserveOnDB(bool $condition = true): self
    {
        $this->isPreserveOnDbEnabled = $condition;

        return $this;
    }

    public function isPreserveOnDBEnabled(): bool
    {
        return $this->isPreserveOnDbEnabled;
    }

    public function preserveOnSession(bool $condition = true): self
    {
        $this->isPreserveOnSessionEnabled = $condition;

        return $this;
    }

    public function isPreserveOnSessionEnabled(): bool
    {
        return $this->isPreserveOnSessionEnabled;
    }

    public function preserveOnSessionBrowser(bool $condition = true): self
    {
        $this->isPreserveOnSessionBrowserEnabled = $condition;

        return $this;
    }

    public function isPreserveOnSessionBrowserEnabled(): bool
    {
        return $this->isPreserveOnSessionEnabled;
    }
}
