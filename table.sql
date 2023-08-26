
CREATE TABLE `sumup_tokens` (
  `id` int NOT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci NOT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci NOT NULL,
  `expiration` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_cs_0900_ai_ci;


CREATE TABLE `transactions` (
  `id` varchar(36) COLLATE utf8mb4_cs_0900_ai_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_cs_0900_ai_ci NOT NULL,
  `external_reference` varchar(36) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `transaction_code` varchar(10) COLLATE utf8mb4_cs_0900_ai_ci NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_cs_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_cs_0900_ai_ci;




CREATE TABLE `receipts` (
  `id` int NOT NULL,
  `provider_name` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `ic` varchar(50) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `vat_status` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `web` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `receipt_number` int DEFAULT NULL,
  `receipt_prefix` varchar(10) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `cashier` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `service_description` text COLLATE utf8mb4_cs_0900_ai_ci,
  `service_duration` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(50) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `payment_type` varchar(50) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `merchant_code` varchar(50) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `transaction_code` varchar(50) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `card_type` varchar(50) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `card_last_4_digits` varchar(4) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `product_summary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `external_reference` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_cs_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_cs_0900_ai_ci;
