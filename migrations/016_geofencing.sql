-- Phase 2.2: Add geofencing fields to farms table

-- Add geofence_polygon for complex polygon boundaries (stored as GeoJSON)
ALTER TABLE farms ADD COLUMN geofence_polygon JSON NULL AFTER geofence_radius;

-- Rename geofence_radius to geofence_radius_metres for clarity
ALTER TABLE farms CHANGE COLUMN geofence_radius geofence_radius_metres INT DEFAULT 200;

-- Add index for geofence queries
ALTER TABLE farms ADD INDEX idx_geofence_radius (geofence_radius_metres);
