-- Create training_sessions table
CREATE TABLE `training_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 25,
  `max_participants` int(11) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('scheduled','active','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `instructor_id` (`instructor_id`),
  CONSTRAINT `fk_session_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Modify enrolments to add training_session_id
ALTER TABLE `enrolments` ADD COLUMN `training_session_id` int(11) DEFAULT NULL AFTER `course_id`;

-- Migrate data from courses to training_sessions
INSERT INTO `training_sessions` (`course_id`, `instructor_id`, `start_date`, `end_date`, `capacity`, `max_participants`, `fee`, `status`, `created_at`, `updated_at`)
SELECT `id`, `instructor_id`, `start_date`, `end_date`, `capacity`, `max_participants`, `fee`, 
       IF(`status` = 'active', 'active', IF(`status` = 'completed', 'completed', 'scheduled')), 
       `created_at`, `updated_at` 
FROM `courses`;

-- Update enrolments to point to the new training sessions
UPDATE `enrolments` e
JOIN `training_sessions` ts ON e.course_id = ts.course_id
SET e.training_session_id = ts.id;

-- Ensure training_session_id is populated. 
-- Important: If there were any enrolments for a course that didn't migrate to a session correctly, they will be deleted. We should avoid doing ON DELETE CASCADE unless safe.
-- We must drop the foreign key `enrolments_ibfk_1` first
ALTER TABLE `enrolments` DROP FOREIGN KEY `enrolments_ibfk_1`;

-- Delete orphan enrolments where training_session_id is NULL (just in case)
DELETE FROM `enrolments` WHERE `training_session_id` IS NULL;

ALTER TABLE `enrolments` MODIFY `training_session_id` int(11) NOT NULL;
ALTER TABLE `enrolments` ADD CONSTRAINT `fk_enrolment_session` FOREIGN KEY (`training_session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE;

-- We need to drop the unique key `unique_enrolment` and recreate it with training_session_id
ALTER TABLE `enrolments` DROP INDEX `unique_enrolment`;
ALTER TABLE `enrolments` ADD UNIQUE KEY `unique_enrolment` (`training_session_id`, `trainee_id`);

ALTER TABLE `enrolments` DROP COLUMN `course_id`;

-- Cleanup courses table
-- Drop foreign key for instructor_id first
ALTER TABLE `courses` DROP FOREIGN KEY `courses_ibfk_1`;
ALTER TABLE `courses` 
  DROP COLUMN `instructor_id`,
  DROP COLUMN `start_date`,
  DROP COLUMN `end_date`,
  DROP COLUMN `capacity`,
  DROP COLUMN `max_participants`,
  DROP COLUMN `fee`;

-- Cleanup trainee_profiles
ALTER TABLE `trainee_profiles` DROP INDEX `idx_trainee_profiles_identity`;
ALTER TABLE `trainee_profiles` DROP COLUMN `identity_number`;
