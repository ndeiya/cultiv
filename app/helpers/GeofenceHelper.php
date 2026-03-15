<?php
/**
 * Geofence Helper
 * Utilities for geofencing validation using Haversine formula.
 */

class GeofenceHelper
{
    /**
     * Calculate distance between two points using Haversine formula.
     * Returns distance in meters.
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lng1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lng2 Longitude of point 2
     * @return float Distance in meters
     */
    public static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within a circular geofence.
     * 
     * @param float $lat Latitude to check
     * @param float $lng Longitude to check
     * @param float $centerLat Center latitude of geofence
     * @param float $centerLng Center longitude of geofence
     * @param int $radiusMeters Radius in meters
     * @return bool True if within geofence
     */
    public static function isWithinCircularGeofence(float $lat, float $lng, float $centerLat, float $centerLng, int $radiusMeters): bool
    {
        $distance = self::haversineDistance($lat, $lng, $centerLat, $centerLng);
        return $distance <= $radiusMeters;
    }

    /**
     * Check if coordinates are within a polygon geofence (GeoJSON format).
     * Uses ray casting algorithm.
     * 
     * @param float $lat Latitude to check
     * @param float $lng Longitude to check
     * @param array $polygon GeoJSON polygon coordinates [[[lng, lat], ...]]
     * @return bool True if within polygon
     */
    public static function isWithinPolygonGeofence(float $lat, float $lng, array $polygon): bool
    {
        if (empty($polygon) || !isset($polygon[0]) || !is_array($polygon[0])) {
            return false;
        }

        // Extract coordinates from GeoJSON format
        $coordinates = $polygon[0]; // First ring (exterior)
        $points = [];
        foreach ($coordinates as $coord) {
            if (is_array($coord) && count($coord) >= 2) {
                $points[] = ['lng' => $coord[0], 'lat' => $coord[1]];
            }
        }

        if (count($points) < 3) {
            return false; // Not a valid polygon
        }

        // Ray casting algorithm
        $inside = false;
        $j = count($points) - 1;

        for ($i = 0; $i < count($points); $i++) {
            $xi = $points[$i]['lng'];
            $yi = $points[$i]['lat'];
            $xj = $points[$j]['lng'];
            $yj = $points[$j]['lat'];

            $intersect = (($yi > $lat) != ($yj > $lat)) &&
                         ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }

    /**
     * Validate if coordinates are within farm geofence.
     * Supports both circular (radius) and polygon geofences.
     * 
     * @param float $lat Latitude to check
     * @param float $lng Longitude to check
     * @param array $farm Farm record with geofence data
     * @return array ['valid' => bool, 'distance' => float, 'message' => string]
     */
    public static function validateFarmGeofence(float $lat, float $lng, array $farm): array
    {
        if (empty($farm['latitude']) || empty($farm['longitude'])) {
            return [
                'valid' => false,
                'distance' => 0,
                'message' => 'Farm location not configured'
            ];
        }

        $centerLat = (float)$farm['latitude'];
        $centerLng = (float)$farm['longitude'];

        // Check polygon geofence first (if available)
        if (!empty($farm['geofence_polygon'])) {
            $polygon = json_decode($farm['geofence_polygon'], true);
            if ($polygon && isset($polygon['coordinates'])) {
                $within = self::isWithinPolygonGeofence($lat, $lng, $polygon['coordinates']);
                $distance = self::haversineDistance($lat, $lng, $centerLat, $centerLng);
                
                return [
                    'valid' => $within,
                    'distance' => $distance,
                    'message' => $within 
                        ? 'Location verified within farm boundary' 
                        : sprintf('Location is %.0f meters outside farm boundary', $distance)
                ];
            }
        }

        // Fall back to circular geofence
        $radiusMeters = (int)($farm['geofence_radius_metres'] ?? 200);
        $distance = self::haversineDistance($lat, $lng, $centerLat, $centerLng);
        $within = $distance <= $radiusMeters;

        return [
            'valid' => $within,
            'distance' => $distance,
            'message' => $within
                ? 'Location verified within farm boundary'
                : sprintf('Location is %.0f meters outside farm boundary (radius: %d meters)', $distance, $radiusMeters)
        ];
    }
}
