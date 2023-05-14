<?php

if (version_compare(\Composer\InstalledVersions::getVersion('symfony/http-foundation'), '6.0.0') === -1) {
    include 'VirtualSessionStorage<8.php';
} else {
    include 'VirtualSessionStorage8+.php';
}
