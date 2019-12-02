

DROP TABLE IF EXISTS `user_group`;

CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



LOCK TABLES `user_group` WRITE;

INSERT INTO `user_group` VALUES (1,'Guest',1),(2,'Admin',1),(3,'User',1),(4,'Editor',1);

UNLOCK TABLES;

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `groupId` int(10) unsigned NOT NULL,
  `resourceId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`,`resourceId`),
  KEY `resource_fk` (`resourceId`),
  CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `user_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


LOCK TABLES `permissions` WRITE;

INSERT INTO `permissions` VALUES (1,32),(1,33),(1,34),(1,35),(1,36),(1,128),(1,129),(1,137),(1,141),(2,44),(2,45),(2,52),(2,59),(2,60),(2,61),(2,62),(2,63),(2,66),(2,131),(2,135),(2,136),(2,140),(3,5),(3,11),(3,27),(3,28),(3,29),(3,30),(3,31),(3,56),(3,57),(3,58),(3,65),(3,138),(3,139),(4,1),(4,3),(4,4),(4,6),(4,7),(4,9),(4,10),(4,11),(4,12),(4,13),(4,14),(4,15),(4,16),(4,17),(4,18),(4,19),(4,20),(4,21),(4,22),(4,38),(4,39),(4,41),(4,42),(4,43),(4,44),(4,45),(4,130),(4,132),(4,133),(4,134);

UNLOCK TABLES;


DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` char(60) NOT NULL,
  `mustChangePassword` char(1) DEFAULT NULL,
  `status` char(1) NOT NULL DEFAULT 'N',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;


LOCK TABLES `users` WRITE;

