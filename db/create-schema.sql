create database tvod2 default charset utf8 collate utf8_general_ci;
grant all privileges on tvod2.* to 'tvod2'@'localhost' identified by 'tvod2@123';
grant all privileges on tvod2.* to 'tvod2'@'%' identified by 'tvod2@123';
