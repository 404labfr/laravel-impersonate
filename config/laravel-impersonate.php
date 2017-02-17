<?php

return [
    
    /**
     * The session key used to store the original user id.
     */
    'session_key' => 'impersonated_by',

    /**
     * The URI to redirect after taking an impersonation.
     *
     * Only used in the built-in controller.
     */
    'take_redirect_to' => '/',

    /**
     * The URI to redirect after leaving an impersonation.
     *
     * Only used in the built-in controller.
     */
    'leave_redirect_to' => '/',

    /**
     * The URI to redirect when the impersonator doesnt have an acces to a route
     *
     * Only used in the middleware CantAccesIfImpersonate.
     */
    'cant_acces_if_impersonate_redirect_to' => '/'

];
