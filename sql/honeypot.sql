CREATE TABLE `typecho_honeypot_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `data_packet` text DEFAULT NULL,
  `client_ip` text DEFAULT NULL,
  `server_ip` text DEFAULT NULL,
  `server_port` text DEFAULT NULL,
  `url` text DEFAULT NULL,
  `post_data` text DEFAULT NULL,
  `get_data` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `platformaccount` text DEFAULT NULL,
  `vulnerability` text DEFAULT NULL,
  `time` int(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
