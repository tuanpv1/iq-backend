/*
Navicat MySQL Data Transfer

Source Server         : stage_tvod
Source Server Version : 50718
Source Host           : 10.3.0.82:3306
Source Database       : tvod2

Target Server Type    : MYSQL
Target Server Version : 50718
File Encoding         : 65001

Date: 2018-08-31 15:41:19
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `access_system`
-- ----------------------------
DROP TABLE IF EXISTS `access_system`;
CREATE TABLE `access_system` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`subscriber_id`  int(11) NULL DEFAULT NULL ,
`ip_address`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`user_agent`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`site_id`  int(11) NULL DEFAULT NULL ,
`access_date`  int(11) NOT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`updated_at`  int(11) NULL DEFAULT NULL ,
`action`  varchar(126) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`request_detail`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`request_params`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`),
FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
FOREIGN KEY (`subscriber_id`) REFERENCES `subscriber` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='Thống kê lượt truy cập vào hệ thống'
AUTO_INCREMENT=4301373

;

-- ----------------------------
-- Table structure for `auth_assignment`
-- ----------------------------
DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE `auth_assignment` (
`item_name`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`user_id`  int(11) NOT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`updated_at`  int(11) NULL DEFAULT NULL ,
PRIMARY KEY (`item_name`, `user_id`),
FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci

;

-- ----------------------------
-- Table structure for `auth_item`
-- ----------------------------
DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE `auth_item` (
`name`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`type`  int(11) NOT NULL ,
`description`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`rule_name`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`data`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`updated_at`  int(11) NULL DEFAULT NULL ,
`acc_type`  int(11) NULL DEFAULT NULL ,
PRIMARY KEY (`name`),
FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci

;

-- ----------------------------
-- Table structure for `auth_item_child`
-- ----------------------------
DROP TABLE IF EXISTS `auth_item_child`;
CREATE TABLE `auth_item_child` (
`parent`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`child`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
PRIMARY KEY (`parent`, `child`),
FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci

;

-- ----------------------------
-- Table structure for `auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule` (
`name`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`data`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`updated_at`  int(11) NULL DEFAULT NULL ,
PRIMARY KEY (`name`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci

;

-- ----------------------------
-- Table structure for `multilanguage`
-- ----------------------------
DROP TABLE IF EXISTS `multilanguage`;
CREATE TABLE `multilanguage` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`name`  varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`code`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Ma code' ,
`updated_at`  int(11) NULL DEFAULT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`description`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`file_be`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`file_box`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`image`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`status`  int(11) NULL DEFAULT NULL ,
`is_default`  int(11) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='Bảng muti language'
AUTO_INCREMENT=5

;

-- ----------------------------
-- Table structure for `subscriber`
-- ----------------------------
DROP TABLE IF EXISTS `subscriber`;
CREATE TABLE `subscriber` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`site_id`  int(10) NOT NULL ,
`dealer_id`  int(11) NULL DEFAULT NULL ,
`msisdn`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`authen_type`  smallint(6) NOT NULL DEFAULT 1 COMMENT '1 - username(sdt)/pass\n2 - auto MAC login' ,
`channel`  int(11) NULL DEFAULT 7 ,
`username`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ban dau de mac dinh la so dien thoai' ,
`machine_name`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`balance`  int(11) NOT NULL DEFAULT 0 COMMENT 'so du tien ao' ,
`status`  int(11) NOT NULL DEFAULT 1 COMMENT '10 - active' ,
`email`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`full_name`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`auth_key`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`password_hash`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`last_login_at`  int(11) NULL DEFAULT NULL ,
`last_login_session`  int(11) NULL DEFAULT NULL ,
`birthday`  int(11) NULL DEFAULT NULL ,
`sex`  tinyint(1) NULL DEFAULT NULL COMMENT '1 - male, 0 - female' ,
`avatar_url`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`skype_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`google_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`facebook_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`updated_at`  int(11) NULL DEFAULT NULL ,
`client_type`  int(11) NULL DEFAULT NULL COMMENT '1 - wap, \n2 - android, \n3 - iOS\n4 - wp' ,
`using_promotion`  int(11) NULL DEFAULT 0 ,
`auto_renew`  tinyint(1) NULL DEFAULT 1 ,
`verification_code`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`user_agent`  varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`expired_at`  int(11) NULL DEFAULT NULL ,
`address`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`city`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`otp_code`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`expired_code_time`  int(11) NULL DEFAULT NULL ,
`number_otp`  int(11) NULL DEFAULT 3 ,
`whitelist`  int(11) NULL DEFAULT NULL ,
`register_at`  int(11) NULL DEFAULT NULL ,
`is_active`  int(11) NULL DEFAULT NULL ,
`ip_address`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`itvod_type`  int(11) NULL DEFAULT NULL ,
`pass_code`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`number_pass_code`  int(11) NULL DEFAULT NULL ,
`expired_pass_code`  int(11) NULL DEFAULT NULL ,
`ip_to_location`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`province_code`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`type`  int(11) NOT NULL DEFAULT 1 ,
`initialized_at`  int(11) NOT NULL DEFAULT 0 ,
`service_initialized`  int(11) NOT NULL DEFAULT 0 ,
`phone_number`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`ip_location_first`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`),
FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=46823

;

-- ----------------------------
-- Table structure for `subscriber_token`
-- ----------------------------
DROP TABLE IF EXISTS `subscriber_token`;
CREATE TABLE `subscriber_token` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`subscriber_id`  int(11) NOT NULL ,
`package_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`msisdn`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`token`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`type`  smallint(6) NOT NULL DEFAULT 1 COMMENT '1 - wifi password\n2 - access token\n' ,
`ip_address`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`created_at`  int(11) NULL DEFAULT NULL ,
`expired_at`  int(11) NULL DEFAULT NULL ,
`cookies`  varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`status`  int(11) NOT NULL DEFAULT 1 ,
`channel`  smallint(6) NULL DEFAULT NULL ,
`device_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`device_model`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`device_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`),
FOREIGN KEY (`subscriber_id`) REFERENCES `subscriber` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='wifi password hoac access token khi dang nhap vao client'
AUTO_INCREMENT=95021

;

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`username`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`auth_key`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`password_hash`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`password_reset_token`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`email`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`role`  smallint(6) NOT NULL DEFAULT 10 ,
`status`  smallint(6) NOT NULL DEFAULT 10 ,
`created_at`  int(11) NULL DEFAULT NULL ,
`updated_at`  int(11) NULL DEFAULT NULL ,
`type`  smallint(6) NOT NULL DEFAULT 1 COMMENT '1 - Admin\n2 - SP\n3 - dealer' ,
`site_id`  int(10) NULL DEFAULT NULL ,
`dealer_id`  int(10) NULL DEFAULT NULL ,
`parent_id`  int(11) NULL DEFAULT NULL COMMENT 'ID cua accout me' ,
`fullname`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`user_ref_id`  int(11) NULL DEFAULT NULL ,
`access_login_token`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`phone_number`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`cp_id`  int(11) NULL DEFAULT NULL ,
`is_admin_cp`  int(11) NULL DEFAULT NULL ,
PRIMARY KEY (`id`),
FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
FOREIGN KEY (`parent_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='quan ly cac site (tvod viet nam, tvod nga, tvod sec...)'
AUTO_INCREMENT=79

;

-- ----------------------------
-- Table structure for `user_activity`
-- ----------------------------
DROP TABLE IF EXISTS `user_activity`;
CREATE TABLE `user_activity` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) NOT NULL ,
`username`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`ip_address`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`user_agent`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`action`  varchar(126) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`target_id`  int(11) NULL DEFAULT NULL COMMENT 'id cua doi tuong tac dong\n(phim, user...)' ,
`target_type`  smallint(6) NULL DEFAULT NULL COMMENT '1 - user\n2 - cat\n3 - content\n4 - subscriber\n5 - ...' ,
`created_at`  int(11) NULL DEFAULT NULL ,
`description`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`site_id`  int(10) NULL DEFAULT NULL ,
`dealer_id`  int(10) NULL DEFAULT NULL ,
`request_detail`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`request_params`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`),
FOREIGN KEY (`dealer_id`) REFERENCES `dealer` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=234546

;

-- ----------------------------
-- Indexes structure for table `access_system`
-- ----------------------------
CREATE INDEX `fk_access_system_access_date_idx` ON `access_system`(`access_date`) USING BTREE ;
CREATE INDEX `fk_access_system_subscriber_id_idx` ON `access_system`(`subscriber_id`) USING BTREE ;
CREATE INDEX `fk_access_system_site_id_idx` ON `access_system`(`site_id`) USING BTREE ;

-- ----------------------------
-- Auto increment value for `access_system`
-- ----------------------------
ALTER TABLE `access_system` AUTO_INCREMENT=4301373;

-- ----------------------------
-- Indexes structure for table `auth_assignment`
-- ----------------------------
CREATE INDEX `fk_auth_assignment_user1_idx` ON `auth_assignment`(`user_id`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table `auth_item`
-- ----------------------------
CREATE INDEX `rule_name` ON `auth_item`(`rule_name`) USING BTREE ;
CREATE INDEX `idx-auth_item-type` ON `auth_item`(`type`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table `auth_item_child`
-- ----------------------------
CREATE INDEX `child` ON `auth_item_child`(`child`) USING BTREE ;

-- ----------------------------
-- Auto increment value for `multilanguage`
-- ----------------------------
ALTER TABLE `multilanguage` AUTO_INCREMENT=5;

-- ----------------------------
-- Indexes structure for table `subscriber`
-- ----------------------------
CREATE UNIQUE INDEX `username_UNIQUE` ON `subscriber`(`username`) USING BTREE ;
CREATE INDEX `fk_subscriber_subscriber_session1_idx` ON `subscriber`(`last_login_session`) USING BTREE ;
CREATE INDEX `email` ON `subscriber`(`email`) USING BTREE ;
CREATE INDEX `fk_subscriber_service_provider1_idx` ON `subscriber`(`site_id`) USING BTREE ;
CREATE INDEX `idx_msisdn` ON `subscriber`(`msisdn`) USING BTREE ;
CREATE INDEX `fk_subscriber_dealer_idx` ON `subscriber`(`dealer_id`) USING BTREE ;
CREATE INDEX `idx_subscriber_machine_name` ON `subscriber`(`machine_name`) USING BTREE ;
CREATE INDEX `idx_subscriber_authen_type` ON `subscriber`(`authen_type`) USING BTREE ;
CREATE INDEX `idx_subscriber_status` ON `subscriber`(`status`) USING BTREE ;
CREATE INDEX `idx_subscriber_register_at` ON `subscriber`(`register_at`) USING BTREE ;
CREATE INDEX `idx_subscriber_updated_at` ON `subscriber`(`updated_at`) USING BTREE ;
CREATE INDEX `idx_subscriber_ip_to_location` ON `subscriber`(`ip_to_location`) USING BTREE ;
CREATE INDEX `idx_subscriber_ip_address` ON `subscriber`(`ip_address`) USING BTREE ;

-- ----------------------------
-- Auto increment value for `subscriber`
-- ----------------------------
ALTER TABLE `subscriber` AUTO_INCREMENT=46823;

-- ----------------------------
-- Indexes structure for table `subscriber_token`
-- ----------------------------
CREATE INDEX `fk_subscriber_session_subscriber1` ON `subscriber_token`(`subscriber_id`) USING BTREE ;
CREATE INDEX `idx_session_id` ON `subscriber_token`(`token`) USING BTREE ;
CREATE INDEX `idx_is_active` ON `subscriber_token`(`status`) USING BTREE ;
CREATE INDEX `idx_create_time` ON `subscriber_token`(`created_at`) USING BTREE ;
CREATE INDEX `idx_expire_time` ON `subscriber_token`(`expired_at`) USING BTREE ;

-- ----------------------------
-- Auto increment value for `subscriber_token`
-- ----------------------------
ALTER TABLE `subscriber_token` AUTO_INCREMENT=95021;

-- ----------------------------
-- Indexes structure for table `user`
-- ----------------------------
CREATE INDEX `fk_user_service_provider1_idx` ON `user`(`site_id`) USING BTREE ;
CREATE INDEX `fk_user_content_provider1_idx` ON `user`(`dealer_id`) USING BTREE ;
CREATE INDEX `fk_user_user1_idx` ON `user`(`parent_id`) USING BTREE ;

-- ----------------------------
-- Auto increment value for `user`
-- ----------------------------
ALTER TABLE `user` AUTO_INCREMENT=79;

-- ----------------------------
-- Indexes structure for table `user_activity`
-- ----------------------------
CREATE INDEX `fk_user_activity_user1_idx` ON `user_activity`(`user_id`) USING BTREE ;
CREATE INDEX `fk_user_activity_service_provider1_idx` ON `user_activity`(`site_id`) USING BTREE ;
CREATE INDEX `fk_user_activity_content_provider1_idx` ON `user_activity`(`dealer_id`) USING BTREE ;

-- ----------------------------
-- Auto increment value for `user_activity`
-- ----------------------------
ALTER TABLE `user_activity` AUTO_INCREMENT=234546;
