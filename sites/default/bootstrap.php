<?php
/**
 * @author Michael Rynn
 * 
 * Create Fake "all roles" UserSession for bootstrap development.
 * Remove or replace this code and file on a production public web-site
 */
$us = \WC\UserSession::instance();
$us->setValidUser('Test ID', ['Guest','Editor','Admin','User']);
$us->write();