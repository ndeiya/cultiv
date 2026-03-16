-- Add date_of_birth column to animals table
ALTER TABLE animals ADD COLUMN date_of_birth DATE NULL AFTER breed;
