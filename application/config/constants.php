<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| Folders
|--------------------------------------------------------------------------
|
*/
define('CSS_FOLDER', 'css/');
define('JS_FOLDER', 'js/');

/*
|--------------------------------------------------------------------------
| Status and error variables constants
|--------------------------------------------------------------------------
|
*/
define('STATUS_MESSAGE', 'StatusMsg');
define('ERROR_MESSAGE', 'ErrorMsg');
define('RET', 'ret');


/*
|--------------------------------------------------------------------------
| EROR MESSAGES
|--------------------------------------------------------------------------
|
| This is a list of error messages given by API
*/

/*
 * GENEREAL ERRORS
 */
define('NO_PARM', 'Requered paramaeters not given!');

/* 
 * SPACE ERRORS
 */
define('SPACE_NAME_NOT_GIVEN', "Space name was not given!");
define('SPACE_NOT_CREATED', "The space could not be created!");
define('NO_SPACEID', "The space id could not be retriven");
define('NO_HASH', 'There was no space hash given!');
define('SPACE_NOT_EXIST', 'The space does not exist!');
define('SPACE_ALREADY_EXISTS', 'The name given is already taken!');

/*
 * USER ERRORS
 */
define('USER_NOT_CREATED', "The user could not be created!");
define('USERNAME_NOT_GIVEN', 'The username was not given!');
define('USER_NOT_EXIST', 'The user does not exist!');

/* 
 * AREA ERRORS
 */
define('NO_SPACE_OR_NAME_GIVEN', 'The spaceID or area name was given');
define('AREA_NOT_CEREATED', 'The area could not be created!');
define('AREA_ID_NOT_GIVEN', 'The area ID, was not given!');
define('AREA_NOT_EXIST', 'The area does not exist!');
define('NO_ACCESS', 'You do not have access to this area!');

/*
 * MESSAGE ERRORS
 */
define('NO_MESSAGES', 'There are no messages on this board');
define('MESSAGE_NOT_EXIST', 'The message does not exist!');

/*
 * DB ERRORS
 */
define('DB_ERROR', 'An error happened, when executing dbaction!');

/*
|--------------------------------------------------------------------------
| STATUS MESSAGES
|--------------------------------------------------------------------------
|
| A lisst of status massages given by API
*/

/*
 * SPACE STATUSES
 */
define('SPACE_CREATED', 'Space was created!');
define('SPACE_DELETED', 'Space was deleted succesfully!');
define('SPACE_JOINED', 'Space joined');
define('SPACE_EXIT', 'Youve succesfully leaved the space');

/*
 * USER METHODS
 */
define('USERNAME_SET', 'The username was sat');
define('USERNAME_LOG_OUT', 'The user was logged out');

/*
 * AREA SATUS
 */
define('AREA_CREATED', 'Area new area was created');
define('AREA_DELETED', 'Area was deleted succesfully!');
define('AREA_UPDATED', 'Area was updated with new items');

/*
 * MESSAGE STATUS
 */
define('MESSAGE_SUCCESSFULLE_EDITED', 'The message was successfully edited');
define('MESSAGE_SUCCESSFULLY_CREATED', 'The message was successfully created');
define('MESSAGE_SUCCESSFULLY_DELETED', 'The message was successfully deæeted');

/*
|--------------------------------------------------------------------------
| STATUS NUMBERS
|--------------------------------------------------------------------------
|
| A list status numbers given in the STATUS colum of messages
*/

define('STATUS_UPDATED', 1); //Something new had happened to the colum
define('STATUS_CHECKKED', 0); //The culum had been chekked

define('STATUS_NOTHING_NEW', 0); //Nothing new has happened
define('STATUS_NEW_MSG', 200); //A new message has been created
define('STATUS_EDIT_MSG', 300); //Message had been edited
define('STATUS_DELETE', 666); //Message had been sheduled to deletion


/* End of file constants.php */
/* Location: ./application/config/constants.php */