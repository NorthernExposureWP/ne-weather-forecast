<?php

namespace NE\Weather\Api;

interface GeoIpClientInterface
{
    public function getLocationByIp(string $ip): array;
}