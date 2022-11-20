@ECHO OFF
:BEGIN
CLS

set APP_NAME=pama-app
set VERSION=1.1

set PORT=9501
set DIST_PORT=9501

CHOICE /N /C:12 /M "type 1 for build & run or type 2 for run cli"%1
IF ERRORLEVEL ==2 GOTO cli
IF ERRORLEVEL ==1 GOTO build
GOTO END
:cli
docker exec -it %APP_NAME% sh
GOTO END
:build
docker build --no-cache -t %APP_NAME% .
docker run -d --rm --env-file ./.env -p %PORT%:%DIST_PORT% --name="%APP_NAME%" %APP_NAME%
:END
pause