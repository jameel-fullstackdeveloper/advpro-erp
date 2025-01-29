<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class SetFirstCompanyOnLogin
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {

        $user = $event->user;


        // Get the first company associated with the user
        $firstCompany = $user->companies()->first();

        // If the user has a company, store the company ID in the session
        if ($firstCompany) {
            Session::put('company_id', $firstCompany->id);

        }
    }
}

