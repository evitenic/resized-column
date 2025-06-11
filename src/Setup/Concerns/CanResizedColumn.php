<?php

namespace Evitenic\ResizedColumn\Setup\Concerns;

use Evitenic\ResizedColumn\ResizedColumnPlugin;

trait CanResizedColumn
{
    public static function preserveOnDB(): bool
    {
        if (self::resizedColumnPlugged()) {
            return ResizedColumnPlugin::get()->isPreserveOnDBEnabled();
        }

        return false;
    }

    public static function preserveOnSession(): bool
    {
        if (self::resizedColumnPlugged()) {
            return ResizedColumnPlugin::get()->isPreserveOnSessionEnabled();
        }

        return config('resized-column.preserve_on_session', true);
    }

    public static function preserveOnSessionBrowser(): bool
    {
        if (self::resizedColumnPlugged()) {
            return ResizedColumnPlugin::get()->isPreserveOnSessionBrowserEnabled();
        }

        return config('resized-column.preserve_on_session_browser', true);
    }
}
