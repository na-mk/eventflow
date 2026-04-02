INSERT INTO user (email, roles, password, first_name, last_name, phone, consent_date, consent_version, is_anonymized, created_at)
VALUES ('admin@eventflow.local', '["ROLE_ADMIN"]', '$2y$13$Gdbkg9VClkSAEEwVE.jnRebA.tZjA8pafLm1R7Y1vIkEfsEWunoza', 'Admin', 'User', NULL, NOW(), '1.0', 0, NOW());