INSERT INTO `users` VALUES (1,'Michael Rynn','michael.rynn@parracan.org','$2y$08$TFNBUFJBQU5uYW8vM1l0b.xhw.7MUHleP20l3N3JKAfZVIGrbv/zi','0','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(2,'M. Rynn - Gmail','michael.rynn.500@gmail.com','$2a$08$lNnNV1D6E/qtyFURd1PvI.Ge422/waSI8n7PnsHPwSnFGYDWVOMk2','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(5,'John Wilson','uetssfw@hotmail.com','$2a$08$sTnkMRHcutJW29f9zzcDSeTndnmfRAjFCQYc9r0VjZoesqcquXo6i','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(10,'Annie Nielsen','anniphil@bigpond.com','$2y$08$bXh2Zjc0RHF0UEw1VWN5Me18rBObGwHLpAuAkMv69K5v.NAO4x76m','0','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(11,'M. D. Rynn','michaelrynn@optusnet.com.au','$2a$08$znr6dW.LnlIWwLv9UxEH4.Cbh/AmwUmnzXDCwQa3qGD.DrGl2oqc6','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(12,'Phil Bradley','phil_bradley@bigpond.com','$2a$08$8Fk/rGFCGV1pXQlv5ulnWOln4eMtVVTuMHKz41/OBimkXYtq8RwaS','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(13,'Zeny Entropy','zeny.entropy@parracan.org','$2a$08$KEW3t/39VKYTJZrym1QS8eiPp/4lf3lEOUOzfMOoRQ093.STuEEoq','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(14,'Gerald Crick','mrgcrick@optusnet.com.au','$2a$08$0La8vRQszwRpvWUyWbylQewCL7uoczRE7SSEZZXhNsXJEyy9OWFXK','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(15,'Marini Samaratunga','m.samaratunga@student.unsw.edu.au','$2a$08$TVB2IMEX3Bmi0WG.lg9vo.8z/dCYn.lro6YmZpfDjG.02pY4hY6BC','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(16,'harry stevens','hapstevens@yahoo.com.au','$2a$08$DvOf2PhkFxl91ixjflEsy.hOsFQPzVrn05qmDn6CMXlCuzi5gPbqm','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(17,'nailia','unimandala@gmail.com','$2a$08$FL5Cbvp6uoqoBwAnyIbEhOBzryiSjEr5wLp5SG8j./hMZBaiMGH/G','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(18,'Andrew Higgins','valuoils@gmail.com','$2a$08$N2a10dsVNlKYbv..p2LjXOl/7JniQYibMnnoQ2AnHwVsMtZTPvtf.','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(19,'Eileen Salisbury','esalisbury@yahoo.com','$2a$08$vVK6kWn1I3ZOrAa6ODhvX.6W3/sYOi6ra634YifsfdhNxJHSr1yEy','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(20,'Wies Schuiringa','wiesschuiringa@hotmail.com','facebook','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(21,'Richard Maguire','mail@unfoldingfutures.net','$2a$08$8Su54WFjYxJuuH6gnU5BleZ5w.U2mQgHLsfgs.TVg1pGYeca4r.O2','N','C','2016-05-26 22:19:05','2016-05-26 22:19:05'),(23,'Web Master','webmaster@parracan.org','$2a$08$Jkp3eVvWE2fVsaJv9pCW2uiN8tsRwZ7fUARVM.pUo0li7GuGisSny',NULL,'N','2016-05-26 23:06:32','2016-05-26 23:06:32'),(24,'K. Mckaskill','klojm84@gmail.com','$2a$08$JkfVjqq0P7yn84miaXKN8.cMbCecbTz5WDv7dA37rzs4gxrs.PgkK',NULL,'N','2016-06-01 11:35:00','2016-06-01 11:35:00'),(25,'Clare Hinchey','clarehi@yahoo.com.au','$2y$08$Qi9VZnlzbHd2VSt1cWkvOOro7vh2WCynU6h92/QOFczo10RUA2RQS',NULL,'N','2017-03-30 15:14:16','2017-03-30 15:14:16'),(27,'Nicole ','nicsleeman@gmail.com','$2y$08$U0xUeGh5alRHbHh6WlpYNOWpIvBshqWTMeKYfVC7kP//MfcofdMS6','0','N','2017-03-31 20:42:00','2017-03-31 20:42:00'),(29,'Tony Mohr','tmohr4@gmail.com','$2y$08$UUZDOVZDemt1akJjdHFJS.t0Xv131berZOZuUGYzOQQkhgOTPsyI2',NULL,'N','2017-04-10 21:00:35','2017-04-10 21:00:35'),(30,'Barbara Bryan','barbarajbryan@gmail.com','$2y$12$MWNFN25kSVNPb3dYRGJkcuSvZdfdIvc2nvANcfAiMO0LS.hS227z6',NULL,'N','2017-04-19 17:42:20','2017-04-19 17:42:20'),(31,'Shubhangi Singh','singh.shubhangisingh@gmail.com','$2y$12$OUdkZUowRE9Yb0hpVldRKuZsGvZSsS0D/j90DEAgDGAoKljlbmaj2',NULL,'N','2017-04-22 11:54:21','2017-04-22 11:54:21'),(32,'rahul kumar','rahul.88046@gmail.com','$2y$08$ajhwbmxkSDlVdUdaTGR6Te4zsJpMO39J4SIhPuio6AAAfQ67ACvkG',NULL,'N','2018-05-17 15:22:53','2018-05-17 15:22:53');

UNLOCK TABLES;


DROP TABLE IF EXISTS `user_auth`;

CREATE TABLE `user_auth` (
  `userId` int(10) unsigned NOT NULL,
  `groupId` int(10) unsigned NOT NULL DEFAULT '1',
  `status` varchar(4) NOT NULL DEFAULT 'OK',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`,`groupId`),
  KEY `userId` (`userId`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `user_auth_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `user_group` (`id`),
  CONSTRAINT `user_auth_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user_auth` WRITE;

INSERT INTO `user_auth` VALUES (1,2,'OK','2016-04-12 12:21:31','2016-04-12 12:21:31'),(1,3,'OK','2016-04-18 13:25:36','2016-04-18 13:25:36'),(1,4,'OK','2016-04-27 13:26:20','2016-04-27 13:26:20'),(10,2,'OK','2016-10-27 22:55:32','2016-10-27 22:55:32'),(10,3,'OK','2016-10-27 22:55:32','2016-10-27 22:55:32'),(10,4,'OK','2016-10-27 22:55:32','2016-10-27 22:55:32');

UNLOCK TABLES;

DROP TABLE IF EXISTS `user_event`;

CREATE TABLE `user_event` (
  `user_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_type` varchar(8) NOT NULL,
  `data` varchar(125) DEFAULT NULL,
  `status_ip` varchar(45) NOT NULL DEFAULT 'OK',
  PRIMARY KEY (`user_id`,`event_type`,`created_at`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_event_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



