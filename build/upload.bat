@echo off & setlocal enabledelayedexpansion

:: param 1 needs to be defined as ${selected_resource_path}
:: param 2 needs to be defined as ${resource_loc} 

echo %1 > out.txt
echo %2 >> out.txt

set targetdir=%~dp1%
set targetdir=%targetdir:~3,999%
set targetdir=%targetdir:\=/%

:: remove the project name from path
:: put it between the : and the =
set targetdir=%targetdir:libcompactmvc=%

:: in case there exists a subdirectory with the same name as
:: the project directory put that name between the last two slashes
set targetdir=%targetdir://=/libcompactmvc/%
echo %targetdir% >> out.txt


set targetdir=%targetdir:~1,999%

copy login.ftp __tmpupload.ftp
echo cd %targetdir% >> __tmpupload.ftp
echo ls >> __tmpupload.ftp
echo put %2 >> __tmpupload.ftp
echo quit >> __tmpupload.ftp

ftp -i -s:__tmpupload.ftp

del __tmpupload.ftp
del out.txt
