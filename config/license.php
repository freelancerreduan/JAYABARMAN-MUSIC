<?php

/*
 * License check against the Smart BD Host license panel (lisence.smartbdhost.com).
 *
 * The panel answers with a payload signed by its RSA private key; this site holds only the public
 * key, so an answer cannot be faked. If the panel is down, the last signed answer keeps the site
 * running. The verified answer names the domain, so a copy moved to another domain asks again.
 *
 * On a developer machine (local host) the panel is never contacted: the site is unlocked only by
 * typing the developer name below. On a live domain the real license key is required.
 */
return [

    // The site asks the panel:  POST {k: key, d: domain, n: nonce} -> {p: base64 json, g: base64 signature}
    'server' => env('LICENSE_SERVER', 'https://lisence.smartbdhost.com/api/v1/sync'),

    // The panel's public key (storage/app/keys/license-public.pem on the panel). Same panel, same key
    // as the other Smart BD Host sites, so the panel controls this project the same way.
    'public_key' => <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuaKNlXbTnROgsrWHCoXH
EnOXOu4EDMFdFweB9RhPJ+fGKJiWnUzmuUFfPb1a8dPct1CqfR/ezD5Bh3qUJ4Og
RzOucaBKHVAltSfPPkT1wPSsegr9oxuKQRICB/I3otL7OTLdchByea+BCtq69oIf
8FpjlC3lfgSfjwVA6emBHxmP2r/uxsDpa2aIpHcY+0/mZRUwnj+GyXdJvHIF9wd+
2rRSkoiSUmaTOEfSv11SFPQbsUgH6A9r1XoAdsdaIvxo9b3WNfbuWBRQXf8F/AsH
hdXw6To/j5mwHNxOvkUpdOhB4BlPQeuaypcVeqSfNlJWeH4+KvLpShFNHqD4UY2f
XwIDAQAB
-----END PUBLIC KEY-----
PEM,

    // How often a licensed live site asks the panel again (in the background, never blocking a page).
    'recheck_hours' => 12,

    // Seconds to wait for the panel before giving up (the site keeps working when it gives up).
    'timeout' => 5,

    // Developer machines: these hosts may be unlocked with the developer name instead of a real key.
    'local_hosts' => ['localhost', '127.0.0.1', '::1'],

    // On a local host, typing this exact name on the activation page unlocks the site (no panel call).
    // On a live domain it does nothing — the real license key is always required there.
    'developer_name' => env('LICENSE_DEV_NAME', 'reduan360bd'),

    // Extra secret mixed into the encryption of the saved license pieces. Change it per project if you
    // like; it only has to stay the same for the life of one install.
    'pepper' => env('LICENSE_PEPPER', 'jb-music::7f3a9c2e-smartbdhost-vault'),

    // Shown on the activation page so the buyer knows whom to contact.
    'vendor' => [
        'name' => env('LICENSE_VENDOR_NAME', 'Smart BD Host'),
        'email' => env('LICENSE_VENDOR_EMAIL', ''),
        'phone' => env('LICENSE_VENDOR_PHONE', ''),
        'website' => env('LICENSE_VENDOR_WEBSITE', 'https://smartbdhost.com'),
    ],
];
