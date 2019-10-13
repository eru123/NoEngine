# NoEngine
General Purpose PHP Libary

# Installation

### Via Composer
```bash
composer require eru123/noengine
```

# Usage

### Archive

```php
use NoEngine\RChive;

//Archive file and directory

//assuming you have a file named 'file.txt'
//'file.txt' can be a file or a directory
RChive::C('file.txt'); //creates an archive file in current directory named file.txt.ne
RChive::C('file.txt','archives/'); //creates an archive file in the archives directory named file.txt.ne



//Unarchive file and directory

//unarchiving the archive file
//*.ne is NoEngine Archive File Extension
RChive::D('archives/file.txt.ne') // unarchive file in current directory
RChive::D('archives/file.txt.ne','text files') // unarchive file in 'text files' directory

```
### File System
```php
use NoEngine\NoEngine;

//Scanning Directories
NoEngine::_scandir('dir'); //returns an array of files and folders (excluded '.' and '..')
NoEngine::_scandir3('dir'); //returns an array of files and folders and its sub sub directory recursively

//Create diretory
NoEngine::_mkdir('dirname'); //creates directory if not existed, handle error if exists

//Writing files
NoEngine::_fwrite("file.txt","Hello World!\n","w"); //Creates a file, Overrides the file if already exists
NoEngine::_fwrite("file.txt","Hello Another World!\n","a"); //Appends a file

//Deletes a file or diretory
NoEngine::_del('file.txt'); //deletes a file
NoEngine::_del('dirname'); //deletes a directory and its content recursively

//Get Extension of a file
NoEngine::get_ext('file.txt') //returns 'txt'

//Get filename of a file
NoEngine::get_ext('path/to/file.txt'); //returns 'file.txt';

//Get filename of a file without extension
NoEngine::get_ext('path/to/file.txt',NoEngine::get_ext('path/to/file.txt')); //returns 'file';

//File Search
NoEngine::index('txt','dir'); //returns an array of path of all text file in the directory
```
### Internet Protocol
```php
use NoEngine\NoEngine;

//Get My IP Address
NoEngine::get_ip(); //return your ip address

//validates an IP Address
NoEngine::validate_ip('127.0.0.1'); //returns true
```
### Database
```php
use NoEngine\FrecBase;

$db->new FrecBase;

//Default Account
//Username: root
//Password: root

//Create Account
$db->add_user('username','password'); //returns true if account is created

//Login Account
$db->con('username','password'); //returns true if username and password is correct

//NOTE: By Default Root Account is Automatically Login.

//Create database
$db->create('db','yourdatabase') // returns true if created

//Selecting Database
$db->db('yourdatabase'); 

//Delete Database
$db->delete('db'); //deletes the selected database

//Create Table
$db->create('tb','yourtable','column_1,column_2','primary_key','foreign_key_1,foreign_key_2'); //returns true if created

//Create Table without Foreign Keys
$db->create('tb','yourtable','column_1,column_2','primary_key'); //returns true if created

//NOTE: Columns and Foreign Keys Must be seperated by Comma ","

//select Table
$db->tb('yourtable');


//Rename Table
$db->update('tb','newtablename'); //rename the selected table returns true if new table name is not already exists
//NOTE: After renaming the selected table it will automatically selected

//Delete Table
$db->delete('tb'); //deletes the selected table

//NOTE: In naming Database,Tables,Columns,Foreign Keys and Primary Keys Must be a Word, Only AlphaNumeric Characters is Allowed, No Spaces

//Updating Column
$db->update('col','newCol1,newCol2,newCol3,newCol4'); //updates columns of selected table

//Create Rows
$db->create('data',['col1'=>'data here','col2'=>'data here']); //returns true if created

//Read All Rows
$db->read('row'); //returns an array of all rows
/**
* array(
*    [1] => array(
*      [id] => 1
*      [col1] = "data here"
*      [col2] = "data here"
*   )
*    [2] => array(
*      [id] => 1
*     [col1] = "data here"
*      [col2] = "data here"
*    )
*  )
**/

//Read Specific row
$db->read('row',['id'=>1]); //returns an array of row with an id of 1
/**
*  array(
*    [id] => 1
*    [col1] = "data here"
*    [col2] = "data here"
*  )
**/

//Delete Row
$db->delete('data',['id'=>1]); //deletes a row with an id of 1

//Delete Entire Row
$db->delete('row'); //deletes all rows in selected table

//Database Disk Size
$db->size(); //returns the current disk size of the database

//Optimize Database
$db->optimize(); //optimize database, decrease disk size. 

//NOTE: Optimize() must run periodically not regularly because it may cause slowdown your application

//Destroy Database
$db->destroy(); //Deletes the entire database including users account
```

### Cryptography
Base64
```php
use NoEngine\NoEngine;

/**
* NoEngine::se(text,level);
* NoEngine::sd(text,level);
* 
* text - plain text/encrypted text
* level - non zero integer, any positive integer, default value is 1, this will be used in decrypting
**/

//Encryting
NoEngine::se('Plain text',5); //returns "Vm14V2EwNUhSa2hTYkdoUFVqSlNjbFZxUmxwTlJuQkdVbFJzVVZWVU1Eaz0="

//Decrypting
NoEngine::sd('Vm14V2EwNUhSa2hTYkdoUFVqSlNjbFZxUmxwTlJuQkdVbFJzVVZWVU1Eaz0=',5); //returns "Plain text"

```
Binary
```php

use NoEngine\BloCrypt;

//Encrypt
BloCrypt::A('plain text','private key'); //returns a string in binary format

//Decrypt
BloCrypt::A('Encrypted Binary String', 'private key'); //returns a plain text string
```
NoEngine Encryption
```php
use NoEngine\BloCrypt;

//Encrypt
BloCrypt::E('Plain text','private key'); //returns "IB4IHw9UEUUTEQ=="

//Decrypt
BloCrypt::D('IB4IHw9UEUUTEQ==','private key'); //returns "Plain text"
```

