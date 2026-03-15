-- Migration: 024_whatsapp_settings.sql
-- Add WhatsApp Business API credentials to farms table

ALTER TABLE farms
ADD COLUMN IF NOT EXISTS wa_phone_number_id VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS wa_access_token TEXT DEFAULT NULL;
