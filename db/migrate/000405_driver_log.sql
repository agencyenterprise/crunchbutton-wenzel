ALTER TABLE `driver_log` CHANGE `action` `action` ENUM('created-driver','created-cockpit','updated-cockpit','notified-setup','document-sent','account-setup', 'native-app-login', 'enabled-push', 'enabled-location') DEFAULT NULL;