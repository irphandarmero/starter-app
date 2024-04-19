# Angular + PHP Starter Project

Repositori ini dibuat untuk mempermudah inisialisasi proyek, sehingga anda tidak perlu lagi menjalankan prosedur pembuatan proyek baru. Untuk menggunakan repositori ini di proyek lainnya, cukup jalankan perintah berikut 

```sh
mkdir direktori-proyek-baru-anda
cd direktori-proyek-baru-anda
git clone https://github.com/Yasmin-Hospital/starter.git .
```

Setelah repositori berhasil di kloning, langkah selanjutnya adalah menghapus histori git yang sudah ada dengan menjalankan perintah berikut :

```sh
rm -rf .git
```

Perintah diatas akan menghapus folder .git yang berisi histori commit dari repositori ini yang tidak lagi dibutuhkan di proyek baru anda. Selanjutnya, anda bisa menjalankan "Initialize Git Repository" di vscode, atau jalankan perintah berikut diterminal:

```sh
git init
```

Jalankan container dengan klik kanan file `compose.yaml` lalu pilih `Compose Up` atau jalankan perintah berikut di terminal :

```sh
docker compose -f compose.yaml up -d --build
```

Masuk ke shell dari container `(Attach Shell)`, jalankan perintah instalasi dependency :

```sh
npm install
cd api
composer install
```

Selesai, selanjutnya silahkan kreasikan sendiri sesuai kebutuhan aplikasi.

## Mengubah nama proyek hasil clone

Beberapa file yang perlu dirubah jika ingi mengubah nama proyek adalah sebagai berikut :

1. Properti name di file `compose.yaml`
2. Key pada properti `projects` di file `angular.json`

```
{
  ...
  "projects": {
    "nama-proyek-baru-anda": {
      "projectType": "application",
      ...
    }
  }
}
```

3. Properti name di file `api/composer.json`

## Menggunakan Custom Local Domain (Nginx Proxy Manager)

1. Buka notepad (dengan akses administrator), lalu edit file C:\Windows\System32\drivers\etc\hosts
2. Tambahkan domain custom anda di akhir baris

```
...

127.0.0.1 starter.local
::1 starter.local
```

3. Buka `compose.yaml`, pastikan service terdaftar ke network yang sama dengan Nginx Proxy Manager (dengan asumsi npm berada di network `environment`).

```
name: nama_proyek_anda
services:
  dev:
    ...
    networks:
      - environment

networks:
  environment:
    name: environment
    external: true
```

4. Tambahkan proxy host baru di Nginx Proxy Manager dengan konfigurasi sebagai berikut

```
Domain names: starter.local
Scheme: http
Forwared hostname/IP: nama_proyek_anda_container
Forwared port: 80
```

5. Jika sudah berhasil mengakses dengan domain starter.local, konfigurasi ports di compose.yaml menjadi opsional. Hapus jika diperlukan.

## Behind the scene

Repositori ini adalah hasil dari sekumpulan perintah dan prosedur yang dijalankan setiap akan membuat proyek baru. Berikut akan dijelaskan langkah - langkah detailnya.

### Kebutuhan Sistem

- WSL 2 (Windows Subsystem Linux)
- Docker (terinstall di dalam WSL)
- Git (Terinstall di dalam WSL)
- Visual Studio Code
  - Docker Extensions (https://marketplace.visualstudio.com/items?itemName=ms-azuretools.vscode-docker)

### 1. Setup Environment

```sh
mkdir starter
cd starter
code .
```

Lalu buat file compose.yaml dengan konten sebagai berikut :

```yaml
name: yasmin
services:
  dev:
    container_name: yasmin_container
    image: rsyasmin/dev:latest
    volumes:
      - ./:/workspace
    environment:
      - NG_INSTALL=YES
      - NG_VERSION=17
    ports:
      - 9001:80
```

Buat folder `www`, lalu buat file `index.html` dengan konten sebagai berikut :

```html
<html>
    <head>
        <title></title>
    </head>
    <body>
        <h1>It Works !</h1>
    </body>
</html>
```

Jalankan dengan klik kanan file `compose.yaml` dan tekan `compose up` atau dengan menjalankan perintah berikut:

```sh
compose -f compose.yaml up -d --build
```

Buka alamat `http://localhost:9001` di browser untuk melihat hasilnya.

### 2. Setup Frontend

Buka terminal dari container yang sudah dijalankan. Melalui vscode :

- Buka tab Docker di Primary Side bar
- Klik kanan container yang sesuai, lalu tekan `Attach Shell`

Jalankan perintah berikut untuk inisialisasi proyek angular :

```sh
ng new <nama-proyek-baru-anda> --no-standalone --directory . --style scss --skip-git
```

Note: Saat proses inisialisasi mungkin akan ditanyakan beberapa opsi, pilih sesuai kebutuhan proyek.

Selanjutnya, buka file `angular.json` rubah opsi `outputPath` sebagai berikut :

```json
{
    ...
    "projects": {
        ...
        "nama-proyek-baru-anda": {
            ...
            "architect": {
                "build": {
                    ...
                    "options": {
                        "outputPath": {
                            "base": "www",
                            "browser": ""
                        }
                    }
                }
            }
            ...
        }
        ...
    }
    ...
}
```

Selanjutnya jalankan perintah berikut :

```sh
ng build --watch
```

Selesai, jika anda refresh browser `http://localhost:9001`, maka akan muncul tampilan default dari Framework Angular.

### 3. Setup Backend

Buat folder `api` di dalam direktori root proyek, lalu buat file `vhost.conf` sejajar dengan `compose.yaml` dengan konten sebagai berikut :

```shtml
<Directory /workspace>
    Options FollowSymlinks Indexes
    AllowOverride all
    Require all granted
</Directory>

<VirtualHost *:80>
    DocumentRoot    /workspace/www
    Alias   /api    /workspace/api
</virtualHost>
```

Edit file `compose.yaml` untuk menyertakan file `vhost.conf` yang baru saja dibuat :

```yaml
...
services:
  dev:
    ...
    volumes:
      ...
      - ./vhost.conf:/etc/apache2/conf.d/vhost.conf
    ...
```

Klik kanan `compose.yaml` lalu tekan menu `Compose Restart`, tunggu hingga proses restart selesai. Maka saat ini folder `api` dapat diaakses dengan alamat `http://localhost:9001/api`. Selanjutnya, buat file `index.php` dengan konten sebagai berikut :

```php
<?php 

require 'vendor/autoload.php';

use Yasmin\Route;
Route::get('/', function () {
    return response('Hello World !');
});

Yasmin\Framework::run();
```

Masuk ke terminal dari container `(Attach Shell)`. Jalankan perintah berikut untuk inisialisasi composer dalam folder `api`

```sh
cd api
composer init
composer require yasmin/framework
```

Note: saat proses berjalan, mungkin akan ditanyakan beberapa opsi, pilih jawaban default saja

Edit file `composer.json` hasil dari perintah diatas, dengan konten sebagai berikut :

```json
{
    ...
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    ...
}
```