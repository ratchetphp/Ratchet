<?php

/**
 * This is a nasty workaround to support symfony/http-foundation v6+ while maintaining PHP <= 5.6 support.
 * PHP 8 switched signature mismatches from deprecated to fatal error, causing two functions
 * (start and regenerate) to fail tests. We can't add return types with PHP versions earlier than 7.0
 * so two versions of the VirtualSessionStorage class need to be maintained.
 */

if (version_compare(\Composer\InstalledVersions::getVersion('symfony/http-foundation'), '6.0.0') === -1) {
    // The version of http-foundation is < 6, include a class without return types
    include 'VirtualSessionStorage-HttpFoundation<6.php';
} else {
    // The version of http-foundation is >= 6, include a class with return types
    include 'VirtualSessionStorage-HttpFoundation6+.php';
}
