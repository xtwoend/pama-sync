# How to run 

## Persiapan 

1. install docker 

install docker desktop terlebih dahulu, docker bisa di unduh di link berikut https://www.docker.com/
setelah installasi docker selesai, pastikan docker berjalan dengan baik, jika menggunakan windows pastikan wsl sudah terinstal

ref: https://learn.microsoft.com/id-id/windows/wsl/install-manual

2. rename .env-example menjadi .env
3. sesuaikan infromasi yang berada di .env dengan server sql yang tersedia

```
DB_DRIVER=sqlsrv
DB_HOST=192.168.10.14 // ip sql server atau komputer yang terinstall sql server
DB_PORT=1433 // port sql server (pastikan sql server berjalan baik dengan menggunakan autentikasi login)
DB_DATABASE=pama // database yang di tuju
DB_USERNAME=sa // user login
DB_PASSWORD= // password user login
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=
``

4. gunakan terminal untuk menbuild aplikasi yang berjalan didocker

```
D:\<path aplikasi dari github>/./run.bat

// pilih 1 untuk membuild

```

5. pengetesan bisa menggunakan postman

nb. jika ada error saat build pastikan wsl di window bejalan dan docker telah aktif dan pastikan juga untuk sql server bisa menggukan autorisasi ip dan username

