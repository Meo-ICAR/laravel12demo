<?php

namespace App\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class AzureExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
    }
}
